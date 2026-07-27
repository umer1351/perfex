<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
  <div class="col-md-12">
    <h4 class="bold"><?php echo _l('set_taxes_fees'); ?></h4>
    <hr class="bold mtop5 hr-panel-separator">
  </div>
</div>

<?php $base_currency = get_base_currency(); ?>

<?php echo form_open('logistic/taxes_setting_form',array('id'=>'style_and_state-setting-form')); ?>
<div class="row">
	<div class="col-md-6">
		<label for="lg_minium_cost_to_apply_the_tax"><?php echo _l('lg_minium_cost_to_apply_the_tax'); ?><span class="text-danger"> (<?php echo html_entity_decode($base_currency->name); ?>)</span></label>
		<?php echo render_input('lg_minium_cost_to_apply_the_tax','', get_option('lg_minium_cost_to_apply_the_tax'),'number', ['step' => '0.1']); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_tax_percent"><?php echo _l('lg_tax_percent'); ?><span class="text-danger"> (%)</span></label>
		<?php echo render_input('lg_tax_percent','', get_option('lg_tax_percent'),'number', ['step' => '0.1']); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_minium_cost_to_apply_declared_tax"><?php echo _l('lg_minium_cost_to_apply_declared_tax'); ?><span class="text-danger"> (<?php echo html_entity_decode($base_currency->name); ?>)</span></label>
		<?php echo render_input('lg_minium_cost_to_apply_declared_tax','', get_option('lg_minium_cost_to_apply_declared_tax'),'number', ['step' => '0.1'] ); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_tax_declared"><?php echo _l('lg_tax_declared'); ?><span class="text-danger"> (%)</span></label>
		<?php echo render_input('lg_tax_declared','', get_option('lg_tax_declared'),'number', ['step' => '0.1']); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_shipping_insurance_percent"><?php echo _l('lg_shipping_insurance_percent'); ?><span class="text-danger"> (%)</span></label>
		<?php echo render_input('lg_shipping_insurance_percent','', get_option('lg_shipping_insurance_percent'),'number', ['step' => '0.1'] ); ?>
	</div>

	<div class="col-md-6">
		<label for="lg_customs_duties"><?php echo _l('lg_customs_duties'); ?><span class="text-danger"> (%)</span></label>
		<?php echo render_input('lg_customs_duties','', get_option('lg_customs_duties'),'number', ['step' => '0.1'] ); ?>
	</div>

	<div class="col-md-3">
		<label for="lg_volume_percentage_l_w_h"><?php echo _l('lg_volume_percentage_l_w_h'); ?></label>
		<?php echo render_input('lg_volume_percentage_l_w_h','', get_option('lg_volume_percentage_l_w_h'),'number', ['step' => '0.1'] ); ?>
	</div>

	<div class="col-md-3">

		<label for="lg_length_units"><?php echo _l('lg_length_units'); ?></label>
		<?php 
		$length_options = [
			['id' => 'cm', 'name' => _l('centimeter')],
			['id' => 'm', 'name' => _l('meter')],
			['id' => 'Pie', 'name' => _l('foot')],
			['id' => 'in', 'name' => _l('inch')],
		]; 
		?>
		<?php echo render_select('lg_length_units', $length_options, array('id', 'name'), '',get_option('lg_length_units')); ?>
	</div>

	<div class="col-md-3">
		<label for="lg_weight_value"><?php echo _l('lg_weight_value'); ?><span class="text-danger"> (<?php echo html_entity_decode($base_currency->name); ?>)</span></label>
		<?php echo render_input('lg_weight_value','', get_option('lg_weight_value'),'number', ['step' => '0.1'] ); ?>
	</div>


	<div class="col-md-3">

		<label for="lg_length_units"><?php echo _l('lg_weight_units'); ?></label>
		<?php 
		$length_options = [
			['id' => 'kg', 'name' => _l('kilo')],
			['id' => 'lb', 'name' => _l('pound')],
		
		]; 
		?>
		<?php echo render_select('lg_weight_units', $length_options, array('id', 'name'), '',get_option('lg_weight_units')); ?>
	</div>
</div>

<div class="row">
  <div class="col-md-12">
    <hr class="bold mtop5 hr-panel-separator">
  </div>
</div>
<button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>

<?php echo form_close(); ?>