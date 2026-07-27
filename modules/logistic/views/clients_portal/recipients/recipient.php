<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-invoices">
    <?php echo e($title); ?>

</h4>

<div class="panel_s">
    <div class="panel-body">
  
         <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'alert-form')); ?>
         <div class="row">
            
               <?php echo form_hidden('client_id', $client_id); ?>
               <?php echo form_hidden('recipient_id',  (isset($recipient) ? $recipient->id : '')); ?>




            <div class="col-md-6">
                <label for="first_name"><span class="text-danger">* </span><?php echo _l('lg_first_name'); ?></label>
                <?php $first_name = (isset($recipient) ? $recipient->first_name : '');
                echo render_input('first_name', '', $first_name, 'text', ['required' => 'true']); ?>
            </div>


            <div class="col-md-6">
                <label for="last_name"><span class="text-danger">* </span><?php echo _l('lg_last_name'); ?></label>
                <?php $last_name = (isset($recipient) ? $recipient->last_name : '');
                echo render_input('last_name', '', $last_name, 'text', ['required' => 'true']); ?>
            </div>

            <div class="col-md-6">
                <label for="phone"><span class="text-danger">* </span><?php echo _l('lg_phone'); ?></label>
                <?php $phone = (isset($recipient) ? $recipient->phone : '');
                echo render_input('phone', '', $phone, 'text', ['required' => 'true']); ?>
            </div>

            <div class="col-md-6">
                <label for="email"><span class="text-danger">* </span><?php echo _l('lg_email'); ?></label>
                <?php $email = (isset($recipient) ? $recipient->email : '');
                echo render_input('email', '', $email, 'text', ['required' => 'true']); ?>
            </div>


            <div class="col-md-12">
                <h4><?php echo _l('lg_address'); ?></h4>
                <hr>
                <div class="row" id="address_list" >
                    <?php if(!isset($recipient)){ ?>
                        <div class="address_info">
                            <div class="col-md-4">
                                <label for="name"><span class="text-danger">* </span><?php echo _l('lg_country'); ?></label>        
                                <?php
                                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' => '0', 'onchange' => 'country_change(this); return false;' ];
                                     $selected = '';

                                     echo render_select('address[0][country]', $countries, ['id', 'country_name'], '', $selected, $s_attrs, [], '', 'country_select');
                                     ?>  
                            </div>   

                            <div class="col-md-4">
                                <label for="name"><span class="text-danger">* </span><?php echo _l('lg_state'); ?></label>         
                                <?php
                                    $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' => '0', 'onchange' => 'state_change(this); return false;' ];
                                     echo render_select('address[0][state]', $states, ['id', 'state_name'], '', $selected, $s_attrs, [], '','state_select');
                                     ?>
                            </div>

                            <div class="col-md-4">
                                <label for="name"><span class="text-danger">* </span><?php echo _l('lg_city'); ?></label>       
                                <?php
                                    $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' => '0'];
                                     echo render_select('address[0][city]', $cities, ['id', 'city_name'], '', $selected, $s_attrs, [], '','city_select');
                                     ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('address[0][zip_code]', 'lg_zip_code', '', 'text'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_input('address[0][address]', 'lg_address', '', 'text'); ?>
                            </div>

                            <div class="col-md-2"></div>
                        </div>
                    <?php }else{ ?>
                        <?php foreach($recipient->address as $key => $address){ ?>
                             <div class="address_info">
                                <div class="col-md-4">
                                    <?php echo form_hidden('address_update['.($key).'][id]', $address['id']); ?>
                                    <label for="name"><span class="text-danger">* </span><?php echo _l('lg_country'); ?></label>        
                                    <?php
                                         $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' => $key, 'onchange' => 'country_change(this); return false;'];
                                         $selected = $address['country'];
                                        

                                         echo render_select('address_update['.($key).'][country]', $countries, ['id', 'country_name'], '', $selected, $s_attrs, [], '', 'country_select');
                                         ?>  
                                </div>   

                                <div class="col-md-4">
                                    <label for="name"><span class="text-danger">* </span><?php echo _l('lg_state'); ?></label>         
                                    <?php
                                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' => $key, 'onchange' => 'state_change(this); return false;'];
                                        $selected = $address['state'];
                                         $states = lg_get_state_of_country($address['country']);
                                         echo render_select('address_update['.($key).'][state]', $states, ['id', 'state_name'], '', $selected, $s_attrs, [], '','state_select');
                                         ?>
                                </div>

                                <div class="col-md-4">
                                    <label for="name"><span class="text-danger">* </span><?php echo _l('lg_city'); ?></label>       
                                    <?php
                                        $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' =>  $key];
                                        $selected = $address['city'];
                                        $cities = lg_get_city_of_state($address['country'], $address['state']);
                                         echo render_select('address_update['.($key).'][city]', $cities, ['id', 'city_name'], '', $selected, $s_attrs, [], '','city_select');
                                         ?>
                                </div>
                                <div class="col-md-4">
                                    <?php echo render_input('address_update['.($key).'][zip_code]', 'lg_zip_code', $address['zip_code'], 'text'); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('address_update['.($key).'][address]', 'lg_address', $address['address'], 'text'); ?>
                                </div>

                                <div class="col-md-2">
                                    <a onclick="remove_address(<?php echo e($key); ?>, this);" class="btn btn-danger mtop25 pull-right" data-address_id="<?php echo e($address['id']); ?>"><i class="fa fa-trash"></i></a>
                                </div>

                                <div class="col-md-12"><hr></div>
                            </div>

                        <?php } ?>
                    <?php } ?>
                </div>

                <div id="remove_address_ids">
                                    
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <a  class="btn btn-primary pull-left" onclick="add_address();"><i class="fa fa-plus"></i><?php echo ' '._l('lg_add_address'); ?></a>
                    </div>
                </div>

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

