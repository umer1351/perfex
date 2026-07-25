<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <a href="<?php echo admin_url('customemailandsmsnotifications/recipient_groups/manage'); ?>" class="btn btn-info pull-right">
                        <i class="fa fa-plus"></i> <?php echo _l('add_recipient_group'); ?>
                     </a>
                  </div>
                  <div class="clearfix"></div>
                  <hr class="hr-panel-heading">
                  
                  <h3><?php echo _l('recipient_groups'); ?></h3>
                  
                  <!-- Groups Table -->
                  <div class="row">
                     <div class="col-md-12">
                        <?php render_datatable([
                           _l('id'),
                           _l('group_name'),
                           _l('description'),
                           _l('recipient_type'),
                           _l('recipient_count'),
                           _l('created_at'),
                           _l('actions')
                        ], 'recipient_groups'); ?>
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
$(function(){
    initDataTable('.table-recipient_groups', window.location.href, [6], [6]);
});
</script>
