<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
  <div class="col-md-12">
    <h4 class="bold"><?php echo _l('default_shipping_settings'); ?></h4>
    <hr class="bold mtop5 hr-panel-separator">
  </div>
</div>

<?php echo form_open('logistic/default_shipping_info_form',array('id'=>'default_shipping_info_form')); ?>
<div class="row">
  

  <div class="col-md-3">
    <label for="lg_default_logistic_service"><?php echo _l('lg_default_logistic_service'); ?></label>
    <?php echo render_select('lg_default_logistic_service', $logistics_services, array('id', 'logistics_service_name'), '',get_option('lg_default_logistic_service')); ?>
  </div>
  <div class="col-md-3">
    <label for="lg_default_type_of_package"><?php echo _l('lg_default_type_of_package'); ?></label>
    <?php echo render_select('lg_default_type_of_package', $type_of_packages, array('id', 'type_of_package_name'), '',get_option('lg_default_type_of_package')); ?>
  </div>

  <div class="col-md-3">
    <label for="lg_default_courier_company"><?php echo _l('lg_default_courier_company'); ?></label>
    <?php echo render_select('lg_default_courier_company', $shipping_companys, array('id', 'shipping_company_name'), '',get_option('lg_default_courier_company')); ?>
  </div>

  <div class="col-md-3">
    <label for="lg_default_service_mode"><?php echo _l('lg_default_service_mode'); ?></label>
    <?php echo render_select('lg_default_service_mode', $shipping_modes, array('id', 'shipping_mode_name'), '',get_option('lg_default_service_mode')); ?>
  </div>

  <div class="col-md-3">
    <label for="lg_default_delivery_time"><?php echo _l('lg_default_delivery_time'); ?></label>
    <?php echo render_select('lg_default_delivery_time', $shipping_times, array('id', 'shipping_time_name'), '',get_option('lg_default_delivery_time')); ?>
  </div>

   <div class="col-md-3">
    <label for="lg_default_payment_method"><?php echo _l('lg_default_payment_method'); ?></label>
    <?php echo render_select('lg_default_payment_method', $payment_modes, array('id', 'name'), '',get_option('lg_default_payment_method')); ?>
  </div>

  <div class="col-md-3">
    <label for="lg_default_payment_terms"><?php echo _l('lg_default_payment_terms'); ?></label>
    <?php echo render_select('lg_default_payment_terms', $payment_terms, array('id', 'name'), '',get_option('lg_default_payment_terms')); ?>
  </div>

  <div class="col-md-3">

    <?php 
      $style_and_states[] = ['id' => 'delivered', 'style_name' => _l('lg_delivered')];
      $style_and_states[] = ['id' => 'pickup', 'style_name' => _l('lg_pickup')];
      $style_and_states[] = ['id' => 'consolidate', 'style_name' => _l('lg_consolidate')];
      $style_and_states[] = ['id' => 'cancelled', 'style_name' => _l('lg_cancelled')];
      $style_and_states[] = ['id' => 'quotation', 'style_name' => _l('lg_quotation')];
      $style_and_states[] = ['id' => 'picked_up', 'style_name' => _l('lg_picked_up')];
      $style_and_states[] = ['id' => 'pending', 'style_name' => _l('lg_pending')];
      $style_and_states[] = ['id' => 'no_picked_up', 'style_name' => _l('lg_no_picked_up')];
      $style_and_states[] = ['id' => 'invoiced', 'style_name' => _l('lg_invoiced')];
      $style_and_states[] = ['id' => 'approved', 'style_name' => _l('lg_approved')];
      $style_and_states[] = ['id' => 'rejected', 'style_name' => _l('lg_rejected')];

    ?>
    <label for="lg_default_delivery_status"><?php echo _l('lg_default_delivery_status'); ?></label>
    <?php echo render_select('lg_default_delivery_status', $style_and_states, array('id', 'style_name'), '',get_option('lg_default_delivery_status')); ?>
  </div>


</div>

<div class="row">
  <div class="col-md-12">
    <hr class="bold mtop5 hr-panel-separator">
  </div>
</div>
<button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>

<?php echo form_close(); ?>