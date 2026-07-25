<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12" id="small-table">
				<div class="panel_s">
					<?php echo form_open_multipart(admin_url('warranty_management/add_edit_warranty_claim'), array('id'=>'add_warranty_claim')); ?>
					<div class="panel-body">

						<div class="row">
							<div class="col-md-12">
								<h4 class="no-margin font-bold "><i class="fa fa-object-ungroup menu-icon" aria-hidden="true"></i> <?php echo new_html_entity_decode($title); ?></h4>
								<hr>
							</div>
						</div>

						<?php 
						$id = '';
						$current_day = date("Y-m-d");
						$created_id = get_staff_user_id();
						$datecreated = date("Y-m-d H:i:s");
						$claim_information_detail_id = '';

						if(isset($warranty_claim)){
							$id = $warranty_claim->id;
							$created_id = $warranty_claim->created_id;
							$datecreated =  $warranty_claim->datecreated ;
						}

						if(isset($warranty_claim_information_details) && count($warranty_claim_information_details) > 0){
							$claim_information_detail_id = $warranty_claim_information_details[0]['id'];
						}

						?>
						<input type="hidden" name="id" value="<?php echo new_html_entity_decode($id); ?>">
						<input type="hidden" name="created_type" value="staff">
						<input type="hidden" name="created_id" value="<?php echo new_html_entity_decode($created_id); ?>">
						<input type="hidden" name="claim_information_detail_id" value="<?php echo new_html_entity_decode($claim_information_detail_id); ?>">

						<div class="row" >
							<div class="col-md-12">
								<div class="row">

									<div class="col-md-6">
										<?php $claim_code = isset($warranty_claim)? $warranty_claim->claim_code: $claim_code; ?>
										<?php echo render_input('claim_code', 'wm_claim_code',$claim_code,'',array('readonly' => 'true')) ?>
									</div>

									<div class="col-md-6">
										<?php echo render_datetime_input('datecreated','wm_date_created', _dt($datecreated)) ?>

									</div>

									<br>
									<div class="col-md-6">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="client_id"><?php echo _l('client'); ?></label>
													<select name="client_id" id="client_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>"  >
														<option value=""></option>
														<?php foreach($clients as $s) { ?>
															<option value="<?php echo new_html_entity_decode($s['userid']); ?>" <?php if(isset($warranty_claim) && $warranty_claim->client_id == $s['userid']){ echo 'selected'; } ?>><?php echo new_html_entity_decode($s['company']); ?></option>
														<?php } ?>
													</select>
												</div>
											</div>

											<div class="col-md-6">
												<div class="form-group">
													<label for="invoice_id"><?php echo _l('wm_invoice'); ?></label>
													<select name="invoice_id" id="invoice_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>"  >

														<?php if(isset($invoices)){ ?>
															<?php foreach($invoices as $invoice) { ?>
																<option value="<?php echo new_html_entity_decode($invoice['id']); ?>" <?php if(isset($warranty_claim_information_details) && $warranty_claim_information_details[0]['invoice_id'] == $invoice['id']){ echo 'selected'; } ?>><?php echo format_invoice_number($invoice['id']); ?></option>
															<?php } ?>
														<?php } ?>
													</select>
												</div>
											</div>

										</div>

									</div>

									<div class="col-md-6">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="item_id"><?php echo _l('wm_product_service_name'); ?></label>
													<select name="item_id" id="item_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>"  >
														<?php if(isset($list_item_warranty)){ ?>
															<?php foreach($list_item_warranty as $item_warranty) { ?>
																<option value="<?php echo new_html_entity_decode($item_warranty['item_id']); ?>" <?php if(isset($warranty_claim_information_details) && $warranty_claim_information_details[0]['item_id'] == $item_warranty['item_id']){ echo 'selected'; } ?>><?php echo wm_get_item($item_warranty['item_id']); ?></option>
															<?php } ?>
														<?php } ?>

													</select>
												</div>
											</div>

											<div class="col-md-6">
												<div class="form-group">
													<label for="warranty_receipt_process_id"><?php echo _l('wm_warranty_receipt_process'); ?></label>
													<select name="warranty_receipt_process_id" id="warranty_receipt_process_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>"  >
														<?php if(isset($warranty_receipt_processes)){ ?>
															<?php foreach($warranty_receipt_processes as $receipt_process) { ?>
																<option value="<?php echo new_html_entity_decode($receipt_process['id']); ?>" <?php if(isset($warranty_claim_information_details) && $warranty_claim_information_details[0]['warranty_receipt_process_id'] == $receipt_process['id']){ echo 'selected'; } ?>><?php echo new_html_entity_decode($receipt_process['code'].' '.$receipt_process['name']); ?></option>
															<?php } ?>
														<?php } ?>
													</select>
												</div>
											</div>
										</div>
										
									</div>


								</div>


							</div>

						</div>

					</div>

					<div class="panel-body mtop10 invoice-item">
						<div class="row">
							<div class="col-md-4">
								
							</div>
							<div class="col-md-8 text-right hide">
								<label class="bold mtop10 text-right" data-toggle="tooltip" title="" data-original-title="<?php echo _l('support_barcode_scanner_tooltip'); ?>"><?php echo _l('support_barcode_scanner'); ?>
								<i class="fa fa-question-circle i_tooltip"></i></label>
							</div>
						</div>

						<div class="table-responsive s_table ">
							<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
								<thead>
									<tr>
										<th></th>
										<th width="5%"><?php echo _l('wm_order_number'); ?></th>
										<th width="30%" align="left"><i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('wm_process_name'); ?></th>
										<th width="10%" align="right" class="available_quantity"><?php echo _l('wm_person_in_charge'); ?></th>
										<th width="10%" align="right" class="available_quantity"><?php echo _l('wm_estimate_time'); ?></th>
										<th width="50%" align="right" class="qty"><?php echo _l('wm_description'); ?></th>
										<th align="center"></th>
										<th align="center"><i class="fa fa-cog"></i></th>
									</tr>
								</thead>
								<tbody>
									<?php echo new_html_entity_decode($warranty_claim_row_template); ?>
								</tbody>
							</table>
						</div>
						
						<div id="removed-items"></div>
					</div>


					<div class="row">
						<div class="col-md-12 mtop15">
							<div class="panel-body bottom-transaction">

								<?php $description = (isset($warranty_claim) ? $warranty_claim->description : ''); ?>
								<?php $client_note = (isset($warranty_claim) ? $warranty_claim->client_note : ''); ?>
								<?php $admin_note = (isset($warranty_claim) ? $warranty_claim->admin_note : ''); ?>
								<?php echo render_textarea('description','wm_description',$description,array(),array(),'mtop15'); ?>
								<?php echo render_textarea('client_note','wm_client_note',$client_note,array(),array(),'mtop15'); ?>
								<?php echo render_textarea('admin_note','wm_admin_note',$admin_note,array(),array(),'mtop15'); ?>


								<div class="btn-bottom-toolbar text-right">
									<a href="<?php echo admin_url('warranty_management/warranty_claim_information'); ?>"class="btn btn-default text-right mright5"><?php echo _l('close'); ?></a>

									
									<?php if (is_admin() || has_permission('warranty_management', '', 'edit') || has_permission('warranty_management', '', 'create')) { ?>
										<button type="submit" class="btn btn-info"><?php echo _l('save'); ?></button>

									<?php } ?>

								</div>
							</div>
							<div class="btn-bottom-pusher"></div>
						</div>
					</div>

				</div>

			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<div id="modal_wrapper"></div>
<div id="change_serial_modal_wrapper"></div>

<?php init_tail(); ?>
<?php require 'modules/warranty_management/assets/js/warranty_claims/add_edit_warranty_claim_js.php';?>
</body>
</html>



