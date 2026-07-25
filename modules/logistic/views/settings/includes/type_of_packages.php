<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_type_of_package(); return false;"><?php echo _l('add_type_of_package'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('package_type_name'); ?></th>
		<th><?php echo _l('package_type_details'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($type_of_packages) && count($type_of_packages) > 0){ ?>
			<?php foreach($type_of_packages as $type_of_package){ ?>
				<tr>
					<td><?php echo html_entity_decode($type_of_package['id']); ?></td>
					<td><?php echo html_entity_decode($type_of_package['type_of_package_name']); ?></td>
					<td><?php echo html_entity_decode($type_of_package['package_type_details']); ?></td>

					<td>
						<a href="#" onclick="edit_type_of_package(this,<?php echo html_entity_decode($type_of_package['id']); ?>); return false" data-type_of_package_name="<?php echo html_entity_decode($type_of_package['type_of_package_name']); ?>" data-package_type_details="<?php echo html_entity_decode($type_of_package['package_type_details']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_type_of_package/' . $type_of_package['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="type_of_package_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_type_of_package'); ?></span>
					<span class="add-title"><?php echo _l('new_type_of_package'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/type_of_package_form',array('id'=>'type_of_package-setting-form')); ?>
			<?php echo form_hidden('type_of_package_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_type_of_package'); ?></label>
						<?php echo render_input('type_of_package_name','','','text', ['required' => 'true']); ?>

						<?php echo render_textarea('package_type_details','package_type_details'); ?>

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