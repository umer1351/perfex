<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix">
                            <h3 class="pull-left"><?php echo _l('edit'); ?> <?php echo _l('scheduled_messages'); ?></h3>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('customemailandsmsnotifications/scheduled_messages'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">
                        
                        <?php
                            $selected_customers = [];
                            $selected_leads = [];
                            $stored_ids = json_decode($message->select_customer, true);
                            $stored_ids = is_array($stored_ids) ? $stored_ids : [];
                            if ($message->customer_or_leads == 'leads') {
                                $selected_leads = $stored_ids;
                            } else {
                                $selected_customers = $stored_ids;
                            }
                        ?>
                        
                        <form id="scheduled_message_form">
                            <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                            <input type="hidden" name="id" value="<?php echo $message->id; ?>">
                            
                            <div class="form-group">
                                <label for="customer_or_leads"><?php echo _l('recipient_type'); ?></label>
                                <select class="selectpicker" name="customer_or_leads" id="customer_or_leads" data-width="100%">
                                    <option value="customers" <?php echo $message->customer_or_leads == 'customers' ? 'selected' : ''; ?>><?php echo _l('ceasn_customers'); ?></option>
                                    <option value="leads" <?php echo $message->customer_or_leads == 'leads' ? 'selected' : ''; ?>><?php echo _l('ceasn_leads'); ?></option>
                                </select>
                            </div>
                            
                            <div id="customers" style="display: none;">
                                <div class="form-group">
                                    <label for="select_customer"><?php echo _l('select_customer'); ?></label>
                                    <select class="selectpicker" name="select_customer[]" multiple data-live-search="true" data-width="100%">
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client->userid; ?>" <?php echo in_array($client->userid, $selected_customers) ? 'selected' : ''; ?>>
                                                <?php echo $client->company; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div id="leads" style="display: none;">
                                <div class="form-group">
                                    <label for="select_lead"><?php echo _l('select_lead'); ?></label>
                                    <select class="selectpicker" name="select_lead[]" multiple data-live-search="true" data-width="100%">
                                        <?php foreach ($leads as $lead): ?>
                                            <option value="<?php echo $lead->id; ?>" <?php echo in_array($lead->id, $selected_leads) ? 'selected' : ''; ?>>
                                                <?php echo $lead->name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="template"><?php echo _l('template_select_title'); ?></label>
                                <select class="selectpicker" name="template" data-width="100%">
                                    <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?php echo $template['id']; ?>" <?php echo $message->template == $template['id'] ? 'selected' : ''; ?>>
                                            <?php echo $template['template_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject"><?php echo _l('subject'); ?></label>
                                <input type="text" class="form-control" name="subject" value="<?php echo htmlspecialchars($message->subject); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="message"><?php echo _l('message'); ?></label>
                                <textarea class="form-control" name="message" rows="6"><?php echo htmlspecialchars($message->message); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo _l('notification_type'); ?></label><br>
                                <label class="radio-inline">
                                    <input type="radio" name="mail_or_sms" value="mail" <?php echo $message->mail_or_sms == 'mail' ? 'checked' : ''; ?>> <?php echo _l('send_as_email'); ?>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="mail_or_sms" value="sms" <?php echo $message->mail_or_sms == 'sms' ? 'checked' : ''; ?>> <?php echo _l('send_as_sms'); ?>
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label for="custom_date"><?php echo _l('custom_date'); ?></label>
                                <input type="date" class="form-control" name="custom_date" value="<?php echo $message->custom_date; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="custom_time"><?php echo _l('custom_time'); ?></label>
                                <input type="time" class="form-control" name="custom_time" value="<?php echo $message->custom_time; ?>">
                            </div>
                            
                            <button type="button" class="btn btn-info" id="save_scheduled_message"><?php echo _l('save'); ?></button>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function(){
    function toggleRecipientFields() {
        var type = $('#customer_or_leads').val();
        if (type === 'leads') {
            $('#customers').hide();
            $('#leads').show();
        } else {
            $('#leads').hide();
            $('#customers').show();
        }
    }
    
    toggleRecipientFields();
    $('#customer_or_leads').on('change', toggleRecipientFields);
    
    $('#save_scheduled_message').on('click', function() {
        var formData = $('#scheduled_message_form').serialize();
        $.post('<?php echo admin_url('customemailandsmsnotifications/scheduled_messages/update'); ?>', formData, function(response) {
            var res = JSON.parse(response);
            if (res.success) {
                alert_float('success', res.message);
                window.location = '<?php echo admin_url('customemailandsmsnotifications/scheduled_messages'); ?>';
            } else {
                alert_float('danger', res.message);
            }
        });
    });
});
</script>
