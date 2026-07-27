<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading"><?php echo _l('lg_address_book'); ?></h4>

<div class="inline-block new-contact-wrapper" >
    <a href="#" onclick="add_address_book(); return false;" class="btn btn-primary new-contact mbot15">
        <i class="fa-regular fa-plus tw-mr-1"></i>
        <?php echo _l('lg_new_address_book'); ?>
    </a>
</div>

<?php $client_address = lg_get_client_address_list($client->userid); ?>

<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('country'); ?></th>
		<th><?php echo _l('state'); ?></th>
		<th><?php echo _l('city'); ?></th>
		<th><?php echo _l('lg_zip_code'); ?></th>
		<th><?php echo _l('lg_address'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($client_address) && count($client_address) > 0){ ?>
			<?php foreach($client_address as $address){ ?>
				<tr>
					<td><?php echo html_entity_decode($address['id']); ?></td>
					<td><?php echo lg_get_country_name_by_id($address['country']); ?></td>
					<td><?php echo lg_get_state_name_by_id($address['state']); ?></td>
					<td><?php echo lg_get_city_name_by_id($address['city']); ?></td>
					<td><?php echo html_entity_decode($address['zip_code']); ?></td>
					<td>
						<?php echo html_entity_decode($address['address']); ?>
					</td>

					<td>
						<a href="#" onclick="edit_address(this,<?php echo html_entity_decode($address['id']); ?>); return false" data-country="<?php echo html_entity_decode($address['country']); ?>" data-state="<?php echo html_entity_decode($address['state']); ?>" data-city="<?php echo html_entity_decode($address['city']); ?>" data-zip_code="<?php echo html_entity_decode($address['zip_code']); ?>" data-address="<?php echo html_entity_decode($address['address']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('logistic/delete_address/' . $address['id'].'/'.$client->userid); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>


<?php
 	$countries = lg_get_countries(); 
 	$states = [];
 	$cities = [];
?>

<div class="modal fade" id="address_book_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_address'); ?></span>
					<span class="add-title"><?php echo _l('new_address'); ?></span>
				</h4>
			</div>
			<?php echo form_open('logistic/address_form',array('id'=>'address-form')); ?>
			<?php echo form_hidden('address_book_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">

						<?php echo form_hidden('client_id', $client->userid); ?>
						<label for="country"><span class="text-danger">* </span><?php echo _l('lg_country'); ?></label>        
		                <?php
		                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true'];
		                     $selected = '';

		                     echo render_select('country', $countries, ['id', 'country_name'], '', $selected, $s_attrs);
		                     ?>     

		                <label for="state"><span class="text-danger">* </span><?php echo _l('lg_state'); ?></label>         
		                <?php
		                     echo render_select('state', $states, ['id', 'state_name'], '', $selected, $s_attrs);
		                     ?>
		                <label for="city"><span class="text-danger">* </span><?php echo _l('lg_city'); ?></label>       
		                <?php
		                     echo render_select('city', $cities, ['id', 'city_name'], '', $selected, $s_attrs);
		                     ?>


		                <label for="zip_code"><span class="text-danger">* </span><?php echo _l('lg_zip_code'); ?></label>  
		                <?php echo render_input('zip_code', '', '', 'text', ['required' => 'true']) ?>  

		                <label for="address"><span class="text-danger">* </span><?php echo _l('lg_address'); ?></label>  
		                <?php echo render_textarea('address', '', '', ['required' => 'true']) ?>  
						
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

<?php } ?>
