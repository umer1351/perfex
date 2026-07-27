<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="row">

			<div class="col-md-3">
				<ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked">
					<?php
					$i = 0;
					foreach($tab as $gr){
						?>
						<li<?php if($i == 0){echo " class='active'"; } ?>>
						<a href="<?php echo admin_url('warranty_management/setting?group='.$gr); ?>" data-group="<?php echo new_html_entity_decode($gr); ?>">
							<?php
								$icon['warranty_receipt_process'] = '<span class="fa fa-area-chart"></span>';
								$icon['unit'] = '<span class="fa fa-certificate"></span>';
								$icon['status'] = '<span class="fa fa-list-alt"></span>';
								$icon['prefix_number'] = '<span class="fa fa-bars menu-icon"></span>';
								$icon['general'] = '<span class="fa fa-bars menu-icon"></span>';

								if($gr == 'prefix_number'){
									echo new_html_entity_decode($icon[$gr] .' '. _l('wm_prefix_settings')); 

								}else{
									echo new_html_entity_decode($icon[$gr] .' '. _l('wm_'.$gr)); 
								}
							
							?>
						</a>
					</li>
					<?php $i++; } ?>
				</ul>
			</div>
			<div class="col-md-9">
				<div class="panel_s">
					<div class="panel-body">

						<?php $this->load->view($tabs['view']); ?>

					</div>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php echo form_close(); ?>
		<div class="btn-bottom-pusher"></div>
	</div>
</div>
<div id="new_version"></div>
<?php init_tail(); ?>

<?php 
$viewuri = $_SERVER['REQUEST_URI'];
 ?>

<?php if(!(strpos($viewuri,'admin/warranty_management/setting?group=general') === false)){
	require 'modules/warranty_management/assets/js/settings/general/general_js.php';
}elseif(!(strpos($viewuri,'admin/warranty_management/setting?group=warranty_receipt_process') === false)){
	require('modules/warranty_management/assets/js/settings/warranty_receipt_process/warranty_receipt_process_manage_js.php');

}

 ?>
</body>
</html>
