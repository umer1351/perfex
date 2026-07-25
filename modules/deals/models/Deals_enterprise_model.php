<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Deals_enterprise_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('deals/deals_model', 'deals_model', true);
        $this->load->model('deals/deals_comms_model', 'deals_comms_model', true);
    }

    public function get_deal_approvals($dealId)
    {
        if (!$this->db->table_exists('tbl_deals_approvals')) {
            return [];
        }

        $select = [
            'tbl_deals_approvals.*',
            'CONCAT(req.firstname, " ", req.lastname) as requested_by_name',
            'CONCAT(app.firstname, " ", app.lastname) as approver_name',
        ];

        if ($this->db->table_exists('tbl_deals_approval_policies') && $this->db->field_exists('policy_id', 'tbl_deals_approvals')) {
            $select[] = 'tbl_deals_approval_policies.name as policy_name';
        }

        $this->db->select(implode(', ', $select), false);
        $this->db->from('tbl_deals_approvals');
        $this->db->join(db_prefix() . 'staff as req', 'req.staffid = tbl_deals_approvals.requested_by', 'left');
        $this->db->join(db_prefix() . 'staff as app', 'app.staffid = tbl_deals_approvals.assigned_to', 'left');

        if ($this->db->table_exists('tbl_deals_approval_policies') && $this->db->field_exists('policy_id', 'tbl_deals_approvals')) {
            $this->db->join('tbl_deals_approval_policies', 'tbl_deals_approval_policies.id = tbl_deals_approvals.policy_id', 'left');
        }

        $this->db->where('tbl_deals_approvals.deal_id', $dealId);
        $this->db->order_by('tbl_deals_approvals.requested_at', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_pending_approval_count($dealId)
    {
        if (!$this->db->table_exists('tbl_deals_approvals')) {
            return 0;
        }

        return (int) $this->db
            ->where('deal_id', $dealId)
            ->where('status', 'pending')
            ->count_all_results('tbl_deals_approvals');
    }

    public function create_approval_request($dealId, $data)
    {
        if (!$this->db->table_exists('tbl_deals_approvals')) {
            return false;
        }

        $payload = [
            'deal_id' => $dealId,
            'approval_type' => !empty($data['approval_type']) ? $data['approval_type'] : 'custom',
            'title' => !empty($data['title']) ? $data['title'] : 'Deal approval',
            'requested_by' => get_staff_user_id(),
            'assigned_to' => !empty($data['assigned_to']) ? $data['assigned_to'] : null,
            'status' => 'pending',
            'priority' => !empty($data['priority']) ? $data['priority'] : 'medium',
            'notes' => $data['notes'] ?? null,
            'due_at' => !empty($data['due_at']) ? $data['due_at'] : null,
            'metadata_json' => json_encode($data['metadata'] ?? []),
        ];

        if ($this->db->field_exists('policy_id', 'tbl_deals_approvals')) {
            $payload['policy_id'] = !empty($data['policy_id']) ? (int) $data['policy_id'] : null;
        }

        $this->db->insert('tbl_deals_approvals', $payload);
        $approvalId = $this->db->insert_id();

        $this->deals_model->log_deals_activity(
            $dealId,
            'Deal approval requested',
            false,
            serialize([$payload['approval_type'], $payload['title'], $payload['assigned_to'], $payload['policy_id'] ?? null])
        );
        $this->dispatch_event_webhooks('approval_requested', $dealId, [
            'approval_id' => $approvalId,
            'approval_type' => $payload['approval_type'],
            'policy_id' => $payload['policy_id'] ?? null,
        ]);
        $this->deals_comms_model->dispatch_event_connectors('approval_requested', $dealId, [
            'approval_id' => $approvalId,
            'approval_type' => $payload['approval_type'],
            'policy_id' => $payload['policy_id'] ?? null,
        ]);

        return $approvalId;
    }

    public function get_approval_request($dealId, $approvalId)
    {
        if (!$this->db->table_exists('tbl_deals_approvals')) {
            return null;
        }

        return $this->db
            ->where('id', $approvalId)
            ->where('deal_id', $dealId)
            ->get('tbl_deals_approvals')
            ->row_array();
    }

    public function save_approval_request($dealId, $data, $approvalId = null)
    {
        if (!$this->db->table_exists('tbl_deals_approvals')) {
            return false;
        }

        $payload = [
            'approval_type' => !empty($data['approval_type']) ? $data['approval_type'] : 'custom',
            'title' => !empty($data['title']) ? $data['title'] : 'Deal approval',
            'assigned_to' => !empty($data['assigned_to']) ? $data['assigned_to'] : null,
            'priority' => !empty($data['priority']) ? $data['priority'] : 'medium',
            'notes' => $data['notes'] ?? null,
            'due_at' => !empty($data['due_at']) ? $data['due_at'] : null,
        ];

        if ($approvalId) {
            $existing = $this->get_approval_request($dealId, $approvalId);
            if (!$existing || ($existing['status'] ?? 'pending') !== 'pending') {
                return false;
            }

            $this->db->where('id', $approvalId)->update('tbl_deals_approvals', $payload);
            $this->deals_model->log_deals_activity(
                $dealId,
                'Deal approval updated',
                false,
                serialize([$approvalId, $payload['approval_type'], $payload['title'], $payload['assigned_to']])
            );
            $this->dispatch_event_webhooks('approval_updated', $dealId, [
                'approval_id' => $approvalId,
                'approval_type' => $payload['approval_type'],
                'policy_id' => $existing['policy_id'] ?? null,
            ]);
            $this->deals_comms_model->dispatch_event_connectors('approval_updated', $dealId, [
                'approval_id' => $approvalId,
                'approval_type' => $payload['approval_type'],
                'policy_id' => $existing['policy_id'] ?? null,
            ]);

            return $approvalId;
        }

        return $this->create_approval_request($dealId, $data);
    }

    public function approve_request($dealId, $approvalId, $comment = '')
    {
        if (!$this->db->table_exists('tbl_deals_approvals')) {
            return false;
        }

        $approval = $this->db->where('id', $approvalId)->where('deal_id', $dealId)->get('tbl_deals_approvals')->row();
        if (!$approval || $approval->status !== 'pending' || !$this->can_current_user_decide_approval($approval)) {
            return false;
        }

        $update = [
            'status' => 'approved',
            'decision_notes' => $comment ?: null,
            'decided_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('decided_by', 'tbl_deals_approvals')) {
            $update['decided_by'] = get_staff_user_id();
        }

        $this->db->where('id', $approvalId)->update('tbl_deals_approvals', $update);

        $this->deals_model->log_deals_activity($dealId, 'Deal approval approved', false, serialize([$approvalId, get_staff_user_id()]));
        $this->dispatch_event_webhooks('approval_approved', $dealId, [
            'approval_id' => $approvalId,
            'approval_type' => $approval->approval_type,
            'policy_id' => $approval->policy_id ?? null,
        ]);
        $this->deals_comms_model->dispatch_event_connectors('approval_approved', $dealId, [
            'approval_id' => $approvalId,
            'approval_type' => $approval->approval_type,
            'policy_id' => $approval->policy_id ?? null,
        ]);

        return true;
    }

    public function reject_request($dealId, $approvalId, $comment = '')
    {
        if (!$this->db->table_exists('tbl_deals_approvals')) {
            return false;
        }

        $approval = $this->db->where('id', $approvalId)->where('deal_id', $dealId)->get('tbl_deals_approvals')->row();
        if (!$approval || $approval->status !== 'pending' || !$this->can_current_user_decide_approval($approval)) {
            return false;
        }

        $update = [
            'status' => 'rejected',
            'decision_notes' => $comment ?: null,
            'decided_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('decided_by', 'tbl_deals_approvals')) {
            $update['decided_by'] = get_staff_user_id();
        }

        $this->db->where('id', $approvalId)->update('tbl_deals_approvals', $update);

        $this->deals_model->log_deals_activity($dealId, 'Deal approval rejected', false, serialize([$approvalId, get_staff_user_id()]));
        $this->dispatch_event_webhooks('approval_rejected', $dealId, [
            'approval_id' => $approvalId,
            'approval_type' => $approval->approval_type,
            'policy_id' => $approval->policy_id ?? null,
        ]);
        $this->deals_comms_model->dispatch_event_connectors('approval_rejected', $dealId, [
            'approval_id' => $approvalId,
            'approval_type' => $approval->approval_type,
            'policy_id' => $approval->policy_id ?? null,
        ]);

        return true;
    }

    public function has_pending_approval_type($dealId, $approvalType)
    {
        if (!$this->db->table_exists('tbl_deals_approvals')) {
            return false;
        }

        return $this->db
            ->where('deal_id', $dealId)
            ->where('approval_type', $approvalType)
            ->where('status', 'pending')
            ->count_all_results('tbl_deals_approvals') > 0;
    }

    public function get_approval_policies()
    {
        if (!$this->db->table_exists('tbl_deals_approval_policies')) {
            return [];
        }

        $this->db->select('tbl_deals_approval_policies.*, CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as approver_name, tbl_deals_pipelines.pipeline_name, tbl_deals_stages.stage_name', false);
        $this->db->from('tbl_deals_approval_policies');
        $this->db->join(db_prefix() . 'staff', 'tblstaff.staffid = tbl_deals_approval_policies.assigned_to', 'left');
        $this->db->join('tbl_deals_pipelines', 'tbl_deals_pipelines.pipeline_id = tbl_deals_approval_policies.pipeline_id', 'left');
        $this->db->join('tbl_deals_stages', 'tbl_deals_stages.stage_id = tbl_deals_approval_policies.stage_id', 'left');
        $this->db->order_by('tbl_deals_approval_policies.name', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_approval_policy($id)
    {
        if (!$this->db->table_exists('tbl_deals_approval_policies')) {
            return null;
        }

        return $this->db->where('id', $id)->get('tbl_deals_approval_policies')->row_array();
    }

    public function save_approval_policy($data, $id = null)
    {
        if (!$this->db->table_exists('tbl_deals_approval_policies')) {
            return false;
        }

        $payload = [
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'approval_type' => $data['approval_type'] ?: 'custom',
            'title_template' => $data['title_template'],
            'assigned_to' => !empty($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            'priority' => $data['priority'] ?: 'high',
            'pipeline_id' => !empty($data['pipeline_id']) ? (int) $data['pipeline_id'] : null,
            'stage_id' => !empty($data['stage_id']) ? (int) $data['stage_id'] : null,
            'min_deal_value' => $data['min_deal_value'] !== '' ? (float) $data['min_deal_value'] : null,
            'health_status' => !empty($data['health_status']) ? $data['health_status'] : null,
            'due_in_hours' => max((int) ($data['due_in_hours'] ?? 24), 1),
            'auto_create' => !empty($data['auto_create']) ? 1 : 0,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        if ($id) {
            $this->db->where('id', $id)->update('tbl_deals_approval_policies', $payload);
            return $id;
        }

        $this->db->insert('tbl_deals_approval_policies', $payload);
        return $this->db->insert_id();
    }

    public function delete_approval_policy($id)
    {
        if (!$this->db->table_exists('tbl_deals_approval_policies')) {
            return false;
        }

        return $this->db->where('id', $id)->delete('tbl_deals_approval_policies');
    }

    public function ensure_policy_approvals($dealId, $triggerEvent, $context = [])
    {
        $results = [
            'matched' => 0,
            'created' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'policies' => [],
        ];

        if (!$this->db->table_exists('tbl_deals_approval_policies') || !$this->db->table_exists('tbl_deals_approvals')) {
            return $results;
        }

        $deal = $this->deals_model->dealInfo($dealId);
        if (!$deal) {
            return $results;
        }

        $policies = $this->get_matching_approval_policies($deal, $triggerEvent, $context);
        $results['matched'] = count($policies);

        foreach ($policies as $policy) {
            $state = $this->get_policy_approval_state($dealId, $policy['id']);
            $results['policies'][] = [
                'id' => $policy['id'],
                'name' => $policy['name'],
                'state' => $state ?: 'missing',
            ];

            if ($state === 'approved') {
                $results['approved']++;
                continue;
            }

            if ($state === 'pending') {
                $results['pending']++;
                continue;
            }

            if ($state === 'rejected') {
                $results['rejected']++;
                continue;
            }

            if (!empty($policy['auto_create'])) {
                $approvalId = $this->create_approval_request($dealId, [
                    'approval_type' => $policy['approval_type'],
                    'title' => $this->format_policy_title($policy, $deal),
                    'assigned_to' => $policy['assigned_to'],
                    'priority' => $policy['priority'],
                    'notes' => 'Auto-created from approval policy: ' . $policy['name'],
                    'due_at' => date('Y-m-d H:i:s', strtotime('+' . (int) $policy['due_in_hours'] . ' hours')),
                    'policy_id' => $policy['id'],
                    'metadata' => [
                        'policy_id' => $policy['id'],
                        'policy_name' => $policy['name'],
                        'trigger_event' => $triggerEvent,
                        'context' => $context,
                    ],
                ]);

                if ($approvalId) {
                    $results['created']++;
                    $results['pending']++;
                }
            }
        }

        return $results;
    }

    public function get_webhooks()
    {
        if (!$this->db->table_exists('tbl_deals_webhooks')) {
            return [];
        }

        return $this->db->order_by('name', 'ASC')->get('tbl_deals_webhooks')->result_array();
    }

    public function get_webhook($id)
    {
        if (!$this->db->table_exists('tbl_deals_webhooks')) {
            return null;
        }

        return $this->db->where('id', $id)->get('tbl_deals_webhooks')->row_array();
    }

    public function get_recent_webhook_logs($limit = 100)
    {
        if (!$this->db->table_exists('tbl_deals_webhook_logs')) {
            return [];
        }

        $this->db->select('tbl_deals_webhook_logs.*, tbl_deals_webhooks.name as webhook_name, tbl_deals.title as deal_title', false);
        $this->db->from('tbl_deals_webhook_logs');
        $this->db->join('tbl_deals_webhooks', 'tbl_deals_webhooks.id = tbl_deals_webhook_logs.webhook_id', 'left');
        $this->db->join('tbl_deals', 'tbl_deals.id = tbl_deals_webhook_logs.deal_id', 'left');
        $this->db->order_by('tbl_deals_webhook_logs.attempted_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function save_webhook($data, $id = null)
    {
        if (!$this->db->table_exists('tbl_deals_webhooks')) {
            return false;
        }

        $payload = [
            'name' => $data['name'],
            'endpoint_url' => $data['endpoint_url'],
            'secret_key' => !empty($data['secret_key']) ? $data['secret_key'] : null,
            'trigger_events_json' => json_encode($this->normalise_events($data['trigger_events'] ?? [])),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        if ($id) {
            $this->db->where('id', $id)->update('tbl_deals_webhooks', $payload);
            return $id;
        }

        $this->db->insert('tbl_deals_webhooks', $payload);
        return $this->db->insert_id();
    }

    public function delete_webhook($id)
    {
        if (!$this->db->table_exists('tbl_deals_webhooks')) {
            return false;
        }

        if ($this->db->table_exists('tbl_deals_webhook_logs')) {
            $this->db->where('webhook_id', $id)->delete('tbl_deals_webhook_logs');
        }

        return $this->db->where('id', $id)->delete('tbl_deals_webhooks');
    }

    public function retry_webhook_log($logId)
    {
        if (!$this->db->table_exists('tbl_deals_webhook_logs') || !$this->db->table_exists('tbl_deals_webhooks')) {
            return false;
        }

        $log = $this->db->where('id', $logId)->get('tbl_deals_webhook_logs')->row_array();
        if (!$log) {
            return false;
        }

        $webhook = $this->get_webhook($log['webhook_id']);
        if (!$webhook || empty($webhook['is_active'])) {
            return false;
        }

        $deal = !empty($log['deal_id']) ? $this->deals_model->dealInfo($log['deal_id']) : null;
        if (!$deal) {
            return false;
        }

        $payload = json_decode($log['request_payload'] ?? '[]', true);

        return $this->send_webhook($webhook, $log['event_type'], $deal, $payload['context'] ?? [], [
            'is_test' => !empty($log['is_test']),
            'retry_of_log_id' => $logId,
        ]);
    }

    public function test_webhook($webhookId, $dealId = null)
    {
        $webhook = $this->get_webhook($webhookId);
        if (!$webhook) {
            return false;
        }

        $deal = null;
        if (!empty($dealId)) {
            $deal = $this->deals_model->dealInfo($dealId);
        }

        if (!$deal) {
            $deal = $this->db->order_by('id', 'DESC')->get('tbl_deals', 1)->row();
            if ($deal) {
                $deal = $this->deals_model->dealInfo($deal->id);
            }
        }

        if (!$deal) {
            return false;
        }

        return $this->send_webhook($webhook, 'webhook_test', $deal, [
            'mode' => 'manual_test',
            'requested_by' => get_staff_user_id(),
        ], ['is_test' => true]);
    }

    public function dispatch_event_webhooks($eventType, $dealId, $context = [])
    {
        if (!$this->db->table_exists('tbl_deals_webhooks') || !$this->db->table_exists('tbl_deals_webhook_logs')) {
            return 0;
        }

        $webhooks = $this->get_active_webhooks_for_event($eventType);
        if (empty($webhooks)) {
            return 0;
        }

        $deal = $this->deals_model->dealInfo($dealId);
        if (!$deal) {
            return 0;
        }

        $count = 0;
        foreach ($webhooks as $webhook) {
            $count += $this->send_webhook($webhook, $eventType, $deal, $context) ? 1 : 0;
        }

        return $count;
    }

    public function get_deal_intelligence($dealId)
    {
        $deal = $this->deals_model->dealInfo($dealId);
        if (!$deal) {
            return [];
        }

        $pendingApprovals = $this->get_pending_approval_count($dealId);
        $nextFollowupTs = !empty($deal->next_follow_up_at) ? strtotime($deal->next_follow_up_at) : null;
        $lastActivityTs = !empty($deal->last_activity_at) ? strtotime($deal->last_activity_at) : strtotime($deal->created_at);
        $daysInactive = $lastActivityTs ? max(0, floor((time() - $lastActivityTs) / 86400)) : 0;
        $overdueFollowup = $nextFollowupTs && $nextFollowupTs < time();
        $daysToClose = !empty($deal->days_to_close) ? max(0, floor((strtotime($deal->days_to_close) - time()) / 86400)) : null;
        $noOwner = empty($deal->default_deal_owner);
        $noFollowup = ($deal->status ?? '') === 'open' && empty($deal->next_follow_up_at);

        $riskScore = 0;
        $signals = [];
        $recommendations = [];

        if (($deal->health_status ?? '') === 'off_track') {
            $riskScore += 40;
            $signals[] = 'Deal health is off track.';
            $recommendations[] = 'Escalate the opportunity and realign the account plan.';
        } elseif (($deal->health_status ?? '') === 'at_risk') {
            $riskScore += 30;
            $signals[] = 'Deal health is marked at risk.';
            $recommendations[] = 'Review blockers and create a recovery plan.';
        }

        if (($deal->priority ?? '') === 'high') {
            $riskScore += 10;
            $signals[] = 'This is a high-priority opportunity.';
        }

        if ($overdueFollowup) {
            $riskScore += 25;
            $signals[] = 'Next follow-up is overdue.';
            $recommendations[] = 'Complete or reschedule the overdue follow-up immediately.';
        }

        if ($noFollowup) {
            $riskScore += 20;
            $signals[] = 'There is no next follow-up scheduled.';
            $recommendations[] = 'Create a next-step follow-up so the deal does not stall.';
        }

        if ($daysInactive >= 7) {
            $riskScore += 15;
            $signals[] = 'No logged activity for ' . $daysInactive . ' days.';
            $recommendations[] = 'Re-engage the deal owner or assign a next action.';
        }

        if (!empty($daysToClose) && $daysToClose <= 7 && ($deal->status ?? '') === 'open') {
            $riskScore += 10;
            $signals[] = 'Expected close date is within 7 days.';
            $recommendations[] = 'Confirm forecast confidence and unresolved objections.';
        }

        if ($pendingApprovals > 0) {
            $riskScore += 10;
            $signals[] = $pendingApprovals . ' approval request(s) are waiting.';
            $recommendations[] = 'Follow up with approvers to remove process delay.';
        }

        if ($noOwner) {
            $riskScore += 15;
            $signals[] = 'The deal does not have an assigned owner.';
            $recommendations[] = 'Assign a deal owner for accountability and follow-through.';
        }

        if ((float) ($deal->probability ?? 0) < 40 && ($deal->status ?? '') === 'open') {
            $riskScore += 10;
            $signals[] = 'Probability is below 40%.';
            $recommendations[] = 'Review qualification and opportunity fit.';
        }

        $riskScore = min($riskScore, 100);
        if ($riskScore >= 70) {
            $riskBand = 'critical';
        } elseif ($riskScore >= 40) {
            $riskBand = 'elevated';
        } else {
            $riskBand = 'healthy';
        }

        return [
            'risk_score' => $riskScore,
            'risk_band' => $riskBand,
            'days_inactive' => $daysInactive,
            'pending_approvals' => $pendingApprovals,
            'overdue_followup' => (bool) $overdueFollowup,
            'days_to_close' => $daysToClose,
            'no_owner' => (bool) $noOwner,
            'no_followup' => (bool) $noFollowup,
            'signals' => array_values(array_unique($signals)),
            'recommendations' => array_values(array_unique($recommendations)),
        ];
    }

    public function get_attention_queue($limit = 12)
    {
        $pendingApprovalSelect = $this->db->table_exists('tbl_deals_approvals')
            ? '(SELECT COUNT(*) FROM tbl_deals_approvals a WHERE a.deal_id = tbl_deals.id AND a.status = "pending")'
            : '0';

        $attentionScore = "(
            (CASE WHEN tbl_deals.status = 'open' THEN 10 ELSE 0 END) +
            (CASE WHEN tbl_deals.next_follow_up_at IS NULL AND tbl_deals.status = 'open' THEN 15 ELSE 0 END) +
            (CASE WHEN tbl_deals.next_follow_up_at IS NOT NULL AND tbl_deals.next_follow_up_at < NOW() AND tbl_deals.status = 'open' THEN 25 ELSE 0 END) +
            (CASE WHEN DATEDIFF(CURDATE(), DATE(COALESCE(tbl_deals.last_activity_at, tbl_deals.created_at))) >= 7 THEN 15 ELSE 0 END) +
            (CASE WHEN tbl_deals.default_deal_owner IS NULL OR tbl_deals.default_deal_owner = 0 THEN 10 ELSE 0 END) +
            (CASE WHEN tbl_deals.health_status IN ('at_risk', 'off_track') THEN 20 ELSE 0 END) +
            (({$pendingApprovalSelect}) * 8)
        )";

        $this->db->select('tbl_deals.id, tbl_deals.title, tbl_deals.status, tbl_deals.deal_value, tbl_deals.next_follow_up_at, tbl_deals.health_status, tbl_deals.priority, tbl_deals.last_activity_at, tbl_deals.created_at, tbl_deals_stages.stage_name, CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as owner_name, ' . $pendingApprovalSelect . ' as pending_approvals, DATEDIFF(CURDATE(), DATE(COALESCE(tbl_deals.last_activity_at, tbl_deals.created_at))) as inactive_days, ' . $attentionScore . ' as attention_score', false);
        $this->db->from('tbl_deals');
        $this->db->join('tbl_deals_stages', 'tbl_deals_stages.stage_id = tbl_deals.stage_id', 'left');
        $this->db->join(db_prefix() . 'staff', 'tblstaff.staffid = tbl_deals.default_deal_owner', 'left');
        $this->db->where('tbl_deals.status', 'open');
        $this->db->having('attention_score >', 0);
        $this->db->order_by('attention_score', 'DESC');
        $this->db->order_by('tbl_deals.deal_value', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function get_sla_summary()
    {
        $summary = [
            'no_owner_open' => 0,
            'no_followup_open' => 0,
            'inactive_7_plus' => 0,
            'inactive_14_plus' => 0,
            'overdue_followups' => 0,
            'at_risk_open' => 0,
            'pending_close_approvals' => 0,
        ];

        $summary['no_owner_open'] = (int) $this->db
            ->where('status', 'open')
            ->group_start()
            ->where('default_deal_owner IS NULL', null, false)
            ->or_where('default_deal_owner', 0)
            ->group_end()
            ->count_all_results('tbl_deals');

        $summary['no_followup_open'] = (int) $this->db
            ->where('status', 'open')
            ->where('next_follow_up_at IS NULL', null, false)
            ->count_all_results('tbl_deals');

        $summary['inactive_7_plus'] = (int) $this->db
            ->where('status', 'open')
            ->where('DATE(COALESCE(last_activity_at, created_at)) <= ' . $this->db->escape(date('Y-m-d', strtotime('-7 days'))), null, false)
            ->count_all_results('tbl_deals');

        $summary['inactive_14_plus'] = (int) $this->db
            ->where('status', 'open')
            ->where('DATE(COALESCE(last_activity_at, created_at)) <= ' . $this->db->escape(date('Y-m-d', strtotime('-14 days'))), null, false)
            ->count_all_results('tbl_deals');

        $summary['at_risk_open'] = (int) $this->db
            ->where('status', 'open')
            ->where_in('health_status', ['at_risk', 'off_track'])
            ->count_all_results('tbl_deals');

        if ($this->db->table_exists('tbl_deals_followups')) {
            $summary['overdue_followups'] = (int) $this->db
                ->where('status', 'pending')
                ->where('follow_up_at <', date('Y-m-d H:i:s'))
                ->count_all_results('tbl_deals_followups');
        }

        if ($this->db->table_exists('tbl_deals_approvals')) {
            $summary['pending_close_approvals'] = (int) $this->db
                ->where('status', 'pending')
                ->where('approval_type', 'close_won')
                ->count_all_results('tbl_deals_approvals');
        }

        return $summary;
    }

    public function get_diagnostics_summary()
    {
        $requiredTables = [
            'tbl_deals_followups',
            'tbl_deals_campaigns',
            'tbl_deals_campaign_steps',
            'tbl_deals_automation_rules',
            'tbl_deals_automation_queue',
            'tbl_deals_campaign_messages',
            'tbl_deals_email_preferences',
            'tbl_deals_inbound_mailboxes',
            'tbl_deals_email_threads',
            'tbl_deals_approvals',
            'tbl_deals_approval_policies',
            'tbl_deals_webhooks',
            'tbl_deals_webhook_logs',
            'tbl_deals_connectors',
            'tbl_deals_connector_logs',
            'tbl_deals_runtime_qa_logs',
        ];
        $requiredColumns = [
            'probability',
            'expected_revenue',
            'next_follow_up_at',
            'last_contacted_at',
            'last_activity_at',
            'priority',
            'forecast_category',
            'health_status',
            'campaign_id',
        ];

        $missingTables = [];
        foreach ($requiredTables as $table) {
            if (!$this->db->table_exists($table)) {
                $missingTables[] = $table;
            }
        }

        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (!$this->db->field_exists($column, 'tbl_deals')) {
                $missingColumns[] = $column;
            }
        }

        $activeWebhooks = $this->db->table_exists('tbl_deals_webhooks')
            ? $this->db->where('is_active', 1)->count_all_results('tbl_deals_webhooks')
            : 0;
        $pendingApprovals = $this->db->table_exists('tbl_deals_approvals')
            ? $this->db->where('status', 'pending')->count_all_results('tbl_deals_approvals')
            : 0;
        $failedWebhookLogs = $this->db->table_exists('tbl_deals_webhook_logs')
            ? $this->db->where('status', 'failed')->count_all_results('tbl_deals_webhook_logs')
            : 0;
        $activeMailboxes = $this->db->table_exists('tbl_deals_inbound_mailboxes')
            ? $this->db->where('is_active', 1)->count_all_results('tbl_deals_inbound_mailboxes')
            : 0;
        $activeConnectors = $this->db->table_exists('tbl_deals_connectors')
            ? $this->db->where('is_active', 1)->count_all_results('tbl_deals_connectors')
            : 0;
        $failedConnectorLogs = $this->db->table_exists('tbl_deals_connector_logs')
            ? $this->db->where('status', 'failed')->count_all_results('tbl_deals_connector_logs')
            : 0;
        $approvalPolicies = $this->db->table_exists('tbl_deals_approval_policies')
            ? $this->db->where('is_active', 1)->count_all_results('tbl_deals_approval_policies')
            : 0;

        return [
            'missing_tables' => $missingTables,
            'missing_columns' => $missingColumns,
            'active_webhooks' => $activeWebhooks,
            'active_mailboxes' => $activeMailboxes,
            'active_connectors' => $activeConnectors,
            'pending_approvals' => $pendingApprovals,
            'failed_webhook_logs' => $failedWebhookLogs,
            'failed_connector_logs' => $failedConnectorLogs,
            'approval_policies' => $approvalPolicies,
            'sla_summary' => $this->get_sla_summary(),
            'attention_queue' => $this->get_attention_queue(10),
            'recent_logs' => $this->get_recent_webhook_logs(25),
        ];
    }

    protected function get_active_webhooks_for_event($eventType)
    {
        $webhooks = $this->db->where('is_active', 1)->get('tbl_deals_webhooks')->result_array();

        return array_values(array_filter($webhooks, function ($webhook) use ($eventType) {
            $events = json_decode($webhook['trigger_events_json'] ?? '[]', true);
            return in_array($eventType, $events ?: [], true);
        }));
    }

    protected function send_webhook($webhook, $eventType, $deal, $context = [], $options = [])
    {
        $payload = [
            'event' => $eventType,
            'generated_at' => date('c'),
            'deal' => [
                'id' => $deal->id,
                'title' => $deal->title,
                'status' => $deal->status,
                'pipeline_id' => $deal->pipeline_id,
                'stage_id' => $deal->stage_id,
                'deal_value' => $deal->deal_value,
                'probability' => $deal->probability ?? null,
                'expected_revenue' => $deal->expected_revenue ?? null,
                'priority' => $deal->priority ?? null,
                'forecast_category' => $deal->forecast_category ?? null,
                'health_status' => $deal->health_status ?? null,
                'next_follow_up_at' => $deal->next_follow_up_at ?? null,
            ],
            'context' => $context,
        ];

        $payloadJson = json_encode($payload);
        $status = 'queued';
        $responseCode = null;
        $responseBody = null;
        $errorMessage = null;
        $startedAt = microtime(true);

        if (function_exists('curl_init')) {
            $ch = curl_init($webhook['endpoint_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_filter([
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payloadJson),
                !empty($webhook['secret_key']) ? 'X-Deals-Signature: ' . hash_hmac('sha256', $payloadJson, $webhook['secret_key']) : null,
            ]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $responseBody = curl_exec($ch);
            $responseCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $status = ($responseBody !== false && $responseCode >= 200 && $responseCode < 300) ? 'success' : 'failed';
            if ($responseBody === false) {
                $errorMessage = curl_error($ch);
                $responseBody = $errorMessage;
            }
            curl_close($ch);
        } else {
            $status = 'skipped';
            $errorMessage = 'cURL extension is not available.';
            $responseBody = $errorMessage;
        }

        $processingMs = (int) round((microtime(true) - $startedAt) * 1000);

        $logData = [
            'webhook_id' => $webhook['id'],
            'deal_id' => $deal->id,
            'event_type' => $eventType,
            'status' => $status,
            'response_code' => $responseCode ?: null,
            'request_payload' => $payloadJson,
            'response_body' => $responseBody,
        ];

        if ($this->db->field_exists('error_message', 'tbl_deals_webhook_logs')) {
            $logData['error_message'] = $errorMessage;
        }

        if ($this->db->field_exists('is_test', 'tbl_deals_webhook_logs')) {
            $logData['is_test'] = !empty($options['is_test']) ? 1 : 0;
        }

        if ($this->db->field_exists('retry_of_log_id', 'tbl_deals_webhook_logs')) {
            $logData['retry_of_log_id'] = !empty($options['retry_of_log_id']) ? (int) $options['retry_of_log_id'] : null;
        }

        if ($this->db->field_exists('processing_ms', 'tbl_deals_webhook_logs')) {
            $logData['processing_ms'] = $processingMs;
        }

        $this->db->insert('tbl_deals_webhook_logs', $logData);

        $this->db->where('id', $webhook['id'])->update('tbl_deals_webhooks', [
            'last_run_at' => date('Y-m-d H:i:s'),
        ]);

        return $status === 'success';
    }

    protected function get_matching_approval_policies($deal, $triggerEvent, $context = [])
    {
        $policies = $this->db
            ->where('is_active', 1)
            ->where('trigger_event', $triggerEvent)
            ->order_by('name', 'ASC')
            ->get('tbl_deals_approval_policies')
            ->result_array();

        return array_values(array_filter($policies, function ($policy) use ($deal, $context) {
            if (!empty($policy['pipeline_id']) && (int) $policy['pipeline_id'] !== (int) ($context['pipeline_id'] ?? $deal->pipeline_id)) {
                return false;
            }

            if (!empty($policy['stage_id']) && (int) $policy['stage_id'] !== (int) ($context['stage_id'] ?? $deal->stage_id)) {
                return false;
            }

            if ($policy['min_deal_value'] !== null && $policy['min_deal_value'] !== '' && (float) $deal->deal_value < (float) $policy['min_deal_value']) {
                return false;
            }

            if (!empty($policy['health_status']) && $policy['health_status'] !== ($context['health_status'] ?? $deal->health_status)) {
                return false;
            }

            return true;
        }));
    }

    protected function get_policy_approval_state($dealId, $policyId)
    {
        if (!$this->db->field_exists('policy_id', 'tbl_deals_approvals')) {
            return null;
        }

        $approval = $this->db
            ->where('deal_id', $dealId)
            ->where('policy_id', $policyId)
            ->order_by('id', 'DESC')
            ->get('tbl_deals_approvals')
            ->row();

        return $approval ? $approval->status : null;
    }

    protected function format_policy_title($policy, $deal)
    {
        $replacements = [
            '{deal_id}' => $deal->id,
            '{deal_title}' => $deal->title,
            '{deal_value}' => app_format_money((float) $deal->deal_value, get_base_currency()),
            '{stage_name}' => $deal->stage_name ?? '',
            '{owner_name}' => $deal->full_name ?? '',
        ];

        return strtr($policy['title_template'], $replacements);
    }

    protected function can_current_user_decide_approval($approval)
    {
        if (is_admin()) {
            return true;
        }

        if (empty($approval->assigned_to)) {
            return true;
        }

        return (int) $approval->assigned_to === (int) get_staff_user_id();
    }

    protected function normalise_events($events)
    {
        if (!is_array($events)) {
            return [];
        }

        $events = array_map('trim', $events);
        $events = array_filter($events, function ($event) {
            return $event !== '';
        });

        return array_values(array_unique($events));
    }
}
