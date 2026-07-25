<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// Ensure all required variables have defaults
$stats = isset($stats) ? $stats : ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0, 'pending_retry' => 0];
$pages = isset($pages) ? $pages : [];
$statuses = isset($statuses) ? $statuses : [];
$sources = isset($sources) ? $sources : [];
$staff = isset($staff) ? $staff : [];
$app_id = isset($app_id) ? $app_id : '';
$app_secret = isset($app_secret) ? $app_secret : '';
$webhook_url = isset($webhook_url) ? $webhook_url : '';
$webhook_token = isset($webhook_token) ? $webhook_token : '';
$access_token = isset($access_token) ? $access_token : '';
$default_assigned = isset($default_assigned) ? $default_assigned : '';
$default_source = isset($default_source) ? $default_source : '';
$default_status = isset($default_status) ? $default_status : '';
$duplicate_detection = isset($duplicate_detection) ? $duplicate_detection : '0';
$notifications_enabled = isset($notifications_enabled) ? $notifications_enabled : '1';
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Header -->
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin">
                                    <i class="fab fa-facebook" style="color: #3b5998;"></i>
                                    <i class="fab fa-instagram" style="color: #E1306C;"></i>
                                    <?php echo _l('facebookleadsintegration'); ?>
                                    <small class="text-muted">v<?php echo FB_LEADS_MODULE_VERSION; ?></small>
                                </h4>
                                <p class="text-muted mtop5"><?php echo _l('fb_leads_module_description'); ?></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('facebookleadsintegration/sync_history'); ?>" class="btn btn-default">
                                    <i class="fa fa-history"></i> <?php echo _l('fb_leads_sync_history'); ?>
                                </a>
                                <a href="<?php echo admin_url('facebookleadsintegration/field_mapping'); ?>" class="btn btn-default">
                                    <i class="fa fa-exchange"></i> <?php echo _l('fb_leads_field_mapping'); ?>
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Statistics Row -->
                        <div class="row mtop20">
                            <div class="col-md-3">
                                <div class="fb-leads-stat-box success">
                                    <div class="stat-number"><?php echo $stats['success'] ?? 0; ?></div>
                                    <div class="stat-label"><?php echo _l('fb_leads_synced_this_month'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fb-leads-stat-box danger">
                                    <div class="stat-number"><?php echo $stats['failed'] ?? 0; ?></div>
                                    <div class="stat-label"><?php echo _l('fb_leads_failed_this_month'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fb-leads-stat-box warning">
                                    <div class="stat-number"><?php echo $stats['pending_retry'] ?? 0; ?></div>
                                    <div class="stat-label"><?php echo _l('fb_leads_pending_retry'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fb-leads-stat-box info">
                                    <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                                    <div class="stat-label"><?php echo _l('fb_leads_total_this_month'); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Connection Status -->
                        <div class="row mtop20">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <i class="fa fa-plug"></i> <?php echo _l('fb_leads_connection_status'); ?>
                                        </h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="well well-sm text-center">
                                                    <span class="fb-leads-status-indicator <?php echo (!empty($app_id) && $app_id !== 'changeme') ? 'connected' : 'disconnected'; ?>"></span>
                                                    <strong><?php echo _l('fb_leads_app_credentials'); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo (!empty($app_id) && $app_id !== 'changeme') ? _l('fb_leads_configured') : _l('fb_leads_not_configured'); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="well well-sm text-center">
                                                    <?php $webhook_verified = get_option('fb_leads_webhook_verified') === '1'; ?>
                                                    <span class="fb-leads-status-indicator <?php echo $webhook_verified ? 'connected' : 'disconnected'; ?>"></span>
                                                    <strong><?php echo _l('fb_leads_webhook_status'); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo $webhook_verified ? _l('fb_leads_verified') : _l('fb_leads_not_verified'); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="well well-sm text-center">
                                                    <span class="fb-leads-status-indicator <?php echo !empty($access_token) ? 'connected' : 'disconnected'; ?>"></span>
                                                    <strong><?php echo _l('fb_leads_access_token'); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo !empty($access_token) ? _l('fb_leads_token_saved') : _l('fb_leads_no_token'); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mtop15">
                                            <button type="button" class="btn btn-info" id="btn-test-connection">
                                                <i class="fa fa-check-circle"></i> <?php echo _l('fb_leads_test_connection'); ?>
                                            </button>
                                            <button type="button" class="btn btn-success" id="btn-send-test-lead">
                                                <i class="fa fa-paper-plane"></i> <?php echo _l('fb_leads_send_test_lead'); ?>
                                            </button>
                                        </div>
                                        <div id="connection-test-results" class="mtop15" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Settings -->
                        <div class="row mtop20">
                            <!-- Facebook App Settings -->
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <i class="fab fa-facebook"></i> <i class="fab fa-instagram" style="color: #E1306C;"></i> <?php echo _l('fb_leads_app_settings'); ?>
                                        </h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label for="fb_leads_app_id"><?php echo _l('fb_leads_app_id'); ?></label>
                                            <input type="text" class="form-control" id="fb_leads_app_id" name="fb_leads_app_id" 
                                                   value="<?php echo htmlspecialchars($app_id); ?>" placeholder="123456789012345">
                                            <p class="fb-leads-help-text">
                                                <?php echo _l('fb_leads_app_id_help'); ?>
                                                <a href="https://developers.facebook.com/apps/" target="_blank"><?php echo _l('fb_leads_get_from_meta'); ?></a>
                                            </p>
                                        </div>
                                        <div class="form-group">
                                            <label for="fb_leads_app_secret"><?php echo _l('fb_leads_app_secret'); ?></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="fb_leads_app_secret" name="fb_leads_app_secret" 
                                                       value="<?php echo htmlspecialchars($app_secret); ?>" placeholder="••••••••••••••••">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button" onclick="togglePassword('fb_leads_app_secret')">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </span>
                                            </div>
                                            <p class="fb-leads-help-text"><?php echo _l('fb_leads_app_secret_help'); ?></p>
                                        </div>
                                        <button type="button" class="btn btn-primary" id="btn-save-app-settings">
                                            <i class="fa fa-save"></i> <?php echo _l('fb_leads_save_settings'); ?>
                                        </button>
                                    </div>
                                </div>

                                <!-- Webhook Settings -->
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <i class="fa fa-link"></i> <?php echo _l('fb_leads_webhook_settings'); ?>
                                        </h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label><?php echo _l('fb_leads_webhook_url'); ?></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($webhook_url); ?>" id="webhook-url">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button" onclick="copyToClipboard('webhook-url')">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                </span>
                                            </div>
                                            <p class="fb-leads-help-text"><?php echo _l('fb_leads_webhook_url_help'); ?></p>
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo _l('fb_leads_verify_token'); ?></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($webhook_token); ?>" id="verify-token">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button" onclick="copyToClipboard('verify-token')">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                </span>
                                            </div>
                                            <p class="fb-leads-help-text"><?php echo _l('fb_leads_verify_token_help'); ?></p>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i>
                                            <?php echo _l('fb_leads_webhook_instructions'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lead Settings -->
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <i class="fa fa-user-plus"></i> <?php echo _l('fb_leads_lead_settings'); ?>
                                        </h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label for="fb_leads_default_assigned"><?php echo _l('fb_leads_default_assigned'); ?></label>
                                            <select class="form-control selectpicker" id="fb_leads_default_assigned" name="fb_leads_default_assigned" data-live-search="true">
                                                <option value=""><?php echo _l('fb_leads_no_assignment'); ?></option>
                                                <?php foreach ($staff as $member): ?>
                                                    <option value="<?php echo $member['staffid']; ?>" <?php echo $default_assigned == $member['staffid'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($member['firstname'] . ' ' . $member['lastname']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <p class="fb-leads-help-text"><?php echo _l('fb_leads_default_assigned_help'); ?></p>
                                        </div>
                                        <div class="form-group">
                                            <label for="fb_leads_default_source"><?php echo _l('fb_leads_default_source'); ?></label>
                                            <select class="form-control selectpicker" id="fb_leads_default_source" name="fb_leads_default_source" data-live-search="true">
                                                <option value=""><?php echo _l('fb_leads_select_source'); ?></option>
                                                <?php foreach ($sources as $source): ?>
                                                    <option value="<?php echo $source['id']; ?>" <?php echo $default_source == $source['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($source['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fb_leads_default_status"><?php echo _l('fb_leads_default_status'); ?></label>
                                            <select class="form-control selectpicker" id="fb_leads_default_status" name="fb_leads_default_status" data-live-search="true">
                                                <option value=""><?php echo _l('fb_leads_select_status'); ?></option>
                                                <?php foreach ($statuses as $status): ?>
                                                    <option value="<?php echo $status['id']; ?>" <?php echo $default_status == $status['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($status['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <hr />
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" id="fb_leads_duplicate_detection" name="fb_leads_duplicate_detection" value="1" 
                                                   <?php echo $duplicate_detection == '1' ? 'checked' : ''; ?>>
                                            <label for="fb_leads_duplicate_detection"><?php echo _l('fb_leads_enable_duplicate_detection'); ?></label>
                                        </div>
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" id="fb_leads_notifications_enabled" name="fb_leads_notifications_enabled" value="1"
                                                   <?php echo $notifications_enabled == '1' ? 'checked' : ''; ?>>
                                            <label for="fb_leads_notifications_enabled"><?php echo _l('fb_leads_enable_notifications'); ?></label>
                                        </div>
                                        <button type="button" class="btn btn-primary mtop15" id="btn-save-lead-settings">
                                            <i class="fa fa-save"></i> <?php echo _l('fb_leads_save_settings'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Facebook Pages -->
                        <div class="row mtop20">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <i class="fa fa-flag"></i> <?php echo _l('fb_leads_facebook_pages'); ?>
                                        </h4>
                                    </div>
                                    <div class="panel-body">
                                        <p class="text-muted"><?php echo _l('fb_leads_pages_description'); ?></p>
                                        <button type="button" class="btn btn-info" id="btn-fetch-pages">
                                            <i class="fab fa-facebook"></i> <?php echo _l('fb_leads_connect_facebook'); ?>
                                        </button>
                                        <button type="button" class="btn btn-default" id="btn-refresh-pages" style="display: none;">
                                            <i class="fa fa-refresh"></i> <?php echo _l('fb_leads_refresh_pages'); ?>
                                        </button>
                                        <hr />
                                        <div id="pages-container">
                                            <?php if (!empty($pages)): ?>
                                                <table class="table table-striped table-bordered" id="pages-table">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo _l('page_name'); ?></th>
                                                            <th><?php echo _l('fb_leads_status'); ?></th>
                                                            <th><?php echo _l('fb_leads_leads_received'); ?></th>
                                                            <th><?php echo _l('fb_leads_page_assigned_to'); ?></th>
                                                            <th class="text-center"><?php echo _l('action'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($pages as $page): ?>
                                                            <tr data-page-id="<?php echo htmlspecialchars($page['page_id']); ?>">
                                                                <td>
                                                                    <strong><?php echo htmlspecialchars($page['page_name']); ?></strong>
                                                                    <br><small class="text-muted">ID: <?php echo htmlspecialchars($page['page_id']); ?></small>
                                                                </td>
                                                                <td>
                                                                    <?php if ($page['is_subscribed']): ?>
                                                                        <span class="label label-success"><?php echo _l('fb_leads_monitoring'); ?></span>
                                                                    <?php else: ?>
                                                                        <span class="label label-default"><?php echo _l('fb_leads_not_monitoring'); ?></span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?php echo (int) $page['leads_count']; ?></td>
                                                                <td>
                                                                    <select class="form-control input-sm page-assigned-select" 
                                                                            data-page-id="<?php echo htmlspecialchars($page['page_id']); ?>" 
                                                                            style="min-width: 140px;">
                                                                        <option value=""><?php echo _l('fb_leads_use_default'); ?></option>
                                                                        <?php foreach ($staff as $member): ?>
                                                                            <option value="<?php echo $member['staffid']; ?>" <?php echo (isset($page['assigned_to']) && $page['assigned_to'] == $member['staffid']) ? 'selected' : ''; ?>>
                                                                                <?php echo htmlspecialchars($member['firstname'] . ' ' . $member['lastname']); ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php if ($page['is_subscribed']): ?>
                                                                        <button type="button" class="btn btn-danger btn-sm page-action-btn" 
                                                                                data-page-id="<?php echo htmlspecialchars($page['page_id']); ?>" 
                                                                                data-action="unsubscribe">
                                                                            <?php echo _l('fb_leads_unsubscribe'); ?>
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <button type="button" class="btn btn-success btn-sm page-action-btn" 
                                                                                data-page-id="<?php echo htmlspecialchars($page['page_id']); ?>" 
                                                                                data-action="subscribe">
                                                                            <?php echo _l('fb_leads_subscribe'); ?>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <div class="alert alert-info">
                                                    <i class="fa fa-info-circle"></i> <?php echo _l('fb_leads_no_pages_yet'); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documentation Link -->
                        <div class="row mtop20">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-book"></i>
                                    <strong><?php echo _l('fb_leads_need_help'); ?></strong>
                                    <?php echo _l('fb_leads_documentation_text'); ?>
                                    <a href="https://themesic-docs.gitbook.io/facebook-and-instagram-leads-integration-module/" target="_blank" class="alert-link">
                                        <?php echo _l('fb_leads_view_documentation'); ?>
                                    </a>
                                    |
                                    <a href="https://themesic.com/support" target="_blank" class="alert-link">
                                        <?php echo _l('fb_leads_contact_support'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    // Wait for FBLeads to be available
    function waitForFBLeads(callback) {
        if (typeof FBLeads !== 'undefined') {
            callback();
        } else {
            setTimeout(function() { waitForFBLeads(callback); }, 100);
        }
    }

    // Toggle password visibility
    window.togglePassword = function(fieldId) {
        var field = document.getElementById(fieldId);
        field.type = field.type === 'password' ? 'text' : 'password';
    };

    // Copy to clipboard
    window.copyToClipboard = function(fieldId) {
        var field = document.getElementById(fieldId);
        field.select();
        document.execCommand('copy');
        alert_float('success', '<?php echo _l('fb_leads_copied_to_clipboard'); ?>');
    };

    waitForFBLeads(function() {
        // Test connection
        $('#btn-test-connection').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_testing'); ?>');
            
            FBLeads.testConnection(function(response) {
                btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> <?php echo _l('fb_leads_test_connection'); ?>');
                
                var html = '<div class="fb-leads-connection-test ' + (response.success ? 'success' : 'error') + '">';
                html += '<h5>' + (response.success ? '<?php echo _l('fb_leads_connection_successful'); ?>' : '<?php echo _l('fb_leads_connection_failed'); ?>') + '</h5>';
                
                if (response.results) {
                    html += '<ul class="list-unstyled">';
                    html += '<li><i class="fa fa-' + (response.results.credentials.valid ? 'check text-success' : 'times text-danger') + '"></i> <?php echo _l('fb_leads_app_credentials'); ?>: ' + response.results.credentials.message + '</li>';
                    html += '<li><i class="fa fa-' + (response.results.access_token.valid ? 'check text-success' : 'times text-danger') + '"></i> <?php echo _l('fb_leads_access_token'); ?>: ' + response.results.access_token.message + '</li>';
                    if (response.results.permissions) {
                        html += '<li><i class="fa fa-' + (response.results.permissions.has_all ? 'check text-success' : 'warning text-warning') + '"></i> <?php echo _l('fb_leads_permissions'); ?>: ';
                        if (response.results.permissions.has_all) {
                            html += '<?php echo _l('fb_leads_all_permissions_granted'); ?>';
                        } else {
                            html += '<?php echo _l('fb_leads_missing_permissions'); ?>: ' + response.results.permissions.missing.join(', ');
                        }
                        html += '</li>';
                    }
                    html += '</ul>';
                }
                html += '</div>';
                
                $('#connection-test-results').html(html).show();
            });
        });

        // Send test lead
        $('#btn-send-test-lead').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_sending'); ?>');
            
            FBLeads.sendTestLead(function(response) {
                btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo _l('fb_leads_send_test_lead'); ?>');
                
                if (response.success) {
                    alert_float('success', response.message);
                    if (response.lead_url) {
                        setTimeout(function() {
                            if (confirm('<?php echo _l('fb_leads_view_test_lead_confirm'); ?>')) {
                                window.open(response.lead_url, '_blank');
                            }
                        }, 1000);
                    }
                } else {
                    alert_float('danger', response.message);
                }
            });
        });

        // Save app settings
        $('#btn-save-app-settings').on('click', function() {
            var settings = {
                fb_leads_app_id: $('#fb_leads_app_id').val(),
                fb_leads_app_secret: $('#fb_leads_app_secret').val()
            };
            
            saveSettings(settings, $(this));
        });

        // Save lead settings
        $('#btn-save-lead-settings').on('click', function() {
            var settings = {
                fb_leads_default_assigned: $('#fb_leads_default_assigned').val(),
                fb_leads_default_source: $('#fb_leads_default_source').val(),
                fb_leads_default_status: $('#fb_leads_default_status').val(),
                fb_leads_duplicate_detection: $('#fb_leads_duplicate_detection').is(':checked') ? '1' : '0',
                fb_leads_notifications_enabled: $('#fb_leads_notifications_enabled').is(':checked') ? '1' : '0'
            };
            
            saveSettings(settings, $(this));
        });

        function saveSettings(settings, btn) {
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_saving'); ?>');
            
            FBLeads.saveSettings(settings, function(response) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> <?php echo _l('fb_leads_save_settings'); ?>');
                
                if (response.success) {
                    alert_float('success', response.message);
                } else {
                    alert_float('danger', response.message);
                }
            });
        }

        // Fetch pages
        $('#btn-fetch-pages').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_connecting'); ?>');
            
            FBLeads.loginWithFacebook(function(response) {
                btn.prop('disabled', false).html('<i class="fab fa-facebook"></i> <?php echo _l('fb_leads_connect_facebook'); ?>');
                
                if (response.success) {
                    alert_float('success', '<?php echo _l('fb_leads_pages_fetched'); ?>');
                    if (response.html) {
                        $('#pages-container').html(response.html);
                    }
                    $('#btn-refresh-pages').show();
                } else {
                    alert_float('danger', response.message);
                }
            });
        });

        // Per-page staff assignment
        $(document).on('change', '.page-assigned-select', function() {
            var select = $(this);
            var pageId = select.data('page-id');
            var staffId = select.val();
            
            $.ajax({
                url: admin_url + 'facebookleadsintegration/update_page_settings',
                type: 'POST',
                dataType: 'json',
                data: {
                    page_id: pageId,
                    assigned_to: staffId,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function() {
                    alert_float('danger', 'Failed to update page settings');
                }
            });
        });

        // Page action (subscribe/unsubscribe)
        $(document).on('click', '.page-action-btn', function() {
            var btn = $(this);
            var pageId = btn.data('page-id');
            var action = btn.data('action');
            
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            FBLeads.pageAction(pageId, action, function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    // Reload page to refresh status
                    location.reload();
                } else {
                    alert_float('danger', response.message);
                    btn.prop('disabled', false);
                    if (action === 'subscribe') {
                        btn.html('<?php echo _l('fb_leads_subscribe'); ?>');
                    } else {
                        btn.html('<?php echo _l('fb_leads_unsubscribe'); ?>');
                    }
                }
            });
        });
    });
});
</script>
