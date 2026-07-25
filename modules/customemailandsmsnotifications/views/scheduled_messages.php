<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="clearfix">
                     <h3 class="pull-left"><?php echo _l('scheduled_messages'); ?></h3>
                     <div class="pull-right">
                        <a href="<?php echo admin_url('customemailandsmsnotifications/email_sms/email_or_sms'); ?>" class="btn btn-info">
                           <i class="fa fa-plus"></i> <?php echo _l('schedule_new_message'); ?>
                        </a>
                     </div>
                  </div>
                  <hr class="hr-panel-heading">
                  
                  <!-- Statistics -->
                  <div class="row">
                     <div class="col-md-3">
                        <div class="panel_s">
                           <div class="panel-body text-center">
                              <h4 class="no-margin bold"><?php echo $scheduled_count; ?></h4>
                              <small class="text-muted"><?php echo _l('total_scheduled'); ?></small>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <!-- Scheduled Messages Table -->
                  <div class="clearfix"></div>
                  <div class="_buttons">
                     <div class="visible-xs">
                        <div class="clearfix"></div>
                     </div>
                  </div>
                  <div class="clearfix"></div>
                  <br>
                  <div class="row">
                     <div class="col-md-12">
                        <div class="panel_s">
                           <div class="panel-body">
                              <?php render_datatable([
                                 _l('id'),
                                 _l('recipient_type'),
                                 _l('subject'),
                                 _l('message_type'),
                                 _l('scheduled_for'),
                                 _l('actions')
                              ], 'scheduled_messages'); ?>
                           </div>
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
$(function(){
    initDataTable('.table-scheduled_messages', window.location.href, [5], [5]);
});
</script>
