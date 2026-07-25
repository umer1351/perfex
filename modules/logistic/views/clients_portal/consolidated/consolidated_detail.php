<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
        	<div class="col-md-12">
        		<h4 class=""><?php echo lg_html_entity_decode($title); ?></h4>
        	</div>
            <?php 
            $this->load->model('invoices_model');

             ?>

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="bold"><?php echo '#'.e($consolidation->shipping_prefix.$consolidation->number_code); ?> <?php echo format_lg_package_status($consolidation->delivery_status); ?></h4>
                                
                                <p><?php echo _l('lg_package_type').': '._l('lg_'.$consolidation->rel_type); ?></p>
                                <?php 

                                $rel_str = _l('lg_package_details').': ';
                                if($consolidation->rel_type == 'locker_packages'){
                                    $url = site_url('logistic/client/package_detail/');

                                }else if($consolidation->rel_type == 'shipping'){
                                    $url = site_url('logistic/client/shipping_detail/');
                                }

                                if($consolidation->rel_id != ''){
                                    $rel_arr = explode(',', $consolidation->rel_id);
                                    foreach($rel_arr as $key => $rel_id){
                                        if($key == 0){
                                            $rel_str .= '<a href="'.$url.$rel_id.'">'.lg_get_tracking_number_by_type($consolidation->rel_type, $rel_id).'</a>';
                                        }else{
                                            $rel_str .= ', <a href="'.$url.$rel_id.'">'.lg_get_tracking_number_by_type($consolidation->rel_type, $rel_id).'</a>';
                                        }
                                    }
                                }

                                echo lg_html_entity_decode($rel_str);

                                ?>

                            </div>

                            <div class="col-md-6">
                                <div class="btn-group pull-right mright5">
                                     <button type="button" class="btn btn-default  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                     <?php echo _l('lg_action'); ?><span class="caret"></span>
                                     </button>
                                     <ul class="dropdown-menu dropdown-menu-right">

                                        <li>
                                           <a href="<?php echo site_url('logistic/client/export_consolidation_shipment/'.$consolidation->id.'?output_type=I'); ?>" class=" delete-text" target="_blank"><i class="fa fa-file-lines"></i> <?php echo _l('lg_export_shipment'); ?></a>
                                        </li>

                                        <li>
                                           <a href="<?php echo site_url('logistic/client/export_consolidation_label/'.$consolidation->id.'?output_type=I'); ?>" target="_blank" class=" delete-text"><i class="fa fa-file-contract"></i> <?php echo _l('lg_export_label'); ?></a>
                                        </li>
                                     </ul>
                                </div>
                            </div>


                        </div>

                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_agency'); ?></p>
                                <p class=""><?php echo lg_get_agency_name_by_id($consolidation->agency); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_office_of_origin'); ?></p>
                                <p class=""><?php echo get_office_group_name_by_id($consolidation->office_of_origin); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_service_mode'); ?></p>
                                <p class=""><?php echo lg_get_service_name_by_id($consolidation->service_mode); ?></p>
                            </div>
                        </div>


                        <div class="row mtop15">
                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_consolidation_date'); ?></p>
                                <p class=""><?php echo _dt($consolidation->created_at); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_type_of_packages'); ?></p>
                                <p class=""><?php echo lg_get_package_type_name_by_id($consolidation->type_of_package); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_courrier_company'); ?></p>
                                <p class=""><?php echo lg_get_shipping_company_name($consolidation->courrier_company); ?></p>
                            </div>
                        </div>

                        <div class="row mtop15">
                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_delivery_time'); ?></p>
                                <p class=""><?php echo lg_get_delivery_time_name_by_id($consolidation->delivery_time); ?></p>

                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_logistic_service'); ?></p>
                                <p class=""><?php echo lg_get_logistic_service_name_by_id($consolidation->logistic_service_id); ?></p>
                            </div>

  
                        </div>


                        <h4 class=""><?php echo _l('lg_attachments'); ?></h4>
                        <hr class="hr-panel-heading" />
                         <?php if(isset($consolidation)){ ?>
                            <div class="col-md-12" id="consolidation_pv_file">
                            <?php
                                $file_html = '';
                                if(count($consolidation_attachments) > 0){
                                    $file_html .= '';
                                    foreach ($consolidation_attachments as $f) {
                                        $href_url = site_url(LOGISTIC_PATH.'consolidated/'.$f['rel_id'].'/'.$f['file_name']).'" download';
                                                        if(!empty($f['external'])){
                                                          $href_url = $f['external_link'];
                                                        }
                                       $file_html .= '<div class="mbot15 row inline-block full-width" data-attachment-id="'. $f['id'].'">
                                      <div class="col-md-8">
                                         <a name="preview-consolidation-btn" onclick="preview_consolidation_btn(this); return false;" rel_id = "'. $f['rel_id']. '" id = "'.$f['id'].'" href="Javascript:void(0);" class="mbot10 mright5 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'. _l('preview_file').'"><i class="fa fa-eye"></i></a>
                                         <div class="pull-left"><i class="'. get_mime_class($f['filetype']).'"></i></div>
                                         <a href=" '. $href_url.'" target="_blank" download>'.$f['file_name'].'</a>
                                         <br />
                                         <small class="text-muted">'.$f['filetype'].'</small>
                                      </div>
                                      <div class="col-md-4 text-right">';
                                        if($f['staffid'] == get_staff_user_id() || is_admin()){
                                        $file_html .= '<a href="#" class="text-danger" onclick="delete_consolidation_attachment('. $f['id'].'); return false;"><i class="fa fa-times"></i></a>';
                                        } 
                                       $file_html .= '</div></div>';
                                    }
                                    echo lg_html_entity_decode($file_html);
                                }else{
                                    echo '<p>'._l('no_files_found').'</p>';
                                }
                             ?>
                            </div>

                            <div id="consolidation_file_data"></div>
                        <?php } ?>

                        <hr class="hr-panel-heading" />



                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class=""><?php echo _l('lg_delivery_details'); ?></h4>
                                <hr class="hr-panel-heading" />
                            </div>


                            <div class="col-md-12">
                                <?php if(isset($delivery_shipments->id)){ ?>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="bold"><?php echo _l('lg_delivery_date'); ?></p>
                                            <p class=""><?php echo _dt($delivery_shipments->delivery_date); ?></p>
                                        </div>

                                        <div class="col-md-4">
                                             <p class="bold"><?php echo _l('lg_delivery_by'); ?></p>
                                            <p class=""><?php echo get_staff_full_name($delivery_shipments->delivered_by); ?></p>
                                        </div>

                                        <div class="col-md-4">
                                            <p class="bold"><?php echo _l('lg_receive_by_str'); ?></p>
                                            <p class=""><?php echo e($delivery_shipments->receive_by); ?></p>
                                        </div>

                                        <div class="col-md-4 mtop15">
                                            <p class="bold"><?php echo _l('lg_signature'); ?></p>
                                            <?php if(file_exists(LOGISTIC_MODULE_UPLOAD_FOLDER.'/consolidation_delivery_shipment/sign/'.$consolidation->id.'/signature_'.$consolidation->id.'.png')){ ?>
                                              <img src="<?php echo site_url(LOGISTIC_PATH.'consolidation_delivery_shipment/sign/'.$consolidation->id.'/signature_'.$consolidation->id.'.png'); ?>" class="">

                                                 
                                            <?php }elseif(file_exists(LOGISTIC_MODULE_UPLOAD_FOLDER.'/consolidation_delivery_shipment/sign/'.$consolidation->id.'/signature_'.$consolidation->id.'.jpg')){ ?>
                                                 <img src="<?php echo site_url(LOGISTIC_PATH.'consolidation_delivery_shipment/sign/'.$consolidation->id.'/signature_'.$consolidation->id.'.jpg'); ?>" class="">

                                                 
                                            <?php } ?>
                                        </div>

                                        <div class="col-md-6 mtop15">
                                            <div class="col-md-12" id="consolidation_pv_file">
                                                <?php
                                                    $sm_file_html = '';
                                                    if(count($shipment_attachments) > 0){
                                                        $sm_file_html .= '';
                                                        foreach ($shipment_attachments as $ff) {
                                                            $href_url = site_url(LOGISTIC_PATH.'consolidation_delivery_shipment/attachments/'.$ff['rel_id'].'/'.$ff['file_name']).'" download';
                                                                            if(!empty($ff['external'])){
                                                                              $href_url = $ff['external_link'];
                                                                            }
                                                           $sm_file_html .= '<div class="mbot15 row inline-block full-width" data-attachment-id="'. $ff['id'].'">
                                                          <div class="col-md-8">
                                                             <a name="preview-consolidation-btn" onclick="preview_consolidation_shipment_btn(this); return false;" rel_id = "'. $ff['rel_id']. '" id = "'.$ff['id'].'" href="Javascript:void(0);" class="mbot10 mright5 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'. _l('preview_file').'"><i class="fa fa-eye"></i></a>
                                                             <div class="pull-left"><i class="'. get_mime_class($ff['filetype']).'"></i></div>
                                                             <a href=" '. $href_url.'" target="_blank" download>'.$ff['file_name'].'</a>
                                                             <br />
                                                             <small class="text-muted">'.$ff['filetype'].'</small>
                                                          </div>
                                                          <div class="col-md-4 text-right">';
                                                           
                                                           $sm_file_html .= '</div></div>';
                                                        }
                                                        echo lg_html_entity_decode($sm_file_html);
                                                    }else{
                                                        echo '<p>'._l('no_files_found').'</p>';
                                                    }
                                                 ?>
                                                </div>

                                                <div id="shipment_file_data"></div>

                                        </div>
                                    </div>

                                <?php }else{ ?>

                                    <p class="bold"><?php echo _l('no_delivery_shipment_info_found'); ?></p>
                                <?php } ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class=""><?php echo _l('lg_histories'); ?></h4>
                                <hr class="hr-panel-heading mbot5" />
                            </div>


                            <div class="col-md-12">

                                <div class="horizontal-scrollable-tabs preview-tabs-top">
                                    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                                    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                                    <div class="horizontal-tabs">
                                       <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                                          <li role="presentation" class="active">
                                             <a href="#tracking_history" aria-controls="tracking_history" role="tab" data-toggle="tab">
                                             <?php echo _l('lg_tracking_history'); ?>
                                             </a>
                                          </li>
                                           
                                          
                                       </ul>
                                    </div>
                                 </div>

                                 <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active" id="tracking_history">
                                        <table class="table dt-table">
                                            <thead>
                                                <th><?php echo _l('lg_time_update'); ?></th>
                                                <th><?php echo _l('lg_new_location'); ?></th>
                                                <th><?php echo _l('lg_package_status'); ?></th>
                                                <th><?php echo _l('lg_remarks'); ?></th>
                                            </thead>
                                            <tbody>
                                                <?php if(isset($tracking_histories) && count($tracking_histories) > 0){ ?>
                                                    <?php foreach($tracking_histories as $tracking_history){ ?>
                                                        <tr>

                                                            <td><?php echo _dt($tracking_history['time_update']); ?></td>
                                                            <td><?php echo lg_get_country_name_by_id($tracking_history['new_location']); ?></td>
                                                            <td><?php echo format_lg_package_status($tracking_history['delivery_status']); ?></td>
                                                            <td><?php echo e($tracking_history['remark']); ?></td>
                                                            
                                                        </tr>       

                                                    <?php } ?>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    

                                 </div>
                               
                            </div>

                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class=""><?php echo _l('lg_consolidation_details'); ?></h4>
                                <hr class="hr-panel-heading mbot5" />
                            </div>

                            <div class="col-md-12">
                                <div class="table-responsive">

                                    <table class="table items items-preview estimate-items-preview mtop5" data-type="estimate">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('lg_amount'); ?></th>
                                                <th><?php echo _l('lg_package_description'); ?></th>
                                                <th><?php echo _l('lg_weight'); ?></th>
                                                <th><?php echo _l('lg_length'); ?></th>
                                                <th><?php echo _l('lg_width'); ?></th>
                                                <th><?php echo _l('lg_height'); ?></th>
                                                <th><?php echo _l('lg_weight_vol'); ?></th>
                                                <th class="text-right"><?php echo _l('lg_fixed_charge'); ?></th>
                                                <th class="text-right"><?php echo _l('lg_dec_value'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $weight = 0;
                                            $volumetric_weight = 0;
                                            $total_dec = 0;
                                            foreach($consolidation->shipment_detail as $key => $detail){ 
                                                if($detail['weight_vol'] >= $detail['weight']){
                                                    $volumetric_weight += $detail['weight_vol'];
                                                }else{
                                                    $weight += $detail['weight'];
                                                }

                                                $total_dec += $detail['dec_value'];

                                                ?>
                                                <tr>

                                                    <td><?php echo e($detail['amount']); ?></td>
                                                    <td><?php echo e($detail['package_description']); ?></td>
                                                    <td><?php echo e($detail['weight']); ?></td>
                                                    <td><?php echo e($detail['length']); ?></td>
                                                    <td><?php echo e($detail['width']); ?></td>
                                                    <td><?php echo e($detail['height']); ?></td>
                                                    <td><?php echo e($detail['weight_vol']); ?></td>
                                                    <td class="text-right"><?php echo e($detail['fixed_charge']); ?></td>
                                                    <td class="text-right"><?php echo e($detail['dec_value']); ?></td>
                                                </tr>

                                            <?php } ?>

                                            <tr>

                                                <td colspan="2"><span class="bold"><?php echo _l('lg_price').' '.$consolidation->weight_units_setting.': '; ?></span><?php echo e($consolidation->price_kg); ?></td>
                                               
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right"><?php echo _l('lg_subtotal'); ?></td>
                                                <td class="text-right"><?php echo app_format_money($consolidation->subtotal, $consolidation->currency); ?></td>
                                            </tr>

                                            <tr>
                                                <td colspan="2"><span class="bold"><?php echo _l('lg_weight').': '; ?></span><?php echo e($weight); ?></td>
                                               
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right"><?php echo _l('lg_discount'). ' '.$consolidation->discount_percent.'%'; ?></td>
                                                <td class="text-right"><?php echo app_format_money($consolidation->discount, $consolidation->currency); ?></td>
                                            </tr>

                                            <tr>
                                                <td colspan="2"><span class="bold"><?php echo _l('lg_volumetric_weight').': '; ?></span><?php echo e($volumetric_weight); ?></td>
                                               
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right"></td>
                                                <td class="text-right"></td>
                                            </tr>

                                            <tr>
                                                <td colspan="2"><span class="bold"><?php echo _l('lg_total_weight_calculation').': '; ?></span><?php echo e($volumetric_weight + $weight); ?></td>
                                               
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right"></td>
                                                <td class="text-right"></td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <h4 class=""><?php echo _l('lg_rate_and_tax_information'); ?></h4>
                                <hr class="hr-panel-heading mbot5" />
                            </div>

                            <div class="col-md-12">
                                    <table class="table items items-preview estimate-items-preview mtop5" data-type="estimate">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('lg_value_assured'); ?></th>
                                                <th><?php echo _l('lg_shipping_insurance').' '.app_format_number($consolidation->shipping_insurance_percent).'%'; ?></th>
                                                <th><?php echo _l('lg_custom_duties').' '.app_format_number($consolidation->custom_duties_percent).'%'; ?></th>
                                                <th><?php echo _l('lg_declared_total_value'); ?></th>
                                                <th><?php echo _l('lg_declared_value'). ' '.app_format_number($consolidation->declared_value_percent).'%'; ?></th>
                                                <th><?php echo _l('lg_tax'). ' '.app_format_number($consolidation->tax_percent).'%'; ?></th>
                                                <th><?php echo _l('lg_fixed_charge'); ?></th>
                                                <th><?php echo _l('lg_reissue'); ?></th>
                                                <th><?php echo _l('lg_total'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo app_format_number($consolidation->value_assured); ?></td>
                                                <td><?php echo app_format_number($consolidation->shipping_insurance); ?></td>
                                                <td><?php echo app_format_number($consolidation->custom_duties); ?></td>
                                                <td><?php echo app_format_number($total_dec); ?></td>
                                                <td><?php echo app_format_number($consolidation->declared_value); ?></td>
                                                <td><?php echo app_format_number($consolidation->tax); ?></td>
                                                <td><?php echo app_format_number($consolidation->fixed_charge); ?></td>
                                                <td><?php echo app_format_number($consolidation->reissue); ?></td>
                                                <td><?php echo app_format_money($consolidation->total, $consolidation->currency); ?></td>
                                            </tr>

                                        </tbody>
                                    </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class=""><?php echo _l('lg_sender_details'); ?></h4>
                                <hr class="hr-panel-heading mbot5" />
                            </div>

                            

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_name'); ?></p>
                                <p class=""><?php echo e($client->company); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_email'); ?></p>
                                <p class=""><?php echo lg_get_contact_primary_email($client->userid); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_phone'); ?></p>
                                <p class=""><?php echo ($client->phonenumber != '') ? e($client->phonenumber) : _l('no_entries_found'); ?></p>
                            </div>

                            
                        </div>
                        <div class="row mtop15">
                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_address'); ?></p>
                                <p class=""><?php echo e($client_address->address); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_country'); ?></p>
                                <p class=""><?php echo lg_get_country_name_by_id($client_address->country); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_city'); ?></p>
                                <p class=""><?php echo lg_get_city_name_by_id($client_address->city); ?></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class=""><?php echo _l('lg_recipient_details'); ?></h4>
                                <hr class="hr-panel-heading mbot5" />
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_name'); ?></p>
                                <p class=""><?php echo e($recipient->first_name.' '.$recipient->last_name); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_email'); ?></p>
                                <p class=""><?php echo e($recipient->email); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_phone'); ?></p>
                                <p class=""><?php echo ($recipient->phone != '') ? e($recipient->phone) : _l('no_entries_found'); ?></p>
                            </div>

                            
                        </div>
                        <div class="row mtop15">
                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_address'); ?></p>
                                <p class=""><?php echo e($recipient_address->address); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_country'); ?></p>
                                <p class=""><?php echo lg_get_country_name_by_id($recipient_address->country); ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="bold"><?php echo _l('lg_city'); ?></p>
                                <p class=""><?php echo lg_get_city_name_by_id($recipient_address->city); ?></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>



        </div>