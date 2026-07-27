<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                  <h3><?php echo _l('sms_title'); ?></h3>
                  <br>
                  <div class="emailsmswrapper">
                  <form action="<?php print(admin_url('customemailandsmsnotifications/email_sms/sendEmailSms')) ?>" enctype='multipart/form-data' method="post">
                    
                    <!-- Selection Method -->
                    <h5><?php echo _l('selection_method'); ?></h5>
                    <div class="radio radio-primary">
                        <input type="radio" name="selection_method" id="method_manual" value="manual" checked onchange="toggleSelectionMethod();">
                        <label for="method_manual"><?php echo _l('manual_selection'); ?></label>
                    </div>
                    <?php if (!empty($recipient_groups)): ?>
                    <div class="radio radio-primary">
                        <input type="radio" name="selection_method" id="method_group" value="group" onchange="toggleSelectionMethod();">
                        <label for="method_group"><?php echo _l('use_recipient_group'); ?></label>
                    </div>
                    <?php endif; ?>
                    <br>
                    
                    <!-- Recipient Groups (shown when group method selected) -->
                    <?php if (!empty($recipient_groups)): ?>
                    <div id="recipient_group_section" style="display: none;">
                        <h5><?php echo _l('select_recipient_group'); ?></h5>
                        <select class="selectpicker" name="recipient_group_id" id="recipient_group_id" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <option value=""><?php echo _l('none'); ?></option>
                            <?php foreach ($recipient_groups as $group): ?>
                                <option value="<?php echo $group->id; ?>"><?php echo $group->group_name; ?> (<?php echo $group->recipient_count; ?> recipients)</option>
                            <?php endforeach; ?>
                        </select>
                        <br><br>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Manual Selection (shown by default) -->
                    <div id="manual_selection_section">
                        <h5><?php echo _l('which_type_of_people'); ?></h5>
                        <select class="selectpicker"
                          name="customer_or_leads"
                          data-width="100%" id="customer_or_leads" onchange="show();">	      
                            <option value=""><?php echo _l('ceasn_none'); ?></option>
                            <option value="customers"><?php echo _l('ceasn_customers'); ?></option>
                            <option value="leads"><?php echo _l('ceasn_leads'); ?></option>
                        </select>
                        <br><br>
					<div class="customers" id="customers" style="display: none;">
						<div class="form-group select-placeholder">
							<label for="clientid" class="control-label"><h5><?php echo _l('select_customer'); ?></h5></label>
							<select id="clientid" name="select_customer[]" multiple="true" data-live-search="true" data-width="100%" class="ajax-search<?php if(isset($invoice) && empty($invoice->clientid)){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">

							<?php $selected = (isset($invoice) ? $invoice->clientid : '');

							if($selected == ''){
								$selected = (isset($customer_id) ? $customer_id: '');
							}

							if($selected != ''){
								$rel_data = get_relation_data('customer',$selected);
								$rel_val = get_relation_values($rel_data,'customer');
								echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
							}?>
							
							</select>
						</div>
					</div>

	                <div id="leads" style="display: none;">
	                    <div class="form-group select-placeholder">
        	                <label for="leadid" class="control-label"><h5><?php echo _l('select_lead'); ?></h5></label>
                            <select id="leadid" name="select_lead[]" multiple="multiple" data-live-search="true" data-width="100%" class="selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <?php foreach ($leads as $lead) { ?>
                                    <option value="<?php echo $lead->id; ?>"><?php echo $lead->name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
	                </div>
	                <br>
                </div>
				<hr>
			        <h5><?php echo _l('template_select_title'); ?></h5>
			        <select class="selectpicker"
		                  name="template"
		                  data-actions-box="true"
		                  data-width="100%" id="tempaltes">
	                     	<option value="">Nothing Selected</option>
	                     <?php foreach ($templates as $template) { ?>
							<option value="<?php print($template['id']) ?>"><?php print($template['template_name']) ?></option>
		                 <?php } ?>
	                  </select>
				    <br><br>

					  <div class="form-group">
					  	<h5><?php echo _l('subject'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="Supports {contact_firstname}, {contact_lastname} & {client_company}" data-original-title="" title=""></i></h5>
						<input type="text" class="form-control" name="subject">
					  </div>
						<br>
					  <h5><?php echo _l('write_your_notification'); ?></h5>
                      <script>
                        function smsMetrics(text) {
                            var gsmChars = /^[\x00-\x7F]*$/.test(text);
                            var single = gsmChars ? 160 : 70;
                            var multi = gsmChars ? 153 : 67;
                            var len = text.length;
                            var segments = len === 0 ? 0 : (len <= single ? 1 : Math.ceil(len / multi));
                            return { length: len, segments: segments };
                        }
                      </script>
	                  <textarea placeholder="<?php echo _l('sms_textarea_placeholder'); ?>" name="message" rows="10" class="form-control" id="msg_content"></textarea>
	                <p id="charNum"><i class="fa fa-calculator" aria-hidden="true"></i> 0</p>

						<hr>
	                  <div>
	                  		<h5><?php echo _l('attachment_note'); ?></h5>
		                  <input name="file_mail" value="filemail" class="check_label radio" type="file">
	                  </div>
						
					  <div><br><br></div>
	                  <div class="check_div_mail" style="margin-top:0px;"><hr>
					  <h5><?php echo _l('notification_type'); ?></h5>
		                  <input name="mail_or_sms" value="mail" class="check_label radio" type="radio" checked style="display:inline-block"> <span class="mail-or-sms-choice"><?php echo _l('send_as_email'); ?></span>
	                  </div>
					  <div class="check_div_sms">
		                  <input name="mail_or_sms" value="sms" class="check_label radio" type="radio" style="display:inline-block"> <span class="mail-or-sms-choice"><?php echo _l('send_as_sms'); ?></span>
					  </div>
                      <?php if (!empty($whatsapp_enabled)): ?>
					  <div class="check_div_sms">
		                  <input name="mail_or_sms" value="whatsapp" class="check_label radio" type="radio" style="display:inline-block"> <span class="mail-or-sms-choice"><?php echo _l('send_as_whatsapp'); ?></span>
					  </div>
                      <?php endif; ?>

					  <div class="check_div_mail" style="margin-top:0px;"><hr>
	                  	<label for="custom_date"><?php echo _l('custom_date'); ?></label>
						<input type="date" class="form-control" name="custom_date" id="date">
						<br>
						<div id="custom_time_div">
    						<label for="custom_time"><?php echo _l('custom_time'); ?></label>
    						<input type="time" class="form-control timepicker" name="custom_time" id="custom_time">
						</div>
	                  </div>
	                  <br>
	                  <button class="btn-tr btn btn-info invoice-form-submit transaction-submit"><?php echo _l('send'); ?></button>
                  </form>
                 </div>
               </div>
				
            </div>
         </div>
         <div class="col-md-6">
            <div class="panel_s">
                <div class="panel-body">
                    <h4><?php echo _l('delivery_preview'); ?></h4>
                    <div class="alert alert-info" style="margin-bottom:15px;">
                        <?php echo _l('delivery_preview_note'); ?>
                    </div>
                    <div class="preview-card" style="border:1px solid #e4e5e7;border-radius:6px;padding:15px;background:#fafafa;">
                        <div class="clearfix" style="margin-bottom:10px;">
                            <span id="preview_type" class="label label-primary"><?php echo _l('send_as_email'); ?></span>
                            <span id="preview_segments" class="label label-default pull-right" style="display:none;"></span>
                        </div>
                        <div id="preview_subject_wrap" style="margin-bottom:8px;">
                            <strong><?php echo _l('subject_simple'); ?>:</strong>
                            <span id="preview_subject" class="text-muted">—</span>
                        </div>
                        <div id="preview_message" style="white-space:pre-wrap;background:#fff;border:1px solid #eee;border-radius:6px;padding:10px;min-height:120px;">
                            <?php echo _l('delivery_preview_placeholder'); ?>
                        </div>
                        <div id="preview_whatsapp_badges" class="text-right" style="margin-top:10px;display:none;">
                            <span class="label label-default"><?php echo _l('whatsapp_badge_sent'); ?></span>
                            <span class="label label-info"><?php echo _l('whatsapp_badge_delivered'); ?></span>
                            <span class="label label-success"><?php echo _l('whatsapp_badge_read'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">

	function show(){
		var c_l = $('#customer_or_leads').val();
		
		if(c_l == 'customers'){
			$('#customers').show();
			$('#leads').hide();
		}else if(c_l == 'leads'){
			$('#leads').show();
			$('#customers').hide();
		}else{
			$('#leads').hide();
			$('#customers').hide();
		}
		
	}

	jQuery(document).ready(function($) {
        function sanitizePreviewHtml(html) {
            if (!html) {
                return '';
            }
            return html.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '');
        }

        function updatePreview() {
            var type = $('input[name="mail_or_sms"]:checked').val() || 'mail';
            var subject = $('input[name="subject"]').val();
            var message = $('#msg_content').val();
            var metrics = smsMetrics(message || '');
            var badgeEnabled = <?php echo (int) get_option('sms_whatsapp_delivery_badges'); ?> === 1;
            
            if (type === 'mail') {
                $('#preview_type').removeClass('label-info label-success').addClass('label-primary').text('<?php echo _l('send_as_email'); ?>');
                $('#preview_subject_wrap').show();
                $('#preview_segments').hide();
            } else if (type === 'sms') {
                $('#preview_type').removeClass('label-primary label-success').addClass('label-info').text('<?php echo _l('send_as_sms'); ?>');
                $('#preview_subject_wrap').hide();
                $('#preview_segments').show().text('<?php echo _l('sms_segments'); ?>: ' + metrics.segments + ' • <?php echo _l('sms_characters'); ?>: ' + metrics.length);
            } else {
                $('#preview_type').removeClass('label-primary label-info').addClass('label-success').text('<?php echo _l('send_as_whatsapp'); ?>');
                $('#preview_subject_wrap').hide();
                $('#preview_segments').show().text('<?php echo _l('sms_segments'); ?>: ' + metrics.segments + ' • <?php echo _l('sms_characters'); ?>: ' + metrics.length);
            }
            
            $('#preview_subject').text(subject ? subject : '—');
            if (message) {
                $('#preview_message').html(sanitizePreviewHtml(message));
            } else {
                $('#preview_message').text('<?php echo _l('delivery_preview_placeholder'); ?>');
            }
            
            if (type === 'whatsapp' && badgeEnabled) {
                $('#preview_whatsapp_badges').show();
            } else {
                $('#preview_whatsapp_badges').hide();
            }
            
            $('#charNum').html('<i class="fa fa-calculator" aria-hidden="true"></i> ' + metrics.length + ' • <?php echo _l('sms_segments'); ?>: ' + metrics.segments);
        }

        updatePreview();
        $('#msg_content').on('keyup change', updatePreview);
        $('input[name="subject"]').on('keyup change', updatePreview);
        $('input[name="mail_or_sms"]').on('change', updatePreview);

		$('#tempaltes').change(function(e){
        	var template_info_url = "<?= base_url(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/template/get_template_data'); ?>";
        	var template_id = $(this).val();
        	if (template_id === "") {
    			return false;
			}
			$.ajax({
				url: template_info_url,
				type: 'POST',
				dataType: 'json',
				data: {template_id:template_id},
				success:function(resJSON){
					$("#msg_content").val(resJSON[0].template_content);
                    updatePreview();
				}
			});	
		});
		$('#custom_time_div').hide();
		 $('input[name="custom_date"]').change(function () {
            var customDate = $(this).val();
            if (customDate !== "") {
                $('#custom_time_div').show();
            } else {
                $('#custom_time_div').hide();
            }
        });
		
		// Toggle selection method
		window.toggleSelectionMethod = function() {
			var method = $('input[name="selection_method"]:checked').val();
			if (method === 'group') {
				$('#recipient_group_section').show();
				$('#manual_selection_section').hide();
			} else {
				$('#recipient_group_section').hide();
				$('#manual_selection_section').show();
			}
		};
		
		// Load recipient group
		$('#recipient_group_id').on('change', function() {
			var groupId = $(this).val();
			if (!groupId) return;
			
			$.ajax({
				url: '<?php echo admin_url('customemailandsmsnotifications/recipient_groups/load_to_send_form'); ?>/' + groupId,
				type: 'GET',
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						// Set recipient type
						$('#customer_or_leads').val(response.group.recipient_type).trigger('change');
						
						// Wait for the fields to show, then populate
						setTimeout(function() {
							// Clear existing selections
							$('#clientid').val(null).trigger('change');
							$('#leadid').val(null).trigger('change');
							
							// Load customers
							if (response.customer_ids && response.customer_ids.length > 0) {
								response.customer_ids.forEach(function(customerId) {
									// Add option if not exists
									if ($('#clientid option[value="' + customerId + '"]').length === 0) {
										var newOption = new Option('Loading...', customerId, true, true);
										$('#clientid').append(newOption);
									} else {
										$('#clientid option[value="' + customerId + '"]').prop('selected', true);
									}
								});
								$('#clientid').trigger('change');
							}
							
							// Load leads
							if (response.lead_ids && response.lead_ids.length > 0) {
								response.lead_ids.forEach(function(leadId) {
									// Add option if not exists
									if ($('#leadid option[value="' + leadId + '"]').length === 0) {
										var newOption = new Option('Loading...', leadId, true, true);
										$('#leadid').append(newOption);
									} else {
										$('#leadid option[value="' + leadId + '"]').prop('selected', true);
									}
								});
								$('#leadid').trigger('change');
							}
						}, 500);
						
						alert_float('success', 'Recipient group loaded successfully');
					}
				}
			});
		});

		// Initialize selection method on load
		if (typeof window.toggleSelectionMethod === 'function') {
			window.toggleSelectionMethod();
		}
	});
</script>
<?php $toast = $this->session->flashdata('cesn_toast'); ?>
<?php if (is_array($toast) && !empty($toast['message'])): ?>
<script>
$(function() {
    alert_float('<?php echo $toast['type']; ?>', <?php echo json_encode($toast['message']); ?>);
});
</script>
<?php endif; ?>
</body>
</html>