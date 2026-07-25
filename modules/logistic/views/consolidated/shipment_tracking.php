<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">


            <?php echo form_open($this->uri->uri_string(),array('id'=>'shipment_tracking_consolidation-form')); ?>

            <?php $consolidation_id = $consolidation->id;
            echo form_hidden('rel_id', $consolidation_id); ?>

            <div class="col-md-12">
                    <div class="panel_s panel-table-full">
                        <div class="panel-body">
                            <h4 class="no-margin font-bold"><?php echo '<span class="text-danger">'._l('lg_shipment_tracking').'</span> | '.$consolidation->shipping_prefix.$consolidation->number_code; ?></h4>
                            <hr class="hr-panel-heading" />


                            <div class="col-md-6">
                                <?php
                                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string')];
                                     $selected = '';

                                     echo render_select('new_location', $countries, ['id', 'country_name'], 'lg_new_location', $selected, $s_attrs);
                                     ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_input('city_or_address', 'lg_city_or_address', ''); ?>
                            </div>


                            <div class="col-md-6">
                                <?php
                                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string')];
                                     $selected = '';

                                     echo render_select('office', $office_groups, ['id', 'office_name'], 'lg_office', $selected, $s_attrs);
                                     ?>
                            </div>

                            <div class="col-md-6">
                                <?php
                                     $s_attrs  = ['data-none-selected-text' => _l('system_default_string')];
                                     $selected = '';

                                     echo render_select('delivery_status', $statuses, ['id', 'style_name'], 'lg_delivery_status', $selected, $s_attrs);
                                     ?>
                            </div>

                            <div class="col-md-6">
                                <?php echo render_datetime_input('time_update', 'lg_ship_time', _dt(date('Y-m-d H:i:s'))); ?>
                            </div>

                            <div class="col-md-6"> 
                                <?php echo render_textarea('remark', 'lg_message', ''); ?>
                            </div>

                        </div>
                    </div>
            </div>

            <div class="btn-bottom-toolbar text-right">
                <button type="submit" class="btn btn-primary"><?php echo _l('submit') ?></button>
            </div>
            <?php echo form_close(); ?>


        </div>
    </div>
</div>            

<?php init_tail(); ?>