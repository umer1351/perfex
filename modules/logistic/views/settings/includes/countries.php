<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_country(); return false;"><?php echo _l('add_country'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('country_name'); ?></th>
		<th><?php echo _l('iso_code'); ?></th>
		<th><?php echo _l('region'); ?></th>
		<th><?php echo _l('currency'); ?></th>
		<th><?php echo _l('active'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($countries) && count($countries) > 0){ ?>
			<?php foreach($countries as $country){ ?>
				<tr>
					<td><?php echo html_entity_decode($country['id']); ?></td>
					<td><?php echo html_entity_decode($country['country_name']); ?></td>
					<td><?php echo html_entity_decode($country['iso_code']); ?></td>
					<td><?php echo html_entity_decode($country['region']); ?></td>
					<td><?php 
						$_currency = ($country['currency_id'] != 0) ? get_currency($country['currency_id']) : '';
						echo isset($_currency->name) ? $_currency->name.' ('.$_currency->symbol.')' : '';
					 ?></td>
					<td>
						<?php 

							$checked = '';
				            if ($country['active'] == 1) {
				                $checked = 'checked';
				            }
							$switch_html = '<div class="onoffswitch">
		                <input type="checkbox"  data-switch-url="' . admin_url() . 'logistic/change_logistic_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $country['id'] . '" data-id="' . $country['id'] . '" ' . $checked . '>
		                <label class="onoffswitch-label" for="c_' . $country['id'] . '"></label>
		            </div>';

		            	echo lg_html_entity_decode($switch_html);
						?>
					</td>

					<td>
						<a href="#" onclick="edit_country(this,<?php echo html_entity_decode($country['id']); ?>); return false" data-country_name="<?php echo html_entity_decode($country['country_name']); ?>" data-iso_code="<?php echo html_entity_decode($country['iso_code']); ?>" data-phone_code="<?php echo html_entity_decode($country['phone_code']); ?>" data-capital="<?php echo html_entity_decode($country['capital']); ?>" data-region="<?php echo html_entity_decode($country['region']); ?>" data-currency_id="<?php echo html_entity_decode($country['currency_id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_country/' . $country['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="country_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_country'); ?></span>
					<span class="add-title"><?php echo _l('new_country'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/country_form',array('id'=>'country-setting-form')); ?>
			<?php echo form_hidden('country_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_country'); ?></label>
						<?php echo render_input('country_name','','','text', ['required' => 'true']); ?>
						<?php echo render_input('iso_code','iso_code','','text'); ?>
						<?php echo render_input('phone_code','phone_code','','text'); ?>
						<?php echo render_input('capital','capital','','text'); ?>
						<?php echo render_input('region','region','','text'); ?>
						 <?php
		                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string')];
		                     $selected = '';
		                   
		                     foreach ($currencies as $currency) {
		                         if (isset($client)) {
		                             if ($currency['id'] == $client->default_currency) {
		                                 $selected = $currency['id'];
		                             }
		                         }
		                     }
		                            // Do not remove the currency field from the customer profile!
		                     echo render_select('currency_id', $currencies, ['id', 'name', 'symbol'], 'invoice_add_edit_currency', $selected, $s_attrs);
		                     ?>


					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>