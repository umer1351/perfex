<?php

namespace modules\deals\libraries;

defined('BASEPATH') or exit('No direct script access allowed');

class DealsProviderAdapter
{
    public function detectInboundProvider($payload)
    {
        return $this->normaliseProvider($this->readFirst($payload, ['provider', 'source']) ?: $this->inferProviderFromPayload($payload, 'inbound'));
    }

    public function detectBounceProvider($payload)
    {
        return $this->normaliseProvider($this->readFirst($payload, ['provider', 'source']) ?: $this->inferProviderFromPayload($payload, 'bounce'));
    }

    public function normalizeInbound($provider, $payload)
    {
        $provider = $this->normaliseProvider($provider ?: $this->detectInboundProvider($payload));

        if ($provider === 'mailgun') {
            return $this->normalizeMailgunInbound($payload);
        }

        if ($provider === 'sendgrid') {
            return $this->normalizeSendgridInbound($payload);
        }

        if ($provider === 'postmark') {
            return $this->normalizePostmarkInbound($payload);
        }

        return $this->normalizeGenericInbound($payload, $provider ?: 'inbound_api');
    }

    public function normalizeBounce($provider, $payload)
    {
        $provider = $this->normaliseProvider($provider ?: $this->detectBounceProvider($payload));

        if ($provider === 'mailgun') {
            return $this->normalizeMailgunBounce($payload);
        }

        if ($provider === 'sendgrid') {
            return $this->normalizeSendgridBounce($payload);
        }

        if ($provider === 'postmark') {
            return $this->normalizePostmarkBounce($payload);
        }

        return $this->normalizeGenericBounce($payload);
    }

