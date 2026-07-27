<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_shipping_company(); return false;"><?php echo _l('add_shipping_company'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('name'); ?></th>
		<th><?php echo _l('address'); ?></th>
		<th><?php echo _l('phone'); ?></th>
		<th><?php echo _l('country'); ?></th>
		<th><?php echo _l('city'); ?></th>
		<th><?php echo _l('postcode'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($shipping_companys) && count($shipping_companys) > 0){ ?>
			<?php foreach($shipping_companys as $shipping_company){ ?>
				<tr>
					<td><?php echo html_entity_decode($shipping_company['id']); ?></td>
					<td><?php echo html_entity_decode($shipping_company['shipping_company_name']); ?></td>
					<td><?php echo html_entity_decode($shipping_company['address']); ?></td>
					
					<td><?php echo html_entity_decode($shipping_company['phone']); ?></td>
					<td><?php echo lg_get_country_name_by_id($shipping_company['country']); ?></td>
					<td><?php echo html_entity_decode($shipping_company['city']); ?></td>
					<td><?php echo html_entity_decode($shipping_company['postcode']); ?></td>

					<td>
						<a href="#" onclick="edit_shipping_company(this,<?php echo html_entity_decode($shipping_company['id']); ?>); return false" data-shipping_company_name="<?php echo html_entity_decode($shipping_company['shipping_company_name']); ?>" data-address="<?php echo html_entity_decode($shipping_company['address']); ?>" data-city="<?php echo html_entity_decode($shipping_company['city']); ?>" data-postcode="<?php echo html_entity_decode($shipping_company['postcode']); ?>" data-country="<?php echo html_entity_decode($shipping_company['country']); ?>" data-phone="<?php echo html_entity_decode($shipping_company['phone']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_shipping_company/' . $shipping_company['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="shipping_company_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_shipping_company'); ?></span>
					<span class="add-title"><?php echo _l('new_shipping_company'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/shipping_company_form',array('id'=>'shipping_company-setting-form')); ?>
			<?php echo form_hidden('shipping_company_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="name"><span class="text-danger">* </span><?php echo _l('lg_shipping_company'); ?></label>
						<?php echo render_input('shipping_company_name','','','text', ['required' => 'true']); ?>

						<?php echo render_textarea('address','address'); ?>

						<?php 
	                     $selected                 = '';
	                     echo render_select('country', $countries, [ 'id', [ 'country_name']], 'clients_country', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
	                    ?>

						<?php echo render_input('city','city','','text'); ?>
						<?php echo render_input('postcode','postcode','','text'); ?>
						<?php echo render_input('phone','phone','','text'); ?>
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