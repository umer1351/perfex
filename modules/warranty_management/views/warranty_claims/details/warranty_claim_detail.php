<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
				<div class="row">

					<div class="col-md-12">

						<div class="card">
							<div class="row">
								<div class="col-md-12">
									<div class="card-header no-padding-top">
										<div class="row">
											<div class="col-md-6">
												<h4><?php  echo new_html_entity_decode(_l('wm_warranty_claim_detail')); ?></h4>
											</div>
											
											<div class="col-md-6 ">
												<div class="pull-right">
													<?php echo render_warranty_status_html($warranty_claim->id, 'warranty_claim', $warranty_claim->status); ?>
												</div>
											</div>

										</div>
									</div>
								</div>
								<div class="col-md-1">
								</div>
							</div>
							<hr class="no-margin">

							<div class="card-block">

								<div class="row">
									<div class="col-md-12 padding-bottom-3x mb-1">
										<div class="card mb-3">
											<div class="p-4 text-center text-white text-lg bg-dark rounded-top"><span class="text-uppercase"><?php echo _l('wm_claim_code'); ?> - </span><span class="text-medium"><?php echo new_html_entity_decode($warranty_claim->claim_code); ?></span></div>
											<div class="d-flex flex-wrap flex-sm-nowrap justify-content-between py-3 px-2 bg-secondary">
												<div class="w-100 text-center py-1 px-2"><span class="text-medium"></span></div>
												<div class="w-100 text-center py-1 px-2"><span class="text-medium">Status: </span><?php echo _l($warranty_claim->status); ?></div>
												<div class="w-100 text-center py-1 px-2"><span class="text-medium"></span></div>
											</div>
											
											<div class="card-body">
												<div class="steps d-flex flex-wrap flex-sm-nowrap justify-content-between padding-top-2x padding-bottom-1x">
													<?php foreach ($warranty_process_details as $key => $warranty_process_detail) { ?>
														<?php 
														$step_icon = 'fa fa-edit';
														if(new_strlen($warranty_process_detail['step_icon']) > 0){
															$step_icon = $warranty_process_detail['step_icon'];
														}

														?>
														<div class="step <?php echo new_html_entity_decode($warranty_process_detail['status']); ?>">
															<div class="step-icon-wrap">
																<div class="step-icon"><i class="fa <?php echo new_html_entity_decode($step_icon); ?> mtop-18 icon_mtop"></i></div>
															</div>
															<h4 class="step-title"><?php echo new_html_entity_decode($warranty_process_detail['process_name']); ?></h4>
														</div>
													<?php } ?>
												</div>
											</div>
										</div>

									</div>
								</div>
								<hr class="no-mtop">

								

							</div>
						</div>

					</div>
				</div>

			</div>

			<!-- warranty process detail start -->
			<?php if(count($warranty_process_details) > 0){ ?>

				<div class="row">
					<?php 
					$id = '';
					$title = '';
					$title .= _l('view_manufacturing_order_lable');

					$start_working_hide='';
					$action_hide='';
					$cancel_hide='';

					$waiting_for_another_wo_active='';
					$ready_active='';
					$progress_active='';
					$finished_active='';


					switch ($one_warranty_process_detail->status) {
						case 'pendding':
						$waiting_for_another_wo_active=' active';
						$start_working_hide = '';
						$start_working_status = 'default';
						$action_hide=' hide';
						$pause_hide=' hide';
						break;

						case 'processing':
						$progress_active=' active';
						$start_working_hide = ' hide';
						$start_working_status = 'default';
						$action_hide=' ';
						$pause_hide=' hide';
						break;

						case 'complete':
						$finished_active=' active';
						$start_working_hide = ' hide';
						$start_working_status = 'default';
						$action_hide=' hide';
						$pause_hide=' hide';
						$cancel_hide=' hide';
						break;

						case 'pause':
						$pause_hide=' ';
						$start_working_hide = ' hide';
						$start_working_status = 'default';
						$action_hide=' hide';
						$progress_active=' active';
						break;

						default:
						$waiting_for_another_wo_active=' active';
						$start_working_hide = '';
						$start_working_status = 'default';
						$action_hide=' hide';
						$pause_hide=' hide';
						break;

					}


					?>

					<div class="col-md-12" >
						<div class="panel_s">

							<div class="panel-body">
								<div class="row mb-5">
									<div class="col-md-9">
										<h4 class="no-margin"><?php echo new_html_entity_decode($header); ?> 
									</div>
									<div class="col-md-3">

										<a href="<?php echo admin_url('warranty_management/warranty_claim_detail/'.$warranty_claim_id.'/'.$prev_id); ?>" class=" btn mright5 btn-info pull-right button-text-transform"><?php echo _l('mrp_next'); ?> <i class="fa fa-chevron-circle-right"></i></a>

										<a href="<?php echo admin_url('warranty_management/warranty_claim_detail/'.$warranty_claim_id.'/'.$next_id); ?>" class=" btn mright5 btn-default pull-right button-text-transform"><i class="fa fa-chevron-circle-left"></i>
											<?php echo _l('mrp_prev'); ?>
										</a>
										<button type="button" class=" btn mright5 btn-default pull-right button-text-transform"><?php echo new_html_entity_decode($pager_value).'/'.new_html_entity_decode($pager_limit); ?> </button>

									</div>
								</div>
								<br>
								<hr class="hr-color no-margin">
								<br>

								<!-- action related work order -->
								<div class="row">
									<div class="col-md-6">

										<?php if(has_permission('warranty_management', '', 'create') || has_permission('warranty_management', '', 'edit') ){ ?>

											<?php if(!$check_mo_cancelled){ ?>
												<div class="<?php echo new_html_entity_decode($start_working_hide) ?>">
													<button type="button" class="btn btn-sm btn-info btn-<?php echo new_html_entity_decode($start_working_status); ?> pull-left mark_start_working mright5"><?php echo _l('wm_start'); ?></button>
												</div>

												<div class="<?php echo new_html_entity_decode($action_hide) ?>">
													<button type="button" class="btn btn-warning pull-left mark_pause mright5"><?php echo _l('wm_pause'); ?></button>
													<button type="button" class="btn btn-success pull-left mark_done mright5"><?php echo _l('wm_done'); ?></button>
												</div>

												<div class="<?php echo new_html_entity_decode($pause_hide) ?>">
													<button type="button" class="btn btn-sm btn-info pull-left mark_start_working mright5"><?php echo _l('mrp_continute_production'); ?></button>
												</div>

											<?php } ?>

										<?php } ?>
									</div>

									<!-- status -->
									<div class="col-md-6">
										<div class="sw-main sw-theme-arrows pull-right">

											<!-- SmartWizard html -->
											<ul class="nav nav-tabs step-anchor">
												<li class="<?php echo new_html_entity_decode($waiting_for_another_wo_active) ?>"><a href="#"><?php echo _l('wm_pendding'); ?></a></li>
												
												<li class="<?php echo new_html_entity_decode($progress_active) ?>"><a href="#"><?php echo _l('wm_processing'); ?></a></li>
												<li class="<?php echo new_html_entity_decode($finished_active) ?>"><a href="#"><?php echo _l('wm_complete'); ?></a></li>
											</ul>
										</div>

									</div>
									<!-- status -->

								</div>
								<!-- action related work order -->

								<hr class="">


								<!-- start tab -->
								<div class="modal-body">
									<div class="tab-content">
										<!-- start general infor -->
										<?php 

										$one_warranty_process_detail_id = isset($one_warranty_process_detail) ? $one_warranty_process_detail->id : '';

										$duration_expected = isset($one_warranty_process_detail) ? $one_warranty_process_detail->estimate_time : '';
										$real_duration = isset($one_warranty_process_detail) ? $one_warranty_process_detail->actual_time : '';

										?>
										<div class="row">

											<div class="col-md-6">

												<div class="row">
													<div class="col-md-6 col-sm-6">
														<h4 class="bold">
															<span id="invoice-number">
																<?php echo new_html_entity_decode($warranty_claim->claim_code); ?>
															</span>
														</h4>
														<address>
															<?php echo format_organization_info(); ?>
														</address>



													</div>

													<div class="col-md-6 col-sm-6 text-right">
														<span class="bold"><?php echo _l('client'); ?>:</span>
														<address class="invoice-html-customer-shipping-info">
															<b><?php echo get_company_name($warranty_claim->client_id); ?></b>
															<br>
														</address>

														<p class="no-mbot">
															<span class="bold">
																<?php echo _l('wm_date_created'); ?>
															</span>
															<?php echo _dt($warranty_claim->datecreated); ?>
															<h5 class="bold">
															</h5>
														</p>

														<?php if(isset($warranty_claim) && $warranty_claim && $warranty_claim->invoice_id){ ?>
															<p class="no-mbot">
																<span class="bold">
																	<?php echo _l('wm_invoice_related'); ?>
																	<a href="<?php echo admin_url('invoices#'.$warranty_claim->invoice_id) ?>" ><?php echo format_invoice_number($warranty_claim->invoice_id); ?></a>
																</span>

															</p>
														<?php } ?>

														<?php if(count($warranty_claim_information_details) > 0 && $warranty_claim_information_details && $warranty_claim_information_details[0]['rel_type'] == 'delivery' && wm_get_status_modules('warehouse')){ ?>
															<p class="no-mbot">
																<span class="bold">
																	<?php echo _l('wm_delivery_note_related'); ?>

																	<?php 
																	$delivery_code = get_goods_delivery_code($warranty_claim_information_details[0]['rel_id']) != null ? get_goods_delivery_code($warranty_claim_information_details[0]['rel_id'])->goods_delivery_code : '';
																	?>
																	<a href="<?php echo admin_url('warehouse/manage_delivery/' . $warranty_claim_information_details[0]['rel_id'] ) ?>" ><?php echo new_html_entity_decode($delivery_code); ?></a>
																</span>

															</p>
														<?php }elseif(count($warranty_claim_information_details) > 0 && $warranty_claim_information_details && $warranty_claim_information_details[0]['rel_type'] == 'sm_order'){ ?>
															<p class="no-mbot">
																<span class="bold">
																	<?php echo _l('wm_order_related'); ?>

																	<a href="<?php echo admin_url('service_management/order_detail/' . $warranty_claim_information_details[0]['rel_id']) ?>" ><?php echo sm_order_code($warranty_claim_information_details[0]['rel_id']); ?></a>
																</span>
																<h5 class="bold">
																</h5>
															</p>
														<?php } ?>

													</div>

													<div class="col-md-12">
														<p class="no-mbot">
															<span class="bold">
																<?php echo _l('wm_product_service_name').': '; ?>
															</span>
															<span class="bold">
																<?php echo new_html_entity_decode($warranty_claim_information_details[0]['items_name']); ?>
															</span>
															<h5 class="bold">
															</h5>
														</p>
													</div>

													<?php if(new_strlen($warranty_claim->client_note) > 0){ ?>
														<div class="col-md-12">
															<p class="no-mbot">
																<span class="bold">
																	<?php echo _l('wm_client_note'); ?>
																</span>
																<?php echo new_html_entity_decode($warranty_claim->client_note); ?>
															</p>
														</div>
													<?php } ?>

													<?php if(new_strlen($warranty_claim->admin_note) > 0){ ?>
														<div class="col-md-12">
															<p class="no-mbot">
																<span class="bold">
																	<?php echo _l('wm_admin_note'); ?>
																</span>
																<?php echo new_html_entity_decode($warranty_claim->admin_note); ?>
															</p>
														</div>
													<?php } ?>

													<div class="col-md-12">
														<!-- Nav tabs -->
														<ul class="nav nav-tabs  tabs" role="tablist">
															<?php if(isset($goods_delivery) && count($goods_delivery) > 0){ ?>
																<li class="nav-item active">
																	<a class="nav-link" data-toggle="tab" href="#delivery_note" role="tab" aria-selected="false"><strong><?php echo _l('goods_delivery'); ?></strong></a>
																</li>
															<?php } ?>
															<?php if(isset($packing_lists) && count($packing_lists) > 0){ ?>
																<li class="nav-item">
																	<a class="nav-link" data-toggle="tab" href="#packing_list" role="tab" aria-selected="false"><strong><?php echo _l('wm_packing_list'); ?></strong></a>
																</li>
															<?php } ?>

														</ul>

													</div>

												</div>

											</div>
											<div class="col-md-6 panel-padding" >
												<input type="hidden" name="warranty_claim_id" value="<?php echo new_html_entity_decode($warranty_claim_id) ?>">
												<input type="hidden" name="warranty_process_detail_id" value="<?php echo new_html_entity_decode($warranty_process_detail_id) ?>">

												<table class="table border table-striped no-margin" >
													<tbody>
														<tr class="project-overview">
															<td class="bold td-width"><?php echo _l('wm_description'); ?></td>
														</tr>
														<tr class="project-overview">
															<td class="btd-width"><?php echo new_html_entity_decode($one_warranty_process_detail->description); ?></td>
														</tr>

													</tbody>
												</table>
											</div>

										</div>


										<div class="row">
											<h5 class="h5-color"><?php echo _l('wm_warranty_information'); ?></h5>
										</div>

										<div class="row">
											<div class="horizontal-scrollable-tabs preview-tabs-top">
												<div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
												<div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
												<div class="horizontal-tabs">
													<ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
														<li role="presentation" class="">
															<a href="#work_instruction" aria-controls="work_instruction"  class="work_instruction" role="tab" data-toggle="tab">
																<span class="glyphicon glyphicon-align-justify"></span>&nbsp;<?php echo _l('wm_warranty_guide'); ?>
															</a>
														</li>
														
														<li role="presentation" class="active">
															<a href="#time_tracking" aria-controls="time_tracking" role="tab" data-toggle="tab">
																<span class="fa fa-balance-scale menu-icon"></span>&nbsp;<?php echo _l('time_tracking'); ?>
															</a>
														</li>

													</ul>
												</div>
											</div>
											<br>


											<div class="tab-content active">
												<div role="tabpanel" class="tab-pane " id="work_instruction">
													<div class="row">
														<?php if(count($warranty_process_detail_file) > 0){ ?>
															<div class="col-md-12 border-right work_order_area">

																<?php	foreach ($warranty_process_detail_file as $file) {

																	?>

																	<?php if(!empty($file['external']) && $file['external'] == 'dropbox'){ ?>
																		<a href="<?php echo new_html_entity_decode($file['external_link']); ?>" target="_blank" class="btn btn-info mbot20"><i class="fa fa-dropbox" aria-hidden="true"></i> <?php echo _l('open_in_dropbox'); ?></a><br />
																	<?php } ?>
																	<?php

																	$path = WARRANTY_CLAIM_PROCESS_UPLOAD.'/' .$file['rel_id'].'/'.$file['file_name'];

																	if(is_image($path)){ ?>
																		<img src="<?php echo base_url(CLAIM_PROCESS_ATTACHMENTS.$file['rel_id'].'/'.$file['file_name']); ?>" class="img img-responsive" >
																	<?php } else if(!empty($file['external']) && !empty($file['thumbnail_link'])){ ?>
																		<img src="<?php echo optimize_dropbox_thumbnail($file['thumbnail_link']); ?>" class="img img-responsive">
																	<?php } else if(strpos($file['filetype'],'pdf') !== false && empty($file['external'])){ ?>
																		<iframe src="<?php echo base_url(CLAIM_PROCESS_ATTACHMENTS.$file['rel_id'].'/'.$file['file_name']); ?>" height="100%" width="100%" frameborder="0"></iframe>
																	<?php } else if(strpos($file['filetype'],'xls') !== false && empty($file['external'])){ ?>
																		<iframe src='https://view.officeapps.live.com/op/embed.aspx?src=<?php echo base_url(CLAIM_PROCESS_ATTACHMENTS.$file['rel_id'].'/'.$file['file_name']); ?>' width='100%' height='100%' frameborder='0'>
																		</iframe>
																	<?php } else if(strpos($file['filetype'],'xlsx') !== false && empty($file['external'])){ ?>
																		<iframe src='https://view.officeapps.live.com/op/embed.aspx?src=<?php echo base_url(CLAIM_PROCESS_ATTACHMENTS.$file['rel_id'].'/'.$file['file_name']); ?>' width='100%' height='100%' frameborder='0'>
																		</iframe>
																	<?php } else if(strpos($file['filetype'],'doc') !== false && empty($file['external'])){ ?>
																		<iframe src='https://view.officeapps.live.com/op/embed.aspx?src=<?php echo base_url(CLAIM_PROCESS_ATTACHMENTS.$file['rel_id'].'/'.$file['file_name']); ?>' width='100%' height='100%' frameborder='0'>
																		</iframe>
																	<?php } else if(strpos($file['filetype'],'docx') !== false && empty($file['external'])){ ?>
																		<iframe src='https://view.officeapps.live.com/op/embed.aspx?src=<?php echo base_url(CLAIM_PROCESS_ATTACHMENTS.$file['rel_id'].'/'.$file['file_name']); ?>' width='100%' height='100%' frameborder='0'>
																		</iframe>
																	<?php } else if(is_html5_video($path)) { ?>
																		<video width="100%" height="100%" src="<?php echo site_url('download/preview_video?path='.protected_file_url_by_path($path).'&type='.$file['filetype']); ?>" controls>
																			Your browser does not support the video tag.
																		</video>
																	<?php } else if(is_markdown_file($path) && $previewMarkdown = markdown_parse_preview($path)) {
																		echo new_html_entity_decode($previewMarkdown);
																	} else {
																		echo '<a href="'.site_url(CLAIM_PROCESS_ATTACHMENTS.$file['rel_id'].'/'.$file['file_name']).'" download>'.$file['file_name'].'</a>';
																		echo '<p class="text-muted">'._l('no_preview_available_for_file').'</p>';
																	} ?>

																<?php } ?>
															</div>
														<?php } ?>

													</div>

												</div>

												
												<div role="tabpanel" class="tab-pane active" id="time_tracking">
													<div class="row">

														<div class="col-md-12 panel-padding" >
															<table class="table table-striped table-margintop">
																<tbody>
																	<tr class="project-overview">
																		<td class="bold" width="40%"><?php echo _l('expected_duration'); ?></td>
																		<td><b><?php echo new_html_entity_decode($duration_expected)?></b> <?php echo _l('wm_hours')  ?></td>
																	</tr>
																	<tr class="project-overview">
																		<td class="bold"><?php echo _l('real_duration'); ?></td>
																		<td><b><?php echo new_html_entity_decode($real_duration)?></b> <?php echo _l('wm_hours')  ?></td>
																	</tr>

																</tbody>
															</table>
														</div>
													</div>

													<div class="row">
														<div class="col-md-12">
															<div class="form"> 
																<div id="time_tracking_hs" class="product_tab handsontable htColumnHeaders">
																</div>
																<?php echo form_hidden('time_tracking_hs'); ?>
															</div>

														</div>
													</div>

												</div>


											</div>
										</div>

									</div>


								</div>
							</div>
						</div>

					</div>
				</div>
			<?php } ?>

			<!-- warranty process detail end -->
			<div class="row">
				<div class="col-md-12" >
					<div class="panel_s">

						<div class="panel-body">

							<div class="row mbot15">
								<div class="col-md-12">
									<a href="javascript:void(0)" class="btn btn-sm btn-info pull-right " onclick="warranty_claim_activity_log_modal('add', '' ,'<?php echo new_html_entity_decode($warranty_claim->id); ?>', 0); return false;"><?php echo _l('wm_add_warranty_log'); ?></a>
								</div>
							</div>
							<div class="row">

								<div class="col-md-12">
									<div class="activity-feed pr-0">
										<?php $key = 0; ?>
										<?php foreach ($arr_activity_logs as $date => $activity_log) { ?>
											<div class="feed-item ">
												<div class="date <?php if($key == 0){ echo ' text-info';} ?>">
													<div class="row">
														<div class="col-md-8 col-sm-8">
															<span class="text-has-action <?php if($key == 0){ echo ' text-info';} ?>" ><?php echo _dt($activity_log['date']).' '; ?> </span>
															<span class="text-has-action <?php if($key == 0){ echo ' text-info';} ?>" ><?php echo new_html_entity_decode($activity_log['description']).' '; ?></span>
															<?php if($activity_log['rel_type'] == 'warranty_claim' && (is_admin() || has_permission('warranty_management', '', 'delete') ) ){ ?>
															</div>

															<div class="col-md-4 col-sm-4">

																<?php echo icon_btn('#', 'fa fa-remove', 'btn-danger pull-right', ['data-original-title' => _l('wm_delete_shipment_log'), 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'onclick' => "delete_wh_activitylog(this,".$activity_log['id'].");return false;" ]); ?>
															<?php } ?>

															<?php if($activity_log['rel_type'] == 'warranty_claim' && ( has_permission('warranty_management', '', 'edit') )){ ?>

																<?php echo icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-warning pull-right', ['data-original-title' => _l('wm_edit_shipment_log'), 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'onclick' => "warranty_claim_activity_log_modal('edit',".$activity_log['id'].",".$warranty_claim->id.", 0); return false;"]); ?>
															<?php } ?>

														</div>
													</div>

												</div>

											</div>
											<?php $key++; ?>
										<?php } ?>

									</div>
								</div>

							</div>
						</div>
					</div>
				</div>
			</div>



			<div class="row">
				<div class="col-md-12 ">
					<div class="panel-body bottom-transaction">
						<div class="btn-bottom-toolbar text-right">
							<a href="<?php echo admin_url('warranty_management/warranty_claim_information'); ?>"class="btn btn-info text-right"><?php echo _l('wm_back'); ?></a>
						</div>
					</div>
					<div class="btn-bottom-pusher"></div>
				</div>
			</div>

		</div>
		<div id="modal_wrapper"></div>
	</div>
</div>

<?php init_tail(); ?>
<?php require 'modules/warranty_management/assets/js/warranty_claims/warranty_claim_detail_js.php';?>


