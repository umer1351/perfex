<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/AbstractProvider.php';

class GoogleBaseProvider extends AbstractProvider
{
    public function getKey()
    {
        return 'google_workspace';
    }

    public function getName()
    {
        return 'Google Workspace';
    }

    public function getDefinition()
    {
        return [
            'provider_key' => $this->getKey(),
            'name' => $this->getName(),
            'driver_class' => static::class,
            'supports_oauth' => 1,
            'supports_webhooks' => 1,
            'supports_sync' => 1,
            'status' => 'active',
            'config' => [
                'resources' => $this->getSupportedResources(),
                'oauth_scopes' => [
                    'https://www.googleapis.com/auth/userinfo.email',
                    'https://www.googleapis.com/auth/userinfo.profile',
                    'https://www.googleapis.com/auth/gmail.modify',
                    'https://www.googleapis.com/auth/calendar',
                    'https://www.googleapis.com/auth/drive.file',
                ],
            ],
        ];
    }

    public function buildAuthorizationUrl(array $account)
    {
        $meta = json_decode($account['account_meta_json'] ?? '[]', true) ?: [];
        $clientId = $meta['client_id'] ?? '';
        if ($clientId === '') {
            return null;
        }

        $scopes = json_decode($account['scopes_json'] ?? '[]', true) ?: ($this->getDefinition()['config']['oauth_scopes'] ?? []);
        return 'https://accounts.google.com/o/oauth2/v2/auth?response_type=code'
            . '&client_id=' . rawurlencode($clientId)
            . '&redirect_uri=' . rawurlencode(site_url('deals/platform/oauth/google_workspace'))
            . '&scope=' . rawurlencode(implode(' ', $scopes))
            . '&access_type=offline&prompt=consent'
            . '&state=' . rawurlencode($account['oauth_state'] ?? '');
    }

    public function exchangeAuthorizationCode(array $account, $authorizationCode, $redirectUri)
    {
        $meta = json_decode($account['account_meta_json'] ?? '[]', true) ?: [];
        $clientId = $meta['client_id'] ?? '';
        $clientSecret = $meta['client_secret'] ?? '';
        if ($clientId === '' || $clientSecret === '' || $authorizationCode === '') {
            return [
                'success' => false,
                'message' => 'Google client credentials or authorization code are missing.',
            ];
        }

        $response = $this->postForm('https://oauth2.googleapis.com/token', [
            'code' => (string) $authorizationCode,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => (string) $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        $json = $response['json'] ?? [];
        if (empty($response['success']) || empty($json['access_token'])) {
            return [
                'success' => false,
                'message' => $json['error_description'] ?? $json['error'] ?? $response['error'] ?? 'Google OAuth token exchange failed.',
                'context' => $json,
            ];
        }

        return [
            'success' => true,
            'access_token' => $json['access_token'],
            'refresh_token' => $json['refresh_token'] ?? null,
            'token_expires_at' => $this->calculateExpiryTimestamp($json['expires_in'] ?? 0),
            'scopes' => !empty($json['scope']) ? preg_split('/\s+/', trim((string) $json['scope'])) : [],
            'raw' => $json,
        ];
    }

    public function fetchAccountProfile(array $account)
    {
        $accessToken = (string) ($account['access_token'] ?? '');
        if ($accessToken === '') {
            return [
                'success' => false,
                'message' => 'Google access token is missing.',
            ];
        }

        $response = $this->getJson('https://www.googleapis.com/oauth2/v2/userinfo?alt=json', [
            'Authorization: Bearer ' . $accessToken,
        ]);
        $json = $response['json'] ?? [];
        if (empty($response['success']) || empty($json['email'])) {
            return [
                'success' => false,
                'message' => $json['error']['message'] ?? $response['error'] ?? 'Google account profile lookup failed.',
                'context' => $json,
            ];
        }

        return [
            'success' => true,
            'external_account_id' => $json['email'],
            'account_label' => !empty($json['name']) ? $json['name'] : $json['email'],
            'profile' => $json,
        ];
    }

    public function validateWebhookSignature(array $account, array $payload, array $context)
    {
        $headers = $context['headers'] ?? [];
        $token = (string) ($headers['X-Goog-Channel-Token'] ?? $headers['x-goog-channel-token'] ?? '');
        $expected = (string) ($account['webhook_secret'] ?? '');

        if ($expected === '') {
            return true;
        }

        return $token !== '' && hash_equals($expected, $token);
    }

    public function normalizeWebhookEvent(array $account, array $payload, array $context)
    {
        $headers = $context['headers'] ?? [];
        $resourceState = strtolower((string) ($headers['X-Goog-Resource-State'] ?? $headers['x-goog-resource-state'] ?? ''));
        $resourceId = (string) ($headers['X-Goog-Resource-Id'] ?? $headers['x-goog-resource-id'] ?? '');
        $channelId = (string) ($headers['X-Goog-Channel-Id'] ?? $headers['x-goog-channel-id'] ?? '');
        $historyId = (string) ($payload['historyId'] ?? $headers['X-Goog-Message-Number'] ?? '');

        $eventName = 'integration.google.webhook_received';
        if (($payload['resource'] ?? '') === 'gmail' || strpos($resourceId, 'gmail') !== false) {
            $eventName = 'email.received';
        } elseif (($payload['resource'] ?? '') === 'calendar' || strpos($resourceId, 'calendar') !== false) {
            $eventName = 'meeting.created';
        } elseif (($payload['resource'] ?? '') === 'drive' || strpos($resourceId, 'drive') !== false) {
            $eventName = 'file.uploaded';
        }

        return [
            'event_name' => $eventName,
            'payload' => array_merge($payload, [
                'resource_state' => $resourceState,
                'resource_id' => $resourceId,
                'channel_id' => $channelId,
            ]),
            'resource_type' => $payload['resource'] ?? 'gmail',
            'cursor_token' => $historyId ?: null,
            'external_channel_id' => $channelId ?: null,
        ];
    }

    public function getSupportedResources()
    {
        return ['gmail', 'calendar', 'drive'];
    }
}
