<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h3><?php echo isset($group) ? _l('edit_recipient_group') : _l('add_recipient_group'); ?></h3>
                  <hr class="hr-panel-heading">
                  
                  <form id="group_form">
                     <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                     <input type="hidden" name="id" value="<?php echo isset($group) ? $group->id : ''; ?>">
                     
                     <div class="form-group">
                        <label for="group_name"><?php echo _l('group_name'); ?> *</label>
                        <input type="text" class="form-control" name="group_name" value="<?php echo isset($group) ? $group->group_name : ''; ?>" required>
                     </div>
                     
                     <div class="form-group">
                        <label for="description"><?php echo _l('group_description'); ?></label>
                        <textarea class="form-control" name="description" rows="3"><?php echo isset($group) ? $group->description : ''; ?></textarea>
                     </div>
                     
                     <div class="form-group">
                        <label for="recipient_type"><?php echo _l('recipient_type'); ?> *</label>
                        <select class="form-control" name="recipient_type" id="recipient_type" onchange="toggleRecipientFields();" required>
                           <option value="">Select type</option>
                           <option value="customers" <?php echo isset($group) && $group->recipient_type == 'customers' ? 'selected' : ''; ?>>Customers</option>
                           <option value="leads" <?php echo isset($group) && $group->recipient_type == 'leads' ? 'selected' : ''; ?>>Leads</option>
                           <option value="mixed" <?php echo isset($group) && $group->recipient_type == 'mixed' ? 'selected' : ''; ?>>Mixed</option>
                        </select>
                     </div>
                     
                     <div class="form-group" id="customer_field" style="display:none;">
                        <label for="customer_ids"><?php echo _l('select_customer'); ?></label>
                        <select class="form-control selectpicker" name="customer_ids[]" multiple data-live-search="true" data-width="100%">
                           <?php 
                           $selected_customers = isset($group) ? json_decode($group->customer_ids, true) : [];
                           foreach ($clients as $client): ?>
                              <option value="<?php echo $client['id']; ?>" <?php echo in_array($client['id'], $selected_customers) ? 'selected' : ''; ?>>
                                 <?php echo $client['company']; ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                     
                     <div class="form-group" id="lead_field" style="display:none;">
                        <label for="lead_ids"><?php echo _l('select_lead'); ?></label>
                        <select class="form-control selectpicker" name="lead_ids[]" multiple data-live-search="true" data-width="100%">
                           <?php 
                           $selected_leads = isset($group) ? json_decode($group->lead_ids, true) : [];
                           foreach ($leads as $lead): ?>
                              <option value="<?php echo $lead['id']; ?>" <?php echo in_array($lead['id'], $selected_leads) ? 'selected' : ''; ?>>
                                 <?php echo $lead['name']; ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                     
                     <div class="form-group">
                        <button type="button" class="btn btn-info" onclick="saveGroup();"><?php echo _l('save'); ?></button>
                        <a href="<?php echo admin_url('customemailandsmsnotifications/recipient_groups'); ?>" class="btn btn-default"><?php echo _l('back'); ?></a>
                     </div>
                  </form>
                  
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>

<script>
function toggleRecipientFields() {
   var type = $('#recipient_type').val();
   $('#customer_field').hide();
   $('#lead_field').hide();
   
   if (type == 'customers' || type == 'mixed') {
      $('#customer_field').show();
   }
   if (type == 'leads' || type == 'mixed') {
      $('#lead_field').show();
   }
}

function saveGroup() {
   var formData = new FormData($('#group_form')[0]);
   
   $.ajax({
      url: '<?php echo admin_url('customemailandsmsnotifications/recipient_groups/save'); ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
         var res = JSON.parse(response);
         if (res.success) {
            alert_float('success', res.message);
            setTimeout(function() {
               window.location = '<?php echo admin_url('customemailandsmsnotifications/recipient_groups'); ?>';
            }, 1000);
         } else {
            alert_float('danger', res.message);
         }
      }
   });
}

$(document).ready(function() {
   toggleRecipientFields();
});
</script>
