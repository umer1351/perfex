<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
	<div class="col-md-12">
		<h4 class="h4-color"><i class="fa fa-bars menu-icon" aria-hidden="true"></i> <?php echo _l('wm_prefix_settings'); ?></h4>
	</div>
</div>
<hr class="hr-color">

<?php echo form_open_multipart(admin_url('warranty_management/prefix_number'),array('class'=>'prefix_number','autocomplete'=>'off')); ?>

<div class="row">
	<div class="col-md-12">
		<h5 class="no-margin font-bold h5-color"><?php echo _l('wm_warranty_receipt_process_code') ?></h5>
		<hr class="hr-color">
	</div>
</div>

<div class="form-group">
	<label><?php echo _l('wm_warranty_receipt_process_prefix'); ?></label>
	<div  class="form-group" app-field-wrapper="wm_warranty_receipt_process_prefix">
		<input type="text" id="wm_warranty_receipt_process_prefix" name="wm_warranty_receipt_process_prefix" class="form-control" value="<?php echo get_option('wm_warranty_receipt_process_prefix'); ?>"></div>
	</div>

	<div class="form-group">
		<label><?php echo _l('wm_warranty_receipt_process_number'); ?></label>
		<i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('wm_next_number_tooltip'); ?>"></i>
		<div  class="form-group" app-field-wrapper="wm_warranty_receipt_process_number">
			<input type="number" min="0" id="wm_warranty_receipt_process_number" name="wm_warranty_receipt_process_number" class="form-control" value="<?php echo get_option('wm_warranty_receipt_process_number'); ?>">
		</div>

	</div>

	<div class="row">
		<div class="col-md-12">
			<h5 class="no-margin font-bold h5-color"><?php echo _l('wm_warranty_claim_code') ?></h5>
			<hr class="hr-color">
		</div>
	</div>

	<div class="form-group">
		<label><?php echo _l('wm_warranty_claim_prefix'); ?></label>
		<div  class="form-group" app-field-wrapper="wm_warranty_claim_prefix">
			<input type="text" id="wm_warranty_claim_prefix" name="wm_warranty_claim_prefix" class="form-control" value="<?php echo get_option('wm_warranty_claim_prefix'); ?>"></div>
		</div>

		<div class="form-group">
			<label><?php echo _l('wm_warranty_claim_number'); ?></label>
			<i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('wm_next_number_tooltip'); ?>"></i>
			<div  class="form-group" app-field-wrapper="wm_warranty_claim_number">
				<input type="number" min="0" id="wm_warranty_claim_number" name="wm_warranty_claim_number" class="form-control" value="<?php echo get_option('wm_warranty_claim_number'); ?>">
			</div>

		</div>


		<div class="clearfix"></div>

		<div class="modal-footer">
			<?php if(has_permission('warranty_management', '', 'create') || has_permission('warranty_management', '', 'edit') ){ ?>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			<?php } ?>
		</div>
		<?php echo form_close(); ?>


	</body>
	</html>


