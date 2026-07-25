<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Deals_comms_model extends App_Model
{
    protected $provider_adapter;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('deals/deals_model', 'deals_model', true);
        $this->provider_adapter = new \modules\deals\libraries\DealsProviderAdapter();
    }

    public function get_mailboxes()
    {
        if (!$this->db->table_exists('tbl_deals_inbound_mailboxes')) {
            return [];
        }

        $inboundCountSql = $this->db->table_exists('tbl_deals_email')
            ? '(SELECT COUNT(id) FROM tbl_deals_email WHERE mailbox_id = tbl_deals_inbound_mailboxes.id AND direction = "inbound")'
            : '0';
        $bounceCountSql = $this->db->table_exists('tbl_deals_email')
            ? '(SELECT COUNT(id) FROM tbl_deals_email WHERE mailbox_id = tbl_deals_inbound_mailboxes.id AND delivery_status = "bounced")'
            : '0';

        $this->db->select('tbl_deals_inbound_mailboxes.*, CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as default_owner_name, ' . $inboundCountSql . ' as inbound_count, ' . $bounceCountSql . ' as bounce_count', false);
        $this->db->from('tbl_deals_inbound_mailboxes');
        $this->db->join(db_prefix() . 'staff', 'tblstaff.staffid = tbl_deals_inbound_mailboxes.default_owner_id', 'left');
        $this->db->order_by('tbl_deals_inbound_mailboxes.name', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_mailbox($id)
    {
        if (!$this->db->table_exists('tbl_deals_inbound_mailboxes')) {
            return null;
        }

        return $this->db->where('id', $id)->get('tbl_deals_inbound_mailboxes')->row_array();
    }

    public function save_mailbox($data, $id = null)
    {
        if (!$this->db->table_exists('tbl_deals_inbound_mailboxes')) {
            return false;
        }

        $existing = $id ? $this->get_mailbox($id) : null;
        $payload = [
            'name' => trim((string) $data['name']),
            'mailbox_email' => strtolower(trim((string) $data['mailbox_email'])),
            'provider_type' => !empty($data['provider_type']) ? trim((string) $data['provider_type']) : 'custom',
            'secret_token' => !empty($data['secret_token']) ? $data['secret_token'] : ($existing['secret_token'] ?? bin2hex(random_bytes(16))),
            'verification_mode' => !empty($data['verification_mode']) ? trim((string) $data['verification_mode']) : 'token_only',
            'verification_header' => !empty($data['verification_header']) ? trim((string) $data['verification_header']) : 'X-Deals-Signature',
            'verification_secret' => !empty($data['verification_secret']) ? trim((string) $data['verification_secret']) : null,
            'allowed_sources_json' => json_encode($this->normalise_list($data['allowed_sources'] ?? [])),
            'routing_mode' => !empty($data['routing_mode']) ? trim((string) $data['routing_mode']) : 'thread_or_message_id',
            'allowed_sender_domains_json' => json_encode($this->normalise_domains($data['allowed_sender_domains'] ?? [])),
            'default_owner_id' => !empty($data['default_owner_id']) ? (int) $data['default_owner_id'] : null,
            'allow_reply_processing' => !empty($data['allow_reply_processing']) ? 1 : 0,
            'allow_bounce_processing' => !empty($data['allow_bounce_processing']) ? 1 : 0,
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        $payload = $this->filter_table_payload('tbl_deals_inbound_mailboxes', $payload);

        if ($id) {
            $this->db->where('id', $id)->update('tbl_deals_inbound_mailboxes', $payload);
            return $id;
        }

        $this->db->insert('tbl_deals_inbound_mailboxes', $payload);
        return $this->db->insert_id();
    }

    public function delete_mailbox($id)
    {
        if (!$this->db->table_exists('tbl_deals_inbound_mailboxes')) {
            return false;
        }

        return $this->db->where('id', $id)->delete('tbl_deals_inbound_mailboxes');
    }

    public function get_connectors()
    {
        if (!$this->db->table_exists('tbl_deals_connectors')) {
            return [];
        }

        $successCountSql = $this->db->table_exists('tbl_deals_connector_logs')
            ? '(SELECT COUNT(id) FROM tbl_deals_connector_logs WHERE connector_id = tbl_deals_connectors.id AND status = "success")'
            : '0';
        $failedCountSql = $this->db->table_exists('tbl_deals_connector_logs')
            ? '(SELECT COUNT(id) FROM tbl_deals_connector_logs WHERE connector_id = tbl_deals_connectors.id AND status = "failed")'
            : '0';

        return $this->db
            ->select('tbl_deals_connectors.*, ' . $successCountSql . ' as success_count, ' . $failedCountSql . ' as failed_count', false)
            ->order_by('name', 'ASC')
            ->get('tbl_deals_connectors')
            ->result_array();
    }

    public function get_connector($id)
    {
        if (!$this->db->table_exists('tbl_deals_connectors')) {
            return null;
        }

        return $this->db->where('id', $id)->get('tbl_deals_connectors')->row_array();
    }

    public function save_connector($data, $id = null)
    {
        if (!$this->db->table_exists('tbl_deals_connectors')) {
            return false;
        }

        $payload = [
            'name' => trim((string) $data['name']),
            'connector_type' => trim((string) $data['connector_type']),
            'endpoint_url' => trim((string) $data['endpoint_url']),
            'delivery_method' => !empty($data['delivery_method']) ? strtoupper(trim((string) $data['delivery_method'])) : 'POST',
            'auth_type' => !empty($data['auth_type']) ? trim((string) $data['auth_type']) : 'bearer',
            'auth_header_name' => !empty($data['auth_header_name']) ? trim((string) $data['auth_header_name']) : 'Authorization',
            'auth_username' => !empty($data['auth_username']) ? trim((string) $data['auth_username']) : null,
            'auth_token' => !empty($data['auth_token']) ? $data['auth_token'] : null,
            'custom_headers_json' => json_encode($this->normalise_header_map($data['custom_headers'] ?? [])),
            'payload_format' => !empty($data['payload_format']) ? trim((string) $data['payload_format']) : 'json',
            'timeout_seconds' => !empty($data['timeout_seconds']) ? max(1, (int) $data['timeout_seconds']) : 10,
            'retry_limit' => isset($data['retry_limit']) ? max(0, (int) $data['retry_limit']) : 0,
            'retry_backoff_ms' => isset($data['retry_backoff_ms']) ? max(0, (int) $data['retry_backoff_ms']) : 250,
            'signature_header_name' => !empty($data['signature_header_name']) ? trim((string) $data['signature_header_name']) : 'X-Deals-Signature',
            'signature_secret' => !empty($data['signature_secret']) ? trim((string) $data['signature_secret']) : null,
            'channel_identifier' => !empty($data['channel_identifier']) ? $data['channel_identifier'] : null,
            'trigger_events_json' => json_encode($this->normalise_list($data['trigger_events'] ?? [])),
            'template_text' => !empty($data['template_text']) ? $data['template_text'] : null,
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        $payload = $this->filter_table_payload('tbl_deals_connectors', $payload);

        if ($id) {
            $this->db->where('id', $id)->update('tbl_deals_connectors', $payload);
            return $id;
        }

        $this->db->insert('tbl_deals_connectors', $payload);
        return $this->db->insert_id();
    }

    public function delete_connector($id)
    {
        if (!$this->db->table_exists('tbl_deals_connectors')) {
            return false;
        }

        if ($this->db->table_exists('tbl_deals_connector_logs')) {
            $this->db->where('connector_id', $id)->delete('tbl_deals_connector_logs');
        }

        return $this->db->where('id', $id)->delete('tbl_deals_connectors');
    }

    public function get_recent_connector_logs($limit = 100)
    {
        if (!$this->db->table_exists('tbl_deals_connector_logs')) {
            return [];
        }

        $this->db->select('tbl_deals_connector_logs.*, tbl_deals_connectors.name as connector_name, tbl_deals.title as deal_title', false);
        $this->db->from('tbl_deals_connector_logs');
        $this->db->join('tbl_deals_connectors', 'tbl_deals_connectors.id = tbl_deals_connector_logs.connector_id', 'left');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_connector_logs.deal_id', 'left');
        $this->db->order_by('tbl_deals_connector_logs.attempted_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function get_recent_mailbox_activity($limit = 25)
    {
        if (!$this->db->table_exists('tbl_deals_email')) {
            return [];
        }

        $this->db->select('tbl_deals_email.id, tbl_deals_email.deals_id, tbl_deals_email.subject, tbl_deals_email.email_from, tbl_deals_email.message_time, tbl_deals_email.direction, tbl_deals_email.delivery_status, tbl_deals_email.connector_source, tbl_deals_inbound_mailboxes.name as mailbox_name, tbl_deals.title as deal_title', false);
        $this->db->from('tbl_deals_email');
        $this->db->join('tbl_deals_inbound_mailboxes', 'tbl_deals_inbound_mailboxes.id = tbl_deals_email.mailbox_id', 'left');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_email.deals_id', 'left');
        $this->db->where('tbl_deals_email.mailbox_id IS NOT NULL', null, false);
        $this->db->group_start();
        $this->db->where('tbl_deals_email.direction', 'inbound');
        $this->db->or_where('tbl_deals_email.delivery_status', 'bounced');
        $this->db->group_end();
        $this->db->order_by('tbl_deals_email.message_time', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function prepare_outbound_email($emailId, $dealId, $subject, $mailboxId = null)
    {
        if (!$this->db->table_exists('tbl_deals_email') || !$this->db->table_exists('tbl_deals_email_threads')) {
            return [];
        }

        $thread = $this->ensure_thread($dealId, $subject, $mailboxId);
        $host = parse_url(site_url(), PHP_URL_HOST) ?: 'local.invalid';
        $messageId = '<deal-' . $dealId . '-email-' . $emailId . '-' . substr($thread['thread_token'], 0, 16) . '@' . $host . '>';
        $replyTo = null;

        $mailbox = null;
        if (!empty($mailboxId)) {
            $mailbox = $this->get_mailbox($mailboxId);
        }

        if (!$mailbox) {
            $mailbox = $this->db->table_exists('tbl_deals_inbound_mailboxes')
                ? $this->db->where('is_active', 1)->order_by('id', 'ASC')->get('tbl_deals_inbound_mailboxes')->row_array()
                : null;
        }

        if (!empty($mailbox['mailbox_email']) && strpos($mailbox['mailbox_email'], '@') !== false) {
            [$localPart, $domainPart] = explode('@', $mailbox['mailbox_email'], 2);
            $replyTo = $localPart . '+' . $thread['thread_token'] . '@' . $domainPart;
        }

        $update = [
            'direction' => 'outbound',
            'thread_token' => $thread['thread_token'],
            'message_id' => $messageId,
            'mailbox_id' => $mailbox['id'] ?? null,
            'delivery_status' => 'queued',
            'parsed_payload_json' => json_encode([
                'thread_token' => $thread['thread_token'],
                'reply_to' => $replyTo,
            ]),
        ];

        $this->db->where('id', $emailId)->update('tbl_deals_email', $update);

        return [
            'thread_token' => $thread['thread_token'],
            'message_id' => $messageId,
            'reply_to' => $replyTo,
            'mailbox_id' => $mailbox['id'] ?? null,
            'thread_marker' => "\n\n<!-- DEAL-THREAD:" . $thread['thread_token'] . " -->",
        ];
    }

    public function record_outbound_delivery_result($emailId, $status, $meta = [])
    {
        if (!$this->db->table_exists('tbl_deals_email')) {
            return false;
        }

        $update = [
            'delivery_status' => $status,
        ];

        if (!empty($meta)) {
            $update['parsed_payload_json'] = json_encode($meta);
        }

        return $this->db->where('id', $emailId)->update('tbl_deals_email', $update);
    }

    public function ingest_inbound_message($mailboxToken, $payload, $requestContext = [])
    {
        $mailbox = $this->resolve_mailbox_by_token($mailboxToken);
        if (!$mailbox) {
            return ['success' => false, 'message' => 'Mailbox token is invalid.'];
        }

        if (empty($mailbox['allow_reply_processing'])) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'Inbound reply processing is disabled for this mailbox.']);
            return ['success' => false, 'message' => 'Inbound reply processing is disabled for this mailbox.'];
        }

        $verification = $this->verify_mailbox_request($mailbox, $payload, $requestContext);
        if (empty($verification['success'])) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => $verification['message']]);
            return $verification;
        }

        $normalized = $this->normalise_inbound_payload($payload);
        if (!$this->mailbox_allows_source($mailbox, $normalized['source'] ?? null)) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'Inbound source is not allowed for this mailbox.']);
            return ['success' => false, 'message' => 'Inbound source is not allowed for this mailbox.'];
        }

        if (!$this->mailbox_allows_sender($mailbox, $normalized['from'] ?? '')) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'Sender domain is not allowed for this mailbox.']);
            return ['success' => false, 'message' => 'Sender domain is not allowed for this mailbox.'];
        }

        if (empty($normalized['from']) || (empty($normalized['text']) && empty($normalized['html']))) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'Inbound payload is missing sender or content.']);
            return ['success' => false, 'message' => 'Inbound payload is missing sender or content.'];
        }

        $match = $this->resolve_inbound_deal_match($normalized, $mailbox);
        if (empty($match['deal_id'])) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'No matching deal thread could be identified.']);
            return ['success' => false, 'message' => 'No matching deal thread could be identified.'];
        }

        $thread = $this->ensure_thread($match['deal_id'], $normalized['subject'], $mailbox['id'], $match['thread_token'] ?? null);
        $messageBody = !empty($normalized['html']) ? $normalized['html'] : nl2br(html_escape($normalized['text']));

        $record = [
            'email_to' => implode(';', $normalized['to']),
            'email_cc' => implode(';', $normalized['cc']),
            'email_from' => $normalized['from'],
            'user_id' => !empty($mailbox['default_owner_id']) ? (int) $mailbox['default_owner_id'] : 0,
            'deals_id' => $match['deal_id'],
            'subject' => $normalized['subject'] ?: 'Reply received',
            'message_body' => $messageBody,
            'message_time' => date('Y-m-d H:i:s'),
            'direction' => 'inbound',
            'thread_token' => $thread['thread_token'],
            'in_reply_to' => $normalized['in_reply_to'],
            'message_id' => $normalized['message_id'],
            'delivery_status' => 'received',
            'connector_source' => $normalized['source'],
            'raw_headers_json' => json_encode($normalized['headers']),
            'parsed_payload_json' => json_encode($payload),
            'mailbox_id' => $mailbox['id'],
            'is_bounce' => 0,
        ];

        $this->db->insert('tbl_deals_email', $record);
        $emailId = $this->db->insert_id();

        $this->db->where('id', $thread['id'])->update('tbl_deals_email_threads', [
            'last_message_at' => date('Y-m-d H:i:s'),
            'subject' => $record['subject'],
        ]);

        $this->deals_model->sync_deal_metrics($match['deal_id'], [
            'last_contacted_at' => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);
        $this->deals_model->log_deals_activity($match['deal_id'], 'Deal inbound email received', true, serialize([$record['subject'], $record['email_from'], $emailId]));
        $this->touch_mailbox_runtime($mailbox['id'], [
            'last_received_at' => date('Y-m-d H:i:s'),
            'last_error_at' => null,
            'last_error_message' => null,
        ]);

        return [
            'success' => true,
            'deal_id' => $match['deal_id'],
            'email_id' => $emailId,
            'thread_token' => $thread['thread_token'],
        ];
    }

    public function register_bounce($mailboxToken, $payload, $requestContext = [])
    {
        $mailbox = $this->resolve_mailbox_by_token($mailboxToken);
        if (!$mailbox) {
            return ['success' => false, 'message' => 'Mailbox token is invalid.'];
        }

        if (empty($mailbox['allow_bounce_processing'])) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'Bounce processing is disabled for this mailbox.']);
            return ['success' => false, 'message' => 'Bounce processing is disabled for this mailbox.'];
        }

        $verification = $this->verify_mailbox_request($mailbox, $payload, $requestContext);
        if (empty($verification['success'])) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => $verification['message']]);
            return $verification;
        }

        $normalized = $this->normalise_bounce_payload($payload);
        if (!$this->mailbox_allows_source($mailbox, $payload['provider'] ?? ($payload['source'] ?? $normalized['source'] ?? null))) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'Bounce source is not allowed for this mailbox.']);
            return ['success' => false, 'message' => 'Bounce source is not allowed for this mailbox.'];
        }

        if (empty($normalized['recipient']) && empty($normalized['message_id'])) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'Bounce payload is missing recipient or message ID.']);
            return ['success' => false, 'message' => 'Bounce payload is missing recipient or message ID.'];
        }

        $email = null;
        if (!empty($normalized['message_id'])) {
            $email = $this->db->where('message_id', $normalized['message_id'])->get('tbl_deals_email')->row_array();
        }

        if (!$email && !empty($normalized['recipient'])) {
            $email = $this->db
                ->where('email_to LIKE', '%' . $normalized['recipient'] . '%')
                ->order_by('id', 'DESC')
                ->get('tbl_deals_email')
                ->row_array();
        }

        if (!$email) {
            $this->touch_mailbox_runtime($mailbox['id'], ['last_error_message' => 'No matching outbound email was found for this bounce.']);
            return ['success' => false, 'message' => 'No matching outbound email was found for this bounce.'];
        }

        $update = [
            'delivery_status' => 'bounced',
            'bounce_status' => $normalized['bounce_type'],
            'is_bounce' => 1,
            'parsed_payload_json' => json_encode($payload),
        ];
        $this->db->where('id', $email['id'])->update('tbl_deals_email', $update);

        if (!empty($normalized['recipient']) && $this->db->table_exists('tbl_deals_email_preferences')) {
            $this->upsert_email_preference_bounce($normalized['recipient'], $normalized['bounce_type'], $normalized['reason']);
        }

        $dealId = $email['deals_id'] ?? null;
        if (!empty($dealId)) {
            $this->deals_model->sync_deal_metrics($dealId, ['last_activity_at' => date('Y-m-d H:i:s')]);
            $this->deals_model->log_deals_activity($dealId, 'Deal email bounced', true, serialize([$normalized['recipient'], $normalized['bounce_type'], $normalized['reason']]));
        }
        $this->touch_mailbox_runtime($mailbox['id'], [
            'last_bounced_at' => date('Y-m-d H:i:s'),
            'last_error_at' => null,
            'last_error_message' => null,
        ]);

        return [
            'success' => true,
            'deal_id' => $dealId,
            'email_id' => $email['id'],
            'bounce_type' => $normalized['bounce_type'],
        ];
    }

    public function dispatch_event_connectors($eventType, $dealId, $context = [])
    {
        if (!$this->db->table_exists('tbl_deals_connectors') || !$this->db->table_exists('tbl_deals_connector_logs')) {
            return 0;
        }

        $connectors = $this->get_active_connectors_for_event($eventType);
        if (empty($connectors)) {
            return 0;
        }

        $deal = $this->deals_model->dealInfo($dealId);
        if (!$deal) {
            return 0;
        }

        $count = 0;
        foreach ($connectors as $connector) {
            $count += $this->send_connector($connector, $eventType, $deal, $context) ? 1 : 0;
        }

        return $count;
    }

    public function test_connector($id, $dealId = null)
    {
        $connector = $this->get_connector($id);
        if (!$connector) {
            return false;
        }

        $deal = null;
        if (!empty($dealId)) {
            $deal = $this->deals_model->dealInfo($dealId);
        }
        if (!$deal) {
            $latest = $this->db->order_by('id', 'DESC')->get('tbl_deals', 1)->row();
            if ($latest) {
                $deal = $this->deals_model->dealInfo($latest->id);
            }
        }
        if (!$deal) {
            return false;
        }

        return $this->send_connector($connector, 'connector_test', $deal, [
            'mode' => 'manual_test',
            'requested_by' => get_staff_user_id(),
        ], ['is_test' => true]);
    }

    public function retry_connector_log($logId)
    {
        if (!$this->db->table_exists('tbl_deals_connector_logs')) {
            return false;
        }

        $log = $this->db->where('id', $logId)->get('tbl_deals_connector_logs')->row_array();
        if (!$log) {
            return false;
        }

        $connector = $this->get_connector($log['connector_id']);
        if (!$connector || empty($connector['is_active'])) {
            return false;
        }

        $deal = !empty($log['deal_id']) ? $this->deals_model->dealInfo($log['deal_id']) : null;
        if (!$deal) {
            return false;
        }

        $payload = json_decode($log['request_payload'] ?? '[]', true);

        return $this->send_connector($connector, $log['event_type'], $deal, $payload['context'] ?? [], [
            'is_test' => !empty($log['is_test']),
            'retry_of_log_id' => $logId,
        ]);
    }

    public function run_runtime_qa_suite($dealId = null)
    {
        $results = [];
        $sampleDealId = $dealId;

        if (empty($sampleDealId)) {
            $latestDeal = $this->db->order_by('id', 'DESC')->get('tbl_deals', 1)->row();
            $sampleDealId = $latestDeal->id ?? null;
        }

        $this->push_qa_result($results, 'schema', empty($sampleDealId) ? 'warning' : 'success', 'Sample deal context ' . ($sampleDealId ? 'found' : 'missing') . '.', ['deal_id' => $sampleDealId]);
        $this->push_qa_result($results, 'mailboxes', $this->db->table_exists('tbl_deals_inbound_mailboxes') ? 'success' : 'failed', 'Inbound mailbox table ' . ($this->db->table_exists('tbl_deals_inbound_mailboxes') ? 'present' : 'missing') . '.');
        $this->push_qa_result($results, 'connectors', $this->db->table_exists('tbl_deals_connectors') ? 'success' : 'failed', 'Connector table ' . ($this->db->table_exists('tbl_deals_connectors') ? 'present' : 'missing') . '.');
        $this->push_qa_result($results, 'email_threads', $this->db->table_exists('tbl_deals_email_threads') ? 'success' : 'failed', 'Email thread table ' . ($this->db->table_exists('tbl_deals_email_threads') ? 'present' : 'missing') . '.');
        $this->run_provider_fixture_suite($results);

        if ($sampleDealId) {
            try {
                $deal = $this->deals_model->dealInfo($sampleDealId);
                $this->push_qa_result($results, 'deal_info', $deal ? 'success' : 'failed', $deal ? 'dealInfo loaded successfully.' : 'dealInfo returned no record.');
            } catch (Throwable $e) {
                $this->push_qa_result($results, 'deal_info', 'failed', 'dealInfo failed.', ['error' => $e->getMessage()]);
            }

            $legacyChecks = [
                'comments' => function () use ($sampleDealId) {
                    return count($this->deals_model->get_deal_comments($sampleDealId));
                },
                'attachments' => function () use ($sampleDealId) {
                    return count($this->deals_model->get_deal_attachments($sampleDealId));
                },
                'followups' => function () use ($sampleDealId) {
                    return count($this->deals_model->get_deal_followups($sampleDealId));
                },
                'activity_log' => function () use ($sampleDealId) {
                    return count($this->deals_model->get_lead_activity_log($sampleDealId));
                },
                'emails' => function () use ($sampleDealId) {
                    return (int) $this->db->where('deals_id', $sampleDealId)->count_all_results('tbl_deals_email');
                },
                'calls' => function () use ($sampleDealId) {
                    return (int) $this->db->where('module', 'deals')->where('module_field_id', $sampleDealId)->count_all_results('tbl_deal_calls');
                },
                'meetings' => function () use ($sampleDealId) {
                    return (int) $this->db->where('module', 'deals')->where('module_field_id', $sampleDealId)->count_all_results('tbl_deals_mettings');
                },
                'items' => function () use ($sampleDealId) {
                    return (int) $this->db->where('deals_id', $sampleDealId)->count_all_results('tbl_deals_items');
                },
            ];

            foreach ($legacyChecks as $area => $callback) {
                try {
                    $count = $callback();
                    $this->push_qa_result($results, $area, 'success', 'Legacy flow query executed successfully.', ['count' => $count]);
                } catch (Throwable $e) {
                    $this->push_qa_result($results, $area, 'failed', 'Legacy flow query failed.', ['error' => $e->getMessage()]);
                }
            }
        }

        foreach ($results as $result) {
            if ($this->db->table_exists('tbl_deals_runtime_qa_logs')) {
                $this->db->insert('tbl_deals_runtime_qa_logs', [
                    'area' => $result['area'],
                    'status' => $result['status'],
                    'deal_id' => $sampleDealId,
                    'details_json' => json_encode($result),
                ]);
            }
        }

        $summary = [
            'passed' => count(array_filter($results, function ($result) {
                return $result['status'] === 'success';
            })),
            'warnings' => count(array_filter($results, function ($result) {
                return $result['status'] === 'warning';
            })),
            'failed' => count(array_filter($results, function ($result) {
                return $result['status'] === 'failed';
            })),
        ];

        return [
            'deal_id' => $sampleDealId,
            'summary' => $summary,
            'checks' => $results,
        ];
    }

    public function get_recent_runtime_qa_logs($limit = 50, $offset = 0)
    {
        if (!$this->db->table_exists('tbl_deals_runtime_qa_logs')) {
            return [];
        }

        return $this->db->order_by('created_at', 'DESC')->limit($limit, $offset)->get('tbl_deals_runtime_qa_logs')->result_array();
    }

    public function get_runtime_qa_logs_count()
    {
        if (!$this->db->table_exists('tbl_deals_runtime_qa_logs')) {
            return 0;
        }

        return (int) $this->db->count_all('tbl_deals_runtime_qa_logs');
    }

    protected function ensure_thread($dealId, $subject = '', $mailboxId = null, $forcedThreadToken = null)
    {
        if (!empty($forcedThreadToken)) {
            $thread = $this->db->where('thread_token', $forcedThreadToken)->get('tbl_deals_email_threads')->row_array();
            if ($thread) {
                return $thread;
            }
        }

        $thread = $this->db
            ->where('deal_id', $dealId)
            ->order_by('last_message_at IS NULL', 'ASC', false)
            ->order_by('updated_at', 'DESC')
            ->get('tbl_deals_email_threads')
            ->row_array();

        if ($thread) {
            return $thread;
        }

        $thread = [
            'deal_id' => $dealId,
            'mailbox_id' => $mailboxId,
            'thread_token' => $forcedThreadToken ?: bin2hex(random_bytes(12)),
            'subject' => $subject ?: 'Deal Conversation',
            'last_message_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tbl_deals_email_threads', $thread);
        $thread['id'] = $this->db->insert_id();

        return $thread;
    }

    protected function resolve_mailbox_by_token($token)
    {
        if (!$this->db->table_exists('tbl_deals_inbound_mailboxes') || empty($token)) {
            return null;
        }

        return $this->db
            ->where('secret_token', $token)
            ->where('is_active', 1)
            ->get('tbl_deals_inbound_mailboxes')
            ->row_array();
    }

    protected function normalise_inbound_payload($payload)
    {
        $provider = $this->provider_adapter->detectInboundProvider($payload);
        $normalized = $this->provider_adapter->normalizeInbound($provider, is_array($payload) ? $payload : []);
        $normalized['to'] = $this->normalise_email_field($normalized['to'] ?? []);
        $normalized['cc'] = $this->normalise_email_field($normalized['cc'] ?? []);
        $normalized['from'] = trim((string) ($normalized['from'] ?? ''));
        $normalized['subject'] = trim((string) ($normalized['subject'] ?? ''));
        $normalized['text'] = trim((string) ($normalized['text'] ?? ''));
        $normalized['html'] = trim((string) ($normalized['html'] ?? ''));
        $normalized['message_id'] = trim((string) ($normalized['message_id'] ?? ''));
        $normalized['in_reply_to'] = trim((string) ($normalized['in_reply_to'] ?? ''));
        $normalized['headers'] = is_array($normalized['headers'] ?? null) ? $normalized['headers'] : [];
        $normalized['thread_token'] = trim((string) ($normalized['thread_token'] ?? ''));
        $normalized['deal_id'] = !empty($normalized['deal_id']) ? (int) $normalized['deal_id'] : null;
        $normalized['source'] = $normalized['source'] ?? $provider;

        return $normalized;
    }

    protected function resolve_inbound_deal_match($normalized, $mailbox = [])
    {
        $routingMode = $mailbox['routing_mode'] ?? 'thread_or_message_id';

        if (!empty($normalized['deal_id'])) {
            return [
                'deal_id' => (int) $normalized['deal_id'],
                'thread_token' => $normalized['thread_token'] ?: null,
            ];
        }

        if ($routingMode === 'direct_deal_id') {
            return [];
        }

        $threadToken = $normalized['thread_token'];
        if (empty($threadToken)) {
            $threadToken = $this->extract_thread_token_from_recipients($normalized['to'], $normalized['cc']);
        }

        if (empty($threadToken)) {
            $threadToken = $this->extract_thread_token_from_body($normalized['html'] ?: $normalized['text']);
        }

        if (!empty($threadToken) && $this->db->table_exists('tbl_deals_email_threads')) {
            $thread = $this->db->where('thread_token', $threadToken)->get('tbl_deals_email_threads')->row_array();
            if ($thread) {
                return [
                    'deal_id' => (int) $thread['deal_id'],
                    'thread_token' => $threadToken,
                ];
            }
        }

        if ($routingMode === 'thread_only') {
            return [];
        }

        if (!empty($normalized['in_reply_to'])) {
            $email = $this->db->where('message_id', $normalized['in_reply_to'])->get('tbl_deals_email')->row_array();
            if ($email) {
                return [
                    'deal_id' => (int) $email['deals_id'],
                    'thread_token' => $email['thread_token'] ?? null,
                ];
            }
        }

        return [];
    }

    protected function normalise_bounce_payload($payload)
    {
        $provider = $this->provider_adapter->detectBounceProvider($payload);
        $normalized = $this->provider_adapter->normalizeBounce($provider, is_array($payload) ? $payload : []);
        $normalized['recipient'] = trim((string) ($normalized['recipient'] ?? ''));
        $normalized['message_id'] = trim((string) ($normalized['message_id'] ?? ''));
        $normalized['bounce_type'] = trim((string) ($normalized['bounce_type'] ?? 'hard'));
        $normalized['reason'] = trim((string) ($normalized['reason'] ?? 'Bounce received'));
        $normalized['source'] = $provider;

        return $normalized;
    }

    protected function upsert_email_preference_bounce($email, $bounceType, $reason)
    {
        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail === '') {
            return;
        }

        $preference = $this->db->where('email', $normalizedEmail)->get('tbl_deals_email_preferences')->row_array();
        $payload = [
            'email' => $normalizedEmail,
            'token' => $preference['token'] ?? bin2hex(random_bytes(16)),
            'bounce_status' => $bounceType,
            'last_bounced_at' => date('Y-m-d H:i:s'),
            'bounce_reason' => $reason,
        ];

        if (strtolower($bounceType) === 'hard') {
            $payload['is_unsubscribed'] = 1;
            $payload['unsubscribed_at'] = date('Y-m-d H:i:s');
        }

        if ($preference) {
            $this->db->where('id', $preference['id'])->update('tbl_deals_email_preferences', $payload);
            return;
        }

        $payload['is_unsubscribed'] = $payload['is_unsubscribed'] ?? 0;
        $payload['unsubscribed_at'] = $payload['unsubscribed_at'] ?? null;
        $this->db->insert('tbl_deals_email_preferences', $payload);
    }

    protected function get_active_connectors_for_event($eventType)
    {
        $connectors = $this->db->where('is_active', 1)->get('tbl_deals_connectors')->result_array();

        return array_values(array_filter($connectors, function ($connector) use ($eventType) {
            $events = json_decode($connector['trigger_events_json'] ?? '[]', true);
            return in_array($eventType, $events ?: [], true);
        }));
    }

    protected function send_connector($connector, $eventType, $deal, $context = [], $options = [])
    {
        $payload = $this->format_connector_payload($connector, $eventType, $deal, $context);
        $request = $this->build_connector_request($connector, $payload);
        $payloadJson = $request['body'];
        $status = 'queued';
        $responseCode = null;
        $responseBody = null;
        $errorMessage = null;
        $startedAt = microtime(true);
        $attemptCount = 0;

        if (empty($connector['endpoint_url'])) {
            $status = 'failed';
            $errorMessage = 'Connector endpoint URL is missing.';
            $responseBody = $errorMessage;
        } elseif (function_exists('curl_init')) {
            $maxAttempts = max(1, ((int) ($connector['retry_limit'] ?? 0)) + 1);
            $timeoutSeconds = max(1, (int) ($connector['timeout_seconds'] ?? 10));

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $attemptCount = $attempt;
                $ch = curl_init($request['url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($request['method']));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $request['headers']);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);

                if (!empty($request['userpwd'])) {
                    curl_setopt($ch, CURLOPT_USERPWD, $request['userpwd']);
                }

                if (!empty($request['body']) && strtoupper($request['method']) !== 'GET') {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $request['body']);
                }

                $responseBody = curl_exec($ch);
                $responseCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $status = ($responseBody !== false && $responseCode >= 200 && $responseCode < 300) ? 'success' : 'failed';
                if ($responseBody === false) {
                    $errorMessage = curl_error($ch);
                    $responseBody = $errorMessage;
                } else {
                    $errorMessage = $status === 'success' ? null : ('Connector returned HTTP ' . $responseCode . '.');
                }
                curl_close($ch);

                if ($status === 'success' || $attempt >= $maxAttempts) {
                    break;
                }

                $backoffMs = max(0, (int) ($connector['retry_backoff_ms'] ?? 0));
                if ($backoffMs > 0) {
                    usleep($backoffMs * 1000);
                }
            }
        } else {
            $status = 'skipped';
            $errorMessage = 'cURL extension is not available.';
            $responseBody = $errorMessage;
        }

        $processingMs = (int) round((microtime(true) - $startedAt) * 1000);
        $logPayload = $this->filter_table_payload('tbl_deals_connector_logs', [
            'connector_id' => $connector['id'],
            'deal_id' => $deal->id,
            'event_type' => $eventType,
            'status' => $status,
            'response_code' => $responseCode ?: null,
            'request_payload' => $payloadJson,
            'request_headers_json' => json_encode($request['headers']),
            'response_body' => $responseBody,
            'error_message' => $errorMessage,
            'processing_ms' => $processingMs,
            'attempt_count' => max(1, $attemptCount),
            'retry_of_log_id' => !empty($options['retry_of_log_id']) ? (int) $options['retry_of_log_id'] : null,
            'is_test' => !empty($options['is_test']) ? 1 : 0,
        ]);
        $this->db->insert('tbl_deals_connector_logs', $logPayload);

        $connectorUpdate = [
            'last_run_at' => date('Y-m-d H:i:s'),
            'last_status' => $status,
            'consecutive_failures' => $status === 'success' ? 0 : ((int) ($connector['consecutive_failures'] ?? 0)) + 1,
        ];
        if ($status === 'success') {
            $connectorUpdate['last_success_at'] = date('Y-m-d H:i:s');
        }
        $connectorUpdate = $this->filter_table_payload('tbl_deals_connectors', $connectorUpdate);
        $this->db->where('id', $connector['id'])->update('tbl_deals_connectors', $connectorUpdate);

        return $status === 'success';
    }

    protected function format_connector_payload($connector, $eventType, $deal, $context)
    {
        $text = $this->render_connector_text($connector, $eventType, $deal, $context);
        $dealData = is_object($deal) ? get_object_vars($deal) : (array) $deal;
        $dealData['detail_url'] = admin_url('deals/details/' . (int) ($dealData['id'] ?? 0));
        $dealData['channel_identifier'] = $connector['channel_identifier'] ?? '';

        $payload = $this->provider_adapter->formatConnectorPayload($connector['connector_type'] ?? 'generic_json', $eventType, $dealData, is_array($context) ? $context : [], $text);

        if (is_array($payload) && array_key_exists('channel', $payload) && empty($payload['channel'])) {
            unset($payload['channel']);
        }

        return $payload;
    }

    protected function render_connector_text($connector, $eventType, $deal, $context)
    {
        $template = !empty($connector['template_text'])
            ? $connector['template_text']
            : "Deal event: {event}\nDeal: {deal_title}\nStatus: {status}\nStage: {stage}\nValue: {deal_value}\nOwner: {owner_name}";

        return strtr($template, [
            '{event}' => ucwords(str_replace('_', ' ', $eventType)),
            '{deal_id}' => $deal->id,
            '{deal_title}' => $deal->title,
            '{status}' => ucfirst($deal->status ?? 'open'),
            '{stage}' => $deal->stage_name ?? '-',
            '{deal_value}' => app_format_money((float) ($deal->deal_value ?? 0), get_base_currency()),
            '{owner_name}' => $deal->full_name ?? 'Unassigned',
            '{context_json}' => json_encode($context),
        ]);
    }

    protected function build_connector_request($connector, $payload)
    {
        $payloadFormat = strtolower((string) ($connector['payload_format'] ?? 'json'));
        $method = strtoupper((string) ($connector['delivery_method'] ?? 'POST'));
        $url = trim((string) ($connector['endpoint_url'] ?? ''));
        $headers = $this->normalise_header_map(json_decode($connector['custom_headers_json'] ?? '[]', true) ?: []);
        $body = '';
        $userpwd = null;

        if ($payloadFormat === 'form') {
            $body = http_build_query(is_array($payload) ? $payload : ['payload' => $payload]);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        } else {
            $body = json_encode($payload);
            $headers['Content-Type'] = 'application/json';
        }

        $authType = strtolower((string) ($connector['auth_type'] ?? 'bearer'));
        $authHeaderName = trim((string) ($connector['auth_header_name'] ?? 'Authorization'));
        $authToken = (string) ($connector['auth_token'] ?? '');

        if ($authType === 'bearer' && $authToken !== '') {
            $headers[$authHeaderName ?: 'Authorization'] = 'Bearer ' . $authToken;
        } elseif ($authType === 'header' && $authToken !== '') {
            $headers[$authHeaderName ?: 'X-Auth-Token'] = $authToken;
        } elseif ($authType === 'basic' && $authToken !== '') {
            $userpwd = (string) ($connector['auth_username'] ?? '') . ':' . $authToken;
        } elseif ($authType === 'query' && $authToken !== '') {
            $url = $this->append_query_param($url, $authHeaderName ?: 'api_key', $authToken);
        }

        if (!empty($connector['signature_secret'])) {
            $headers[trim((string) ($connector['signature_header_name'] ?? 'X-Deals-Signature'))] = hash_hmac('sha256', $body, (string) $connector['signature_secret']);
        }

        $headerLines = [];
        foreach ($headers as $key => $value) {
            if ($value !== null && $value !== '') {
                $headerLines[] = $key . ': ' . $value;
            }
        }
        $headerLines[] = 'Content-Length: ' . strlen((string) $body);

        return [
            'url' => $url,
            'method' => $method,
            'body' => (string) $body,
            'headers' => $headerLines,
            'userpwd' => $userpwd,
        ];
    }

    protected function push_qa_result(&$results, $area, $status, $message, $meta = [])
    {
        $results[] = [
            'area' => $area,
            'status' => $status,
            'message' => $message,
            'meta' => $meta,
        ];
    }

    protected function extract_thread_token_from_recipients($to, $cc)
    {
        $allRecipients = array_merge($to, $cc);
        foreach ($allRecipients as $recipient) {
            if (preg_match('/\+([a-zA-Z0-9]+)@/', $recipient, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    protected function extract_thread_token_from_body($body)
    {
        if (preg_match('/DEAL-THREAD:([a-zA-Z0-9]+)/', (string) $body, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function normalise_email_field($value)
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(function ($item) {
                return strtolower(trim((string) $item));
            }, $value)));
        }

        $value = str_replace(',', ';', (string) $value);
        $parts = array_map('trim', explode(';', $value));

        return array_values(array_filter(array_map('strtolower', $parts)));
    }

    protected function normalise_list($items)
    {
        if (is_string($items)) {
            $items = preg_split('/[\r\n,;]+/', $items);
        }

        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_unique(array_map('trim', $items))));
    }

    protected function normalise_domains($items)
    {
        return array_values(array_filter(array_map(function ($item) {
            $item = strtolower(trim((string) $item));
            return ltrim($item, '@');
        }, $this->normalise_list($items))));
    }

    protected function normalise_header_map($headers)
    {
        if (is_string($headers)) {
            $lines = preg_split('/\r\n|\r|\n/', $headers);
            $headers = [];
            foreach ($lines as $line) {
                if (strpos($line, ':') === false) {
                    continue;
                }
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }

        if (!is_array($headers)) {
            return [];
        }

        $normalized = [];
        foreach ($headers as $key => $value) {
            if (is_int($key) && is_array($value) && isset($value['name'])) {
                $normalized[trim((string) $value['name'])] = trim((string) ($value['value'] ?? ''));
                continue;
            }

            if (!is_int($key)) {
                $normalized[trim((string) $key)] = trim((string) $value);
            }
        }

        return array_filter($normalized, function ($value, $key) {
            return $key !== '' && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected function mailbox_allows_source($mailbox, $source)
    {
        $allowedSources = json_decode($mailbox['allowed_sources_json'] ?? '[]', true);
        $allowedSources = array_values(array_filter(array_map('strtolower', array_map('trim', is_array($allowedSources) ? $allowedSources : []))));
        $source = strtolower(trim((string) $source));

        if (empty($allowedSources) || $source === '') {
            return true;
        }

        return in_array($source, $allowedSources, true);
    }

    protected function mailbox_allows_sender($mailbox, $senderEmail)
    {
        $allowedDomains = json_decode($mailbox['allowed_sender_domains_json'] ?? '[]', true);
        $allowedDomains = $this->normalise_domains(is_array($allowedDomains) ? $allowedDomains : []);
        $senderDomain = strtolower((string) substr(strrchr(strtolower(trim((string) $senderEmail)), '@') ?: '', 1));

        if (empty($allowedDomains) || $senderDomain === '') {
            return true;
        }

        return in_array($senderDomain, $allowedDomains, true);
    }

    protected function verify_mailbox_request($mailbox, $payload, $requestContext)
    {
        $mode = strtolower((string) ($mailbox['verification_mode'] ?? 'token_only'));
        $secret = (string) ($mailbox['verification_secret'] ?? '');
        $headerName = trim((string) ($mailbox['verification_header'] ?? 'X-Deals-Signature'));

        if ($mode === '' || $mode === 'token_only' || $mode === 'none') {
            return ['success' => true];
        }

        if ($mode === 'secret_header') {
            $headerValue = $this->get_request_header($requestContext, $headerName);
            return ($secret !== '' && hash_equals($secret, (string) $headerValue))
                ? ['success' => true]
                : ['success' => false, 'message' => 'Mailbox verification failed for secret header mode.'];
        }

        if ($mode === 'body_hmac_sha256') {
            $headerValue = strtolower((string) $this->get_request_header($requestContext, $headerName));
            $raw = (string) ($requestContext['raw'] ?? '');
            $expected = strtolower(hash_hmac('sha256', $raw, $secret));
            return ($secret !== '' && $headerValue !== '' && hash_equals($expected, $headerValue))
                ? ['success' => true]
                : ['success' => false, 'message' => 'Mailbox verification failed for body signature mode.'];
        }

        if ($mode === 'mailgun_signature') {
            $timestamp = (string) ($payload['timestamp'] ?? '');
            $token = (string) ($payload['token'] ?? '');
            $signature = strtolower((string) ($payload['signature'] ?? ''));
            $expected = strtolower(hash_hmac('sha256', $timestamp . $token, $secret));
            return ($secret !== '' && $timestamp !== '' && $token !== '' && $signature !== '' && hash_equals($expected, $signature))
                ? ['success' => true]
                : ['success' => false, 'message' => 'Mailbox verification failed for Mailgun signature mode.'];
        }

        return ['success' => true];
    }

    protected function get_request_header($requestContext, $headerName)
    {
        $headers = is_array($requestContext['headers'] ?? null) ? $requestContext['headers'] : [];
        foreach ($headers as $key => $value) {
            if (strtolower((string) $key) === strtolower((string) $headerName)) {
                return $value;
            }
        }

        return null;
    }

    protected function touch_mailbox_runtime($mailboxId, $fields = [])
    {
        if (empty($mailboxId) || !$this->db->table_exists('tbl_deals_inbound_mailboxes')) {
            return;
        }

        $update = [];
        foreach (['last_received_at', 'last_bounced_at', 'last_error_at', 'last_error_message'] as $field) {
            if (array_key_exists($field, $fields)) {
                $update[$field] = $fields[$field];
            }
        }

        if (array_key_exists('last_error_message', $fields) && $fields['last_error_message'] !== null && !array_key_exists('last_error_at', $fields)) {
            $update['last_error_at'] = date('Y-m-d H:i:s');
        }

        if (!empty($update)) {
            $update = $this->filter_table_payload('tbl_deals_inbound_mailboxes', $update);
            $this->db->where('id', $mailboxId)->update('tbl_deals_inbound_mailboxes', $update);
        }
    }

    protected function append_query_param($url, $key, $value)
    {
        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
    }

    protected function filter_table_payload($table, $payload)
    {
        if (!$this->db->table_exists($table) || empty($payload) || !is_array($payload)) {
            return $payload;
        }

        $fields = array_map(function ($field) {
            return $field->name;
        }, $this->db->field_data($table));

        return array_intersect_key($payload, array_flip($fields));
    }

    protected function run_provider_fixture_suite(&$results)
    {
        $fixturePath = module_dir_path(DEALS_MODULE, 'fixtures/providers/');
        if (!is_dir($fixturePath)) {
            $this->push_qa_result($results, 'provider_fixtures', 'warning', 'Provider fixture directory is missing.');
            return;
        }

        $files = glob($fixturePath . '*.json') ?: [];
        if (empty($files)) {
            $this->push_qa_result($results, 'provider_fixtures', 'warning', 'No provider fixtures were found.');
            return;
        }

        foreach ($files as $file) {
            $basename = basename($file);
            $rawFixture = @file_get_contents($file);
            $fixture = json_decode($rawFixture ?: '', true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($fixture)) {
                $this->push_qa_result($results, 'fixture_' . $this->normalise_fixture_area($basename), 'failed', 'Fixture could not be parsed.', ['fixture' => $basename]);
                continue;
            }

            try {
                $actual = $this->execute_provider_fixture($fixture);
                $matched = $this->matches_expected_subset($actual, $fixture['expected_subset'] ?? []);
                $this->push_qa_result(
                    $results,
                    'fixture_' . $this->normalise_fixture_area($fixture['name'] ?? $basename),
                    $matched ? 'success' : 'failed',
                    ($fixture['description'] ?? $basename) . ($matched ? ' passed.' : ' failed.'),
                    [
                        'fixture' => $basename,
                        'provider' => $fixture['provider'] ?? '',
                        'kind' => $fixture['kind'] ?? '',
                        'actual' => $matched ? [] : $actual,
                    ]
                );
            } catch (Throwable $e) {
                $this->push_qa_result($results, 'fixture_' . $this->normalise_fixture_area($fixture['name'] ?? $basename), 'failed', 'Fixture execution failed.', [
                    'fixture' => $basename,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function execute_provider_fixture($fixture)
    {
        $kind = strtolower(trim((string) ($fixture['kind'] ?? '')));
        $provider = strtolower(trim((string) ($fixture['provider'] ?? '')));
        $input = is_array($fixture['input'] ?? null) ? $fixture['input'] : [];

        if ($kind === 'inbound') {
            return $this->provider_adapter->normalizeInbound($provider, $input);
        }

        if ($kind === 'bounce') {
            return $this->provider_adapter->normalizeBounce($provider, $input);
        }

        if ($kind === 'connector') {
            $deal = is_array($fixture['deal'] ?? null) ? $fixture['deal'] : [];
            $deal['detail_url'] = $deal['detail_url'] ?? admin_url('deals/details/' . (int) ($deal['id'] ?? 0));
            $deal['channel_identifier'] = $input['channel_identifier'] ?? ($deal['channel_identifier'] ?? '');

            return $this->provider_adapter->formatConnectorPayload(
                $provider,
                $fixture['event_type'] ?? 'connector_test',
                $deal,
                is_array($fixture['context'] ?? null) ? $fixture['context'] : [],
                $fixture['text'] ?? 'Fixture connector payload'
            );
        }

        throw new RuntimeException('Unsupported fixture kind: ' . $kind);
    }

    protected function matches_expected_subset($actual, $expected)
    {
        if (is_array($expected)) {
            if (!is_array($actual)) {
                return false;
            }

            foreach ($expected as $key => $value) {
                if (is_int($key)) {
                    if (!array_key_exists($key, $actual) || !$this->matches_expected_subset($actual[$key], $value)) {
                        return false;
                    }
                    continue;
                }

                if (!array_key_exists($key, $actual) || !$this->matches_expected_subset($actual[$key], $value)) {
                    return false;
                }
            }

            return true;
        }

        return $actual === $expected;
    }

    protected function normalise_fixture_area($value)
    {
        $value = strtolower((string) $value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_');
    }
}
