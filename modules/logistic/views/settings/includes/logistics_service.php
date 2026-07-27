<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_logistics_service(); return false;"><?php echo _l('add_logistics_service'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('name'); ?></th>
		<th><?php echo _l('details'); ?></th>
	
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($logistics_services) && count($logistics_services) > 0){ ?>
			<?php foreach($logistics_services as $logistics_service){ ?>
				<tr>
					<td><?php echo html_entity_decode($logistics_service['id']); ?></td>
					<td><?php echo html_entity_decode($logistics_service['logistics_service_name']); ?></td>
					<td><?php echo html_entity_decode($logistics_service['description']); ?></td>
					<td>
						<a href="#" onclick="edit_logistics_service(this,<?php echo html_entity_decode($logistics_service['id']); ?>); return false" data-logistics_service_name="<?php echo html_entity_decode($logistics_service['logistics_service_name']); ?>" data-description="<?php echo html_entity_decode($logistics_service['description']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_logistics_service/' . $logistics_service['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="logistics_service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_logistics_service'); ?></span>
					<span class="add-title"><?php echo _l('new_logistics_service'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/logistics_service_form',array('id'=>'logistics_service-setting-form')); ?>
			<?php echo form_hidden('logistics_service_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_logistics_service'); ?></label>
						<?php echo render_input('logistics_service_name','','','text', ['required' => 'true']); ?>

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