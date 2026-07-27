<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/EventBus.php';
require_once __DIR__ . '/SyncEngine.php';
require_once __DIR__ . '/AutomationEngine.php';
require_once __DIR__ . '/integrations/Providers/GoogleBaseProvider.php';
require_once __DIR__ . '/integrations/Providers/SlackBaseProvider.php';

class IntegrationManager
{
    protected $CI;
    protected $providers = [];
    protected $eventBus;
    protected $syncEngine;
    protected $automationEngine;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('deals/deals_integration_model', 'deals_integration_model', true);
        $this->eventBus = new EventBus();
        $this->syncEngine = new SyncEngine();
        $this->automationEngine = new AutomationEngine();

        $this->registerProvider(new GoogleBaseProvider());
        $this->registerProvider(new SlackBaseProvider());
        $this->bootstrapDefinitions();
        $this->bootstrapListeners();
    }

    public function registerProvider(ProviderDriverInterface $provider)
    {
        $this->providers[$provider->getKey()] = $provider;
    }

    public function getProvider($providerKey)
    {
        return $this->providers[$providerKey] ?? null;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function getDashboardData($runSmokeChecks = false)
    {
        $integrations = $this->CI->deals_integration_model->get_integrations();
        $accounts = $this->CI->deals_integration_model->get_accounts();
        $events = $this->CI->deals_integration_model->get_recent_events(50);
        $logs = $this->CI->deals_integration_model->get_recent_logs(50);
        $syncStates = $this->CI->deals_integration_model->get_sync_states();

        return [
            'integrations' => $integrations,
            'provider_registry' => $this->describeProviders(),
            'accounts' => $accounts,
            'events' => $events,
            'logs' => $logs,
            'sync_states' => $syncStates,
            'summary' => $this->CI->deals_integration_model->get_dashboard_summary(),
            'smoke_tests' => $runSmokeChecks ? $this->runSmokeChecks() : [],
        ];
    }

    public function saveAccount(array $data, $id = null)
    {
        $provider = $this->getProvider($data['provider_key'] ?? '');
        if (!$provider) {
            return false;
        }

        $existing = $id ? $this->CI->deals_integration_model->get_account($id) : null;
        $scopes = array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', (string) ($data['scopes_text'] ?? '')))));
        $status = !empty($data['is_active'])
            ? (!empty($data['access_token']) || !empty($existing['access_token_encrypted']) ? 'connected' : 'pending_auth')
            : 'disconnected';

        $accountId = $this->CI->deals_integration_model->save_account([
            'provider_key' => $provider->getKey(),
            'account_label' => $data['account_label'] ?? $provider->getName() . ' Account',
            'external_account_id' => $data['external_account_id'] ?? '',
            'connection_status' => $status,
            'scopes' => !empty($scopes) ? $scopes : ($provider->getDefinition()['config']['oauth_scopes'] ?? []),
            'access_token' => $data['access_token'] ?? null,
            'refresh_token' => $data['refresh_token'] ?? null,
            'token_expires_at' => $data['token_expires_at'] ?? null,
            'oauth_state' => $data['oauth_state'] ?? null,
            'account_meta' => [
                'client_id' => $data['client_id'] ?? '',
                'client_secret' => $data['client_secret'] ?? '',
                'notes' => $data['notes'] ?? '',
            ],
            'is_active' => !empty($data['is_active']),
        ], $id);

        if ($accountId) {
            $account = $this->CI->deals_integration_model->get_account($accountId);
            $this->CI->deals_integration_model->log_message([
                'integration_id' => $account['integration_id'] ?? null,
                'account_id' => $accountId,
                'level' => 'info',
                'action' => $id ? 'account.updated' : 'account.connected',
                'message' => $id ? 'Integration account updated.' : 'Integration account connected.',
                'context' => [
                    'provider_key' => $provider->getKey(),
                    'authorization_url' => $provider->buildAuthorizationUrl($account),
                ],
            ]);
        }

        return $accountId;
    }

    public function beginAuthorization($accountId)
    {
        $account = $this->CI->deals_integration_model->get_account($accountId);
        if (!$account) {
            return ['success' => false, 'message' => 'Integration account was not found.'];
        }

        $provider = $this->getProvider($account['provider_key'] ?? '');
        if (!$provider) {
            return ['success' => false, 'message' => 'Provider is not registered.'];
        }

        $meta = json_decode($account['account_meta_json'] ?? '[]', true) ?: [];
        if (empty($meta['client_id']) || empty($meta['client_secret'])) {
            return ['success' => false, 'message' => 'Client ID and Client Secret are required before OAuth can start.'];
        }

        $newState = bin2hex(random_bytes(24));
        $this->CI->deals_integration_model->save_account([
            'provider_key' => $provider->getKey(),
            'account_label' => $account['account_label'],
            'external_account_id' => $account['external_account_id'],
            'connection_status' => 'pending_auth',
            'scopes' => json_decode($account['scopes_json'] ?? '[]', true) ?: ($provider->getDefinition()['config']['oauth_scopes'] ?? []),
            'token_expires_at' => $account['token_expires_at'] ?? null,
            'oauth_state' => $newState,
            'account_meta' => [
                'client_id' => $meta['client_id'] ?? '',
                'client_secret' => $meta['client_secret'] ?? '',
                'notes' => $meta['notes'] ?? '',
            ],
            'is_active' => !empty($account['is_active']),
        ], $accountId);

        $account = $this->CI->deals_integration_model->get_account($accountId);
        $authorizationUrl = $provider->buildAuthorizationUrl($account);
        if (empty($authorizationUrl)) {
            return ['success' => false, 'message' => 'Authorization URL could not be generated for this provider.'];
        }

        $this->CI->deals_integration_model->log_message([
            'integration_id' => $account['integration_id'] ?? null,
            'account_id' => $accountId,
            'level' => 'info',
            'action' => 'oauth.authorization_started',
            'message' => 'Provider authorization flow started.',
            'context' => [
                'provider_key' => $provider->getKey(),
                'authorization_url' => $authorizationUrl,
            ],
        ]);

        return [
            'success' => true,
            'account_id' => $accountId,
            'authorization_url' => $authorizationUrl,
        ];
    }

    public function handleAuthorizationCallback($providerKey, $state, $code, $error = null, $errorDescription = null)
    {
        $provider = $this->getProvider($providerKey);
        if (!$provider) {
            return ['success' => false, 'message' => 'Provider is not registered.'];
        }

        if ($state === '') {
            return ['success' => false, 'message' => 'OAuth state is missing.'];
        }

        $account = $this->CI->deals_integration_model->get_account_by_state($providerKey, $state);
        if (!$account) {
            return ['success' => false, 'message' => 'No pending integration account matches this OAuth state.'];
        }

        if (!empty($error)) {
            $message = $errorDescription ?: $error;
            $this->CI->deals_integration_model->log_message([
                'integration_id' => $account['integration_id'] ?? null,
                'account_id' => $account['id'],
                'level' => 'error',
                'action' => 'oauth.authorization_failed',
                'message' => 'Provider authorization failed: ' . $message,
                'context' => ['provider_key' => $providerKey],
            ]);

            return [
                'success' => false,
                'account_id' => $account['id'],
                'message' => 'Provider authorization failed: ' . $message,
            ];
        }

        if ($code === '') {
            return [
                'success' => false,
                'account_id' => $account['id'],
                'message' => 'Authorization code is missing.',
            ];
        }

        $redirectUri = site_url('deals/platform/oauth/' . $providerKey);
        $exchange = $provider->exchangeAuthorizationCode($this->hydrateAccountSecrets($account), $code, $redirectUri);
        if (empty($exchange['success']) || empty($exchange['access_token'])) {
            $this->CI->deals_integration_model->log_message([
                'integration_id' => $account['integration_id'] ?? null,
                'account_id' => $account['id'],
                'level' => 'error',
                'action' => 'oauth.exchange_failed',
                'message' => $exchange['message'] ?? 'OAuth token exchange failed.',
                'context' => $exchange['context'] ?? [],
            ]);

            return [
                'success' => false,
                'account_id' => $account['id'],
                'message' => $exchange['message'] ?? 'OAuth token exchange failed.',
            ];
        }

        $hydratedAccount = $this->hydrateAccountSecrets($account);
        $hydratedAccount['access_token'] = $exchange['access_token'];
        $hydratedAccount['refresh_token'] = $exchange['refresh_token'] ?? null;
        $profile = $provider->fetchAccountProfile($hydratedAccount);

        $accountMeta = json_decode($account['account_meta_json'] ?? '[]', true) ?: [];
        $accountMeta['oauth_last_connected_at'] = date('Y-m-d H:i:s');
        if (!empty($profile['profile'])) {
            $accountMeta['provider_profile'] = $profile['profile'];
        }
        if (!empty($exchange['raw'])) {
            $accountMeta['last_token_payload'] = $exchange['raw'];
        }

        $scopes = !empty($exchange['scopes']) ? $exchange['scopes'] : (json_decode($account['scopes_json'] ?? '[]', true) ?: []);
        $accountLabel = $account['account_label'];
        if (($accountLabel === '' || stripos($accountLabel, 'workspace') !== false || stripos($accountLabel, 'account') !== false) && !empty($profile['account_label'])) {
            $accountLabel = $profile['account_label'];
        }

        $accountId = $this->CI->deals_integration_model->save_account([
            'provider_key' => $providerKey,
            'account_label' => $accountLabel,
            'external_account_id' => $profile['external_account_id'] ?? $account['external_account_id'],
            'connection_status' => 'connected',
            'scopes' => $scopes,
            'access_token' => $exchange['access_token'],
            'refresh_token' => $exchange['refresh_token'] ?? null,
            'token_expires_at' => $exchange['token_expires_at'] ?? null,
            'oauth_state' => bin2hex(random_bytes(24)),
            'account_meta' => $accountMeta,
            'is_active' => 1,
        ], $account['id']);

        foreach ($provider->getSupportedResources() as $resourceType) {
            $this->CI->deals_integration_model->save_sync_state($providerKey, $accountId, $resourceType, [
                'sync_status' => 'ready',
                'last_synced_at' => null,
                'state' => [
                    'connected_via_oauth' => true,
                ],
            ]);
        }

        $connectedAccount = $this->CI->deals_integration_model->get_account($accountId);
        $this->CI->deals_integration_model->log_message([
            'integration_id' => $connectedAccount['integration_id'] ?? null,
            'account_id' => $accountId,
            'level' => 'info',
            'action' => 'oauth.authorization_completed',
            'message' => 'Provider account connected successfully.',
            'context' => [
                'provider_key' => $providerKey,
                'external_account_id' => $profile['external_account_id'] ?? null,
            ],
        ]);

        return [
            'success' => true,
            'account_id' => $accountId,
            'message' => 'Provider account connected successfully.',
        ];
    }

    public function disconnectAccount($id)
    {
        $account = $this->CI->deals_integration_model->get_account($id);
        $success = $this->CI->deals_integration_model->disconnect_account($id);
        if ($success && $account) {
            $this->CI->deals_integration_model->log_message([
                'integration_id' => $account['integration_id'] ?? null,
                'account_id' => $id,
                'level' => 'warning',
                'action' => 'account.disconnected',
                'message' => 'Integration account disconnected.',
                'context' => ['provider_key' => $account['provider_key'] ?? null],
            ]);
        }

        return $success;
    }

    public function emitEvent($eventName, array $payload = [], array $options = [])
    {
        return $this->eventBus->emit($eventName, $payload, $options);
    }

    public function processQueue($limit = 50)
    {
        return $this->eventBus->process($limit);
    }

    public function handleWebhook($providerKey, $accountId, array $payload, array $context = [])
    {
        $provider = $this->getProvider($providerKey);
        if (!$provider) {
            return ['success' => false, 'message' => 'Provider is not registered.'];
        }

        $account = $this->CI->deals_integration_model->get_account($accountId);
        if (!$account || ($account['provider_key'] ?? '') !== $providerKey) {
            return ['success' => false, 'message' => 'Integration account not found.'];
        }

        if (!$provider->validateWebhookSignature($account, $payload, $context)) {
            $this->CI->deals_integration_model->log_message([
                'integration_id' => $account['integration_id'] ?? null,
                'account_id' => $accountId,
                'level' => 'error',
                'action' => 'webhook.signature_failed',
                'message' => 'Webhook signature validation failed.',
                'context' => ['provider_key' => $providerKey],
            ]);
            return ['success' => false, 'message' => 'Webhook signature is invalid.'];
        }

        $normalized = $provider->normalizeWebhookEvent($account, $payload, $context);
        $this->CI->deals_integration_model->touch_account_webhook($accountId);
        $this->CI->deals_integration_model->save_sync_state($providerKey, $accountId, $normalized['resource_type'] ?? 'webhook', [
            'cursor_token' => $normalized['cursor_token'] ?? null,
            'external_channel_id' => $normalized['external_channel_id'] ?? null,
            'last_webhook_at' => date('Y-m-d H:i:s'),
            'sync_status' => 'webhook_received',
            'state' => [
                'last_payload_keys' => array_keys($payload),
            ],
        ]);

        $eventId = $this->emitEvent($normalized['event_name'] ?? 'integration.webhook.received', array_merge(
            $normalized['payload'] ?? [],
            ['provider_key' => $providerKey, 'account_id' => $accountId]
        ), [
            'integration_id' => $account['integration_id'] ?? null,
            'account_id' => $accountId,
            'deal_id' => $payload['deal_id'] ?? null,
            'source' => 'webhook:' . $providerKey,
            'direction' => 'inbound',
            'queue_name' => 'webhooks',
        ]);

        $this->CI->deals_integration_model->log_message([
            'integration_id' => $account['integration_id'] ?? null,
            'account_id' => $accountId,
            'event_id' => $eventId ?: null,
            'level' => 'info',
            'action' => 'webhook.received',
            'message' => 'Webhook accepted and queued.',
            'context' => [
                'provider_key' => $providerKey,
                'event_name' => $normalized['event_name'] ?? 'integration.webhook.received',
            ],
        ]);

        return [
            'success' => true,
            'message' => 'Webhook accepted.',
            'event_name' => $normalized['event_name'] ?? 'integration.webhook.received',
            'event_id' => $eventId,
        ];
    }

    public function runSmokeChecks()
    {
        $results = [];

        $providers = $this->describeProviders();
        $results[] = [
            'name' => 'provider_registration',
            'status' => count($providers) >= 2 ? 'success' : 'failed',
            'message' => count($providers) >= 2 ? 'Google and Slack providers are registered.' : 'Provider registration is incomplete.',
        ];

        $encrypted = $this->CI->deals_integration_model->encrypt_value('integration-smoke-token');
        $decrypted = $this->CI->deals_integration_model->decrypt_value($encrypted);
        $results[] = [
            'name' => 'token_encryption',
            'status' => $decrypted === 'integration-smoke-token' ? 'success' : 'failed',
            'message' => $decrypted === 'integration-smoke-token' ? 'Token encryption round-trip passed.' : 'Token encryption round-trip failed.',
        ];

        $eventId = $this->emitEvent('integration.smoke', ['smoke' => true], ['source' => 'smoke_test', 'queue_name' => 'smoke']);
        $processed = $this->processQueue(10);
        $results[] = [
            'name' => 'event_dispatch',
            'status' => !empty($eventId) && ($processed['processed'] ?? 0) > 0 ? 'success' : 'failed',
            'message' => !empty($eventId) && ($processed['processed'] ?? 0) > 0 ? 'Event bus dispatch + process passed.' : 'Event bus dispatch + process failed.',
        ];

        return $results;
    }

    protected function describeProviders()
    {
        $descriptions = [];
        foreach ($this->providers as $provider) {
            $definition = $provider->getDefinition();
            $integration = $this->CI->deals_integration_model->get_integration_by_key($provider->getKey());
            $descriptions[] = [
                'provider_key' => $provider->getKey(),
                'name' => $provider->getName(),
                'resources' => $provider->getSupportedResources(),
                'supports_oauth' => !empty($definition['supports_oauth']),
                'supports_webhooks' => !empty($definition['supports_webhooks']),
                'supports_sync' => !empty($definition['supports_sync']),
                'status' => $integration['status'] ?? 'active',
                'driver_class' => $definition['driver_class'],
            ];
        }

        return $descriptions;
    }

    protected function bootstrapDefinitions()
    {
        $definitions = [];
        foreach ($this->providers as $provider) {
            $definitions[] = $provider->getDefinition();
        }
        $this->CI->deals_integration_model->ensure_default_integrations($definitions);
    }

    protected function bootstrapListeners()
    {
        $this->eventBus->registerListener('deal.*', function ($event, $payload) {
            $this->automationEngine->handleBusinessEvent($event, $payload);
        });
        $this->eventBus->registerListener('email.received', function ($event, $payload) {
            $this->automationEngine->handleBusinessEvent($event, $payload);
        });
        $this->eventBus->registerListener('meeting.created', function ($event, $payload) {
            $this->automationEngine->handleBusinessEvent($event, $payload);
        });
        $this->eventBus->registerListener('file.uploaded', function ($event, $payload) {
            $this->automationEngine->handleBusinessEvent($event, $payload);
        });
        $this->eventBus->registerListener('sync.requested', function ($event, $payload) {
            $provider = $this->getProvider($payload['provider_key'] ?? '');
            if (!$provider) {
                throw new RuntimeException('Sync provider is not registered.');
            }

            $this->syncEngine->handleSyncRequest($event, $payload, $provider);
        });
    }

    protected function hydrateAccountSecrets(array $account)
    {
        $account['access_token'] = $this->CI->deals_integration_model->decrypt_value($account['access_token_encrypted'] ?? null);
        $account['refresh_token'] = $this->CI->deals_integration_model->decrypt_value($account['refresh_token_encrypted'] ?? null);

        return $account;
    }
}
