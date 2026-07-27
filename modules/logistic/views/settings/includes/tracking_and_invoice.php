<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
  <div class="col-md-12">
    <h4 class="bold"><?php echo _l('tracking_and_billing_information'); ?></h4>
    <hr class="bold mtop5 hr-panel-separator">
  </div>
</div>


<?php echo form_open('logistic/tracking_and_invoice_form',array('id'=>'tracking_and_invoice_form')); ?>
<div class="row">
	<div class="col-md-6">
		<label for="lg_delivery_prefix"><?php echo _l('lg_delivery_prefix'); ?></label>
		<?php echo render_input('lg_delivery_prefix','', get_option('lg_delivery_prefix'),'text'); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_number_digits_in_the_trace"><?php echo _l('lg_number_digits_in_the_trace'); ?></label>
		<?php echo render_input('lg_number_digits_in_the_trace','', get_option('lg_number_digits_in_the_trace'),'number', ['step' => '1']); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_consolidate_prefix"><?php echo _l('lg_consolidate_prefix'); ?></label>
		<?php echo render_input('lg_consolidate_prefix','', get_option('lg_consolidate_prefix'),'text' ); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_number_digits_in_the_consolidate"><?php echo _l('lg_number_digits_in_the_consolidate'); ?></label>
		<?php echo render_input('lg_number_digits_in_the_consolidate','', get_option('lg_number_digits_in_the_consolidate'),'number', ['step' => '1']); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_internet_shopping_prefix"><?php echo _l('lg_internet_shopping_prefix'); ?></label>
		<?php echo render_input('lg_internet_shopping_prefix','', get_option('lg_internet_shopping_prefix'),'text'); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_number_digits_to_track_locker_packages"><?php echo _l('lg_number_digits_to_track_locker_packages'); ?></label>
		<?php echo render_input('lg_number_digits_to_track_locker_packages','', get_option('lg_number_digits_to_track_locker_packages'),'number', ['step' => '1'] ); ?>
	</div>


	<div class="col-md-6">

		<label for="lg_tracking_number_type"><?php echo _l('lg_tracking_number_type'); ?></label>
		<?php 
		$options = [
			['id' => 'auto_increment', 'name' => _l('auto_increment')],
			['id' => 'random', 'name' => _l('lg_random')],
		
		]; 
		?>
		<?php echo render_select('lg_tracking_number_type', $options, array('id', 'name'), '',get_option('lg_tracking_number_type')); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_number_of_random_digits"><?php echo _l('lg_number_of_random_digits'); ?></label>
		<?php echo render_input('lg_number_of_random_digits','', get_option('lg_number_of_random_digits'),'number', ['step' => '1'] ); ?>
	</div>

	<div class="col-md-4">

		<label for="lg_virtual_locker_number_type"><?php echo _l('lg_virtual_locker_number_type'); ?></label>
		<?php 
		$options = [
			['id' => 'auto_increment', 'name' => _l('auto_increment')],
			['id' => 'random', 'name' => _l('lg_random')],
		
		]; 
		?>
		<?php echo render_select('lg_virtual_locker_number_type', $options, array('id', 'name'), '',get_option('lg_virtual_locker_number_type')); ?>
	</div>

	<div class="col-md-4">
		<label for="lg_locker_number_of_random_digits"><?php echo _l('lg_locker_number_of_random_digits'); ?></label>
		<?php echo render_input('lg_locker_number_of_random_digits','', get_option('lg_locker_number_of_random_digits'),'number', ['step' => '1'] ); ?>
	</div>

	<div class="col-md-4">
		<label for="lg_locker_prefix"><?php echo _l('lg_locker_prefix'); ?></label>
		<?php echo render_input('lg_locker_prefix','', get_option('lg_locker_prefix'), ); ?>
	</div>


	<div class="col-md-12">

		<?php echo render_textarea('lg_default_invoice_terms', 'lg_default_invoice_terms', get_option('lg_default_invoice_terms')); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_invoice_company_signature"><?php echo _l('lg_invoice_company_signature'); ?></label>
		<?php echo render_input('lg_invoice_company_signature','', get_option('lg_invoice_company_signature'),'text'); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_customer_signature_billing"><?php echo _l('lg_customer_signature_billing'); ?></label>
		<?php echo render_input('lg_customer_signature_billing','', get_option('lg_customer_signature_billing'),'text' ); ?>
	</div>


</div>

<div class="row">
  <div class="col-md-12">
    <hr class="bold mtop5 hr-panel-separator">
  </div>
</div>
<button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>

<?php echo form_close(); ?>