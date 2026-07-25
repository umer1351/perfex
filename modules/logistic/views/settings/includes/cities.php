<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_city(); return false;"><?php echo _l('add_city'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('lg_state'); ?></th>
		<th><?php echo _l('lg_city'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($cities) && count($cities) > 0){ ?>
			<?php foreach($cities as $city){ ?>
				<tr>
					<td><?php echo html_entity_decode($city['id']); ?></td>
					<td><?php echo lg_get_state_name_by_id($city['state']); ?></td>
					<td><?php echo html_entity_decode($city['city_name']); ?></td>

					<td>
						<a href="#" onclick="edit_city(this,<?php echo html_entity_decode($city['id']); ?>); return false" data-country="<?php echo html_entity_decode($city['country']); ?>" data-state="<?php echo html_entity_decode($city['state']); ?>" data-city_name="<?php echo html_entity_decode($city['city_name']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_city/' . $city['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="city_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_city'); ?></span>
					<span class="add-title"><?php echo _l('new_city'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/city_form',array('id'=>'city-setting-form')); ?>
			<?php echo form_hidden('city_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_city'); ?></label>
						<?php echo render_input('city_name','','','text', ['required' => 'true']); ?>


						<?php
		                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string')];
		                     $selected = '';

		                     echo render_select('country', $countries, ['id', 'country_name'], 'lg_country', $selected, $s_attrs);
		                     ?>

		                <?php
		                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string')];
		                     $selected = '';

		                     echo render_select('state', $states, ['id', 'state_name'], 'lg_state', $selected, $s_attrs);
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