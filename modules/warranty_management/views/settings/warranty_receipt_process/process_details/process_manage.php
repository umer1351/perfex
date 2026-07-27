<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">

			<div class="col-md-5">
				<div class="row">
					<div class="panel_s">
						<?php 

						$id = isset($warranty_receipt_process) ? $warranty_receipt_process->id : '';
						$code = isset($warranty_receipt_process) ? $warranty_receipt_process->code : '';
						$name = isset($warranty_receipt_process) ? $warranty_receipt_process->name : '';
						$product_group_id = isset($warranty_receipt_process) ? $warranty_receipt_process->product_group_id : '';
						$description = isset($warranty_receipt_process) ? $warranty_receipt_process->description : '';
						?>
						<?php echo form_open(admin_url('warranty_management/add_warranty_receipt_process_modal/'.$id), array('id' => 'add_warranty_receipt_process')); ?>

						<div class="panel-body">
							<h4 class="no-margin">
								<?php echo new_html_entity_decode($code); ?>
							</h4>
							<hr class="hr-panel-heading" />

							<div class="row">
								<div class="col-md-12">
									<?php echo render_input('code','wm_code', $code,'text'); ?>   
								</div>
								<div class="col-md-12">
									<?php echo render_input('name','wm_name', $name,'text'); ?>   
								</div>

								<div class="col-md-12">
									<?php echo render_select('product_group_id',$product_groups, array('id', 'name'),'wm_product_group', $product_group_id); ?>   
							</div>
								<div class="col-md-12">

									<p class="bold"><?php echo _l('wm_description'); ?></p>
									<?php
               					// onclick and onfocus used for convert ticket to task too
									echo render_textarea('description','',($description),array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($warranty_receipt_process) || isset($warranty_receipt_process) && $warranty_receipt_process->description == '' ? 'warranty_receipt_process_init_editor(\'.tinymce-task\', {height:200, auto_focus: true});' : 'warranty_receipt_process_init_editor(\'.tinymce-task\', {height:200, auto_focus: true});')),array(),'no-mbot','tinymce-task'); ?>
								</div>	
							</div>

							<hr />
							<button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
							<a href="<?php echo admin_url('warranty_management/setting?group=warranty_receipt_process'); ?>"  class="btn btn-default pull-right mright5 "><?php echo _l('close'); ?></a>
						</div>
						<?php echo form_close(); ?>
					</div>

				</div>
			</div>

			<div class="col-md-7">
				<div class="row">

					<div class="panel_s"> 
						<div class="panel-body">

							<div class="row">
								<div class="col-md-12">
									<h4 class="h4-color no-margin"><i class="fa fa-area-chart" aria-hidden="true"></i> <?php echo _l('wm_processes'); ?></h4>
								</div>
							</div>
							<hr class="hr-color">

							<?php if(has_permission('warranty_management', '', 'create')){ ?>
								<div class="_buttons">
									<a href="#" onclick="add_process(<?php echo new_html_entity_decode($id) ?>,0,'add'); return false;" class="btn btn-info mbot10"><?php echo _l('wm_add_process'); ?></a>

								</div>
								<br>
							<?php } ?>

							<?php render_datatable(array(
								_l('id'),
								_l('wm_order_number'),
								_l('wm_process_name'),
								_l('wm_person_in_charge'),
								_l('wm_estimate_time'),
							),'process_table'); ?>
						</div>

					</div>
				</div>
				<div id="modal_wrapper"></div>
			</div>


		</div>
	</div>
</div>
<div id="contract_file_data"></div>

<?php echo form_hidden('warranty_process_id',$id); ?>
<?php init_tail(); ?>
<?php 
require('modules/warranty_management/assets/js/settings/warranty_receipt_process/add_edit_warranty_receipt_process_js.php');
require('modules/warranty_management/assets/js/settings/warranty_receipt_process/warranty_receipt_process_details/process_manage_js.php');

?>
</body>
</html>
