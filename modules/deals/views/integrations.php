<?php init_head(); ?>
<link rel="stylesheet" id="deals-style" href="<?php echo module_dir_url(DEALS_MODULE); ?>assets/css/style.css">
<?php
$editingWebhook = $editing_webhook ?? null;
$webhookCount = count($webhooks ?? []);
$activeCount = 0;
$failedCount = 0;
foreach ($webhooks as $webhook) {
    if (!empty($webhook['is_active'])) {
        $activeCount++;
    }
}
foreach ($logs as $log) {
    if (($log['status'] ?? '') === 'failed') {
        $failedCount++;
    }
}
?>
<div id="wrapper">
    <div class="content">
        <div class="deal-page-shell">
            <div class="deal-page-hero">
                <div>
                    <div class="deal-page-hero__eyebrow">Integrations</div>
                    <h3 class="deal-page-hero__title">Webhook Control Center</h3>
                    <p class="deal-page-hero__description">Manage outbound integration endpoints, validate payload delivery, and retry failures without leaving the deals workspace.</p>
                </div>
                <div class="deal-page-hero__actions">
                    <a href="<?php echo admin_url('deals/mailboxes'); ?>" class="btn btn-default">Inbox</a>
                    <a href="<?php echo admin_url('deals/connectors'); ?>" class="btn btn-default">Connectors</a>
                    <a href="<?php echo admin_url('deals/approval_policies'); ?>" class="btn btn-default">Governance</a>
                    <a href="<?php echo admin_url('deals/diagnostics'); ?>" class="btn btn-default">Diagnostics</a>
                </div>
            </div>

            <div class="deal-stat-strip">
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Configured</span>
                    <strong><?php echo $webhookCount; ?></strong>
                    <span class="deal-stat-card__meta">Webhook endpoints</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Active</span>
                    <strong><?php echo $activeCount; ?></strong>
                    <span class="deal-stat-card__meta">Currently dispatching</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Failures</span>
                    <strong><?php echo $failedCount; ?></strong>
                    <span class="deal-stat-card__meta">Recent failed deliveries</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="deal-panel">
                        <h4 class="deal-panel__title"><?php echo $editingWebhook ? 'Edit Webhook' : 'New Webhook'; ?></h4>
                        <?php echo form_open(admin_url('deals/save_webhook')); ?>
                        <?php echo form_hidden('webhook_id', $editingWebhook['id'] ?? ''); ?>
                        <?php echo render_input('name', 'Name', $editingWebhook['name'] ?? ''); ?>
                        <?php echo render_input('endpoint_url', 'Endpoint URL', $editingWebhook['endpoint_url'] ?? ''); ?>
                        <?php echo render_input('secret_key', 'Signing Secret', $editingWebhook['secret_key'] ?? ''); ?>
                        <?php echo render_select('trigger_events[]', [
                            ['id' => 'deal_created', 'name' => 'Deal Created'],
                            ['id' => 'deal_updated', 'name' => 'Deal Updated'],
                            ['id' => 'stage_changed', 'name' => 'Stage Changed'],
                            ['id' => 'status_changed', 'name' => 'Status Changed'],
                            ['id' => 'followup_scheduled', 'name' => 'Follow-Up Scheduled'],
                            ['id' => 'followup_completed', 'name' => 'Follow-Up Completed'],
                            ['id' => 'deal_email_sent', 'name' => 'Deal Email Sent'],
                            ['id' => 'inbound_email_received', 'name' => 'Inbound Email Received'],
                            ['id' => 'email_bounced', 'name' => 'Email Bounced'],
                            ['id' => 'approval_requested', 'name' => 'Approval Requested'],
                            ['id' => 'approval_approved', 'name' => 'Approval Approved'],
                            ['id' => 'approval_rejected', 'name' => 'Approval Rejected'],
                        ], ['id', 'name'], 'Trigger Events', !empty($editingWebhook['trigger_events_json']) ? (json_decode($editingWebhook['trigger_events_json'], true) ?: []) : [], ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo !isset($editingWebhook['is_active']) || !empty($editingWebhook['is_active']) ? 'checked' : ''; ?>>
                            <label for="is_active">Webhook is active</label>
                        </div>
                        <div class="deal-form-actions">
                            <?php if ($editingWebhook) { ?>
                                <a href="<?php echo admin_url('deals/integrations'); ?>" class="btn btn-default">Cancel</a>
                            <?php } ?>
                            <button type="submit" class="btn btn-primary"><?php echo $editingWebhook ? 'Update Webhook' : 'Save Webhook'; ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="deal-panel">
                        <h4 class="deal-panel__title">Configured Endpoints</h4>
                        <div class="table-responsive">
                            <table class="table table-hover deal-data-table">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Events</th>
                                    <th>Last Run</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($webhooks as $webhook) { ?>
                                    <?php $events = json_decode($webhook['trigger_events_json'] ?? '[]', true) ?: []; ?>
                                    <tr>
                                        <td>
                                            <div class="deal-table-title"><?php echo html_escape($webhook['name']); ?></div>
                                            <div class="text-muted deal-url-text"><?php echo html_escape($webhook['endpoint_url']); ?></div>
                                        </td>
                                        <td class="text-muted"><?php echo !empty($events) ? implode(', ', array_map(function ($event) {
                                            return ucwords(str_replace('_', ' ', $event));
                                        }, $events)) : 'No events'; ?></td>
                                        <td><?php echo !empty($webhook['last_run_at']) ? _dt($webhook['last_run_at']) : '-'; ?></td>
                                        <td>
                                            <span class="deal-pill <?php echo !empty($webhook['is_active']) ? 'deal-pill--status-open' : 'deal-pill--status-lost'; ?>">
                                                <?php echo !empty($webhook['is_active']) ? 'Active' : 'Paused'; ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?php echo admin_url('deals/test_webhook/' . $webhook['id']); ?>" class="btn btn-default btn-sm">Test</a>
                                            <a href="<?php echo admin_url('deals/integrations?id=' . $webhook['id']); ?>" class="btn btn-default btn-sm">Edit</a>
                                            <a href="<?php echo admin_url('deals/delete_webhook/' . $webhook['id']); ?>" class="btn btn-danger btn-sm _delete">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($webhooks)) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No webhook endpoints configured yet.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="deal-panel tw-mt-4">
                        <h4 class="deal-panel__title">Recent Delivery Log</h4>
                        <div class="table-responsive">
                            <table class="table table-hover deal-data-table">
                                <thead>
                                <tr>
                                    <th>Attempted</th>
                                    <th>Webhook</th>
                                    <th>Event</th>
                                    <th>Deal</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($logs as $log) { ?>
                                    <tr>
                                        <td>
                                            <div><?php echo _dt($log['attempted_at']); ?></div>
                                            <div class="text-muted"><?php echo !empty($log['response_code']) ? 'HTTP ' . (int) $log['response_code'] : 'No response'; ?></div>
                                        </td>
                                        <td><?php echo $log['webhook_name'] ?: '-'; ?></td>
                                        <td><?php echo ucwords(str_replace('_', ' ', $log['event_type'])); ?></td>
                                        <td><?php echo $log['deal_title'] ?: ('#' . $log['deal_id']); ?></td>
                                        <td>
                                            <span class="deal-pill <?php echo ($log['status'] ?? '') === 'success' ? 'deal-pill--status-won' : (($log['status'] ?? '') === 'failed' ? 'deal-pill--status-lost' : ''); ?>">
                                                <?php echo ucfirst($log['status']); ?>
                                            </span>
                                            <?php if (!empty($log['is_test'])) { ?>
                                                <span class="deal-pill">Test</span>
                                            <?php } ?>
                                        </td>
                                        <td class="text-right">
                                            <?php if (($log['status'] ?? '') === 'failed') { ?>
                                                <a href="<?php echo admin_url('deals/retry_webhook/' . $log['id']); ?>" class="btn btn-default btn-sm">Retry</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php if (!empty($log['error_message']) || !empty($log['response_body'])) { ?>
                                        <tr>
                                            <td colspan="6" class="deal-log-detail">
                                                <?php if (!empty($log['error_message'])) { ?>
                                                    <div><strong>Error:</strong> <?php echo html_escape($log['error_message']); ?></div>
                                                <?php } elseif (!empty($log['response_body'])) { ?>
                                                    <div><strong>Response:</strong> <?php echo html_escape(mb_strimwidth(strip_tags($log['response_body']), 0, 240, '...')); ?></div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                                <?php if (empty($logs)) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No webhook deliveries recorded yet.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
