<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Load models if not already loaded
$CI = &get_instance();
$CI->load->model('leads_model');
$CI->load->model('staff_model');

$statuses = $CI->leads_model->get_status();
$sources = $CI->leads_model->get_source();
$staff = $CI->staff_model->get('', ['active' => 1]);

$app_id = get_option('fb_leads_app_id') ?: '';
$webhook_token = get_option('fb_leads_webhook_token') ?: '';
$webhook_url = site_url('facebookleadsintegration/webhook');
$default_assigned = get_option('fb_leads_default_assigned');
$default_source = get_option('fb_leads_default_source');
$default_status = get_option('fb_leads_default_status');
$duplicate_detection = get_option('fb_leads_duplicate_detection');
$notifications_enabled = get_option('fb_leads_notifications_enabled');
$webhook_verified = get_option('fb_leads_webhook_verified') === '1';
?>

<div class="row">
    <div class="col-md-12">
        <!-- Quick Status -->
        <div class="alert <?php echo $webhook_verified && !empty($app_id) && $app_id !== 'changeme' ? 'alert-success' : 'alert-warning'; ?>">
            <div class="row">
                <div class="col-md-8">
                    <strong>
                        <i class="fab fa-facebook"></i> <i class="fab fa-instagram" style="color: #E1306C;"></i> <?php echo _l('facebookleadsintegration'); ?>
                    </strong>
                    <br>
                    <?php if ($webhook_verified && !empty($app_id) && $app_id !== 'changeme'): ?>
                        <i class="fa fa-check text-success"></i> <?php echo _l('fb_leads_module_configured'); ?>
                    <?php else: ?>
                        <i class="fa fa-exclamation-triangle text-warning"></i> <?php echo _l('fb_leads_module_needs_config'); ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-right">
                    <a href="<?php echo admin_url('facebookleadsintegration'); ?>" class="btn btn-info">
                        <i class="fa fa-cog"></i> <?php echo _l('fb_leads_open_full_settings'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Settings -->
        <h4 class="bold"><?php echo _l('fb_leads_quick_settings'); ?></h4>
        <hr class="hr-panel-heading" />

        <div class="row">
            <div class="col-md-6">
                <?php echo render_input('settings[fb_leads_app_id]', 'fb_leads_app_id', $app_id); ?>
            </div>
            <div class="col-md-6">
                <?php echo render_input('settings[fb_leads_app_secret]', 'fb_leads_app_secret', '', 'password'); ?>
                <p class="text-muted"><small><?php echo _l('fb_leads_leave_blank_unchanged'); ?></small></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><?php echo _l('fb_leads_webhook_url'); ?></label>
                    <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($webhook_url); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><?php echo _l('fb_leads_verify_token'); ?></label>
                    <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($webhook_token); ?>">
                </div>
            </div>
        </div>

        <h4 class="bold mtop20"><?php echo _l('fb_leads_default_lead_settings'); ?></h4>
        <hr class="hr-panel-heading" />

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label><?php echo _l('fb_leads_default_assigned'); ?></label>
                    <select name="settings[fb_leads_default_assigned]" class="form-control selectpicker" data-live-search="true">
                        <option value=""><?php echo _l('fb_leads_no_assignment'); ?></option>
                        <?php foreach ($staff as $member): ?>
                            <option value="<?php echo $member['staffid']; ?>" <?php echo $default_assigned == $member['staffid'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($member['firstname'] . ' ' . $member['lastname']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?php echo _l('fb_leads_default_source'); ?></label>
                    <select name="settings[fb_leads_default_source]" class="form-control selectpicker" data-live-search="true">
                        <option value=""><?php echo _l('fb_leads_select_source'); ?></option>
                        <?php foreach ($sources as $source): ?>
                            <option value="<?php echo $source['id']; ?>" <?php echo $default_source == $source['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($source['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><?php echo _l('fb_leads_default_status'); ?></label>
                    <select name="settings[fb_leads_default_status]" class="form-control selectpicker" data-live-search="true">
                        <option value=""><?php echo _l('fb_leads_select_status'); ?></option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status['id']; ?>" <?php echo $default_status == $status['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?php echo render_yes_no_option('fb_leads_duplicate_detection', 'fb_leads_enable_duplicate_detection'); ?>
            </div>
            <div class="col-md-6">
                <?php echo render_yes_no_option('fb_leads_notifications_enabled', 'fb_leads_enable_notifications'); ?>
            </div>
        </div>

        <!-- Links to full settings -->
        <div class="mtop20">
            <a href="<?php echo admin_url('facebookleadsintegration'); ?>" class="btn btn-info">
                <i class="fa fa-cog"></i> <?php echo _l('fb_leads_full_settings'); ?>
            </a>
            <a href="<?php echo admin_url('facebookleadsintegration/sync_history'); ?>" class="btn btn-default">
                <i class="fa fa-history"></i> <?php echo _l('fb_leads_sync_history'); ?>
            </a>
            <a href="<?php echo admin_url('facebookleadsintegration/field_mapping'); ?>" class="btn btn-default">
                <i class="fa fa-exchange"></i> <?php echo _l('fb_leads_field_mapping'); ?>
            </a>
        </div>
    </div>
</div>
