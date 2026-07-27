<div class="modal fade" id="appointmentModal">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">

				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo new_html_entity_decode($title); ?></h4>
			</div>
			<?php echo form_open_multipart(admin_url('warranty_management/add_goods_delivery_warranty_period/'.$goods_delivery_detail_id), array('id' => 'add_goods_delivery_warranty')); ?>

			<div class="modal-body">
				<div class="tab-content">
					<div class="row">
						<input type="hidden" value="<?php echo new_html_entity_decode($goods_delivery_detail_id); ?>" name="goods_delivery_detail_id">
						<div class="col-md-12">
							<h5><?php echo _l('wm_product_service_name').': '. $product_name; ?></h5>
							<h5><?php echo _l('wm_warranty_start_date').': '. _d($warranty_start_date); ?></h5>
							<h5><?php echo _l('wm_warranty_month').': '. $warranty_month; ?></h5>
						</div>

						<div class="col-md-12">
							<?php echo render_date_input('warranty_period','wm_expiration_date', _d($warranty_end_date)); ?>  
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default close_btn" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			</div>

		</div>

		<?php echo form_close(); ?>
	</div>
</div>
</div>
<?php require('modules/warranty_management/assets/js/warranty_informations/add_goods_delivery_expiration_modal_js.php'); ?>
