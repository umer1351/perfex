<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_shipping_rates_list(); return false;"><?php echo _l('add_shipping_rates_list'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('lg_origin'); ?></th>
		<th><?php echo _l('destination'); ?></th>
		<th><?php echo _l('start_weight_range'); ?></th>
		<th><?php echo _l('end_weight_range'); ?></th>
		<th><?php echo _l('rate_price'); ?></th>
		<th><?php echo _l('active'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($shipping_rates_lists) && count($shipping_rates_lists) > 0){ ?>
			<?php foreach($shipping_rates_lists as $shipping_rates_list){ ?>
				<tr>
					<td><?php echo html_entity_decode($shipping_rates_list['id']); ?></td>
					<td><?php echo lg_get_country_name_by_id($shipping_rates_list['country']); ?></td>
					<td><?php echo lg_get_country_name_by_id($shipping_rates_list['country']).' - '.lg_get_state_name_by_id($shipping_rates_list['state']).' - '.lg_get_city_name_by_id($shipping_rates_list['city']); ?></td>

					<td><?php echo app_format_number($shipping_rates_list['start_weight_range']); ?></td>
					<td><?php echo app_format_number($shipping_rates_list['end_weight_range']); ?></td>
					<td><?php echo app_format_number($shipping_rates_list['rate_price']); ?></td>
					<td>
						<?php 

							$checked = '';
				            if ($shipping_rates_list['active'] == 1) {
				                $checked = 'checked';
				            }
							$switch_html = '<div class="onoffswitch">
		                <input type="checkbox"  data-switch-url="' . admin_url() . 'logistic/change_logistic_rate_list_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $shipping_rates_list['id'] . '" data-id="' . $shipping_rates_list['id'] . '" ' . $checked . '>
		                <label class="onoffswitch-label" for="c_' . $shipping_rates_list['id'] . '"></label>
		            </div>';

		            	echo lg_html_entity_decode($switch_html);
						?>
					</td>
					<td>
						<a href="#" onclick="edit_shipping_rates_list(this,<?php echo html_entity_decode($shipping_rates_list['id']); ?>); return false" data-origin="<?php echo html_entity_decode($shipping_rates_list['origin']); ?>" data-country="<?php echo html_entity_decode($shipping_rates_list['country']); ?>" data-state="<?php echo html_entity_decode($shipping_rates_list['state']); ?>" data-start_weight_range="<?php echo html_entity_decode($shipping_rates_list['start_weight_range']); ?>" data-end_weight_range="<?php echo html_entity_decode($shipping_rates_list['end_weight_range']); ?>" data-rate_price="<?php echo html_entity_decode($shipping_rates_list['rate_price']); ?>" data-city="<?php echo html_entity_decode($shipping_rates_list['city']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_shipping_rates_list/' . $shipping_rates_list['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="shipping_rates_list_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_address'); ?></span>
					<span class="add-title"><?php echo _l('new_address'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/shipping_rates_list_form',array('id'=>'shipping_rates_list-setting-form')); ?>
			<?php echo form_hidden('shipping_rates_list_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">

						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_origin'); ?></label>   
						<?php
		                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true'];
		                     $selected = '';

		                     echo render_select('origin', $countries, ['id', 'country_name'], '', $selected, $s_attrs);
		                     ?>

		                <label for="name"><span class="text-danger">* </span><?php echo _l('destination').' '._l('lg_country'); ?></label>        
		                <?php
		                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true'];
		                     $selected = '';

		                     echo render_select('country', $countries, ['id', 'country_name'], '', $selected, $s_attrs);
		                     ?>     

		                <label for="name"><span class="text-danger">* </span><?php echo _l('destination').' '._l('lg_state'); ?></label>         
		                <?php
		                     echo render_select('state', $states, ['id', 'state_name'], '', $selected, $s_attrs);
		                     ?>
		                <label for="name"><span class="text-danger">* </span><?php echo _l('destination').' '._l('lg_city'); ?></label>       
		                <?php
		                     echo render_select('city', $cities, ['id', 'city_name'], '', $selected, $s_attrs);
		                     ?>
		              	<label for="name"><span class="text-danger">* </span><?php echo _l('start_weight_range'); ?></label>      
		                <?php echo render_input('start_weight_range','','','number', ['required' => 'true']); ?>

		                <label for="name"><span class="text-danger">* </span><?php echo _l('end_weight_range'); ?></label>
		                <?php echo render_input('end_weight_range','','','number', ['required' => 'true']); ?>

		                <label for="name"><span class="text-danger">* </span><?php echo _l('rate_price'); ?></label>
		                <?php echo render_input('rate_price','','','number', ['required' => 'true']); ?>

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