<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_state(); return false;"><?php echo _l('add_state'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('lg_country'); ?></th>
		<th><?php echo _l('lg_state'); ?></th>
		<th><?php echo _l('iso_code'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($states) && count($states) > 0){ ?>
			<?php foreach($states as $state){ ?>
				<tr>
					<td><?php echo html_entity_decode($state['id']); ?></td>
					<td><?php echo lg_get_country_name_by_id($state['country']); ?></td>
					<td><?php echo html_entity_decode($state['state_name']); ?></td>
					<td><?php echo html_entity_decode($state['iso_code']); ?></td>


					<td>
						<a href="#" onclick="edit_state(this,<?php echo html_entity_decode($state['id']); ?>); return false" data-country="<?php echo html_entity_decode($state['country']); ?>" data-iso_code="<?php echo html_entity_decode($state['iso_code']); ?>" data-state_name="<?php echo html_entity_decode($state['state_name']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_state/' . $state['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="state_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_state'); ?></span>
					<span class="add-title"><?php echo _l('new_state'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/state_form',array('id'=>'state-setting-form')); ?>
			<?php echo form_hidden('state_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_state'); ?></label>
						<?php echo render_input('state_name','','','text', ['required' => 'true']); ?>
						<?php echo render_input('iso_code','iso_code','','text'); ?>

						 <?php
		                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string')];
		                     $selected = '';

		                     echo render_select('country', $countries, ['id', 'country_name'], 'lg_country', $selected, $s_attrs);
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