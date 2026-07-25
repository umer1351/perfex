<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_shipping_time(); return false;"><?php echo _l('add_shipping_time'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('name'); ?></th>
		<th><?php echo _l('service_price_details'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($shipping_times) && count($shipping_times) > 0){ ?>
			<?php foreach($shipping_times as $shipping_time){ ?>
				<tr>
					<td><?php echo html_entity_decode($shipping_time['id']); ?></td>
					<td><?php echo html_entity_decode($shipping_time['shipping_time_name']); ?></td>
					<td><?php echo html_entity_decode($shipping_time['service_price_details']); ?></td>

					<td>
						<a href="#" onclick="edit_shipping_time(this,<?php echo html_entity_decode($shipping_time['id']); ?>); return false" data-shipping_time_name="<?php echo html_entity_decode($shipping_time['shipping_time_name']); ?>" data-service_price_details="<?php echo html_entity_decode($shipping_time['service_price_details']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_shipping_time/' . $shipping_time['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="shipping_time_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_shipping_time'); ?></span>
					<span class="add-title"><?php echo _l('new_shipping_time'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/shipping_time_form',array('id'=>'shipping_time-setting-form')); ?>
			<?php echo form_hidden('shipping_time_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_shipping_time'); ?></label>
						<?php echo render_input('shipping_time_name','','','text', ['required' => 'true']); ?>
						<?php echo render_input('service_price_details','service_price_details','','text'); ?>
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