    public function formatConnectorPayload($connectorType, $eventType, $deal, $context, $text)
    {
        $type = $this->normaliseProvider($connectorType);
        $deal = $this->normaliseDeal($deal);
        $eventLabel = ucwords(str_replace('_', ' ', (string) $eventType));
        $value = $this->formatMoney($deal['deal_value']);
        $facts = [
            ['name' => 'Deal', 'value' => $deal['title']],
            ['name' => 'Status', 'value' => ucfirst($deal['status'] ?: 'open')],
            ['name' => 'Stage', 'value' => $deal['stage_name'] ?: '-'],
            ['name' => 'Owner', 'value' => $deal['owner_name'] ?: 'Unassigned'],
            ['name' => 'Value', 'value' => $value],
        ];

        if ($type === 'slack') {
            return [
                'channel' => $deal['channel_identifier'] ?: null,
                'text' => $text,
                'blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => $eventLabel . ': ' . $deal['title'],
                        ],
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => '*' . $eventLabel . "*\n" . $text,
                        ],
                        'fields' => [
                            ['type' => 'mrkdwn', 'text' => '*Deal ID:*\n#' . $deal['id']],
                            ['type' => 'mrkdwn', 'text' => '*Owner:*\n' . ($deal['owner_name'] ?: 'Unassigned')],
                            ['type' => 'mrkdwn', 'text' => '*Stage:*\n' . ($deal['stage_name'] ?: '-')],
                            ['type' => 'mrkdwn', 'text' => '*Value:*\n' . $value],
                        ],
                    ],
                    [
                        'type' => 'context',
                        'elements' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => 'Status: ' . ucfirst($deal['status'] ?: 'open') . ' | Triggered: ' . date('Y-m-d H:i:s'),
                            ],
                        ],
                    ],
                ],
            ];
        }

        if ($type === 'teams') {
            return [
                '@type' => 'MessageCard',
                '@context' => 'https://schema.org/extensions',
                'summary' => 'Deals event: ' . $eventLabel,
                'themeColor' => '1D4ED8',
                'title' => $eventLabel . ': ' . $deal['title'],
                'text' => nl2br($this->escapeText($text)),
                'sections' => [
                    [
                        'activityTitle' => 'Deal #' . $deal['id'],
                        'facts' => $facts,
                        'markdown' => true,
                    ],
                ],
                'potentialAction' => !empty($deal['detail_url']) ? [[
                    '@type' => 'OpenUri',
                    'name' => 'Open Deal',
                    'targets' => [[
                        'os' => 'default',
                        'uri' => $deal['detail_url'],
                    ]],
                ]] : [],
            ];
        }

        if ($type === 'google_chat') {
            return [
                'text' => $text,
            ];
        }

        if ($type === 'generic_json') {
            return [
                'event' => $eventType,
                'text' => $text,
                'deal' => [
                    'id' => $deal['id'],
                    'title' => $deal['title'],
                    'status' => $deal['status'],
                    'stage' => $deal['stage_name'],
                    'value' => $deal['deal_value'],
                    'owner' => $deal['owner_name'],
                    'url' => $deal['detail_url'],
                ],
                'context' => is_array($context) ? $context : [],
            ];
        }

        return ['text' => $text];
    }

    protected function normalizeMailgunInbound($payload)
    {
        $headers = $this->mergeHeaders(
            $this->normalizeHeaders($payload['headers'] ?? []),
            $this->normalizeHeaders($payload['message-headers'] ?? [])
        );

        return [
            'source' => 'mailgun',
            'from' => $this->firstEmail($this->readFirst($payload, ['from', 'sender'])),
            'to' => $this->normaliseEmailList($this->readFirst($payload, ['to', 'recipient', 'To']) ?: ($headers['To'] ?? '')),
            'cc' => $this->normaliseEmailList($this->readFirst($payload, ['cc', 'Cc']) ?: ($headers['Cc'] ?? '')),
            'subject' => trim((string) ($this->readFirst($payload, ['subject', 'Subject']) ?: ($headers['Subject'] ?? ''))),
            'text' => trim((string) $this->readFirst($payload, ['body-plain', 'stripped-text', 'text'])),
            'html' => trim((string) $this->readFirst($payload, ['body-html', 'stripped-html', 'html'])),
            'message_id' => trim((string) ($this->readFirst($payload, ['Message-Id', 'Message-ID', 'message-id', 'message_id']) ?: ($headers['Message-Id'] ?? ($headers['Message-ID'] ?? '')))),
            'in_reply_to' => trim((string) ($this->readFirst($payload, ['In-Reply-To', 'in_reply_to']) ?: ($headers['In-Reply-To'] ?? ''))),
            'headers' => $headers,
            'thread_token' => trim((string) $this->readFirst($payload, ['thread_token'])),
            'deal_id' => $this->normalizeInteger($payload['deal_id'] ?? null),
        ];
    }

    protected function normalizeSendgridInbound($payload)
    {
        $headers = $this->normalizeHeaders($payload['headers'] ?? []);
        $envelope = $payload['envelope'] ?? [];

        if (is_string($envelope)) {
            $decodedEnvelope = json_decode($envelope, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $envelope = $decodedEnvelope;
            }
        }

        return [
            'source' => 'sendgrid',
            'from' => $this->firstEmail($this->readFirst($payload, ['from']) ?: ($headers['From'] ?? '')),
            'to' => $this->normaliseEmailList($this->readFirst($payload, ['to']) ?: ($envelope['to'] ?? ($headers['To'] ?? ''))),
            'cc' => $this->normaliseEmailList($this->readFirst($payload, ['cc']) ?: ($headers['Cc'] ?? '')),
            'subject' => trim((string) ($this->readFirst($payload, ['subject']) ?: ($headers['Subject'] ?? ''))),
            'text' => trim((string) $this->readFirst($payload, ['text'])),
            'html' => trim((string) $this->readFirst($payload, ['html'])),
            'message_id' => trim((string) ($this->readFirst($payload, ['message_id']) ?: ($headers['Message-ID'] ?? ($headers['Message-Id'] ?? '')))),
            'in_reply_to' => trim((string) ($this->readFirst($payload, ['in_reply_to']) ?: ($headers['In-Reply-To'] ?? ''))),
            'headers' => $headers,
            'thread_token' => trim((string) $this->readFirst($payload, ['thread_token'])),
            'deal_id' => $this->normalizeInteger($payload['deal_id'] ?? null),
        ];
    }

    protected function normalizePostmarkInbound($payload)
    {
        $headers = $this->normalizeHeaders($payload['Headers'] ?? ($payload['headers'] ?? []));
        $to = $this->postmarkAddressesToEmails($payload['ToFull'] ?? []);
        $cc = $this->postmarkAddressesToEmails($payload['CcFull'] ?? []);

        if (empty($to)) {
            $to = $this->normaliseEmailList($this->readFirst($payload, ['To', 'to']) ?: ($headers['To'] ?? ''));
        }

        if (empty($cc)) {
            $cc = $this->normaliseEmailList($this->readFirst($payload, ['Cc', 'cc']) ?: ($headers['Cc'] ?? ''));
        }

        return [
            'source' => 'postmark',
            'from' => $this->firstEmail($this->readNested($payload, ['FromFull', 'Email']) ?: $this->readFirst($payload, ['From', 'from']) ?: ($headers['From'] ?? '')),
            'to' => $to,
            'cc' => $cc,
            'subject' => trim((string) ($this->readFirst($payload, ['Subject', 'subject']) ?: ($headers['Subject'] ?? ''))),
            'text' => trim((string) $this->readFirst($payload, ['TextBody', 'text'])),
            'html' => trim((string) $this->readFirst($payload, ['HtmlBody', 'html'])),
            'message_id' => trim((string) ($this->readFirst($payload, ['MessageID', 'message_id']) ?: ($headers['Message-ID'] ?? ($headers['Message-Id'] ?? '')))),
            'in_reply_to' => trim((string) ($this->readFirst($payload, ['InReplyTo', 'in_reply_to']) ?: ($headers['In-Reply-To'] ?? ''))),
            'headers' => $headers,
            'thread_token' => trim((string) $this->readFirst($payload, ['MailboxHash', 'thread_token'])),
            'deal_id' => $this->normalizeInteger($payload['deal_id'] ?? null),
        ];
    }

    protected function normalizeGenericInbound($payload, $provider)
    {
        $headers = $this->normalizeHeaders($payload['headers'] ?? []);

        return [
            'source' => $provider,
            'from' => $this->firstEmail($this->readFirst($payload, ['from', 'sender'])),
            'to' => $this->normaliseEmailList($this->readFirst($payload, ['to', 'recipient']) ?: ($headers['To'] ?? '')),
            'cc' => $this->normaliseEmailList($this->readFirst($payload, ['cc']) ?: ($headers['Cc'] ?? '')),
            'subject' => trim((string) ($this->readFirst($payload, ['subject']) ?: ($headers['Subject'] ?? ''))),
            'text' => trim((string) $this->readFirst($payload, ['text', 'body_plain'])),
            'html' => trim((string) $this->readFirst($payload, ['html', 'body_html'])),
            'message_id' => trim((string) ($this->readFirst($payload, ['message_id']) ?: ($headers['Message-ID'] ?? ($headers['Message-Id'] ?? '')))),
            'in_reply_to' => trim((string) ($this->readFirst($payload, ['in_reply_to']) ?: ($headers['In-Reply-To'] ?? ''))),
            'headers' => $headers,
            'thread_token' => trim((string) $this->readFirst($payload, ['thread_token'])),
            'deal_id' => $this->normalizeInteger($payload['deal_id'] ?? null),
        ];
    }

    protected function normalizeMailgunBounce($payload)
    {
        $eventData = is_array($payload['event-data'] ?? null) ? $payload['event-data'] : [];
        $headers = $this->normalizeHeaders($this->readNested($eventData, ['message', 'headers']) ?: []);
        $severity = strtolower((string) $this->readNested($eventData, ['severity']));
        $event = strtolower((string) ($eventData['event'] ?? 'failed'));
        $bounceType = ($severity === 'permanent' || in_array($event, ['bounced', 'failed'], true)) ? 'hard' : 'soft';

        return [
            'recipient' => $this->firstEmail($this->readFirst($payload, ['recipient']) ?: ($eventData['recipient'] ?? '')),
            'message_id' => trim((string) ($this->readFirst($payload, ['message_id']) ?: ($headers['Message-Id'] ?? ($headers['message-id'] ?? '')))),
            'bounce_type' => $bounceType,
            'reason' => trim((string) ($this->readNested($eventData, ['delivery-status', 'description']) ?: $this->readNested($eventData, ['delivery-status', 'message']) ?: ($eventData['reason'] ?? 'Bounce received'))),
        ];
    }

    protected function normalizeSendgridBounce($payload)
    {
        $event = isset($payload[0]) && is_array($payload[0]) ? $payload[0] : $payload;
        $eventName = strtolower((string) ($event['event'] ?? 'bounce'));
        $status = (string) ($event['status'] ?? '');
        $bounceType = (strpos($status, '4') === 0 || in_array($eventName, ['deferred', 'dropped', 'blocked'], true)) ? 'soft' : 'hard';

        return [
            'recipient' => $this->firstEmail($event['email'] ?? ''),
            'message_id' => trim((string) ($event['smtp-id'] ?? ($event['sg_message_id'] ?? ($event['message_id'] ?? '')))),
            'bounce_type' => $bounceType,
            'reason' => trim((string) ($event['reason'] ?? ($event['response'] ?? 'Bounce received'))),
        ];
    }

    protected function normalizePostmarkBounce($payload)
    {
        $type = strtolower((string) ($payload['Type'] ?? ($payload['RecordType'] ?? 'hardbounce')));
        $hardTypes = ['hardbounce', 'spamnotification', 'spamcomplaint'];

        return [
            'recipient' => $this->firstEmail($payload['Email'] ?? ''),
            'message_id' => trim((string) ($payload['MessageID'] ?? ($payload['MessageId'] ?? ''))),
            'bounce_type' => in_array($type, $hardTypes, true) ? 'hard' : 'soft',
            'reason' => trim((string) ($payload['Description'] ?? ($payload['Details'] ?? 'Bounce received'))),
        ];
    }

    protected function normalizeGenericBounce($payload)
    {
        return [
            'recipient' => $this->firstEmail($this->readFirst($payload, ['recipient', 'email'])),
            'message_id' => trim((string) $this->readFirst($payload, ['message_id', 'Message-Id', 'Message-ID'])),
            'bounce_type' => trim((string) ($this->readFirst($payload, ['bounce_type', 'type']) ?: 'hard')),
            'reason' => trim((string) ($this->readFirst($payload, ['reason', 'description']) ?: 'Bounce received')),
        ];
    }

    protected function inferProviderFromPayload($payload, $mode)
    {
        if (!is_array($payload)) {
            return $mode === 'bounce' ? 'bounce_api' : 'inbound_api';
        }

        if (isset($payload['event-data']) || isset($payload['signature']) || isset($payload['body-plain'])) {
            return 'mailgun';
        }

        if (isset($payload['MailboxHash']) || isset($payload['FromFull']) || isset($payload['TextBody']) || isset($payload['RecordType'])) {
            return 'postmark';
        }

        if ((isset($payload[0]) && is_array($payload[0]) && isset($payload[0]['event'])) || isset($payload['spam_score']) || isset($payload['charsets']) || isset($payload['dkim'])) {
            return 'sendgrid';
        }

        return $mode === 'bounce' ? 'bounce_api' : 'inbound_api';
    }

    protected function normaliseDeal($deal)
    {
        $data = is_object($deal) ? get_object_vars($deal) : (is_array($deal) ? $deal : []);

        return [
            'id' => (int) ($data['id'] ?? 0),
            'title' => (string) ($data['title'] ?? 'Deal'),
            'status' => (string) ($data['status'] ?? 'open'),
            'stage_name' => (string) ($data['stage_name'] ?? ''),
            'deal_value' => (float) ($data['deal_value'] ?? 0),
            'owner_name' => (string) (($data['owner_name'] ?? ($data['full_name'] ?? ''))),
            'detail_url' => (string) ($data['detail_url'] ?? ''),
            'channel_identifier' => (string) ($data['channel_identifier'] ?? ''),
        ];
    }

    protected function normalizeHeaders($headers)
    {
        if (is_string($headers)) {
            $decoded = json_decode($headers, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $headers = $decoded;
            } else {
                $headers = preg_split("/\r\n|\n|\r/", $headers);
            }
        }

        $normalized = [];
        if (!is_array($headers)) {
            return $normalized;
        }

        foreach ($headers as $key => $value) {
            if (is_int($key) && is_array($value) && isset($value['Name'])) {
                $normalized[trim((string) $value['Name'])] = trim((string) ($value['Value'] ?? ''));
                continue;
            }

            if (is_int($key) && is_array($value) && array_key_exists(0, $value) && array_key_exists(1, $value)) {
                $normalized[trim((string) $value[0])] = trim((string) $value[1]);
                continue;
            }

            if (is_int($key) && is_string($value) && strpos($value, ':') !== false) {
                list($headerName, $headerValue) = explode(':', $value, 2);
                $normalized[trim($headerName)] = trim($headerValue);
                continue;
            }

            if (!is_int($key)) {
                $normalized[trim((string) $key)] = is_scalar($value) ? trim((string) $value) : json_encode($value);
            }
        }

        return $normalized;
    }

    protected function mergeHeaders()
    {
        $merged = [];
        foreach (func_get_args() as $headers) {
            foreach ((array) $headers as $key => $value) {
                if ($value !== null && $value !== '') {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    protected function normaliseEmailList($value)
    {
        $emails = [];

        if (is_array($value)) {
            foreach ($value as $item) {
                $emails = array_merge($emails, $this->normaliseEmailList($item));
            }
        } else {
            $string = (string) $value;
            preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $string, $matches);
            $emails = !empty($matches[0]) ? $matches[0] : preg_split('/[\s,;]+/', $string);
        }

        $emails = array_map(function ($email) {
            return strtolower(trim((string) $email, " \t\n\r\0\x0B<>"));
        }, $emails);

        return array_values(array_filter(array_unique($emails), function ($email) {
            return $email !== '';
        }));
    }

    protected function firstEmail($value)
    {
        $emails = $this->normaliseEmailList($value);
        return $emails[0] ?? '';
    }

    protected function postmarkAddressesToEmails($items)
    {
        $emails = [];

        if (!is_array($items)) {
            return $emails;
        }

        foreach ($items as $item) {
            if (is_array($item)) {
                $emails = array_merge($emails, $this->normaliseEmailList($item['Email'] ?? ($item['email'] ?? '')));
            }
        }

        return array_values(array_unique($emails));
    }

    protected function readFirst($payload, $keys)
    {
        if (!is_array($payload)) {
            return null;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
                return $payload[$key];
            }
        }

        return null;
    }

    protected function readNested($payload, $path)
    {
        $value = $payload;
        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    protected function normalizeInteger($value)
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function normaliseProvider($provider)
    {
        return strtolower(trim((string) $provider));
    }

    protected function escapeText($value)
    {
        if (function_exists('html_escape')) {
            return html_escape($value);
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    protected function formatMoney($amount)
    {
        if (function_exists('app_format_money') && function_exists('get_base_currency')) {
            return app_format_money((float) $amount, get_base_currency());
        }

        return number_format((float) $amount, 2);
    }
}
