<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_payment_term(); return false;"><?php echo _l('add_payment_term'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('name'); ?></th>
		<th><?php echo _l('days'); ?></th>
	
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($payment_terms) && count($payment_terms) > 0){ ?>
			<?php foreach($payment_terms as $payment_term){ ?>
				<tr>
					<td><?php echo html_entity_decode($payment_term['id']); ?></td>
					<td><?php echo html_entity_decode($payment_term['name']); ?></td>
					<td><?php echo html_entity_decode($payment_term['days']); ?></td>
					<td>
						<a href="#" onclick="edit_payment_term(this,<?php echo html_entity_decode($payment_term['id']); ?>); return false" data-name="<?php echo html_entity_decode($payment_term['name']); ?>" data-days="<?php echo html_entity_decode($payment_term['days']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_payment_term/' . $payment_term['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="payment_term_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_payment_term'); ?></span>
					<span class="add-title"><?php echo _l('new_payment_term'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/payment_term_form',array('id'=>'payment_term-setting-form')); ?>
			<?php echo form_hidden('payment_term_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_payment_term'); ?></label>
						<?php echo render_input('name','','','text', ['required' => 'true']); ?>

						<?php echo render_input('days','days', '', 'number', ['step' => '1']); ?>
				
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