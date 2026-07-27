<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Deals_automation_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('deals/deals_model', 'deals_model', true);
    }

    public function get_rules()
    {
        return $this->db->order_by('name', 'ASC')->get('tbl_deals_automation_rules')->result_array();
    }

    public function get_rule($id)
    {
        return $this->db->where('id', $id)->get('tbl_deals_automation_rules')->row();
    }

    public function get_queue($status = null, $limit = 100)
    {
        $this->db->select('tbl_deals_automation_queue.*, tbl_deals.title as deal_title, tbl_deals_automation_rules.name as rule_name, tbl_deals_campaigns.name as campaign_name', false);
        $this->db->from('tbl_deals_automation_queue');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_automation_queue.deal_id', 'left');
        $this->db->join('tbl_deals_automation_rules', 'tbl_deals_automation_rules.id = tbl_deals_automation_queue.rule_id', 'left');
        $this->db->join('tbl_deals_campaigns', 'tbl_deals_campaigns.id = tbl_deals_automation_queue.campaign_id', 'left');
        if ($status) {
            $this->db->where('tbl_deals_automation_queue.status', $status);
        }
        $this->db->order_by('tbl_deals_automation_queue.execute_at', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function get_campaign_steps($campaignId)
    {
        return $this->db->where('campaign_id', $campaignId)->order_by('sort_order', 'ASC')->get('tbl_deals_campaign_steps')->result_array();
    }

    public function get_campaign_messages($campaignId, $limit = 100)
    {
        $this->db->select('tbl_deals_campaign_messages.*, tbl_deals.title as deal_title, tbl_deals_campaign_steps.name as step_name, tbl_deals_email_preferences.is_unsubscribed', false);
        $this->db->from('tbl_deals_campaign_messages');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_campaign_messages.deal_id', 'left');
        $this->db->join('tbl_deals_campaign_steps', 'tbl_deals_campaign_steps.id = tbl_deals_campaign_messages.campaign_step_id', 'left');
        $this->db->join('tbl_deals_email_preferences', 'tbl_deals_email_preferences.email = tbl_deals_campaign_messages.recipient_email', 'left');
        $this->db->where('tbl_deals_campaign_messages.campaign_id', $campaignId);
        $this->db->order_by('tbl_deals_campaign_messages.created_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function save_rule($data, $id = null)
    {
        $payload = [
            'name' => $data['name'],
            'trigger_type' => $data['trigger_type'],
            'pipeline_id' => $data['pipeline_id'] ?: null,
            'stage_id' => $data['stage_id'] ?: null,
            'deal_status' => $data['deal_status'] ?: null,
            'offset_value' => (int) ($data['offset_value'] ?? 0),
            'offset_unit' => $data['offset_unit'] ?: 'hours',
            'conditions_json' => json_encode($data['conditions'] ?? []),
            'actions_json' => json_encode($data['actions'] ?? []),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        if ($id) {
            $this->db->where('id', $id)->update('tbl_deals_automation_rules', $payload);
            return $id;
        }

        $this->db->insert('tbl_deals_automation_rules', $payload);
        return $this->db->insert_id();
    }

    public function save_campaign_step($campaignId, $data, $id = null)
    {
        $payload = [
            'campaign_id' => $campaignId,
            'name' => $data['name'],
            'step_type' => $data['step_type'] ?: 'email',
            'delay_amount' => (int) ($data['delay_amount'] ?? 0),
            'delay_unit' => $data['delay_unit'] ?: 'days',
            'send_to' => $data['send_to'] ?: 'primary_contact',
            'email_subject' => $data['email_subject'] ?? '',
            'email_body' => $data['email_body'] ?? '',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        if ($id) {
            $this->db->where('id', $id)->update('tbl_deals_campaign_steps', $payload);
            return $id;
        }

        $this->db->insert('tbl_deals_campaign_steps', $payload);
        return $this->db->insert_id();
    }

    public function trigger_event_rules($eventType, $dealId, $context = [])
    {
        $deal = $this->db->where('id', $dealId)->get('tbl_deals')->row();
        if (!$deal) {
            return 0;
        }

        $this->db->where('is_active', 1);
        $this->db->where('trigger_type', $eventType);
        $rules = $this->db->get('tbl_deals_automation_rules')->result_array();
        $enqueued = 0;

        foreach ($rules as $rule) {
            if (!$this->rule_matches_deal($rule, $deal)) {
                continue;
            }

            $enqueued += $this->enqueue_rule_actions($rule, $deal, $context);
        }

        return $enqueued;
    }

    public function enqueue_campaign_steps($dealId, $campaignId, $referenceTime = null)
    {
        $deal = $this->db->where('id', $dealId)->get('tbl_deals')->row();
        if (!$deal || empty($campaignId)) {
            return 0;
        }

        $campaign = $this->db->where('id', $campaignId)->get('tbl_deals_campaigns')->row();
        if (!$campaign) {
            return 0;
        }

        $reference = $referenceTime ?: date('Y-m-d H:i:s');
        $steps = $this->get_campaign_steps($campaignId);
        $count = 0;

        foreach ($steps as $step) {
            if (empty($step['is_active'])) {
                continue;
            }

            $executeAt = $this->calculate_offset_datetime($reference, $step['delay_amount'], $step['delay_unit']);
            $payload = [
                'step_name' => $step['name'],
                'send_to' => $step['send_to'],
                'email_subject' => $step['email_subject'],
                'email_body' => $step['email_body'],
                'message' => $step['email_body'],
            ];

            $dedupeKey = 'campaign:' . $campaignId . ':step:' . $step['id'] . ':deal:' . $dealId;
            $queued = $this->enqueue_action([
                'rule_id' => null,
                'campaign_id' => $campaignId,
                'campaign_step_id' => $step['id'],
                'deal_id' => $dealId,
                'action_type' => $step['step_type'],
                'execute_at' => $executeAt,
                'payload' => json_encode($payload),
                'dedupe_key' => $dedupeKey,
            ]);

            $count += $queued ? 1 : 0;
        }

        return $count;
    }

    public function run_scheduled_automation($limit = 100)
    {
        $this->scan_time_based_rules();
        $this->process_queue($limit);
    }

    public function scan_time_based_rules()
    {
        $this->db->where('is_active', 1);
        $this->db->where_in('trigger_type', ['follow_up_overdue', 'inactivity_detected']);
        $rules = $this->db->get('tbl_deals_automation_rules')->result_array();

        foreach ($rules as $rule) {
            $deals = $this->get_deals_for_time_rule($rule);
            foreach ($deals as $deal) {
                $this->enqueue_rule_actions($rule, $deal, ['cron' => true]);
            }

            $this->db->where('id', $rule['id'])->update('tbl_deals_automation_rules', ['last_run_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function process_queue($limit = 100)
    {
        $this->db->where('status', 'pending');
        $this->db->where('execute_at <=', date('Y-m-d H:i:s'));
        $this->db->order_by('execute_at', 'ASC');
        $this->db->limit($limit);
        $items = $this->db->get('tbl_deals_automation_queue')->result_array();

        foreach ($items as $item) {
            try {
                $this->execute_queue_item($item);
                $this->db->where('id', $item['id'])->update('tbl_deals_automation_queue', [
                    'status' => 'processed',
                    'processed_at' => date('Y-m-d H:i:s'),
                    'attempts' => (int) $item['attempts'] + 1,
                    'last_error' => null,
                ]);
            } catch (Throwable $e) {
                $this->db->where('id', $item['id'])->update('tbl_deals_automation_queue', [
                    'status' => ((int) $item['attempts'] >= 2) ? 'failed' : 'pending',
                    'attempts' => (int) $item['attempts'] + 1,
                    'last_error' => $e->getMessage(),
                ]);
                log_activity('Deals automation failed: ' . $e->getMessage());
            }
        }
    }

    protected function execute_queue_item($item)
    {
        $deal = $this->db->where('id', $item['deal_id'])->get('tbl_deals')->row();
        if (!$deal) {
            throw new RuntimeException('Deal not found for automation queue item.');
        }

        $payload = json_decode($item['payload'], true) ?: [];
        switch ($item['action_type']) {
            case 'create_followup':
                $this->create_followup_from_payload($deal, $payload);
                break;
            case 'email':
            case 'queue_email':
                $this->send_automation_email($deal, $payload, $item);
                break;
            case 'add_activity':
                $message = $payload['message'] ?? 'Automation executed';
                $this->deals_model->log_deals_activity($deal->id, $message, false, serialize([$item['id']]));
                break;
            case 'update_health':
                $this->db->where('id', $deal->id)->update('tbl_deals', [
                    'health_status' => $payload['health_status'] ?? 'on_track',
                ]);
                $this->deals_model->log_deals_activity($deal->id, 'Deal health updated by automation', false, serialize([$payload['health_status'] ?? 'on_track']));
                break;
            default:
                throw new RuntimeException('Unsupported automation action: ' . $item['action_type']);
        }
    }

    protected function create_followup_from_payload($deal, $payload)
    {
        $followAt = $payload['follow_up_at'] ?? date('Y-m-d H:i:s');
        $data = [
            'deal_id' => $deal->id,
            'subject' => $payload['subject'] ?? ($deal->title . ' follow-up'),
            'description' => $payload['description'] ?? 'Created by automation rule.',
            'follow_up_at' => $followAt,
            'status' => 'pending',
            'type' => $payload['follow_up_type'] ?? 'call',
            'owner_id' => $payload['owner_id'] ?? ($deal->default_deal_owner ?: get_staff_user_id()),
        ];

        $this->db->insert('tbl_deals_followups', $data);
        $this->deals_model->sync_deal_metrics($deal->id, [
            'next_follow_up_at' => $followAt,
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);
        $this->deals_model->log_deals_activity($deal->id, 'Deal follow-up scheduled by automation', false, serialize([$data['subject'], $followAt]));
    }

    protected function send_automation_email($deal, $payload, $item)
    {
        $recipient = $this->resolve_recipient_email($deal, $payload['send_to'] ?? 'primary_contact');
        if (!$recipient) {
            throw new RuntimeException('No recipient email available for deal #' . $deal->id);
        }

        $preference = $this->ensure_email_preference($recipient);
        $trackingUrls = [
            '{unsubscribe_url}' => site_url('deals/track/unsubscribe/' . $preference['token']),
            '{deal_url}' => admin_url('deals/details/' . $deal->id),
        ];

        $subject = $this->replace_deal_tokens($payload['email_subject'] ?? ('Update on ' . $deal->title), $deal, $trackingUrls);
        $message = $this->replace_deal_tokens($payload['email_body'] ?? ('Deal update for ' . $deal->title), $deal, $trackingUrls);

        $messageRecord = $this->create_campaign_message($deal, $item, $recipient, $subject, $message, 'queued');
        if ($this->is_email_unsubscribed($recipient)) {
            $this->db->where('id', $messageRecord['id'])->update('tbl_deals_campaign_messages', [
                'status' => 'unsubscribed',
                'metadata_json' => json_encode(['skipped_reason' => 'recipient_unsubscribed']),
            ]);
            $this->deals_model->log_deals_activity($deal->id, 'Campaign email skipped because recipient is unsubscribed', true, serialize([$recipient]));
            return;
        }

        $message = $this->decorate_campaign_email($message, $messageRecord['message_token'], $preference['token']);

        $params = [
            'subject' => $subject,
            'message' => $message,
            'recipient' => $recipient,
            'cc' => '',
            'attachments' => [],
            'template' => null,
        ];

        $this->deals_model->send_email($params);

        $this->db->where('id', $messageRecord['id'])->update('tbl_deals_campaign_messages', [
            'status' => 'sent',
            'delivered_at' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($item['campaign_id'])) {
            $this->db->set('sent_count', 'sent_count + 1', false)
                ->where('id', $item['campaign_id'])
                ->update('tbl_deals_campaigns');
        }

        $this->db->insert('tbl_deals_email', [
            'email_to' => $recipient,
            'email_cc' => '',
            'deals_id' => $deal->id,
            'subject' => $subject,
            'message_body' => $message,
            'user_id' => 0,
            'files' => '',
            'uploaded_path' => '',
            'file_name' => '',
            'size' => 0,
            'ext' => '',
            'is_image' => 0,
            'message_time' => date('Y-m-d H:i:s'),
            'attach_file' => '',
            'email_from' => config_item('company_email'),
        ]);

        $this->deals_model->sync_deal_metrics($deal->id, [
            'last_contacted_at' => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);
        $this->deals_model->log_deals_activity($deal->id, 'Deal email sent by automation', false, serialize([$subject, $item['id'], $messageRecord['message_token']]));
    }

    protected function enqueue_rule_actions($rule, $deal, $context = [])
    {
        $actions = json_decode($rule['actions_json'], true) ?: [];
        $count = 0;

        foreach ($actions as $index => $action) {
            $executeAt = $this->calculate_offset_datetime(
                date('Y-m-d H:i:s'),
                $action['delay_value'] ?? 0,
                $action['delay_unit'] ?? 'hours'
            );

            $payload = $action;
            if (($action['type'] ?? '') === 'create_followup' && empty($payload['follow_up_at'])) {
                $payload['follow_up_at'] = $executeAt;
            }

            $dedupeParts = [
                'rule:' . $rule['id'],
                'deal:' . $deal->id,
                'action:' . ($action['type'] ?? 'unknown'),
                'context:' . md5(json_encode($context)),
            ];
            $dedupeKey = implode('|', $dedupeParts);

            $queued = $this->enqueue_action([
                'rule_id' => $rule['id'],
                'campaign_id' => null,
                'campaign_step_id' => null,
                'deal_id' => $deal->id,
                'action_type' => $action['type'] ?? 'add_activity',
                'execute_at' => $executeAt,
                'payload' => json_encode($payload),
                'dedupe_key' => $dedupeKey,
            ]);

            $count += $queued ? 1 : 0;
        }

        return $count;
    }

    public function enqueue_action($data)
    {
        if (!empty($data['dedupe_key'])) {
            $existing = $this->db->where('dedupe_key', $data['dedupe_key'])
                ->where_in('status', ['pending', 'processed'])
                ->get('tbl_deals_automation_queue')
                ->row();
            if ($existing) {
                return false;
            }
        }

        $payload = [
            'rule_id' => $data['rule_id'],
            'campaign_id' => $data['campaign_id'],
            'campaign_step_id' => $data['campaign_step_id'],
            'deal_id' => $data['deal_id'],
            'action_type' => $data['action_type'],
            'execute_at' => $data['execute_at'],
            'status' => 'pending',
            'payload' => $data['payload'],
            'dedupe_key' => $data['dedupe_key'] ?? null,
        ];

        $this->db->insert('tbl_deals_automation_queue', $payload);
        return $this->db->insert_id();
    }

    protected function get_deals_for_time_rule($rule)
    {
        $this->db->from('tbl_deals');
        $this->db->where('status', 'open');
        if (!$this->rule_pipeline_stage_filters($rule)) {
            return [];
        }

        $now = date('Y-m-d H:i:s');
        if ($rule['trigger_type'] === 'follow_up_overdue') {
            $this->db->where('next_follow_up_at IS NOT NULL', null, false);
            $this->db->where('next_follow_up_at <', $now);
        }

        if ($rule['trigger_type'] === 'inactivity_detected') {
            $threshold = $this->calculate_offset_datetime($now, -1 * (int) $rule['offset_value'], $rule['offset_unit'] ?: 'hours');
            $this->db->where('last_activity_at IS NOT NULL', null, false);
            $this->db->where('last_activity_at <=', $threshold);
        }

        return $this->db->get()->result();
    }

    protected function rule_matches_deal($rule, $deal)
    {
        if (!empty($rule['pipeline_id']) && (int) $rule['pipeline_id'] !== (int) $deal->pipeline_id) {
            return false;
        }

        if (!empty($rule['stage_id']) && (int) $rule['stage_id'] !== (int) $deal->stage_id) {
            return false;
        }

        if (!empty($rule['deal_status']) && $rule['deal_status'] !== $deal->status) {
            return false;
        }

        return true;
    }

    protected function rule_pipeline_stage_filters($rule)
    {
        if (!empty($rule['pipeline_id'])) {
            $this->db->where('pipeline_id', $rule['pipeline_id']);
        }

        if (!empty($rule['stage_id'])) {
            $this->db->where('stage_id', $rule['stage_id']);
        }

        if (!empty($rule['deal_status'])) {
            $this->db->where('status', $rule['deal_status']);
        }

        return true;
    }

    protected function calculate_offset_datetime($reference, $amount, $unit)
    {
        $amount = (int) $amount;
        $unit = strtolower($unit ?: 'hours');
        return date('Y-m-d H:i:s', strtotime(($amount >= 0 ? '+' : '') . $amount . ' ' . $unit, strtotime($reference)));
    }

    protected function resolve_recipient_email($deal, $sendTo = 'primary_contact')
    {
        if ($sendTo === 'deal_owner') {
            $owner = $this->db->where('staffid', $deal->default_deal_owner)->get(db_prefix() . 'staff')->row();
            return $owner ? $owner->email : null;
        }

        $clientIds = !empty($deal->client_id) ? json_decode($deal->client_id, true) : [];
        if (!empty($clientIds)) {
            $this->db->select('email');
            $this->db->from(db_prefix() . 'contacts');
            $this->db->where_in('userid', $clientIds);
            $this->db->order_by('is_primary', 'DESC');
            $contact = $this->db->get()->row();
            if ($contact && !empty($contact->email)) {
                return $contact->email;
            }
        }

        if (!empty($deal->rel_type) && $deal->rel_type === 'lead' && !empty($deal->rel_id)) {
            $lead = $this->db->select('email')->where('id', $deal->rel_id)->get(db_prefix() . 'leads')->row();
            return $lead ? $lead->email : null;
        }

        return config_item('company_email');
    }

    public function track_open($token)
    {
        $message = $this->db->where('message_token', $token)->get('tbl_deals_campaign_messages')->row_array();
        if (!$message) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $isFirstOpen = empty($message['first_opened_at']);
        $update = [
            'last_opened_at' => $now,
        ];
        if ($message['status'] !== 'clicked') {
            $update['status'] = 'opened';
        }
        if ($isFirstOpen) {
            $update['first_opened_at'] = $now;
        }

        $this->db->set('open_count', 'open_count + 1', false)
            ->where('id', $message['id'])
            ->update('tbl_deals_campaign_messages', $update);

        if ($isFirstOpen && !empty($message['campaign_id'])) {
            $this->db->set('open_count', 'open_count + 1', false)
                ->where('id', $message['campaign_id'])
                ->update('tbl_deals_campaigns');
            $this->deals_model->log_deals_activity($message['deal_id'], 'Campaign email opened', true, serialize([$message['campaign_id'], $message['id']]));
        }

        return true;
    }

    public function track_click($token, $encodedUrl = null)
    {
        $message = $this->db->where('message_token', $token)->get('tbl_deals_campaign_messages')->row_array();
        $destination = $this->decode_tracking_url($encodedUrl);

        if (!$message) {
            return $destination ?: site_url();
        }

        $now = date('Y-m-d H:i:s');
        $isFirstClick = empty($message['first_clicked_at']);

        $this->db->set('click_count', 'click_count + 1', false)
            ->set('status', 'clicked')
            ->set('last_clicked_at', $now)
            ->where('id', $message['id']);

        if ($isFirstClick) {
            $this->db->set('first_clicked_at', $now);
        }

        $this->db->update('tbl_deals_campaign_messages');

        if ($isFirstClick && !empty($message['campaign_id'])) {
            $this->db->set('click_count', 'click_count + 1', false)
                ->where('id', $message['campaign_id'])
                ->update('tbl_deals_campaigns');
            $this->deals_model->log_deals_activity($message['deal_id'], 'Campaign email link clicked', true, serialize([$message['campaign_id'], $message['id'], $destination]));
        }

        return $destination ?: site_url();
    }

    public function unsubscribe_by_token($token)
    {
        $preference = $this->db->where('token', $token)->get('tbl_deals_email_preferences')->row_array();
        if (!$preference) {
            return false;
        }

        $this->db->where('id', $preference['id'])->update('tbl_deals_email_preferences', [
            'is_unsubscribed' => 1,
            'unsubscribed_at' => date('Y-m-d H:i:s'),
        ]);

        return $preference['email'];
    }

    protected function ensure_email_preference($email)
    {
        $normalizedEmail = strtolower(trim($email));
        $preference = $this->db->where('email', $normalizedEmail)->get('tbl_deals_email_preferences')->row_array();

        if ($preference) {
            if (empty($preference['token'])) {
                $token = hash('sha256', $normalizedEmail . microtime(true) . mt_rand());
                $this->db->where('id', $preference['id'])->update('tbl_deals_email_preferences', ['token' => $token]);
                $preference['token'] = $token;
            }

            return $preference;
        }

        $preference = [
            'email' => $normalizedEmail,
            'token' => hash('sha256', $normalizedEmail . microtime(true) . mt_rand()),
            'is_unsubscribed' => 0,
            'unsubscribed_at' => null,
        ];

        $this->db->insert('tbl_deals_email_preferences', $preference);
        $preference['id'] = $this->db->insert_id();

        return $preference;
    }

    protected function is_email_unsubscribed($email)
    {
        $normalizedEmail = strtolower(trim($email));
        $preference = $this->db->select('is_unsubscribed')
            ->where('email', $normalizedEmail)
            ->get('tbl_deals_email_preferences')
            ->row_array();

        return !empty($preference['is_unsubscribed']);
    }

    protected function create_campaign_message($deal, $item, $recipient, $subject, $message, $status = 'queued')
    {
        $record = [
            'message_token' => hash('sha256', $deal->id . '|' . $recipient . '|' . microtime(true) . '|' . mt_rand()),
            'campaign_id' => $item['campaign_id'] ?? null,
            'campaign_step_id' => $item['campaign_step_id'] ?? null,
            'deal_id' => $deal->id,
            'recipient_email' => strtolower(trim($recipient)),
            'subject' => $subject,
            'status' => $status,
            'metadata_json' => json_encode([
                'queue_id' => $item['id'] ?? null,
                'action_type' => $item['action_type'] ?? null,
                'message_preview' => substr(strip_tags($message), 0, 500),
            ]),
        ];

        $this->db->insert('tbl_deals_campaign_messages', $record);
        $record['id'] = $this->db->insert_id();

        return $record;
    }

    protected function decorate_campaign_email($message, $messageToken, $preferenceToken)
    {
        $message = $this->rewrite_tracking_links($message, $messageToken);
        $openUrl = site_url('deals/track/open/' . $messageToken);
        $unsubscribeUrl = site_url('deals/track/unsubscribe/' . $preferenceToken);
        $footer = '<p style="margin-top:24px;font-size:12px;color:#6b7280;">If you no longer want these updates, <a href="' . $unsubscribeUrl . '">unsubscribe here</a>.</p>';
        $pixel = '<img src="' . $openUrl . '" alt="" width="1" height="1" style="display:block;border:0;outline:none;text-decoration:none;" />';

        if (stripos($message, '</body>') !== false) {
            return str_ireplace('</body>', $footer . $pixel . '</body>', $message);
        }

        return $message . $footer . $pixel;
    }

    protected function rewrite_tracking_links($message, $messageToken)
    {
        return preg_replace_callback('/(href\s*=\s*)(["\'])(.*?)\2/i', function ($matches) use ($messageToken) {
            $originalUrl = trim($matches[3]);
            if ($originalUrl === '' || $originalUrl[0] === '#' || stripos($originalUrl, 'mailto:') === 0 || stripos($originalUrl, 'tel:') === 0 || stripos($originalUrl, 'javascript:') === 0 || strpos($originalUrl, '/deals/track/click/') !== false) {
                return $matches[0];
            }

            $encodedUrl = rtrim(strtr(base64_encode($originalUrl), '+/', '-_'), '=');
            $trackingUrl = site_url('deals/track/click/' . $messageToken . '?u=' . rawurlencode($encodedUrl));

            return $matches[1] . $matches[2] . $trackingUrl . $matches[2];
        }, $message);
    }

    protected function decode_tracking_url($encodedUrl = null)
    {
        if (empty($encodedUrl)) {
            return site_url();
        }

        $normalized = strtr($encodedUrl, '-_', '+/');
        $padding = strlen($normalized) % 4;
        if ($padding !== 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);
        if ($decoded === false || $decoded === '') {
            return site_url();
        }

        if (preg_match('#^https?://#i', $decoded) || stripos($decoded, 'mailto:') === 0 || stripos($decoded, 'tel:') === 0) {
            return $decoded;
        }

        if (strpos($decoded, '/') === 0) {
            return site_url(ltrim($decoded, '/'));
        }

        return site_url($decoded);
    }

    protected function replace_deal_tokens($value, $deal, $extra = [])
    {
        $ownerName = '';
        if (!empty($deal->default_deal_owner)) {
            $owner = $this->db->select('CONCAT(firstname, " ", lastname) as full_name', false)
                ->where('staffid', $deal->default_deal_owner)
                ->get(db_prefix() . 'staff')
                ->row();
            $ownerName = $owner ? $owner->full_name : '';
        }

        $pipelineName = '';
        if (!empty($deal->pipeline_id)) {
            $pipeline = $this->db->select('pipeline_name')->where('pipeline_id', $deal->pipeline_id)->get('tbl_deals_pipelines')->row();
            $pipelineName = $pipeline ? $pipeline->pipeline_name : '';
        }

        $stageName = '';
        if (!empty($deal->stage_id)) {
            $stage = $this->db->select('stage_name')->where('stage_id', $deal->stage_id)->get('tbl_deals_stages')->row();
            $stageName = $stage ? $stage->stage_name : '';
        }

        $replacements = [
            '{deal_title}' => $deal->title,
            '{deal_value}' => number_format((float) $deal->deal_value, 2, '.', ''),
            '{deal_status}' => $deal->status,
            '{next_follow_up_at}' => !empty($deal->next_follow_up_at) ? $deal->next_follow_up_at : '',
            '{expected_revenue}' => number_format((float) ($deal->expected_revenue ?? 0), 2, '.', ''),
            '{probability}' => number_format((float) ($deal->probability ?? 0), 2, '.', ''),
            '{deal_priority}' => $deal->priority ?? '',
            '{deal_health}' => $deal->health_status ?? '',
            '{deal_owner_name}' => $ownerName,
            '{pipeline_name}' => $pipelineName,
            '{stage_name}' => $stageName,
            '{company_name}' => get_option('companyname'),
            '{deal_url}' => admin_url('deals/details/' . $deal->id),
        ];

        return strtr($value, array_merge($replacements, $extra));
    }
}
