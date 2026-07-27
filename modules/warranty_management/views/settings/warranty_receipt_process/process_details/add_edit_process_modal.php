<div class="modal fade" id="appointmentModal">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">

				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<?php 
				$title='';
				$id='';

				$process_name='';
				$person_in_charge='';

				if(isset($process)){
					$title =_l('wm_update_process');
					$id= $process->id;

					$process_name = $process->process_name;
					$estimate_time = $process->estimate_time;
					$person_in_charge = new_explode(',', $process->person_in_charge);
					$order_number = $process->order_number;

				}else{
					$title =_l('wm_add_process');

					$process_name = '';
					$estimate_time = 1;
					$person_in_charge = '';
					$order_number = $get_order_number;
				}

				$warranty_process_id = isset($warranty_process_id) ? $warranty_process_id : '';

				?>
				<h4 class="modal-title"><?php echo new_html_entity_decode($title); ?></h4>
			</div>
			<?php echo form_open_multipart(admin_url('warranty_management/add_edit_process/'.$id), array('id' => 'add_edit_process')); ?>
			<div class="modal-body">
				<div class="tab-content">
					<div class="row">
						<input type="hidden" value="<?php echo new_html_entity_decode($warranty_process_id); ?>" name="warranty_receipt_id">

						<div class="row">
							<div class="col-md-12">
								<div class="col-md-6">
									<?php echo render_input('process_name','wm_process_name', $process_name,'text'); ?>  

									<?php echo render_select('person_in_charge[]',$person_in_charges,array('staffid', array('firstname', 'lastname')),'wm_person_in_charge',$person_in_charge, ['multiple' => true], [], '', '', false); ?>
								</div>
								<div class="col-md-6">

									<div class=''>
										<?php echo render_input('estimate_time','wm_estimate_time', $estimate_time,'number'); ?> 
										<?php echo render_input('order_number','wm_order_number', $order_number,'number'); ?>   
									</div>  

								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-12">
									<div class="input-group">
										<?php
										$icon = 'fa fa-edit';
										?>
										<input type="text" name="step_icon" value="<?php if($icon){echo new_html_entity_decode($icon);} ?>" class="form-control icon-picker" id="step_icon">
										<span class="input-group-addon">
											<i class="<?php if($icon){echo new_html_entity_decode($icon);} ?>"></i>
										</span>
									</div>
									
								</div>
							</div>
						</div>


						<div class="col-md-12">

							<p class="bold"><?php echo _l('operation_description'); ?></p>
							<?php
               					// onclick and onfocus used for convert ticket to task too
							echo render_textarea('description','',(isset($process) ? $process->description : ''),array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($process) || isset($process) && $process->description == '' ? 'warranty_process_init_editor(\'.tinymce-operation\', {auto_focus: true});' : 'warranty_process_init_editor(\'.tinymce-operation\', {auto_focus: true});' )),array(),'','tinymce-operation'); ?>
						</div>

						<div class="col-md-12">
							<div class=" attachments">
								<div class="attachment">
									<div class="col-md-6">
										<div class="form-group">
											<label for="attachment" class="control-label"><?php echo _l('add_task_attachments'); ?></label>
											<div class="input-group">
												<input type="file" extension="<?php echo new_str_replace('.','',get_option('allowed_files')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="file[0]">
												<span class="input-group-btn">
													<button class="btn btn-success add_more_attachments_file p8" type="button"><i class="fa fa-plus"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div id="contract_attachments" class="mtop30 ">
									<?php if(isset($process_attachment)){ ?>

										<?php
										$data = '<div id="attachment_file">';
										foreach($process_attachment as $attachment) {
											$href_url = site_url('modules/warranty_management/uploads/warranty_processes/'.$attachment['rel_id'].'/'.$attachment['file_name']).'" download';
											if(!empty($attachment['external'])){
												$href_url = $attachment['external_link'];
											}
											$data .= '<div class="display-block contract-attachment-wrapper">';
											$data .= '<div class="col-md-10">';
											$data .= '<div class="col-md-1 mr-5">';
											$data .= '<a name="preview-btn" onclick="preview_file(this); return false;" rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" href="Javascript:void(0);" class="mbot10 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'._l("preview_file").'">';
											$data .= '<i class="fa fa-eye"></i>'; 
											$data .= '</a>';
											$data .= '</div>';
											$data .= '<div class=col-md-9>';
											$data .= '<div class="pull-left"><i class="'.get_mime_class($attachment['filetype']).'"></i></div>';
											$data .= '<a href="'.$href_url.'>'.$attachment['file_name'].'</a>';
											$data .= '<p class="text-muted">'.$attachment["filetype"].'</p>';
											$data .= '</div>';
											$data .= '</div>';
											$data .= '<div class="col-md-2 text-right">';
											if( has_permission('warranty_management', '', 'delete')){
												$data .= '<a href="#" class="text-danger" onclick="delete_process_attachment(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
											}
											$data .= '</div>';
											$data .= '<div class="clearfix"></div><hr/>';
											$data .= '</div>';
										}
										$data .= '</div>';
										echo new_html_entity_decode($data);
										?>
									<?php } ?>

								</div>
							</div>
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

<?php require('modules/warranty_management/assets/js/settings/warranty_receipt_process/warranty_receipt_process_details/add_edit_process_js.php'); ?>
<?php require('modules/warranty_management/assets/js/settings/warranty_receipt_process/add_edit_warranty_receipt_process_js.php'); ?>