<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_style_and_state(); return false;"><?php echo _l('add_style_and_state'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('style_name'); ?></th>
		<th><?php echo _l('description'); ?></th>
		<th><?php echo _l('button_color'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($style_and_states) && count($style_and_states) > 0){ ?>
			<?php foreach($style_and_states as $style_and_state){ ?>
				<tr>
					<td><?php echo html_entity_decode($style_and_state['id']); ?></td>
					<td><?php echo html_entity_decode($style_and_state['style_name']); ?></td>
					<td><?php echo html_entity_decode($style_and_state['description']); ?></td>
					<td><span class="label" style="background-color: <?php echo html_entity_decode($style_and_state['button_color']); ?>;"><?php echo html_entity_decode($style_and_state['button_color']); ?></span></td>
					<td>
						<?php if($style_and_state['is_default_status'] != 1){ ?>
						<a href="#" onclick="edit_style_and_state(this,<?php echo html_entity_decode($style_and_state['id']); ?>); return false" data-style_name="<?php echo html_entity_decode($style_and_state['style_name']); ?>" data-description="<?php echo html_entity_decode($style_and_state['description']); ?>" data-button_color="<?php echo html_entity_decode($style_and_state['button_color']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_style_and_state/' . $style_and_state['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
          				<?php } ?>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="style_and_state_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_style_and_state'); ?></span>
					<span class="add-title"><?php echo _l('new_style_and_state'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/style_and_state_form',array('id'=>'style_and_state-setting-form')); ?>
			<?php echo form_hidden('style_and_state_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_style_and_state'); ?></label>
						<?php echo render_input('style_name','','','text', ['required' => 'true']); ?>
						<?php echo render_color_picker('button_color', _l('button_color'),''); ?>
						<?php echo render_textarea('description','description'); ?>
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