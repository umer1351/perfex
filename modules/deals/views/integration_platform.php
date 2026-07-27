<?php init_head(); ?>
<link rel="stylesheet" id="deals-style" href="<?php echo module_dir_url(DEALS_MODULE); ?>assets/css/style.css">
<?php
$editingAccount = $editing_account ?? null;
$accountMeta = json_decode($editingAccount['account_meta_json'] ?? '[]', true) ?: [];
$scopesText = !empty($editingAccount['scopes_json']) ? implode("\n", json_decode($editingAccount['scopes_json'], true) ?: []) : '';
$currentProviderKey = $editingAccount['provider_key'] ?? 'google_workspace';
?>
<div id="wrapper">
    <div class="content">
        <div class="deal-page-shell">
            <div class="deal-page-hero">
                <div>
                    <div class="deal-page-hero__eyebrow">Integration Platform</div>
                    <h3 class="deal-page-hero__title">Event-Driven Foundation</h3>
                    <p class="deal-page-hero__description">Manage provider registration, connected accounts, event queueing, webhook readiness, and sync state from one modular platform surface.</p>
                </div>
                <div class="deal-page-hero__actions">
                    <a href="<?php echo admin_url('deals/process_integration_queue'); ?>" class="btn btn-default">Process Queue</a>
                    <a href="<?php echo admin_url('deals/integrations?run_smoke=1'); ?>" class="btn btn-default">Run Smoke Tests</a>
                    <a href="<?php echo admin_url('deals/mailboxes'); ?>" class="btn btn-default">Mailboxes</a>
                    <a href="<?php echo admin_url('deals/connectors'); ?>" class="btn btn-default">Connectors</a>
                </div>
            </div>

            <div class="deal-stat-strip">
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Providers</span>
                    <strong><?php echo (int) ($summary['providers'] ?? 0); ?></strong>
                    <span class="deal-stat-card__meta">Registered platform drivers</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Connected Accounts</span>
                    <strong><?php echo (int) ($summary['connected_accounts'] ?? 0); ?></strong>
                    <span class="deal-stat-card__meta"><?php echo (int) ($summary['active_accounts'] ?? 0); ?> active right now</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Queued Events</span>
                    <strong><?php echo (int) ($summary['queued_events'] ?? 0); ?></strong>
                    <span class="deal-stat-card__meta">Database-backed event queue</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Sync States</span>
                    <strong><?php echo (int) ($summary['sync_states'] ?? 0); ?></strong>
                    <span class="deal-stat-card__meta"><?php echo (int) ($summary['failed_events'] ?? 0); ?> failed event(s)</span>
                </div>
            </div>

            <div class="deal-config-layout">
                <div class="deal-config-main">
                    <div class="deal-panel">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title"><?php echo $editingAccount ? 'Edit Integration Account' : 'Connect Integration Account'; ?></h4>
                            <span class="text-muted"><?php echo $editingAccount ? 'Update the provider credentials, then reconnect if needed.' : 'Fill only the basic setup fields, then use the connect button.'; ?></span>
                        </div>

                        <?php echo form_open(admin_url('deals/save_integration_account')); ?>
                        <?php echo form_hidden('account_id', $editingAccount['id'] ?? ''); ?>

                        <div class="deal-integration-guide">
                            <div class="deal-integration-guide__header">
                                <div>
                                    <div class="deal-side-card__label">Quick Start</div>
                                    <strong>Normal connection only needs 3 things</strong>
                                </div>
                                <span class="deal-pill deal-pill--status-won">OAuth Setup</span>
                            </div>
                            <div class="deal-integration-guide__steps">
                                <div class="deal-integration-guide__step">
                                    <span>1</span>
                                    <div>
                                        <strong>Choose a provider</strong>
                                        <small>Select `Google Workspace` or `Slack` below.</small>
                                    </div>
                                </div>
                                <div class="deal-integration-guide__step">
                                    <span>2</span>
                                    <div>
                                        <strong>Add Client ID and Client Secret</strong>
                                        <small>You do not need to fill access token fields for normal OAuth connection.</small>
                                    </div>
                                </div>
                                <div class="deal-integration-guide__step">
                                    <span>3</span>
                                    <div>
                                        <strong>Click the blue connect button</strong>
                                        <small>The system will redirect you to the provider login page and bring you back after approval.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="deal-integration-guide__callback">
                                <div class="deal-table-title">OAuth Callback URL</div>
                                <div class="text-muted" id="platform-oauth-callback-preview"><?php echo site_url('deals/platform/oauth/' . $currentProviderKey); ?></div>
                            </div>
                        </div>

                        <div class="deal-provider-focus" id="platform-provider-focus">
                            <div class="deal-provider-focus__header">
                                <div>
                                    <div class="deal-side-card__label">Selected Provider</div>
                                    <strong id="platform-provider-focus-title">Google Workspace</strong>
                                </div>
                                <span class="deal-pill" id="platform-provider-focus-pill">OAuth Ready</span>
                            </div>
                            <p class="deal-provider-focus__description" id="platform-provider-focus-description">Use this for Gmail, Calendar, and Drive. Create a Google OAuth app, paste the client ID and secret, then connect.</p>
                            <div class="deal-provider-focus__list">
                                <div class="deal-provider-focus__item">Create credentials as a <strong>Web application</strong>.</div>
                                <div class="deal-provider-focus__item" id="platform-provider-focus-step-two">Enable Gmail API, Google Calendar API, and Google Drive API.</div>
                                <div class="deal-provider-focus__item" id="platform-provider-focus-step-three">Paste the callback URL below into the provider console.</div>
                            </div>
                        </div>

                        <div class="deal-config-section deal-config-section--static">
                            <div class="deal-config-section__content deal-config-section__content--static">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_select('provider_key', array_map(function ($provider) {
                                            return ['id' => $provider['provider_key'], 'name' => $provider['name']];
                                        }, $provider_registry), ['id', 'name'], 'Step 1: Provider', $currentProviderKey); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('account_label', 'Account Label', $editingAccount['account_label'] ?? 'Primary Workspace'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_input('client_id', 'Step 2: Client ID', $accountMeta['client_id'] ?? '', 'text', ['placeholder' => 'Paste provider client ID here']); ?>
                                        <p class="text-muted" id="platform-client-id-help">Paste the OAuth client ID from Google Cloud Console.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('client_secret', 'Step 2: Client Secret', $accountMeta['client_secret'] ?? '', 'text', ['placeholder' => 'Paste provider client secret here']); ?>
                                        <p class="text-muted" id="platform-client-secret-help">Paste the OAuth client secret from Google Cloud Console.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_input('external_account_id', 'External Account ID / Email / Workspace', $editingAccount['external_account_id'] ?? ''); ?>
                                        <p class="text-muted">Optional. You can leave this empty and the provider account identity will be filled automatically after successful OAuth.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-section">
                            <button type="button" class="deal-config-section__toggle" data-target="platform-advanced-tokens">
                                <span>Advanced: Manual Tokens</span>
                                <small>Only use this if you want to paste access and refresh tokens manually.</small>
                            </button>
                            <div id="platform-advanced-tokens" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_input('access_token', 'Access Token', ''); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('refresh_token', 'Refresh Token', ''); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_input('token_expires_at', 'Token Expires At', $editingAccount['token_expires_at'] ?? ''); ?>
                                        <p class="text-muted">Leave these empty for the normal OAuth flow.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-section">
                            <button type="button" class="deal-config-section__toggle" data-target="platform-scopes">
                                <span>Scopes and Notes</span>
                                <small>Requested scopes and platform notes</small>
                            </button>
                            <div id="platform-scopes" class="deal-config-section__content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_textarea('scopes_text', 'Scopes', $scopesText, ['rows' => 5]); ?>
                                        <p class="text-muted">One scope per line. Leave empty to use the provider default scaffold scopes.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo render_textarea('notes', 'Notes', $accountMeta['notes'] ?? '', ['rows' => 4]); ?>
                                    </div>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" id="platform_account_active" name="is_active" value="1" <?php echo !empty($editingAccount['is_active']) || empty($editingAccount) ? 'checked' : ''; ?>>
                                    <label for="platform_account_active">Account is active</label>
                                </div>
                            </div>
                        </div>

                        <div class="deal-config-actions">
                            <div class="deal-config-actions__info">
                                <span class="deal-pill deal-pill--approval">Database Queue First</span>
                                <span class="deal-pill">Redis Ready Later</span>
                            </div>
                            <div class="deal-config-actions__buttons">
                                <?php if ($editingAccount) { ?>
                                    <a href="<?php echo admin_url('deals/integrations'); ?>" class="btn btn-default">Cancel</a>
                                <?php } ?>
                                <button type="submit" class="btn btn-default"><?php echo $editingAccount ? 'Update Account' : 'Save Account'; ?></button>
                                <button type="submit" name="oauth_connect" value="1" class="btn btn-primary" id="platform-oauth-connect-btn"><?php echo $editingAccount ? 'Step 3: Update and Connect' : 'Step 3: Save and Connect'; ?></button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <div class="deal-config-side">
                    <div class="deal-panel">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title">Provider Registry</h4>
                            <span class="text-muted">Built-in drivers registered by IntegrationManager</span>
                        </div>
                        <div class="deal-side-grid">
                            <?php foreach ($provider_registry as $provider) { ?>
                                <div class="deal-side-card deal-side-card--selectable<?php echo $provider['provider_key'] === $currentProviderKey ? ' is-selected' : ''; ?>" data-provider-key="<?php echo html_escape($provider['provider_key']); ?>" data-provider-name="<?php echo html_escape($provider['name']); ?>">
                                    <strong><?php echo html_escape($provider['name']); ?></strong>
                                    <span class="deal-side-card__meta"><?php echo html_escape($provider['driver_class']); ?></span>
                                    <div class="deal-chip-row">
                                        <?php if (!empty($provider['supports_oauth'])) { ?><span class="deal-pill">OAuth</span><?php } ?>
                                        <?php if (!empty($provider['supports_webhooks'])) { ?><span class="deal-pill">Webhooks</span><?php } ?>
                                        <?php if (!empty($provider['supports_sync'])) { ?><span class="deal-pill">Sync</span><?php } ?>
                                    </div>
                                    <span class="text-muted"><?php echo implode(', ', array_map('ucfirst', $provider['resources'] ?? [])); ?></span>
                                    <div class="tw-mt-3">
                                        <div class="deal-table-title">OAuth Callback</div>
                                        <div class="text-muted"><?php echo site_url('deals/platform/oauth/' . $provider['provider_key']); ?></div>
                                    </div>
                                    <button type="button" class="btn btn-default btn-sm deal-provider-select-btn" data-provider-key="<?php echo html_escape($provider['provider_key']); ?>">
                                        Use <?php echo html_escape($provider['name']); ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <?php if (!empty($smoke_tests)) { ?>
                        <div class="deal-panel tw-mt-4">
                            <div class="deal-panel__header">
                                <h4 class="deal-panel__title">Smoke Checks</h4>
                                <span class="text-muted">Foundation runtime validation</span>
                            </div>
                            <div class="deal-settings-grid">
                                <?php foreach ($smoke_tests as $check) { ?>
                                    <div class="deal-settings-card">
                                        <div class="deal-chip-row">
                                            <span class="deal-pill <?php echo ($check['status'] ?? '') === 'success' ? 'deal-pill--status-won' : 'deal-pill--status-lost'; ?>">
                                                <?php echo ucfirst($check['status']); ?>
                                            </span>
                                            <span class="deal-stat-card__label"><?php echo html_escape($check['name']); ?></span>
                                        </div>
                                        <p class="mbot0 text-muted"><?php echo html_escape($check['message']); ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="row tw-mt-4">
                <div class="col-md-7">
                    <div class="deal-panel">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title">Connected Accounts</h4>
                            <span class="text-muted">Multi-account integration registry</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover deal-data-table">
                                <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Provider</th>
                                    <th>Status</th>
                                    <th>Last Sync</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($accounts as $account) { ?>
                                    <tr>
                                        <td>
                                            <div class="deal-table-title"><?php echo html_escape($account['account_label']); ?></div>
                                            <div class="text-muted"><?php echo html_escape($account['external_account_id'] ?: 'No external mapping'); ?></div>
                                            <div class="text-muted"><?php echo site_url('deals/platform/webhook/' . $account['provider_key'] . '/' . $account['id']); ?></div>
                                        </td>
                                        <td><?php echo html_escape($account['integration_name'] ?: ucfirst(str_replace('_', ' ', $account['provider_key']))); ?></td>
                                        <td>
                                            <span class="deal-pill <?php echo ($account['connection_status'] ?? '') === 'connected' ? 'deal-pill--status-won' : (($account['connection_status'] ?? '') === 'pending_auth' ? 'deal-pill--approval' : 'deal-pill--status-lost'); ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $account['connection_status'] ?? 'disconnected')); ?>
                                            </span>
                                        </td>
                                        <td><?php echo !empty($account['last_synced_at']) ? _dt($account['last_synced_at']) : '-'; ?></td>
                                        <td class="text-right">
                                            <a href="<?php echo admin_url('deals/authorize_integration_account/' . $account['id']); ?>" class="btn btn-primary btn-sm">
                                                <?php echo ($account['connection_status'] ?? '') === 'connected' ? 'Reconnect' : 'Connect'; ?>
                                            </a>
                                            <a href="<?php echo admin_url('deals/integrations?account_id=' . $account['id']); ?>" class="btn btn-default btn-sm">Edit</a>
                                            <a href="<?php echo admin_url('deals/disconnect_integration_account/' . $account['id']); ?>" class="btn btn-danger btn-sm _delete">Disconnect</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($accounts)) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No platform accounts connected yet.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="deal-panel tw-mt-4">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title">Event Queue</h4>
                            <span class="text-muted">Internal event bus backlog and processing results</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover deal-data-table">
                                <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Source</th>
                                    <th>Queue</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($events as $event) { ?>
                                    <tr>
                                        <td>
                                            <div class="deal-table-title"><?php echo html_escape($event['event_name']); ?></div>
                                            <div class="text-muted"><?php echo html_escape($event['event_uuid']); ?></div>
                                        </td>
                                        <td><?php echo html_escape($event['source']); ?></td>
                                        <td><?php echo html_escape($event['queue_name']); ?></td>
                                        <td>
                                            <span class="deal-pill <?php echo ($event['status'] ?? '') === 'completed' ? 'deal-pill--status-won' : (($event['status'] ?? '') === 'failed' ? 'deal-pill--status-lost' : 'deal-pill--approval'); ?>">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo _dt($event['created_at']); ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($events)) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No platform events recorded yet.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="deal-panel">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title">Sync States</h4>
                            <span class="text-muted">Incremental cursors and webhook checkpoints</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover deal-data-table">
                                <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th>Resource</th>
                                    <th>Status</th>
                                    <th>Cursor</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($sync_states as $syncState) { ?>
                                    <tr>
                                        <td><?php echo html_escape(ucwords(str_replace('_', ' ', $syncState['provider_key']))); ?></td>
                                        <td><?php echo html_escape($syncState['resource_type']); ?></td>
                                        <td>
                                            <span class="deal-pill <?php echo ($syncState['sync_status'] ?? '') === 'synced' ? 'deal-pill--status-won' : (($syncState['sync_status'] ?? '') === 'failed' ? 'deal-pill--status-lost' : 'deal-pill--approval'); ?>">
                                                <?php echo ucfirst($syncState['sync_status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo html_escape($syncState['cursor_token'] ?: '-'); ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($sync_states)) { ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No sync states stored yet.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="deal-panel tw-mt-4">
                        <div class="deal-panel__header">
                            <h4 class="deal-panel__title">Platform Log</h4>
                            <span class="text-muted">Provider, event, and queue diagnostics</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover deal-data-table">
                                <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Action</th>
                                    <th>Level</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($logs as $log) { ?>
                                    <tr>
                                        <td><?php echo _dt($log['created_at']); ?></td>
                                        <td>
                                            <div class="deal-table-title"><?php echo html_escape($log['action']); ?></div>
                                            <div class="text-muted"><?php echo html_escape(mb_strimwidth($log['message'], 0, 90, '...')); ?></div>
                                        </td>
                                        <td>
                                            <span class="deal-pill <?php echo ($log['level'] ?? '') === 'error' ? 'deal-pill--status-lost' : (($log['level'] ?? '') === 'warning' ? 'deal-pill--approval' : 'deal-pill--status-won'); ?>">
                                                <?php echo ucfirst($log['level']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($logs)) { ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No platform logs recorded yet.</td>
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
<script>
    (function($) {
        'use strict';

        function updateOauthButtonLabel() {
            var providerKey = $('select[name="provider_key"]').val() || '';
            var labels = {
                google_workspace: 'Step 3: Save and Connect Google',
                slack: 'Step 3: Save and Connect Slack'
            };
            $('#platform-oauth-connect-btn').text(labels[providerKey] || 'Save and Connect');
            $('#platform-oauth-callback-preview').text('<?php echo site_url('deals/platform/oauth/'); ?>' + providerKey);
            updateProviderGuide(providerKey);
            updateSelectedProviderCard(providerKey);
            updateProviderDefaults(providerKey);
        }

        function updateProviderGuide(providerKey) {
            var guides = {
                google_workspace: {
                    title: 'Google Workspace',
                    description: 'Use this for Gmail, Calendar, and Drive. Create a Google OAuth app, paste the client ID and secret, then connect.',
                    stepTwo: 'Enable Gmail API, Google Calendar API, and Google Drive API.',
                    stepThree: 'Add the callback URL to Google Cloud Console under Authorized redirect URIs.',
                    clientIdHelp: 'Paste the OAuth client ID from Google Cloud Console.',
                    clientSecretHelp: 'Paste the OAuth client secret from Google Cloud Console.'
                },
                slack: {
                    title: 'Slack',
                    description: 'Use this for Slack workspace connection, messaging, and bot events. Create a Slack app, paste the client ID and secret, then connect.',
                    stepTwo: 'Set the redirect URL and add OAuth scopes like channels:read, chat:write, commands, and users:read.',
                    stepThree: 'Use the callback URL below in Slack OAuth & Permissions.',
                    clientIdHelp: 'Paste the Client ID from your Slack app OAuth settings.',
                    clientSecretHelp: 'Paste the Client Secret from your Slack app OAuth settings.'
                }
            };
            var guide = guides[providerKey] || guides.google_workspace;
            $('#platform-provider-focus-title').text(guide.title);
            $('#platform-provider-focus-description').text(guide.description);
            $('#platform-provider-focus-step-two').html(guide.stepTwo);
            $('#platform-provider-focus-step-three').html(guide.stepThree);
            $('#platform-client-id-help').text(guide.clientIdHelp);
            $('#platform-client-secret-help').text(guide.clientSecretHelp);
        }

        function updateSelectedProviderCard(providerKey) {
            $('.deal-side-card--selectable').removeClass('is-selected');
            $('.deal-side-card--selectable[data-provider-key="' + providerKey + '"]').addClass('is-selected');
        }

        function updateProviderDefaults(providerKey) {
            var $label = $('input[name="account_label"]');
            var currentValue = ($label.val() || '').trim();
            var defaults = {
                google_workspace: 'Primary Google Workspace',
                slack: 'Primary Slack Workspace'
            };
            var legacyValues = ['Primary Workspace', 'Primary Google Workspace', 'Primary Slack Workspace', ''];
            if (legacyValues.indexOf(currentValue) !== -1) {
                $label.val(defaults[providerKey] || 'Primary Workspace');
            }
        }

        $(function() {
            $(document).on('click', '.deal-config-section__toggle', function () {
                var $section = $(this).closest('.deal-config-section');
                var target = $(this).data('target');
                if (!target) {
                    return;
                }

                $section.toggleClass('is-open');
                $('#' + target).stop(true, true).slideToggle(160);
            });

            $('select[name="provider_key"]').on('change', updateOauthButtonLabel);
            $('.deal-provider-select-btn, .deal-side-card--selectable').on('click', function(e) {
                var providerKey = $(this).data('provider-key');
                if (!providerKey) {
                    return;
                }
                $('select[name="provider_key"]').val(providerKey).trigger('change');
                $('html, body').animate({
                    scrollTop: $('.deal-config-main').offset().top - 20
                }, 250);
            });
            updateOauthButtonLabel();
        });
    })(jQuery);
</script>
