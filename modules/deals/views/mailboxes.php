<?php init_head(); ?>
<link rel="stylesheet" id="deals-style" href="<?php echo module_dir_url(DEALS_MODULE); ?>assets/css/style.css">
<?php
$editingMailbox = $editing_mailbox ?? null;
$activeMailboxes = count(array_filter($mailboxes, function ($mailbox) {
    return !empty($mailbox['is_active']);
}));
$inboundCount = array_sum(array_map(function ($mailbox) {
    return (int) ($mailbox['inbound_count'] ?? 0);
}, $mailboxes));
$bounceCount = array_sum(array_map(function ($mailbox) {
    return (int) ($mailbox['bounce_count'] ?? 0);
}, $mailboxes));
$attentionCount = count(array_filter($mailboxes, function ($mailbox) {
    return !empty($mailbox['last_error_message']);
}));
$allowedSources = !empty($editingMailbox['allowed_sources_json']) ? implode("\n", json_decode($editingMailbox['allowed_sources_json'], true) ?: []) : "mailgun\nsendgrid\npostmark";
$allowedDomains = !empty($editingMailbox['allowed_sender_domains_json']) ? implode("\n", json_decode($editingMailbox['allowed_sender_domains_json'], true) ?: []) : '';
$providerOptions = [
    ['id' => 'custom', 'name' => 'Custom / Middleware'],
    ['id' => 'mailgun', 'name' => 'Mailgun'],
    ['id' => 'sendgrid', 'name' => 'SendGrid'],
    ['id' => 'postmark', 'name' => 'Postmark'],
];
$verificationOptions = [
    ['id' => 'token_only', 'name' => 'Token Only'],
    ['id' => 'secret_header', 'name' => 'Secret Header'],
    ['id' => 'body_hmac_sha256', 'name' => 'Body HMAC SHA256'],
    ['id' => 'mailgun_signature', 'name' => 'Mailgun Signature'],
    ['id' => 'none', 'name' => 'No Extra Verification'],
];
$routingOptions = [
    ['id' => 'thread_or_message_id', 'name' => 'Thread Token + Message ID'],
    ['id' => 'thread_only', 'name' => 'Thread Token Only'],
    ['id' => 'direct_deal_id', 'name' => 'Direct Deal ID Only'],
];
$inboundEndpoint = $editingMailbox ? site_url('deals/ingress/inbound/' . $editingMailbox['secret_token']) : '';
$bounceEndpoint = $editingMailbox ? site_url('deals/ingress/bounce/' . $editingMailbox['secret_token']) : '';
?>
<div id="wrapper">
    <div class="content">
        <div class="deal-page-shell">
            <div class="deal-page-hero">
                <div>
                    <div class="deal-page-hero__eyebrow">Inbound Email</div>
                    <h3 class="deal-page-hero__title">Reply Mailboxes</h3>
                    <p class="deal-page-hero__description">Configure provider-aware reply capture, bounce relay, verification, sender controls, and thread routing without exposing raw endpoint plumbing to staff.</p>
                </div>
                <div class="deal-page-hero__actions">
                    <a href="<?php echo admin_url('deals/connectors'); ?>" class="btn btn-default">Connectors</a>
                    <a href="<?php echo admin_url('deals/integrations'); ?>" class="btn btn-default">Webhooks</a>
                </div>
            </div>

            <div class="deal-stat-strip">
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Active Mailboxes</span>
                    <strong><?php echo (int) $activeMailboxes; ?></strong>
                    <span class="deal-stat-card__meta">Live ingress endpoints</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Inbound Captured</span>
                    <strong><?php echo (int) $inboundCount; ?></strong>
                    <span class="deal-stat-card__meta">Replies linked into deals</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Bounces Tracked</span>
                    <strong><?php echo (int) $bounceCount; ?></strong>
                    <span class="deal-stat-card__meta">Outbound issues routed back</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Attention</span>
                    <strong><?php echo (int) $attentionCount; ?></strong>
                    <span class="deal-stat-card__meta">Mailboxes with recent errors</span>
                </div>
            </div>

            <div class="deal-config-layout">
                <div class="deal-config-main">
                    <div class="deal-panel">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title"><?php echo $editingMailbox ? 'Edit Mailbox' : 'New Mailbox'; ?></h4>
                            <span class="text-muted"><?php echo $editingMailbox ? 'Adjust routing and verification' : 'Create your first reply ingress'; ?></span>
                        </div>

                        <?php echo form_open(admin_url('deals/save_mailbox')); ?>
                        <?php echo form_hidden('mailbox_id', $editingMailbox['id'] ?? ''); ?>

                        <div class="deal-config-section is-open">
                            <button type="button" class="deal-config-section__toggle" data-target="mailbox-identity">
                                <span>Identity</span>
                                <small>Mailbox address, provider, ownership</small>
                            </button>
                            <div id="mailbox-identity" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_input('name', 'Mailbox Name', $editingMailbox['name'] ?? 'Primary Reply Mailbox'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_input('mailbox_email', 'Mailbox Address', $editingMailbox['mailbox_email'] ?? ''); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_select('provider_type', $providerOptions, ['id', 'name'], 'Provider', $editingMailbox['provider_type'] ?? 'custom'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_select('default_owner_id', $staff, ['staffid', ['firstname', 'lastname']], 'Default Owner', $editingMailbox['default_owner_id'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-section is-open">
                            <button type="button" class="deal-config-section__toggle" data-target="mailbox-security">
                                <span>Security</span>
                                <small>Token, verification, callback trust</small>
                            </button>
                            <div id="mailbox-security" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_input('secret_token', 'Endpoint Token', $editingMailbox['secret_token'] ?? ''); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_select('verification_mode', $verificationOptions, ['id', 'name'], 'Verification Mode', $editingMailbox['verification_mode'] ?? 'token_only'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('verification_header', 'Verification Header', $editingMailbox['verification_header'] ?? 'X-Deals-Signature'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_input('verification_secret', 'Verification Secret', $editingMailbox['verification_secret'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-section">
                            <button type="button" class="deal-config-section__toggle" data-target="mailbox-routing">
                                <span>Routing and Policy</span>
                                <small>Deal matching and sender restrictions</small>
                            </button>
                            <div id="mailbox-routing" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_select('routing_mode', $routingOptions, ['id', 'name'], 'Routing Mode', $editingMailbox['routing_mode'] ?? 'thread_or_message_id'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_textarea('allowed_sources', 'Allowed Sources', $allowedSources, ['rows' => 4]); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_textarea('allowed_sender_domains', 'Allowed Sender Domains', $allowedDomains, ['rows' => 4]); ?>
                                        <p class="text-muted">One provider or sender domain per line. Leave sender domains empty to accept any domain.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-section">
                            <button type="button" class="deal-config-section__toggle" data-target="mailbox-operations">
                                <span>Operations</span>
                                <small>Notes and processing toggles</small>
                            </button>
                            <div id="mailbox-operations" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_textarea('notes', 'Ops Notes', $editingMailbox['notes'] ?? '', ['rows' => 4]); ?>
                                    </div>
                                </div>
                                <div class="deal-checkbox-stack">
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" id="allow_reply_processing" name="allow_reply_processing" value="1" <?php echo !isset($editingMailbox['allow_reply_processing']) || !empty($editingMailbox['allow_reply_processing']) ? 'checked' : ''; ?>>
                                        <label for="allow_reply_processing">Process inbound replies</label>
                                    </div>
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" id="allow_bounce_processing" name="allow_bounce_processing" value="1" <?php echo !isset($editingMailbox['allow_bounce_processing']) || !empty($editingMailbox['allow_bounce_processing']) ? 'checked' : ''; ?>>
                                        <label for="allow_bounce_processing">Process bounce callbacks</label>
                                    </div>
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo !isset($editingMailbox['is_active']) || !empty($editingMailbox['is_active']) ? 'checked' : ''; ?>>
                                        <label for="is_active">Mailbox is active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-actions">
                            <div class="deal-config-actions__info">
                                <?php if ($editingMailbox) { ?>
                                    <span class="deal-pill deal-pill--status-open">Editing Live Mailbox</span>
                                <?php } else { ?>
                                    <span class="deal-pill deal-pill--approval">Draft Setup</span>
                                <?php } ?>
                            </div>
                            <div class="deal-config-actions__buttons">
                                <?php if ($editingMailbox) { ?>
                                    <a href="<?php echo admin_url('deals/mailboxes'); ?>" class="btn btn-default">Cancel</a>
                                <?php } ?>
                                <button type="submit" class="btn btn-primary"><?php echo $editingMailbox ? 'Save Mailbox' : 'Create Mailbox'; ?></button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <div class="deal-config-side">
                    <div class="deal-panel">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title"><?php echo $editingMailbox ? 'Operational Overview' : 'Setup Guide'; ?></h4>
                            <span class="text-muted"><?php echo $editingMailbox ? 'Live ingress controls' : 'How this mailbox will work'; ?></span>
                        </div>

                        <?php if ($editingMailbox) { ?>
                            <div class="deal-side-grid">
                                <div class="deal-side-card deal-side-card--primary">
                                    <span class="deal-side-card__label">Inbound Endpoint</span>
                                    <div class="deal-code-block"><?php echo $inboundEndpoint; ?></div>
                                    <button type="button" class="btn btn-default btn-sm deal-copy-btn" data-copy-text="<?php echo html_escape($inboundEndpoint); ?>">Copy URL</button>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Bounce Endpoint</span>
                                    <div class="deal-code-block"><?php echo $bounceEndpoint; ?></div>
                                    <button type="button" class="btn btn-default btn-sm deal-copy-btn" data-copy-text="<?php echo html_escape($bounceEndpoint); ?>">Copy URL</button>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Verification</span>
                                    <strong><?php echo html_escape(ucwords(str_replace('_', ' ', $editingMailbox['verification_mode'] ?? 'token_only'))); ?></strong>
                                    <span class="deal-side-card__meta"><?php echo html_escape($editingMailbox['verification_header'] ?? 'X-Deals-Signature'); ?></span>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Routing</span>
                                    <strong><?php echo html_escape(ucwords(str_replace('_', ' ', $editingMailbox['routing_mode'] ?? 'thread_or_message_id'))); ?></strong>
                                    <span class="deal-side-card__meta"><?php echo !empty($editingMailbox['last_received_at']) ? 'Last reply ' . _dt($editingMailbox['last_received_at']) : 'No inbound yet'; ?></span>
                                </div>
                            </div>

                            <div class="deal-ops-list">
                                <div class="deal-ops-list__item">
                                    <span>Provider</span>
                                    <strong><?php echo html_escape(ucwords(str_replace('_', ' ', $editingMailbox['provider_type'] ?? 'custom'))); ?></strong>
                                </div>
                                <div class="deal-ops-list__item">
                                    <span>Reply Processing</span>
                                    <strong><?php echo !empty($editingMailbox['allow_reply_processing']) ? 'Enabled' : 'Disabled'; ?></strong>
                                </div>
                                <div class="deal-ops-list__item">
                                    <span>Bounce Processing</span>
                                    <strong><?php echo !empty($editingMailbox['allow_bounce_processing']) ? 'Enabled' : 'Disabled'; ?></strong>
                                </div>
                                <div class="deal-ops-list__item">
                                    <span>Last Bounce</span>
                                    <strong><?php echo !empty($editingMailbox['last_bounced_at']) ? _dt($editingMailbox['last_bounced_at']) : 'None'; ?></strong>
                                </div>
                            </div>

                            <?php if (!empty($editingMailbox['last_error_message'])) { ?>
                                <div class="deal-empty-state deal-empty-state--warning">
                                    <h5>Recent mailbox error</h5>
                                    <p><?php echo html_escape($editingMailbox['last_error_message']); ?></p>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="deal-empty-state">
                                <h5>Build a production-ready reply inbox</h5>
                                <p>Choose a provider, generate the endpoint token, then connect inbound and bounce callbacks from your mail service.</p>
                                <ul class="deal-signal-list">
                                    <li>Use `Token Only` for internal middleware or `Mailgun Signature` for direct Mailgun webhooks.</li>
                                    <li>Keep routing on `Thread Token + Message ID` unless your provider can post a trusted `deal_id`.</li>
                                    <li>Restrict sender domains when you need customer-only reply acceptance.</li>
                                </ul>
                            </div>
                            <div class="deal-side-grid">
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Mailgun</span>
                                    <strong>Best for signed direct webhooks</strong>
                                    <span class="deal-side-card__meta">Use `Mailgun Signature` mode</span>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">SendGrid</span>
                                    <strong>Works with token or header verification</strong>
                                    <span class="deal-side-card__meta">Route by thread token and reply headers</span>
                                </div>
                                <div class="deal-side-card">
                                    <span class="deal-side-card__label">Postmark</span>
                                    <strong>Clean inbound webhook payloads</strong>
                                    <span class="deal-side-card__meta">Use token or HMAC middleware</span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="deal-panel">
                <div class="deal-panel__header">
                    <h4 class="deal-panel__title">Configured Mailboxes</h4>
                    <span class="text-muted">Enterprise ingress registry</span>
                </div>
                <?php if (empty($mailboxes)) { ?>
                    <div class="deal-empty-state">
                        <h5>No mailboxes configured yet</h5>
                        <p>Create a mailbox to generate secure inbound and bounce endpoints for your email provider.</p>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-hover deal-data-table">
                            <thead>
                            <tr>
                                <th>Mailbox</th>
                                <th>Provider</th>
                                <th>Policy</th>
                                <th>Health</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($mailboxes as $mailbox) { ?>
                                <tr>
                                    <td>
                                        <div class="deal-table-title"><?php echo html_escape($mailbox['name']); ?></div>
                                        <div class="text-muted"><?php echo html_escape($mailbox['mailbox_email']); ?></div>
                                        <div class="text-muted"><?php echo $mailbox['default_owner_name'] ?: 'No default owner'; ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo ucwords(str_replace('_', ' ', $mailbox['provider_type'] ?? 'custom')); ?></div>
                                        <div class="text-muted"><?php echo ucwords(str_replace('_', ' ', $mailbox['verification_mode'] ?? 'token_only')); ?></div>
                                    </td>
                                    <td>
                                        <div class="text-muted">Sources: <?php echo html_escape(implode(', ', json_decode($mailbox['allowed_sources_json'] ?? '[]', true) ?: [])); ?></div>
                                        <div class="text-muted">Routing: <?php echo html_escape(ucwords(str_replace('_', ' ', $mailbox['routing_mode'] ?? 'thread_or_message_id'))); ?></div>
                                    </td>
                                    <td>
                                        <div class="text-muted">Inbound: <?php echo (int) ($mailbox['inbound_count'] ?? 0); ?></div>
                                        <div class="text-muted">Bounces: <?php echo (int) ($mailbox['bounce_count'] ?? 0); ?></div>
                                        <div class="text-muted"><?php echo !empty($mailbox['last_received_at']) ? 'Last reply ' . _dt($mailbox['last_received_at']) : 'No inbound yet'; ?></div>
                                    </td>
                                    <td>
                                        <span class="deal-pill <?php echo !empty($mailbox['is_active']) ? 'deal-pill--status-open' : 'deal-pill--status-lost'; ?>">
                                            <?php echo !empty($mailbox['is_active']) ? 'Active' : 'Paused'; ?>
                                        </span>
                                        <?php if (!empty($mailbox['last_error_message'])) { ?>
                                            <div class="deal-inline-alert"><?php echo html_escape($mailbox['last_error_message']); ?></div>
                                        <?php } ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?php echo admin_url('deals/mailboxes?id=' . $mailbox['id']); ?>" class="btn btn-default btn-sm">Edit</a>
                                        <a href="<?php echo admin_url('deals/delete_mailbox/' . $mailbox['id']); ?>" class="btn btn-danger btn-sm _delete">Delete</a>
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
                    <h4 class="deal-panel__title">Recent Mailbox Activity</h4>
                    <span class="text-muted">Replies and bounce events</span>
                </div>
                <?php if (empty($mailbox_activity)) { ?>
                    <div class="deal-empty-state">
                        <h5>No mailbox activity yet</h5>
                        <p>Once providers start posting replies or bounce callbacks, activity will appear here with linked deal context.</p>
                    </div>
                <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-hover deal-data-table">
                            <thead>
                            <tr>
                                <th>Time</th>
                                <th>Mailbox</th>
                                <th>Source</th>
                                <th>Deal</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach (($mailbox_activity ?? []) as $activity) { ?>
                                <tr>
                                    <td><?php echo _dt($activity['message_time']); ?></td>
                                    <td>
                                        <div class="deal-table-title"><?php echo html_escape($activity['mailbox_name'] ?: '-'); ?></div>
                                        <div class="text-muted"><?php echo html_escape($activity['subject'] ?: 'No subject'); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo html_escape(ucwords(str_replace('_', ' ', $activity['connector_source'] ?: 'unknown'))); ?></div>
                                        <div class="text-muted"><?php echo html_escape($activity['email_from'] ?: '-'); ?></div>
                                    </td>
                                    <td><?php echo $activity['deal_title'] ?: ('#' . (int) $activity['deals_id']); ?></td>
                                    <td>
                                        <span class="deal-pill <?php echo ($activity['delivery_status'] ?? '') === 'bounced' ? 'deal-pill--status-lost' : 'deal-pill--status-won'; ?>">
                                            <?php echo ucfirst($activity['delivery_status'] ?: $activity['direction']); ?>
                                        </span>
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
