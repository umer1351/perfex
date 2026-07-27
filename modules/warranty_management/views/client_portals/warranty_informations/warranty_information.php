<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('app_customers_portal_head'); ?>

<div class="panel_s section-heading section-invoices">
	<div class="panel-body">
		<div class="col-md-6">

			<h4 class="no-margin section-text"><?php echo _l('wm_warranty_informations'); ?></h4>
			
		</div>

		<div class="col-md-6">
			<ul class="nav navbar-nav navbar-right">
				
				<li class="customers-nav-item-products_services">
					<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_informations') ?>" class="a_active"><strong><?php echo _l('wm_warranty_informations'); ?></strong></a>
				</li>
				<li class="customers-nav-item-services">
					<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_claims') ?>" class=""><?php echo _l('wm_warranty_claims'); ?></a>
				</li>

			</ul>
		</div>
	</div>
</div>

<div class="panel_s">
	<div class="panel-body">
		<table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
			<thead>
				<tr>
					<th class="th-invoice-number hide"><?php echo _l('id'); ?></th>
					<th class="th-invoice-number hide"><?php echo _l('client'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_order_number_delivery_note'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_invoice'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_product_service_name'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_rate'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_quantity'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_expriry_date'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_lot_number'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_serial_number'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_warranty_period'); ?></th>
					<th class="th-invoice-number"><?php echo _l('wm_options'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $client_services = []; ?>
				<?php foreach($warranty_informations as $warranty_information){ ?>
					<tr>
						<td class="hide" data-order="<?php echo new_html_entity_decode($warranty_information['id']); ?>"><?php echo new_html_entity_decode($warranty_information['id']); ?></td>
						<td  class="hide" data-order="<?php echo new_html_entity_decode($warranty_information['client_id']); ?>"><?php echo get_company_name($warranty_information['client_id']); ?></td>


						<?php if(!isset($warranty_information['start_date'])){ ?>
							<?php
							$delivery_link = '';
							$value = '';
							if(wm_get_status_modules('warehouse')){ 
								$value = get_goods_delivery_code($warranty_information['order_id']) != null ? get_goods_delivery_code($warranty_information['order_id'])->goods_delivery_code : '';
								$delivery_link = site_url('warehouse/warehouse_client/stock_export_pdf/'.$warranty_information['order_id'].'?output_type=I');

							} ?>
							<td data-order="<?php echo new_html_entity_decode($warranty_information['order_id']); ?>"><a href="<?php echo new_html_entity_decode($delivery_link); ?>" ><?php echo new_html_entity_decode($value); ?></a></td>
						<?php }else{ ?>
							<td data-order="<?php echo new_html_entity_decode($warranty_information['order_id']); ?>"><a href="<?php echo site_url('service_management/service_management_client/order_detail/'.$warranty_information['order_id']) ?>" ><?php echo sm_order_code($warranty_information['order_id']) ?></a></td>
						<?php } ?>

						<td data-order="<?php echo new_html_entity_decode($warranty_information['invoice_id']); ?>"><a href="<?php echo site_url('invoice/'.$warranty_information['invoice_id'].'/'.sm_get_invoice_hash($warranty_information['invoice_id'])) ?>"><?php echo format_invoice_number($warranty_information['invoice_id']); ?></a></td>

						<?php
						$item_name = '';
						if(!isset($warranty_information['commodity_code'])){
							$item_name = $warranty_information['item_name'];
						}else{
							if(new_strlen($warranty_information['item_name']) > 0){
								$item_name = $warranty_information['item_name'];
							}else{
								$item_name = wm_get_item($warranty_information['commodity_code']);
							}
						}
						?>

						<td data-order="<?php echo new_html_entity_decode($warranty_information['item_name']); ?>"><?php echo new_html_entity_decode($item_name); ?></td>

						<?php 
						$item_rate = '';
						if(!isset($warranty_information['billing_plan_rate'])){
							$item_rate = app_format_money((float)$warranty_information['rate'], $base_currency_id);
						}else{
							$item_rate = app_format_money((float)$warranty_information['billing_plan_rate'], $base_currency_id).' ('. $warranty_information['rate'].' '. _l($warranty_information['billing_plan_type']) . ')';
						}
						?>

						<td data-order="<?php echo new_html_entity_decode($item_rate); ?>"><?php echo new_html_entity_decode($item_rate); ?></td>

						<td data-order="<?php echo new_html_entity_decode($warranty_information['quantity']); ?>"><?php echo app_format_money((float)$warranty_information['quantity'], ''); ?></td>

						<?php 
						$expiry_date = '';
						if(isset($warranty_information['expiry_date'])){
							$expiry_date = $warranty_information['expiry_date'];
						}else{
							$expiry_date = '...';
						}
						?>
						<td data-order="<?php echo new_html_entity_decode($expiry_date); ?>"><?php echo new_html_entity_decode($expiry_date); ?></td>

						<?php 
						$lot_number = '';
						$serial_number = '';

						if(isset($warranty_information['expiry_date'])){
							$lot_number = $warranty_information['lot_number'];
						}else{
							$lot_number = '...';
						}

						if(isset($warranty_information['expiry_date'])){
							$serial_number = $warranty_information['serial_number'];
						}else{
							$serial_number = '...';
						}

						?>

						<td data-order="<?php echo new_html_entity_decode($lot_number); ?>"><?php echo new_html_entity_decode($lot_number); ?></td>

						<td data-order="<?php echo new_html_entity_decode($serial_number); ?>"><?php echo new_html_entity_decode($serial_number); ?></td>

						<?php 
						$warranty_period = '';
						if(isset($warranty_information['start_date'])){
							$warranty_period = _dt($warranty_information['start_date']) .' - '. _dt($warranty_information['warranty_period']);
						}else{
							if($warranty_information['warranty_period'] != null && new_strlen($warranty_information['warranty_period']) > 0){
								$warranty_period = _d($warranty_information['date_add']) .' - '. _d($warranty_information['warranty_period']);
							}else{
								$warranty_period = _d($warranty_information['date_add']) .' - ...';
							}
						}
						?>

						<td data-order="<?php echo new_html_entity_decode($warranty_period); ?>"><?php echo new_html_entity_decode($warranty_period); ?></td>
						
						<?php 
						$option = '';
						if(!isset($client_warranty_claim_process[$warranty_information['invoice_id'].'_'.$warranty_information['item_id']])){

							$option .= '<a href="'. site_url('warranty_management/warranty_management_client/add_warranty_claim/'.$warranty_information['invoice_id'].'/'.$warranty_information['item_id']).'" class="btn btn-success text-right mright5">'. _l('wm_create_warranty_claim') .'</a>';
						}

						?>

						<td data-order=""><?php echo new_html_entity_decode($option); ?></td>

					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php hooks()->do_action('app_customers_portal_footer'); ?>
