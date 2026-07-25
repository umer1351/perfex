<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/AbstractProvider.php';

class SlackBaseProvider extends AbstractProvider
{
    public function getKey()
    {
        return 'slack';
    }

    public function getName()
    {
        return 'Slack';
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
                'oauth_scopes' => ['channels:read', 'chat:write', 'commands', 'users:read'],
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
        return 'https://slack.com/oauth/v2/authorize?client_id=' . rawurlencode($clientId)
            . '&scope=' . rawurlencode(implode(',', $scopes))
            . '&redirect_uri=' . rawurlencode(site_url('deals/platform/oauth/slack'))
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
                'message' => 'Slack client credentials or authorization code are missing.',
            ];
        }

        $response = $this->postForm('https://slack.com/api/oauth.v2.access', [
            'code' => (string) $authorizationCode,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => (string) $redirectUri,
        ], [
            'Accept: application/json',
        ]);

        $json = $response['json'] ?? [];
        if (empty($response['success']) || empty($json['ok']) || empty($json['access_token'])) {
            return [
                'success' => false,
                'message' => $json['error'] ?? $response['error'] ?? 'Slack OAuth token exchange failed.',
                'context' => $json,
            ];
        }

        $scopes = [];
        if (!empty($json['scope'])) {
            $scopes = array_filter(array_map('trim', explode(',', (string) $json['scope'])));
        }

        return [
            'success' => true,
            'access_token' => $json['access_token'],
            'refresh_token' => $json['refresh_token'] ?? ($json['authed_user']['refresh_token'] ?? null),
            'token_expires_at' => $this->calculateExpiryTimestamp($json['expires_in'] ?? ($json['authed_user']['expires_in'] ?? 0)),
            'scopes' => $scopes,
            'raw' => $json,
        ];
    }

    public function fetchAccountProfile(array $account)
    {
        $accessToken = (string) ($account['access_token'] ?? '');
        if ($accessToken === '') {
            return [
                'success' => false,
                'message' => 'Slack access token is missing.',
            ];
        }

        $response = $this->getJson('https://slack.com/api/auth.test', [
            'Authorization: Bearer ' . $accessToken,
        ]);
        $json = $response['json'] ?? [];
        if (empty($response['success']) || empty($json['ok'])) {
            return [
                'success' => false,
                'message' => $json['error'] ?? $response['error'] ?? 'Slack account profile lookup failed.',
                'context' => $json,
            ];
        }

        return [
            'success' => true,
            'external_account_id' => $json['team_id'] ?? ($json['user_id'] ?? ''),
            'account_label' => $json['team'] ?? ($json['url'] ?? 'Slack Workspace'),
            'profile' => $json,
        ];
    }

    public function validateWebhookSignature(array $account, array $payload, array $context)
    {
        $headers = $context['headers'] ?? [];
        $timestamp = (string) ($headers['X-Slack-Request-Timestamp'] ?? $headers['x-slack-request-timestamp'] ?? '');
        $signature = (string) ($headers['X-Slack-Signature'] ?? $headers['x-slack-signature'] ?? '');
        $secret = (string) ($account['webhook_secret'] ?? '');
        $raw = (string) ($context['raw'] ?? '');

        if ($secret === '' || $timestamp === '' || $signature === '' || $raw === '') {
            return $secret === '';
        }

        $expected = 'v0=' . hash_hmac('sha256', 'v0:' . $timestamp . ':' . $raw, $secret);
        return hash_equals($expected, $signature);
    }

    public function normalizeWebhookEvent(array $account, array $payload, array $context)
    {
        $eventType = strtolower((string) ($payload['event']['type'] ?? $payload['type'] ?? ''));
        $eventName = 'integration.slack.webhook_received';
        if ($eventType === 'message') {
            $eventName = 'integration.slack.message.received';
        } elseif ($eventType === 'app_mention') {
            $eventName = 'integration.slack.bot.mentioned';
        }

        return [
            'event_name' => $eventName,
            'payload' => $payload,
            'resource_type' => 'workspace',
            'cursor_token' => (string) ($payload['event_id'] ?? ''),
            'external_channel_id' => (string) ($payload['event']['channel'] ?? ''),
        ];
    }

    public function getSupportedResources()
    {
        return ['workspace', 'channels', 'messaging', 'bot'];
    }
}
