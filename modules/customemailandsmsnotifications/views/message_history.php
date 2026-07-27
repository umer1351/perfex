<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h3><?php echo _l('message_history'); ?></h3>
                  <hr class="hr-panel-heading">
                  
                  <!-- Statistics Cards -->
                  <div class="row">
                     <div class="col-md-3">
                        <div class="panel_s">
                           <div class="panel-body text-center">
                              <h4 class="text-success"><?php echo $total_sent; ?></h4>
                              <p><?php echo _l('total_sent'); ?></p>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="panel_s">
                           <div class="panel-body text-center">
                              <h4 class="text-danger"><?php echo $total_failed; ?></h4>
                              <p><?php echo _l('total_failed'); ?></p>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="panel_s">
                           <div class="panel-body text-center">
                              <h4 class="text-warning"><?php echo $total_scheduled; ?></h4>
                              <p><?php echo _l('total_scheduled'); ?></p>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="panel_s">
                           <div class="panel-body text-center">
                              <h4 class="text-info"><?php echo $total_pending; ?></h4>
                              <p><?php echo _l('total_pending'); ?></p>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <!-- Filters -->
                  <div class="row">
                     <div class="col-md-12">
                        <div class="pull-right">
                           <a href="<?php echo admin_url('customemailandsmsnotifications/message_history/export'); ?>" class="btn btn-primary">
                              <i class="fa fa-download"></i> <?php echo _l('export_history'); ?>
                           </a>
                           <a href="<?php echo admin_url('customemailandsmsnotifications/message_history/clear_logs'); ?>" class="btn btn-danger _delete" data-confirm="<?php echo _l('confirm_clear_logs'); ?>">
                              <i class="fa fa-trash"></i> <?php echo _l('clear_logs'); ?>
                           </a>
                        </div>
                     </div>
                  </div>
                  
                  <div class="clearfix"></div>
                  <br>
                  
                  <!-- Message History Table -->
                  <div class="row">
                     <div class="col-md-12">
                        <?php render_datatable([
                           _l('id'),
                           _l('message_type'),
                           _l('recipient_name'),
                           _l('recipient_contact'),
                           _l('subject'),
                           _l('status'),
                           _l('gateway'),
                           _l('sent_at'),
                           _l('actions')
                        ], 'message_history'); ?>
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
    initDataTable('.table-message_history', window.location.href, [8], [8]);
});
</script>
