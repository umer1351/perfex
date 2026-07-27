<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
	<div class="col-md-12">
		<h4 class="h4-color"><i class="fa fa-area-chart" aria-hidden="true"></i> <?php echo _l('wm_warranty_receipt_process'); ?></h4>
	</div>
</div>
<hr class="hr-color">

<?php if(has_permission('warranty_management', '', 'create')){ ?>
	<div class="_buttons">
		<a href="#" onclick="add_warranty_receipt_process(0,0,' hide'); return false;" class="btn btn-info mbot10"><?php echo _l('wm_add'); ?></a>

	</div>
	<br>
<?php } ?>

<?php render_datatable(array(
	_l('id'),
	_l('wm_code'),
	_l('wm_name'),
	_l('wm_product_group'),
	_l('wm_description'),
),'warranty_receipt_process_table'); ?>

<div id="modal_wrapper"></div>

</body>
</html>