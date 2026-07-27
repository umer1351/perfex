<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../contracts/ProviderDriverInterface.php';

abstract class AbstractProvider implements ProviderDriverInterface
{
    public function buildAuthorizationUrl(array $account)
    {
        return null;
    }

    public function exchangeAuthorizationCode(array $account, $authorizationCode, $redirectUri)
    {
        return [
            'success' => false,
            'message' => 'OAuth exchange is not implemented for this provider.',
        ];
    }

    public function fetchAccountProfile(array $account)
    {
        return [
            'success' => false,
            'message' => 'Account profile lookup is not implemented for this provider.',
        ];
    }

    public function validateWebhookSignature(array $account, array $payload, array $context)
    {
        return true;
    }

    public function normalizeWebhookEvent(array $account, array $payload, array $context)
    {
        return [
            'event_name' => 'integration.webhook.received',
            'payload' => $payload,
            'context' => $context,
            'resource_type' => null,
            'cursor_token' => null,
            'external_channel_id' => null,
        ];
    }

    public function sync(array $account, $resourceType, array $syncState, array $context = [])
    {
        return [
            'success' => true,
            'resource_type' => $resourceType,
            'cursor_token' => $syncState['cursor_token'] ?? null,
            'state' => $syncState,
            'message' => 'Scaffold sync completed.',
        ];
    }

    public function testConnection(array $account)
    {
        return !empty($account['access_token']);
    }

    protected function sendRequest($method, $url, array $headers = [], $body = null)
    {
        $method = strtoupper((string) $method);
        $responseBody = '';
        $statusCode = 0;
        $errorMessage = null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            if ($body !== null && $method !== 'GET') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $responseBody = curl_exec($ch);
            if ($responseBody === false) {
                $errorMessage = curl_error($ch);
            }
            $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                    'content' => $body !== null && $method !== 'GET' ? $body : '',
                    'ignore_errors' => true,
                    'timeout' => 20,
                ],
            ]);

            $responseBody = @file_get_contents($url, false, $context);
            if ($responseBody === false) {
                $errorMessage = 'HTTP request failed.';
            }
            if (!empty($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
                $statusCode = (int) $matches[1];
            }
        }

        $json = null;
        if (is_string($responseBody) && $responseBody !== '') {
            $decoded = json_decode($responseBody, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $json = $decoded;
            }
        }

        return [
            'success' => $errorMessage === null && $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'body' => is_string($responseBody) ? $responseBody : '',
            'json' => is_array($json) ? $json : null,
            'error' => $errorMessage,
        ];
    }

    protected function postForm($url, array $payload, array $headers = [])
    {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        return $this->sendRequest('POST', $url, $headers, http_build_query($payload, '', '&'));
    }

    protected function getJson($url, array $headers = [])
    {
        $headers[] = 'Accept: application/json';
        return $this->sendRequest('GET', $url, $headers);
    }

    protected function calculateExpiryTimestamp($seconds)
    {
        $seconds = (int) $seconds;
        if ($seconds <= 0) {
            return null;
        }

        return date('Y-m-d H:i:s', time() + $seconds);
    }
}
