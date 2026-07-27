<?php hooks()->do_action('head_element_client'); ?>

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">	
				<?php echo form_open_multipart(site_url('warranty_management/warranty_management_client/add_warranty_claim/'.$invoice_id.'/'.$item_id), array('id'=>'add_warranty_claim')); ?>

				<div class="panel_s">
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

						if(isset($warranty_claim)){
							$id = $warranty_claim->id;
							$created_id = $warranty_claim->created_id;
							$datecreated =  $warranty_claim->datecreated ;
						}


						?>
						<input type="hidden" name="id" value="<?php echo new_html_entity_decode($id); ?>">
						<input type="hidden" name="created_type" value="client">
						<input type="hidden" name="created_id" value="<?php echo new_html_entity_decode(get_contact_user_id()); ?>">
						<input type="hidden" name="claim_information_detail_id" value="<?php echo new_html_entity_decode($warranty_receipt_process_id); ?>">
						<input type="hidden" name="client_id" value="<?php echo new_html_entity_decode(get_client_user_id()); ?>">
						<input type="hidden" name="item_id" value="<?php echo new_html_entity_decode($item_id); ?>">
						<input type="hidden" name="invoice_id" value="<?php echo new_html_entity_decode($invoice_id); ?>">

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

									<div class="col-md-12">
										<h4>
											<?php echo _l('wm_invoice_related'); ?> : <a href="<?php echo site_url('invoice/'.$invoice_id.'/'.sm_get_invoice_hash($invoice_id)) ?>" ><?php echo format_invoice_number($invoice_id); ?>
											
										</a>
									</h4>
									<h4>
										<?php echo _l('wm_product_service_name'); ?> : <?php echo wm_get_item($item_id) ?>
									</h4>
									
								</div>

								<br>

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
							<?php echo render_textarea('description','wm_description',$description,array(),array(),'mtop15'); ?>
							<?php echo render_textarea('client_note','wm_client_note',$client_note,array(),array(),'mtop15'); ?>


							<div class="btn-bottom-toolbar text-right">
								<a href="<?php echo site_url('warranty_management/warranty_management_client/warranty_informations'); ?>"class="btn btn-default text-right mright5"><?php echo _l('close'); ?></a>

								<button type="submit" class="btn btn-info"><?php echo _l('save'); ?></button>

							</div>
						</div>
						<div class="btn-bottom-pusher"></div>
					</div>
				</div>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
</div>

<?php hooks()->do_action('app_customers_portal_footer'); ?>



