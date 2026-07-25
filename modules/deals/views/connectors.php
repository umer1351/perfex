<?php init_head(); ?>
<link rel="stylesheet" id="deals-style" href="<?php echo module_dir_url(DEALS_MODULE); ?>assets/css/style.css">
<?php
$editingConnector = $editing_connector ?? null;
$activeConnectors = count(array_filter($connectors, function ($connector) {
    return !empty($connector['is_active']);
}));
$failedConnectors = count(array_filter($connectors, function ($connector) {
    return (int) ($connector['consecutive_failures'] ?? 0) > 0;
}));
$successfulDeliveries = array_sum(array_map(function ($connector) {
    return (int) ($connector['success_count'] ?? 0);
}, $connectors));
$failedDeliveries = array_sum(array_map(function ($connector) {
    return (int) ($connector['failed_count'] ?? 0);
}, $connectors));
$connectorTypes = [
    ['id' => 'slack', 'name' => 'Slack'],
    ['id' => 'teams', 'name' => 'Microsoft Teams'],
    ['id' => 'google_chat', 'name' => 'Google Chat'],
    ['id' => 'generic_json', 'name' => 'Generic JSON'],
];
$deliveryMethods = [
    ['id' => 'POST', 'name' => 'POST'],
    ['id' => 'PUT', 'name' => 'PUT'],
    ['id' => 'PATCH', 'name' => 'PATCH'],
];
$payloadFormats = [
    ['id' => 'json', 'name' => 'JSON'],
    ['id' => 'form', 'name' => 'Form Encoded'],
];
$authTypes = [
    ['id' => 'bearer', 'name' => 'Bearer Token'],
    ['id' => 'basic', 'name' => 'Basic Auth'],
    ['id' => 'header', 'name' => 'Custom Header'],
    ['id' => 'query', 'name' => 'Query Parameter'],
    ['id' => 'none', 'name' => 'No Auth'],
];
$triggerEvents = [
    ['id' => 'deal_created', 'name' => 'Deal Created'],
    ['id' => 'deal_updated', 'name' => 'Deal Updated'],
    ['id' => 'deal_email_sent', 'name' => 'Deal Email Sent'],
    ['id' => 'inbound_email_received', 'name' => 'Inbound Email Received'],
    ['id' => 'email_bounced', 'name' => 'Email Bounced'],
    ['id' => 'stage_changed', 'name' => 'Stage Changed'],
    ['id' => 'status_changed', 'name' => 'Status Changed'],
    ['id' => 'followup_scheduled', 'name' => 'Follow-Up Scheduled'],
    ['id' => 'followup_completed', 'name' => 'Follow-Up Completed'],
    ['id' => 'approval_requested', 'name' => 'Approval Requested'],
    ['id' => 'approval_approved', 'name' => 'Approval Approved'],
    ['id' => 'approval_rejected', 'name' => 'Approval Rejected'],
];
$customHeadersMap = json_decode($editingConnector['custom_headers_json'] ?? '[]', true) ?: [];
$customHeaders = implode("\n", array_map(function ($key, $value) {
    return $key . ': ' . $value;
}, array_keys($customHeadersMap), $customHeadersMap));
?>
<div id="wrapper">
    <div class="content">
        <div class="deal-page-shell">
            <div class="deal-page-hero">
                <div>
                    <div class="deal-page-hero__eyebrow">External Connectors</div>
                    <h3 class="deal-page-hero__title">Connector Hub</h3>
                    <p class="deal-page-hero__description">Manage outbound delivery like an integration platform: transport policy, auth strategy, payload signing, retries, health state, and dispatch history in one place.</p>
                </div>
                <div class="deal-page-hero__actions">
                    <a href="<?php echo admin_url('deals/diagnostics'); ?>" class="btn btn-default">Diagnostics</a>
                    <a href="<?php echo admin_url('deals/runtime_qa'); ?>" class="btn btn-default">Runtime QA</a>
                </div>
            </div>

            <div class="deal-stat-strip">
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Active Connectors</span>
                    <strong><?php echo (int) $activeConnectors; ?></strong>
                    <span class="deal-stat-card__meta">Dispatch endpoints online</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Successful Deliveries</span>
                    <strong><?php echo (int) $successfulDeliveries; ?></strong>
                    <span class="deal-stat-card__meta">Connector calls completed</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Failed Deliveries</span>
                    <strong><?php echo (int) $failedDeliveries; ?></strong>
                    <span class="deal-stat-card__meta">Need retry or investigation</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">At Risk</span>
                    <strong><?php echo (int) $failedConnectors; ?></strong>
                    <span class="deal-stat-card__meta">Consecutive failures > 0</span>
                </div>
            </div>

            <div class="deal-config-layout">
                <div class="deal-config-main">
                    <div class="deal-panel">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title"><?php echo $editingConnector ? 'Edit Connector' : 'New Connector'; ?></h4>
                            <span class="text-muted"><?php echo $editingConnector ? 'Refine transport and retries' : 'Create an outbound delivery destination'; ?></span>
                        </div>

                        <?php echo form_open(admin_url('deals/save_connector')); ?>
                        <?php echo form_hidden('connector_id', $editingConnector['id'] ?? ''); ?>

                        <div class="deal-config-section is-open">
                            <button type="button" class="deal-config-section__toggle" data-target="connector-identity">
                                <span>Identity</span>
                                <small>Connector type, destination, channel</small>
                            </button>
                            <div id="connector-identity" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_input('name', 'Connector Name', $editingConnector['name'] ?? 'Revenue Ops Slack'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_select('connector_type', $connectorTypes, ['id', 'name'], 'Connector Type', $editingConnector['connector_type'] ?? 'slack'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('channel_identifier', 'Channel / Room', $editingConnector['channel_identifier'] ?? ''); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_input('endpoint_url', 'Endpoint URL', $editingConnector['endpoint_url'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-section is-open">
                            <button type="button" class="deal-config-section__toggle" data-target="connector-transport">
                                <span>Transport and Auth</span>
                                <small>HTTP method, auth mode, header strategy</small>
                            </button>
                            <div id="connector-transport" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_select('delivery_method', $deliveryMethods, ['id', 'name'], 'HTTP Method', $editingConnector['delivery_method'] ?? 'POST'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_select('payload_format', $payloadFormats, ['id', 'name'], 'Payload Format', $editingConnector['payload_format'] ?? 'json'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_select('auth_type', $authTypes, ['id', 'name'], 'Auth Type', $editingConnector['auth_type'] ?? 'bearer'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('auth_header_name', 'Auth Header / Param', $editingConnector['auth_header_name'] ?? 'Authorization'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_input('auth_username', 'Auth Username', $editingConnector['auth_username'] ?? ''); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('auth_token', 'Auth Token / Password', $editingConnector['auth_token'] ?? ''); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_textarea('custom_headers', 'Custom Headers', $customHeaders, ['rows' => 4]); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-section">
                            <button type="button" class="deal-config-section__toggle" data-target="connector-reliability">
                                <span>Reliability and Signing</span>
                                <small>Timeouts, retries, payload signatures</small>
                            </button>
                            <div id="connector-reliability" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-4">
                                        <?php echo render_input('timeout_seconds', 'Timeout (s)', $editingConnector['timeout_seconds'] ?? 10, 'number'); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo render_input('retry_limit', 'Retry Limit', $editingConnector['retry_limit'] ?? 0, 'number'); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo render_input('retry_backoff_ms', 'Backoff (ms)', $editingConnector['retry_backoff_ms'] ?? 250, 'number'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_input('signature_header_name', 'Signature Header', $editingConnector['signature_header_name'] ?? 'X-Deals-Signature'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('signature_secret', 'Signature Secret', $editingConnector['signature_secret'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-section">
                            <button type="button" class="deal-config-section__toggle" data-target="connector-policy">
                                <span>Dispatch Policy</span>
                                <small>Trigger events, templates, ops notes</small>
                            </button>
                            <div id="connector-policy" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_select('trigger_events[]', $triggerEvents, ['id', 'name'], 'Trigger Events', !empty($editingConnector['trigger_events_json']) ? (json_decode($editingConnector['trigger_events_json'], true) ?: []) : [], ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_textarea('template_text', 'Message Template', $editingConnector['template_text'] ?? "Deal event: {event}\nDeal: {deal_title}\nStatus: {status}\nStage: {stage}\nValue: {deal_value}\nOwner: {owner_name}", ['rows' => 6]); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_textarea('notes', 'Ops Notes', $editingConnector['notes'] ?? '', ['rows' => 4]); ?>
                                    </div>
                                </div>
                                <div class="deal-checkbox-stack">
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo !isset($editingConnector['is_active']) || !empty($editingConnector['is_active']) ? 'checked' : ''; ?>>
                                        <label for="is_active">Connector is active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-actions">
                            <div class="deal-config-actions__info">
                                <?php if ($editingConnector) { ?>
                                    <span class="deal-pill deal-pill--status-open">Managed Delivery</span>
                                <?php } else { ?>
                                    <span class="deal-pill deal-pill--approval">Draft Connector</span>
                                <?php } ?>
                            </div>
                            <div class="deal-config-actions__buttons">
                                <?php if ($editingConnector) { ?>
                                    <a href="<?php echo admin_url('deals/connectors'); ?>" class="btn btn-default">Cancel</a>
                                <?php } ?>
                                <button type="submit" class="btn btn-primary"><?php echo $editingConnector ? 'Save Connector' : 'Create Connector'; ?></button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <div class="deal-config-side">
                    <div class="deal-panel">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title"><?php echo $editingConnector ? 'Delivery Overview' : 'Connector Patterns'; ?></h4>
                            <span class="text-muted"><?php echo $editingConnector ? 'Runtime health and policy summary' : 'Choose the right transport posture'; ?></span>
                        </div>

                        <?php if ($editingConnector) { ?>
                            <div class="deal-side-grid">
                                <div class="deal-side-card deal-side-card--primary">
                                    <span class="deal-side-card__label">Destination</span>
                                    <div class="deal-code-block"><?php echo html_escape($editingConnector['endpoint_url'] ?? ''); ?></div>
                                    <button type="button" class="btn btn-default btn-sm deal-copy-btn" data-copy-text="<?php echo html_escape($editingConnector['endpoint_url'] ?? ''); ?>">Copy URL</button>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Transport</span>
                                    <strong><?php echo html_escape(($editingConnector['delivery_method'] ?? 'POST') . ' / ' . strtoupper($editingConnector['payload_format'] ?? 'json')); ?></strong>
                                    <span class="deal-side-card__meta"><?php echo html_escape(ucwords($editingConnector['auth_type'] ?? 'bearer')); ?></span>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Last Run</span>
                                    <strong><?php echo !empty($editingConnector['last_run_at']) ? _dt($editingConnector['last_run_at']) : 'Not run yet'; ?></strong>
                                    <span class="deal-side-card__meta"><?php echo !empty($editingConnector['last_status']) ? ucfirst($editingConnector['last_status']) : 'No status'; ?></span>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Retries</span>
                                    <strong><?php echo (int) ($editingConnector['retry_limit'] ?? 0); ?> attempts</strong>
                                    <span class="deal-side-card__meta"><?php echo (int) ($editingConnector['retry_backoff_ms'] ?? 250); ?> ms backoff</span>
                                </div>
                            </div>

                            <div class="deal-ops-list">
                                <div class="deal-ops-list__item">
                                    <span>Consecutive Failures</span>
                                    <strong><?php echo (int) ($editingConnector['consecutive_failures'] ?? 0); ?></strong>
                                </div>
                                <div class="deal-ops-list__item">
                                    <span>Last Success</span>
                                    <strong><?php echo !empty($editingConnector['last_success_at']) ? _dt($editingConnector['last_success_at']) : 'None'; ?></strong>
                                </div>
                                <div class="deal-ops-list__item">
                                    <span>Signature Header</span>
                                    <strong><?php echo html_escape($editingConnector['signature_header_name'] ?? 'X-Deals-Signature'); ?></strong>
                                </div>
                                <div class="deal-ops-list__item">
                                    <span>Channel / Room</span>
                                    <strong><?php echo html_escape($editingConnector['channel_identifier'] ?? 'Not set'); ?></strong>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="deal-empty-state">
                                <h5>Choose the right connector profile</h5>
                                <p>Keep the initial setup lean, then open advanced transport and retry controls only when the destination requires them.</p>
                                <ul class="deal-signal-list">
                                    <li>Use Slack or Teams for human-facing revenue alerts.</li>
                                    <li>Use Generic JSON for downstream systems, middleware, or warehouses.</li>
                                    <li>Enable signing and retries when pushing into production workflows.</li>
                                </ul>
                            </div>
                            <div class="deal-side-grid">
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Slack</span>
                                    <strong>Readable alert cards</strong>
                                    <span class="deal-side-card__meta">Best for SDR or RevOps notification rooms</span>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Teams</span>
                                    <strong>Manager-facing summaries</strong>
                                    <span class="deal-side-card__meta">Good for approvals and escalations</span>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Generic JSON</span>
                                    <strong>System-to-system dispatch</strong>
                                    <span class="deal-side-card__meta">Best for product integrations and event pipelines</span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="deal-panel">
                <div class="deal-panel__header">
                    <h4 class="deal-panel__title">Configured Connectors</h4>
                    <span class="text-muted">Managed delivery registry</span>
                </div>
                <?php if (empty($connectors)) { ?>
                    <div class="deal-empty-state">
                        <h5>No connectors configured yet</h5>
                        <p>Create a connector to push deal lifecycle events into chat, middleware, or downstream systems.</p>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-hover deal-data-table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Transport</th>
                                <th>Events</th>
                                <th>Health</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($connectors as $connector) { ?>
                                <tr>
                                    <td>
                                        <div class="deal-table-title"><?php echo html_escape($connector['name']); ?></div>
                                        <div class="text-muted deal-url-text"><?php echo html_escape($connector['endpoint_url']); ?></div>
                                        <div class="text-muted"><?php echo html_escape(ucwords(str_replace('_', ' ', $connector['connector_type']))); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo html_escape(($connector['delivery_method'] ?? 'POST') . ' / ' . strtoupper($connector['payload_format'] ?? 'json')); ?></div>
                                        <div class="text-muted"><?php echo html_escape(ucwords($connector['auth_type'] ?? 'bearer')); ?></div>
                                    </td>
                                    <td class="text-muted"><?php echo implode(', ', array_map(function ($event) {
                                        return ucwords(str_replace('_', ' ', $event));
                                    }, json_decode($connector['trigger_events_json'] ?? '[]', true) ?: [])); ?></td>
                                    <td>
                                        <div class="text-muted">Success: <?php echo (int) ($connector['success_count'] ?? 0); ?></div>
                                        <div class="text-muted">Failed: <?php echo (int) ($connector['failed_count'] ?? 0); ?></div>
                                        <div class="text-muted"><?php echo !empty($connector['last_success_at']) ? 'Last success ' . _dt($connector['last_success_at']) : 'No success yet'; ?></div>
                                    </td>
                                    <td>
                                        <span class="deal-pill <?php echo !empty($connector['is_active']) ? 'deal-pill--status-open' : 'deal-pill--status-lost'; ?>">
                                            <?php echo !empty($connector['is_active']) ? 'Active' : 'Paused'; ?>
                                        </span>
                                        <?php if (!empty($connector['consecutive_failures'])) { ?>
                                            <div class="deal-inline-alert"><?php echo (int) $connector['consecutive_failures']; ?> consecutive failures</div>
                                        <?php } ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?php echo admin_url('deals/test_connector/' . $connector['id']); ?>" class="btn btn-default btn-sm">Test</a>
                                        <a href="<?php echo admin_url('deals/connectors?id=' . $connector['id']); ?>" class="btn btn-default btn-sm">Edit</a>
                                        <a href="<?php echo admin_url('deals/delete_connector/' . $connector['id']); ?>" class="btn btn-danger btn-sm _delete">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>

            <div class="deal-panel">
                <div class="deal-panel__header">
                    <h4 class="deal-panel__title">Recent Connector Log</h4>
                    <span class="text-muted">Delivery and retry trail</span>
                </div>
                <?php if (empty($connector_logs)) { ?>
                    <div class="deal-empty-state">
                        <h5>No connector deliveries yet</h5>
                        <p>Run a connector test or trigger a deal event to start building an operational delivery history.</p>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-hover deal-data-table">
                            <thead>
                            <tr>
                                <th>Attempted</th>
                                <th>Connector</th>
                                <th>Event</th>
                                <th>Deal</th>
                                <th>Result</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($connector_logs as $log) { ?>
                                <tr>
                                    <td><?php echo _dt($log['attempted_at']); ?></td>
                                    <td><?php echo $log['connector_name'] ?: '-'; ?></td>
                                    <td><?php echo ucwords(str_replace('_', ' ', $log['event_type'])); ?></td>
                                    <td><?php echo $log['deal_title'] ?: ('#' . $log['deal_id']); ?></td>
                                    <td>
                                        <span class="deal-pill <?php echo ($log['status'] ?? '') === 'success' ? 'deal-pill--status-won' : (($log['status'] ?? '') === 'failed' ? 'deal-pill--status-lost' : 'deal-pill--approval'); ?>">
                                            <?php echo ucfirst($log['status']); ?>
                                        </span>
                                        <div class="text-muted">HTTP <?php echo !empty($log['response_code']) ? (int) $log['response_code'] : '-'; ?> | <?php echo !empty($log['processing_ms']) ? (int) $log['processing_ms'] . ' ms' : '-'; ?></div>
                                        <div class="text-muted">Attempts: <?php echo (int) ($log['attempt_count'] ?? 1); ?></div>
                                    </td>
                                    <td class="text-right">
                                        <?php if (($log['status'] ?? '') === 'failed') { ?>
                                            <a href="<?php echo admin_url('deals/retry_connector/' . $log['id']); ?>" class="btn btn-default btn-sm">Retry</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    (function ($) {
        function toggleSection($section, forceOpen) {
            var shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !$section.hasClass('is-open');
            $section.toggleClass('is-open', shouldOpen);
            $section.find('.deal-config-section__content').stop(true, true)[shouldOpen ? 'slideDown' : 'slideUp'](160);
        }

        $('.deal-config-section').each(function () {
            var $section = $(this);
            toggleSection($section, $section.hasClass('is-open'));
        });

        $(document).on('click', '.deal-config-section__toggle', function () {
            toggleSection($(this).closest('.deal-config-section'));
        });

        $(document).on('click', '.deal-copy-btn', function () {
            var text = $(this).data('copyText') || '';
            if (!text) {
                return;
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                var $temp = $('<textarea>').val(text).appendTo('body').select();
                document.execCommand('copy');
                $temp.remove();
            }
        });
    })(jQuery);
</script>
