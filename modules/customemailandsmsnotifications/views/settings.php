<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h3><?php echo _l('settings'); ?></h3>
                        <hr class="hr-panel-heading">
                        
                        <div class="alert alert-info">
                            <strong><?php echo _l('whatsapp_setup_title'); ?></strong><br>
                            <?php echo _l('whatsapp_setup_intro'); ?>
                            <ul class="mtop10">
                                <li><?php echo _l('whatsapp_setup_step_1'); ?></li>
                                <li><?php echo _l('whatsapp_setup_step_2'); ?></li>
                                <li><?php echo _l('whatsapp_setup_step_3'); ?></li>
                                <li><?php echo _l('whatsapp_setup_step_4'); ?></li>
                            </ul>
                            <p class="mtop10"><?php echo _l('whatsapp_setup_compliance'); ?></p>
                        </div>
                        
                        <form method="post" action="<?php echo admin_url('customemailandsmsnotifications/settings/save'); ?>">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            
                            <div class="form-group">
                                <label class="control-label"><?php echo _l('whatsapp_enable'); ?></label><br>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="sms_whatsapp_active" id="sms_whatsapp_active" value="1" <?php echo $whatsapp_active == 1 ? 'checked' : ''; ?>>
                                    <label for="sms_whatsapp_active"><?php echo _l('whatsapp_enable_label'); ?></label>
                                </div>
                                <small class="text-muted"><?php echo _l('whatsapp_enable_warning'); ?></small>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo _l('whatsapp_account_sid'); ?></label>
                                <input type="text" class="form-control" name="sms_whatsapp_account_sid" value="<?php echo html_escape($whatsapp_account_sid); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo _l('whatsapp_auth_token'); ?></label>
                                <input type="password" class="form-control" name="sms_whatsapp_auth_token" value="<?php echo html_escape($whatsapp_auth_token); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo _l('whatsapp_phone_number'); ?></label>
                                <input type="text" class="form-control" name="sms_whatsapp_phone_number" placeholder="+1234567890" value="<?php echo html_escape($whatsapp_phone_number); ?>">
                                <small class="text-muted"><?php echo _l('whatsapp_phone_hint'); ?></small>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label"><?php echo _l('whatsapp_delivery_badges'); ?></label><br>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="sms_whatsapp_delivery_badges" id="sms_whatsapp_delivery_badges" value="1" <?php echo $whatsapp_badges == 1 ? 'checked' : ''; ?>>
                                    <label for="sms_whatsapp_delivery_badges"><?php echo _l('whatsapp_delivery_badges_label'); ?></label>
                                </div>
                                <small class="text-muted"><?php echo _l('whatsapp_delivery_badges_note'); ?></small>
                            </div>
                            
                            <hr>
                            <div class="form-group">
                                <label class="control-label">Scheduled send batch size (per cron run)</label>
                                <input type="number" min="1" max="500" class="form-control" name="customemailandsmsnotifications_cron_batch_size" value="<?php echo html_escape($cron_batch_size); ?>" style="max-width:200px;">
                                <small class="text-muted">How many recipients are emailed/SMSed each time the cron runs. Large scheduled groups are sent a batch at a time across multiple cron runs so the server never times out. Recommended 50-100. Max 500.</small>
                            </div>

                            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                        </form>
                        
                        <hr>
                        
                        <div id="whatsapp_badges_preview" class="panel_s">
                            <div class="panel-body">
                                <strong><?php echo _l('whatsapp_delivery_badges_preview_title'); ?></strong>
                                <p class="text-muted"><?php echo _l('whatsapp_delivery_badges_preview_note'); ?></p>
                                <div class="text-right">
                                    <span class="label label-default"><?php echo _l('whatsapp_badge_sent'); ?></span>
                                    <span class="label label-info"><?php echo _l('whatsapp_badge_delivered'); ?></span>
                                    <span class="label label-success"><?php echo _l('whatsapp_badge_read'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <h4><?php echo _l('whatsapp_test_title'); ?></h4>
                        <p class="text-muted"><?php echo _l('whatsapp_test_note'); ?></p>
                        
                        <form id="whatsapp_test_form">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <div class="form-group">
                                <label><?php echo _l('whatsapp_test_to'); ?></label>
                                <input type="text" class="form-control" name="test_to" placeholder="+1234567890" required>
                            </div>
                            <div class="form-group">
                                <label><?php echo _l('whatsapp_test_message'); ?></label>
                                <textarea class="form-control" name="test_message" rows="3" required><?php echo _l('whatsapp_test_default_message'); ?></textarea>
                            </div>
                            <button type="button" id="whatsapp_test_btn" class="btn btn-info"><?php echo _l('whatsapp_send_test'); ?></button>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
function toggleWhatsappBadgesPreview() {
    var enabled = $('#sms_whatsapp_delivery_badges').is(':checked');
    if (enabled) {
        $('#whatsapp_badges_preview').show();
    } else {
        $('#whatsapp_badges_preview').hide();
    }
}

toggleWhatsappBadgesPreview();
$('#sms_whatsapp_delivery_badges').on('change', toggleWhatsappBadgesPreview);

$('#whatsapp_test_btn').on('click', function() {
    var formData = $('#whatsapp_test_form').serialize();
    $.post('<?php echo admin_url('customemailandsmsnotifications/settings/test_whatsapp'); ?>', formData, function(response) {
        var res = JSON.parse(response);
        if (res.success) {
            alert_float('success', res.message);
        } else {
            alert_float('warning', res.message);
        }
    });
});
</script>
</body>
</html>
