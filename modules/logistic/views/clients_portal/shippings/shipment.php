<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<div class="row accounting-template">
    <div class="col-md-12">
        <h4 class=""><?php echo lg_html_entity_decode($title); ?></h4>
    </div>

    <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'shipment-form')); ?>
    <?php $shipment_id = (isset($shipment) ? $shipment->id : '');
    echo form_hidden('id', $shipment_id); 

    ?>
        <div class="col-md-6">
            <div class="panel_s panel-table-full">
                <div class="panel-body">

                    <div class="row">
                        <div class="form-group col-md-<?php if(!isset($shipment) && $multiple == 1){ echo '12';}else{ echo '6';} ?>">

                            <label for="shipping_prefix"><span class="text-danger">* </span><?php echo _l('lg_shipping_prefix'); ?></label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    
                                    <div class="checkbox">
                                        <input type="checkbox" id="prefix_by_country_code" disabled="true" name="prefix_by_country_code">
                                        <label for="prefix_by_country_code"><?php echo _l('lg_country_code'); ?></label>
                                    </div>

                                </span>

                                <?php $prefix = (isset($shipment) ? $shipment->shipping_prefix : get_option('lg_delivery_prefix')); ?>
                                <input type="text" name="shipping_prefix" readonly="true" class="form-control" value="<?php echo lg_html_entity_decode($prefix); ?>"  >

                                <div id="country_code_select" class="hide">
                                    <select id="country_code" name="country_code" class="selectpicker" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true" tabindex="-98">
                                        <option value=""></option>
                                        <?php foreach($countries as $country){ ?>
                                            <option value="<?php echo lg_html_entity_decode($country['iso_code']); ?>"><?php  echo lg_html_entity_decode($country['iso_code'].' - '. $country['country_name']) ?></option>

                                        <?php } ?>
                                    </select>
                                </div>

                            </div>
                        </div>


                        <div class="col-md-6 <?php if(!isset($shipment) && $multiple == 1){ echo 'hide';} ?>">
                            <?php 
                            $number = ( isset($shipment) ? $shipment->number : get_shipment_next_number());
                            $number_code = (isset($shipment) ? $shipment->number_code : $number);
                                  
                            $number_type = ( isset($shipment) ? $shipment->number_type : get_option('lg_tracking_number_type'));

                            if($number_type == 'auto_increment'){
                                $number_code = str_pad($number,get_option('lg_number_digits_in_the_trace'),'0',STR_PAD_LEFT);
                            }else{
                                $number = 0;
                            }
                             echo render_input('number_code', 'lg_number', $number_code, 'text', ['readonly' => '1'] ); ?>

                             <?php 
                              echo form_hidden('number', $number); ?>

                              <?php  
                              echo form_hidden('number_type', $number_type); ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel_s panel-table-full">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-md-6">
                            <?php $agency = (isset($shipment) ? $shipment->agency : '');
                            echo render_select('agency', $agencies, array('id', 'agency_name'), 'lg_agency', $agency); ?>
                        </div>


                        <div class="col-md-6">
                            <?php $office_of_origin = (isset($shipment) ? $shipment->office_of_origin : '');
                            echo render_select('office_of_origin', $office_groups, array('id', 'office_name'), 'lg_office_of_origin', $office_of_origin); ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="panel_s panel-table-full">
                <div class="panel-body">
                    <h4 class="no-margin font-bold"><?php echo _l('lg_sender_information'); ?></h4>
                    <hr class="hr-panel-heading" />

                    <div class="row">
                        <div class="col-md-6 hide">
                            <label for="customer_id"><span class="text-danger">* </span><?php echo _l('lg_sender_customer'); ?></label>
                            <?php $customer_id = (isset($shipment) ? $shipment->customer_id : get_client_user_id());

                           

                            echo render_select('customer_id', $clients, array('userid', 'company'), '', $customer_id, ['required' => 'true']); ?>
                        </div>


                        <div class="col-md-12">
                            <label for="customer_address"><span class="text-danger">* </span><?php echo _l('lg_sender_customer_address'); ?></label>
                            <?php 
                            $client_address = [];
                            $customer_address = '';
                            echo render_select('customer_address', $client_address, array('id', 'name'), '', $customer_address); ?>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel_s panel-table-full">
                <div class="panel-body">
                    <h4 class="no-margin font-bold"><?php echo _l('lg_recipient_information'); ?></h4>
                    <hr class="hr-panel-heading" />

                    <div class="row">
                        <div class="col-md-6">
                            <label for="recipient_id"><span class="text-danger">* </span><?php echo _l('lg_recipient_customer'); ?></label>
                            <?php $recipient_id = (isset($shipment) ? $shipment->recipient_id : '');

                            $recipients = [];
                            echo render_select('recipient_id', $recipients, array('userid', 'company'), '', $recipient_id, ['required' => 'true']); ?>
                        </div>


                        <div class="col-md-6">
                            <label for="recipient_address_id"><span class="text-danger">* </span><?php echo _l('lg_recipient_address'); ?></label>
                            <?php 
                            $recipient_address = [];
                            $select_address = '';
                            echo render_select('recipient_address_id', $recipient_address, array('id', 'name'), '', $select_address); ?>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel_s panel-table-full">
                <div class="panel-body">
                    <h4 class="no-margin font-bold"><?php echo _l('lg_shipping_information'); ?></h4>
                    <hr class="hr-panel-heading" />

                    <div class="row">

                        <div class="col-md-4">
                            <label for="logistic_service_id"><?php echo _l('lg_logistic_service'); ?></label>
                            <?php $logistic_service_id = (isset($shipment) ? $shipment->logistic_service_id : get_option('lg_default_logistic_service'));
                            echo render_select('logistic_service_id', $logistics_services, array('id', 'logistics_service_name'), '', $logistic_service_id); ?>
                        </div>

                        <div class="col-md-4">
                            <label for="payment_term_id"><?php echo _l('lg_payment_term'); ?></label>
                            <?php $payment_term_id = (isset($shipment) ? $shipment->payment_term_id : get_option('lg_default_payment_terms'));
                            echo render_select('payment_term_id', $payment_terms, array('id', 'name'), '', $payment_term_id); ?>
                        </div>


                        <div class="col-md-4">
                            <label for="type_of_package"><?php echo _l('lg_type_of_packages'); ?></label>
                            <?php $type_of_package = (isset($shipment) ? $shipment->type_of_package : get_option('lg_default_type_of_package'));
                            echo render_select('type_of_package', $type_of_packages, array('id', 'type_of_package_name'), '', $type_of_package); ?>
                        </div>


                        <div class="col-md-4">
                            <label for="courrier_company"><?php echo _l('lg_courrier_company'); ?></label>
                            <?php $courrier_company = (isset($shipment) ? $shipment->courrier_company : get_option('lg_default_courier_company'));
                            if(isset($pre_alert)){
                                $courrier_company = $pre_alert->courier_company;
                            }
                            echo render_select('courrier_company', $shipping_companies, array('id', 'shipping_company_name'), '', $courrier_company); ?>
                        </div>

                        <div class="col-md-4">
                            <label for="service_mode"><?php echo _l('lg_service_mode'); ?></label>
                            <?php $service_mode = (isset($shipment) ? $shipment->service_mode : get_option('lg_default_service_mode'));
                            echo render_select('service_mode', $shipping_modes, array('id', 'shipping_mode_name'), '', $service_mode); ?>
                        </div>

                        <div class="col-md-4">
                            <label for="delivery_time"><?php echo _l('lg_delivery_time'); ?></label>
                            <?php $delivery_time = (isset($shipment) ? $shipment->delivery_time : get_option('lg_default_delivery_time'));
                            echo render_select('delivery_time', $shipping_times, array('id', 'shipping_time_name'), '', $delivery_time); ?>
                        </div>

                        <div class="col-md-4 hide">
                            <label for="assign_driver"><?php echo _l('lg_assign_driver'); ?></label>
                            <?php $assign_driver = (isset($shipment) ? $shipment->assign_driver : '');
                            echo render_select('assign_driver', $drivers, array('staffid', 'full_name'), '', $assign_driver); ?>
                        </div>

                        <div class="col-md-4 hide">
                            <?php
                                $currency_attr = array('data-show-subtext'=>true);

                                $selected = '';
                                foreach($currencies as $currency){
                                  if(isset($shipment) && $shipment->currency != 0){
                                    if($currency['id'] == $shipment->currency){
                                      $selected = $currency['id'];
                                    }
                                  }else{
                                   if($currency['isdefault'] == 1){
                                     $selected = $currency['id'];
                                   }
                                  }
                                }

                                if(isset($pre_alert)){
                                    $courrier_company = $pre_alert->currency;
                                }
               
                                ?>
                            <?php echo render_select('currency', $currencies, array('id','name','symbol'), 'invoice_add_edit_currency', $selected, $currency_attr); ?>
                        </div>

                         <?php
                            $shipment_currency = $base_currency;
                            if(isset($shipment) && $shipment->currency != 0){
                              $shipment_currency = lg_get_currency_by_id($shipment->currency);
                            } 

                            $from_currency = (isset($shipment) && $shipment->from_currency != null) ? $shipment->from_currency : $base_currency->id;
                            echo form_hidden('from_currency', $from_currency);

                          ?>
                        <div class="col-md-4  hide" id="currency_rate_div">
                           
                              
                              <label for="currency_rate"><?php echo _l('currency_rate'); ?><span id="convert_str"><?php echo ' ('.$base_currency->name.' => '.$shipment_currency->name.') ';  ?></span></label>
                 
                       
                              <?php $currency_rate = 1;
                                if(isset($shipment) && $shipment->currency != 0){
                                  $currency_rate = lg_get_currency_rate($base_currency->name, $shipment_currency->name);
                                }
                              echo render_input('currency_rate', '', $currency_rate, 'number', ['step' => 'any'], [], '', 'text-right'); 
                              ?>
        
                        </div>

                        <div class="col-md-4">
                                   
                            <?php 
                            $delivery_status = (isset($shipment) ? $shipment->delivery_status : get_option('lg_default_delivery_status'));

                            echo render_select('delivery_status',$statuses,array('id','style_name'),'lg_delivery_status', $delivery_status,array('data-width'=>'100%'),array(),'',''); ?>
            
                        </div>                     

                        <div class="attachments_area">
                            <div class=" attachments">
                                <div class="attachment">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="attachment" class="control-label"><?php echo _l('lg_attachment') ?></label>
                                            <div class="input-group">
                                                <input type="file" extension="jpg,png,pdf,doc,zip,rar" filesize="67108864" class="form-control" name="attachments[0]" accept=".jpg,.png,.pdf,.doc,.zip,.rar,image/jpeg,image/png,application/pdf,application/msword,application/x-zip,application/x-rar">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default add_more_attachments_shipping" data-max="6" type="button"><i class="fa fa-plus"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                            </div>
                        </div>

                        <?php if(isset($shipment)){ ?>
                            <div class="col-md-12" id="shipment_pv_file">
                            <?php
                                $file_html = '';
                                if(count($shipment_attachments) > 0){
                                    $file_html .= '<hr />';
                                    foreach ($shipment_attachments as $f) {
                                        $href_url = site_url(LOGISTIC_PATH.'shippings/'.$f['rel_id'].'/'.$f['file_name']).'" download';
                                                        if(!empty($f['external'])){
                                                          $href_url = $f['external_link'];
                                                        }
                                       $file_html .= '<div class="mbot15 row inline-block full-width" data-attachment-id="'. $f['id'].'">
                                      <div class="col-md-8">
                                         <a name="preview-shipment-btn" onclick="preview_shipment_btn(this); return false;" rel_id = "'. $f['rel_id']. '" id = "'.$f['id'].'" href="Javascript:void(0);" class="mbot10 mright5 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'. _l('preview_file').'"><i class="fa fa-eye"></i></a>
                                         <div class="pull-left"><i class="'. get_mime_class($f['filetype']).'"></i></div>
                                         <a href=" '. $href_url.'" target="_blank" download>'.$f['file_name'].'</a>
                                         <br />
                                         <small class="text-muted">'.$f['filetype'].'</small>
                                      </div>
                                      <div class="col-md-4 text-right">';
                                        if($f['staffid'] == get_staff_user_id() || is_admin()){
                                        $file_html .= '<a href="#" class="text-danger" onclick="delete_shipment_attachment('. $f['id'].'); return false;"><i class="fa fa-times"></i></a>';
                                        } 
                                       $file_html .= '</div></div>';
                                    }
                                    echo lg_html_entity_decode($file_html);
                                }
                             ?>
                            </div>

                            <div id="shipment_file_data"></div>
                        <?php } ?>
                    </div>


                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel_s panel-table-full">
                <div class="panel-body">

                    <?php echo form_hidden('volume_percentage_setting', get_option('lg_volume_percentage_l_w_h')); ?>

                    <h4 class="no-margin font-bold"><?php echo _l('lg_package_information'); ?></h4>
                    <hr class="hr-panel-heading" />
                    

                    <div class="row" id="shipment_list_info" >
                        <?php if(!isset($shipment)){ ?>
                            <div class="shipment_info">
                                <div class="col-md-1 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][amount]', 'lg_amount', '', 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'amount'); ?>
                                </div>

                                <div class="col-md-3 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][package_description]', 'lg_package_description', '', 'text'); ?>
                                </div>

                                <div class="col-md-1 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][weight]', 'lg_weight', '', 'number' , ['onchange' => 'calculate_shipment(); return false;'], [], '', 'weight'); ?>
                                </div>

                                <div class="col-md-1 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][length]', 'lg_length', '', 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'length'); ?>
                                </div>

                                <div class="col-md-1 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][width]', 'lg_width', '', 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'width'); ?>
                                </div>

                                <div class="col-md-1 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][height]', 'lg_height', '', 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'height'); ?>
                                </div>

                                <div class="col-md-1 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][weight_vol]', 'lg_weight_vol', '', 'number', ['onchange' => 'calculate_shipment(); return false;', 'readonly' => 'true'], [], '', 'weight_vol'); ?>
                                </div>

                                <div class="col-md-1 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][fixed_charge]', 'lg_fixed_charge', '', 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'fixed_charge'); ?>
                                </div>

                                <div class="col-md-1 pad_right_0">
                                    <?php
                                    echo render_input('package_information[0][dec_value]', 'lg_dec_value', '', 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'decvalue'); ?>
                                </div>

                                <div class="col-md-12"><hr class="mtop5"></div>

                            </div>
                        <?php }else{ ?>
                            <?php foreach($shipment->shipment_detail as $key => $detail){ ?>

                                <div class="shipment_info" id="package_row_<?php echo e($key); ?>">
                                    <div class="col-md-1 pad_right_0">
                                        <?php echo form_hidden('package_information_update['.($key).'][id]', $detail['id']); ?>
                                        <?php
                                        echo render_input('package_information_update['.($key).'][amount]', 'lg_amount', $detail['amount'], 'number' , ['onchange' => 'calculate_shipment(); return false;'], [], '', 'amount'); ?>
                                    </div>

                                    <div class="col-md-3 pad_right_0">
                                        <?php
                                        echo render_input('package_information_update['.($key).'][package_description]', 'lg_package_description', $detail['package_description'], 'text'); ?>
                                    </div>

                                    <div class="col-md-1 pad_right_0">
                                        <?php
                                        echo render_input('package_information_update['.($key).'][weight]', 'lg_weight', $detail['weight'], 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'weight'); ?>
                                    </div>

                                    <div class="col-md-1 pad_right_0">
                                        <?php
                                        echo render_input('package_information_update['.($key).'][length]', 'lg_length', $detail['length'], 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'length'); ?>
                                    </div>

                                    <div class="col-md-1 pad_right_0">
                                        <?php
                                        echo render_input('package_information_update['.($key).'][width]', 'lg_width', $detail['width'], 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'width'); ?>
                                    </div>

                                    <div class="col-md-1 pad_right_0">
                                        <?php
                                        echo render_input('package_information_update['.($key).'][height]', 'lg_height', $detail['height'], 'number', ['onchange' => 'calculate_shipment(); return false;'], [], '', 'height'); ?>
                                    </div>

                                    <div class="col-md-1 pad_right_0">
                                        <?php
                                        echo render_input('package_information_update['.($key).'][weight_vol]', 'lg_weight_vol', $detail['weight_vol'], 'number', ['onchange' => 'calculate_shipment(); return false;', 'readonly' => 'true'], [], '', 'weight_vol'); ?>
                                    </div>

                                    <div class="col-md-1 pad_right_0">
                                        <?php
                                        echo render_input('package_information_update['.($key).'][fixed_charge]', 'lg_fixed_charge', $detail['fixed_charge'], 'number', ['onchange' => 'calculate_shipment(); return false;'] , [], '', 'fixed_charge'); ?>
                                    </div>

                                    <div class="col-md-1 pad_right_0">
                                        <?php
                                        echo render_input('package_information_update['.($key).'][dec_value]', 'lg_dec_value', $detail['dec_value'], 'number', ['onchange' => 'calculate_shipment(); return false;'] , [], '', 'decvalue'); ?>
                                    </div>

                                    <div class="col-md-1">
                                        <a onclick="remove_shipment(<?php echo e($key); ?>, this);" class="btn btn-danger mtop25 pull-right" data-shipment_id="<?php echo e($detail['id']); ?>"><i class="fa fa-trash"></i></a>
                                    </div>

                                    <div class="col-md-12"><hr class="mtop5"></div>

                                </div>

                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div id="remove_shipment_ids">
                            
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <a  class="btn btn-primary pull-left" onclick="add_shipment();"><i class="fa fa-plus"></i><?php echo ' '._l('add_box_or_packages'); ?></a>
                        </div>
                    </div>

                    <div class="row mtop15">
                        <hr>
                        <div class="col-md-4 pad_right_0">
                            <span class="bold text-uppercase"><?php echo _l('lg_totals'); ?></span>
                        </div>

                        <div class="col-md-4 pad_right_0">
                            <span id="total_weight"></span>
                        </div>

                        <div class="col-md-1 pad_right_0">
                            <span id="weight_vol_total"></span>
                        </div>

                        <div class="col-md-1 pad_right_0">
                            <span id="fixed_charge_total"></span>
                        </div>

                        <div class="col-md-1 pad_right_0">
                            <span id="decvalue_total"></span>
                        </div>

                    </div>


                    <div class="row mtop15 mbot15">
                        <hr>
                        <div class="col-md-5 pad_right_0 text-right">
                            <span class="bold text-uppercase"><?php echo _l('lg_subtotal'); ?></span>
                            <?php echo form_hidden('subtotal', 0); ?>
                        </div>
                        <div class="col-md-7 text-right">
                            <span id="subtotal_shipment" class="text-success"></span>
                        </div>

                    </div>

                    <h4 class="no-margin font-bold"><?php echo _l('lg_rate_and_tax_information'); ?></h4>
                    <hr class="hr-panel-heading" /> 

                    <div class="row mtop15 mbot15">
                        <div class="col-md-2 pad_right_0">
                            <label for="price_kg"><?php echo _l('lg_price').' '.get_option('lg_weight_units'); ?></label>
                            <?php $price_kg = (isset($shipment) ? $shipment->price_kg : get_option('lg_weight_value'));
                            echo render_input('price_kg', '', $price_kg, 'number', ['onchange' => 'calculate_shipment(1); return false;', 'step' => 'any' ,'readonly' => 'true'], [], '', 'price_kg'); ?>

                            <?php $weight_value_setting = (isset($shipment) ? $shipment->weight_value_setting : get_option('lg_weight_value')); ?>
                            <?php echo form_hidden('weight_value_setting', $weight_value_setting); ?>

                            <?php $weight_units_setting = (isset($shipment) ? $shipment->weight_units_setting : get_option('lg_weight_units')); ?>
                            <?php echo form_hidden('weight_units_setting', $weight_units_setting); ?>
                        </div>

                        <div class="col-md-2 pad_right_0 hide">
                            <label for="discount_percent"><?php echo _l('lg_discount').' %'; ?></label>
                            <?php $discount_percent = (isset($shipment) ? $shipment->discount_percent : '');
                            echo render_input('discount_percent', '', $discount_percent, 'number', ['onchange' => 'calculate_shipment(); return false;' ,'readonly' => 'true'], [], 'mbot5 hide', 'discount_percent'); ?>

                            <label id="discount_value"></label>
                            <?php echo form_hidden('discount', ''); ?>
                        </div>

                        <div class="col-md-2 pad_right_0 hide">
                            <label for="value_assured"><?php echo _l('lg_value_assured'); ?></label>
                            <?php $value_assured = (isset($shipment) ? $shipment->value_assured : '');
                            echo render_input('value_assured', '', $value_assured, 'number', ['onchange' => 'calculate_shipment(); return false;' ,'readonly' => 'true'], [], 'hide', 'value_assured'); ?>
                        </div>


                        <div class="col-md-2 pad_right_0">
                            <label for="shipping_insurance_percent"><?php echo _l('lg_shipping_insurance').' %'; ?></label>
                            <?php $shipping_insurance_percent = (isset($shipment) ? $shipment->shipping_insurance_percent : get_option('lg_shipping_insurance_percent'));
                            echo render_input('shipping_insurance_percent', '', $shipping_insurance_percent, 'number', ['onchange' => 'calculate_shipment(); return false;' ,'readonly' => 'true'], [], 'mbot5', 'shipping_insurance_percent'); ?>

                            <label id="shipping_insurance_value"></label>
                            <?php echo form_hidden('shipping_insurance', ''); ?>
                        </div>

                        <div class="col-md-2 pad_right_0">
                            <label for="custom_duties_percent"><?php echo _l('lg_custom_duties').' %'; ?></label>
                            <?php $custom_duties_percent = (isset($shipment) ? $shipment->custom_duties_percent : get_option('lg_customs_duties'));
                            echo render_input('custom_duties_percent', '', $custom_duties_percent, 'number', ['onchange' => 'calculate_shipment(); return false;' ,'readonly' => 'true'], [], 'mbot5', 'custom_duties_percent'); ?>

                            <label id="custom_duties_value"></label>
                            <?php echo form_hidden('custom_duties', ''); ?>
                        </div>

                        <div class="col-md-2 pad_right_0">
                            <?php $minium_cost_to_apply_the_tax_setting = (isset($shipment) ? $shipment->minium_cost_to_apply_the_tax_setting : get_option('lg_minium_cost_to_apply_the_tax'));
                            echo form_hidden('minium_cost_to_apply_the_tax_setting', $minium_cost_to_apply_the_tax_setting ) ?>

                            <label for="tax_percent"><?php echo _l('lg_tax').' %'; ?></label>
                            <?php $tax_percent = (isset($shipment) ? $shipment->tax_percent : get_option('lg_tax_percent'));
                            echo render_input('tax_percent', '', $tax_percent, 'number', ['onchange' => 'calculate_shipment(); return false;' ,'readonly' => 'true'], [], 'mbot5', 'tax_percent'); ?>

                            <label id="tax_value"></label>
                            <?php echo form_hidden('tax', ''); ?>
                        </div>

                        <div class="col-md-2 pad_right_0">
                            <?php $minium_cost_to_apply_declared_tax_setting = (isset($shipment) ? $shipment->minium_cost_to_apply_declared_tax_setting : get_option('lg_minium_cost_to_apply_declared_tax'));
                            echo form_hidden('minium_cost_to_apply_declared_tax_setting', $minium_cost_to_apply_declared_tax_setting) ?>

                            <label for="declared_value_percent"><?php echo _l('lg_declared_value').' %'; ?></label>
                            <?php $declared_value_percent = (isset($shipment) ? $shipment->declared_value_percent : get_option('lg_tax_declared'));
                            echo render_input('declared_value_percent', '', $declared_value_percent, 'number', ['onchange' => 'calculate_shipment(); return false;' ,'readonly' => 'true'], [], 'mbot5', 'declared_value_percent'); ?>

                            <label id="declared_value_label"></label>
                            <?php echo form_hidden('declared_value', ''); ?>
                        </div>


                        <div class="col-md-2 pad_right_0 hide">
                            <label for="reissue"><?php echo _l('lg_reissue'); ?></label>
                            <?php $reissue = (isset($shipment) ? $shipment->reissue : '');
                            echo render_input('reissue', '', $reissue, 'number', ['onchange' => 'calculate_shipment(); return false;' ,'readonly' => 'true'], [], '', 'reissue'); ?>
                        </div>

                        

                    </div>

                    <div class="row">
                        <div class="col-md-2 pad_right_0">
                            <label for="fixed_charge"><?php echo _l('lg_fixed_charge'); ?></label><br>
                            <?php $fixed_charge = (isset($shipment) ? $shipment->fixed_charge : ''); ?>

                            <label id="fixed_charge_label"></label>
                            <?php echo form_hidden('fixed_charge', $fixed_charge); ?>
                        </div>

                        <div class="col-md-2 pad_right_0 label-warning">
                            <label for="total" class="text-uppercase"><?php echo _l('lg_total'); ?></label><br>
                            <?php $total = (isset($shipment) ? $shipment->total : ''); ?>

                            <label id="total_label" class="text-success"></label>
                            <?php echo form_hidden('total', $total); ?>
                        </div>
                    </div>


                    <div class="row text-right">
                        <div class="col-md-12">
                            <hr class="hr-panel-heading" />
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit') ?></button>
                        </div>
                    </div>

                </div>
            </div>
        </div>


            <?php echo form_close(); ?>

</div>