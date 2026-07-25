<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-invoices">
    <?php echo e($title); ?>

</h4>

<div class="panel_s">
    <div class="panel-body">
  
         <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'alert-form')); ?>
         <div class="row">
            
               <?php echo form_hidden('client_id', $client_id); ?>
               <?php echo form_hidden('currency', $currency_id); ?>

            <div class="col-md-6">   
                <?php $delivery_date = (isset($pre_alert) ? _dt($pre_alert->delivery_date) : _dt(date('Y-m-d H:i:s')));
                 echo render_datetime_input('delivery_date', 'lg_delivery_date', $delivery_date); ?>

            </div>


            <div class="col-md-6">
                <div class="form-group ">
                    <label for="courrier_company"><?php echo _l('lg_courrier_company'); ?></label>
                    <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                        name="courier_company" id="courier_company" class="form-control selectpicker">
                        <option value=""></option>
                        <?php foreach ($shipping_companies as $company) { ?>
                        <option value="<?php echo e($company['id']); ?>" <?php if(isset($pre_alert) && $pre_alert->courier_company == $company['id']){ echo 'selected'; } ?>>
                            <?php echo e($company['shipping_company_name']); ?>
                        </option>
                        <?php } ?>
                    </select>
                    
                </div>
            </div>


            <div class="col-md-6">
                <label for="tracking_purchase"><span class="text-danger">* </span><?php echo _l('lg_tracking_purchase'); ?></label>
                <?php $tracking_purchase = (isset($pre_alert) ? $pre_alert->tracking_purchase : '');
                echo render_input('tracking_purchase', '', $tracking_purchase, 'text', ['required' => 'true']); ?>
            </div>


            <?php if(!isset($pre_alert)){ ?>
                <div class="col-md-6">
                    <?php echo render_input('file', 'lg_attach_invoice', '', 'file', ["accept"=> "image/*,.pdf" ]); ?>
                </div>
            <?php }else{ ?>

               
                     <div class="col-md-6 form-group" id="pre_alert_pv_file">
                        <?php
                            $file_html = '';
                            if(count($pre_alert_attachment) > 0){
                                $file_html .= '';
                                foreach ($pre_alert_attachment as $f) {
                                    $href_url = site_url(LOGISTIC_PATH.'pre_alert/'.$f['rel_id'].'/'.$f['file_name']).'" download';
                                                    if(!empty($f['external'])){
                                                      $href_url = $f['external_link'];
                                                    }
                                   $file_html .= '<div class="mbot15 mtop15 row inline-block full-width" data-attachment-id="'. $f['id'].'">
                                  <div class="col-md-8">
                                     <a name="preview-pre_alert-btn" onclick="preview_pre_alert_btn(this); return false;" rel_id = "'. $f['rel_id']. '" id = "'.$f['id'].'" href="Javascript:void(0);" class="mbot10 mright5 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'. _l('preview_file').'"><i class="fa fa-eye"></i></a>
                                     <div class="pull-left"><i class="'. get_mime_class($f['filetype']).'"></i></div>
                                     <a href=" '. $href_url.'" target="_blank" download>'.$f['file_name'].'</a>
                                     <br />
                                     <small class="text-muted">'.$f['filetype'].'</small>
                                  </div>
                                  <div class="col-md-4 text-right">';
                                    if($f['contact_id'] == get_contact_user_id() || is_primary_contact(get_contact_user_id())){
                                    $file_html .= '<a href="#" class="text-danger" onclick="delete_pre_alert_attachment('.$pre_alert->id.', '. $f['id'].'); return false;"><i class="fa fa-times"></i></a>';
                                    } 
                                   $file_html .= '</div></div>';
                                }
                                echo lg_html_entity_decode($file_html);
                            }else{
                                echo render_input('file', 'lg_attach_invoice', '', 'file', ["accept"=> "image/*,.pdf" ]);
                            }
                         ?>
                        </div>

                        <div id="pre_alert_file_data"></div>
                
            <?php } ?>
            <div class="col-md-6">
                <label for="store_supplier"><span class="text-danger">* </span><?php echo _l('lg_store_supplier'); ?></label>
                <?php $store_supplier = (isset($pre_alert) ? $pre_alert->store_supplier : '');
                echo render_input('store_supplier', '', $store_supplier, 'text', ['required' => 'true']); ?>
            </div>

            <div class="col-md-6">
                <label for="purchase_price"><span class="text-danger">* </span><?php echo _l('lg_purchase_price'); ?></label>
                <?php $purchase_price = (isset($pre_alert) ? $pre_alert->purchase_price : '');
                echo render_input('purchase_price', '', $purchase_price, 'number', ['required' => 'true']); ?>
            </div>

            <div class="col-md-12">
                <?php $package_description = (isset($pre_alert) ? $pre_alert->package_description : '');
                echo render_textarea('package_description', 'lg_package_description', $package_description); ?>
            </div>


        </div>

        <hr class="hr-panel-heading" />
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
            </div>
        </div>


        <?php echo form_close(); ?>
    </div>
</div>

