<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
	<div class="col-md-12">
		<h4 class="h4-color"><i class="fa fa-bars menu-icon" aria-hidden="true"></i> <?php echo _l('wm_general_setting'); ?></h4>
	</div>
</div>
<hr class="hr-color">


<div class="row">
	<div class="col-md-12">
		<h5 class="no-margin font-bold h5-color" ><?php echo _l('wm_warranty_management_label')?></h5>
		<hr class="hr-color" >
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<div class="checkbox checkbox-primary">
				<input onchange="auto_create_change_setting(this); return false" type="checkbox" id="warranty_management_display_on_portal" name="purchase_setting[warranty_management_display_on_portal]" <?php if(get_option('warranty_management_display_on_portal') == 1 ){ echo 'checked';} ?> value="warranty_management_display_on_portal">
				<label for="warranty_management_display_on_portal"><?php echo _l('wm_display_warranty_management_on_client_portal'); ?>
				<a href="#" class="pull-right display-block input_method"><i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('wm_display_warranty_management_on_client_portal'); ?>"></i></a>
			</label>
		</div>
	</div>
</div>
</div>


</body>
</html>


