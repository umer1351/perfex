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
						<?php if(has_permission('warranty_management', '', 'create')){ ?>
							<div class="row ">
								<div class="col-md-4 ">
									<div class="_buttons">
										<a href="<?php echo admin_url('warranty_management/add_edit_warranty_claim'); ?>" class="btn btn-info pull-left display-block mright5"><?php echo _l('wm_add'); ?></a>

									</div>
								</div>
							</div>
							<br>
						<?php } ?>

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

							<div class=" col-md-3 hide">
								<div class="form-group">
									<select name="order_filter[]" id="order_filter" class="selectpicker" multiple="true" data-actions-box="true"  data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('sm_order'); ?>">

										<?php foreach($orders as $order) { ?>
											<option value="<?php echo new_html_entity_decode($order['id']); ?>"><?php echo new_html_entity_decode($order['order_code']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							
							
							<div class=" col-md-3 hide">
								<div class="form-group">
									<select name="product_filter[]" id="product_filter" class="selectpicker" multiple="true" data-actions-box="true"  data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('sm_service_name'); ?>">

										<?php foreach($products as $product) { ?>
											<option value="<?php echo new_html_entity_decode($product['id']); ?>"><?php echo new_html_entity_decode($product['commodity_code'].' '.$product['description']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

							<?php $wm_warranty_claim_status = wm_warranty_claim_status(); ?>
							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="claim_status_filter[]" id="claim_status_filter" class="selectpicker" multiple="true" data-actions-box="true"  data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('wm_status'); ?>">

										<?php foreach($wm_warranty_claim_status as $claim_status) { ?>
											<option value="<?php echo new_html_entity_decode($claim_status['id']); ?>"><?php echo new_html_entity_decode($claim_status['name']); ?></option>
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
									_l('wm_claim_code'),
									_l('wm_created_by'),
									_l('wm_created_type'),
									_l('client'),
									_l('wm_description'),
									_l('wm_date_created'),
									_l('wm_status'),
									
								);

								render_datatable($table_data,'warranty_claim_table',
									array('customizable-table'),
									array(
										'proposal_sm' => 'proposal_sm',
										'id'=>'table-warranty_claim_table',
										'data-last-order-identifier'=>'warranty_claim_table',
										'data-default-order'=>get_table_last_order('warranty_claim_table'),
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
	<?php require 'modules/warranty_management/assets/js/warranty_claims/warranty_claim_manage_js.php';?>
</body>
</html>
