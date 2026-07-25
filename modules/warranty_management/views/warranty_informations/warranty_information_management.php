<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12" id="small-table">
				<div class="panel_s">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<h4 class="no-margin h4-color"><i class="fa fa-clone menu-icon menu-icon" aria-hidden="true"></i> <?php echo _l($title); ?></h4>
							</div>
						</div>
						<hr class="hr-color">

						<div class="row">
							
							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="client_filter[]" id="client_filter" class="selectpicker" multiple="true" data-actions-box="true"  data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('clients'); ?>">

										<?php foreach($clients as $client) { ?>
											<option value="<?php echo new_html_entity_decode($client['userid']); ?>"><?php echo new_html_entity_decode($client['company']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="order_filter[]" id="order_filter" class="selectpicker" multiple="true" data-actions-box="true"  data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('sm_order'); ?>">

										<?php foreach($orders as $order) { ?>
											<option value="<?php echo new_html_entity_decode($order['id']); ?>"><?php echo new_html_entity_decode($order['order_code']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="delivery_note_filter[]" id="delivery_note_filter" class="selectpicker" multiple="true" data-actions-box="true"  data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('wm_inventory_delivery_voucher'); ?>">

										<?php foreach($goods_deliveries as $goods_delivery) { ?>
											<option value="<?php echo new_html_entity_decode($goods_delivery['id']); ?>"><?php echo new_html_entity_decode($goods_delivery['goods_delivery_code']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							
							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="product_filter[]" id="product_filter" class="selectpicker" multiple="true" data-actions-box="true"  data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('sm_service_name'); ?>">

										<?php foreach($products as $product) { ?>
											<option value="<?php echo new_html_entity_decode($product['itemid']); ?>"><?php echo new_html_entity_decode($product['description']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

						</div>
						<br>

						<div class="row">
							<div class="col-md-12">

								<?php 
								$table_data = array(
									_l('id'),
									_l('client'),
									_l('wm_order_number_delivery_note'),
									_l('wm_invoice'),
									_l('wm_product_service_name'),
									_l('wm_rate'),
									_l('wm_quantity'),
									_l('wm_expriry_date'),
									_l('wm_lot_number'),
									_l('wm_serial_number'),
									_l('wm_warranty_period'),
									_l('wm_options'),
									
								);

								render_datatable($table_data,'warranty_information_table',
									array('customizable-table'),
									array(
										'proposal_sm' => 'proposal_sm',
										'id'=>'table-warranty_information_table',
										'data-last-order-identifier'=>'warranty_information_table',
										'data-default-order'=>get_table_last_order('warranty_information_table'),
									)); ?>

								</div>
							</div>

						</div>
					</div>
				</div>
				
			</div>
		</div>

	</div>
	<div id="modal_wrapper"></div>

	<?php init_tail(); ?>
	<?php require 'modules/warranty_management/assets/js/warranty_informations/warranty_information_manage_js.php';?>
</body>
</html>
