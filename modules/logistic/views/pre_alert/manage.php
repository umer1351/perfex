<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s panel-table-full">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold"><i class="fa fa-list" aria-hidden="true"></i> <?php echo lg_html_entity_decode($title); ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="row mbot15"> 
                            <div class="col-md-3">
                                           
                                        <?php 
                                        $statuses = [
                                            ['id' => 1, 'name' => _l('lg_pending')],
                                            ['id' => 2, 'name' => _l('lg_approved')],
                                        ];

                                        echo render_select('status[]',$statuses,array('id','name'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('lg_filter_by_status'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false); ?>
                        
                                    </div>

                                    <div class="col-md-3">
                                           
                                        <?php 
                                       

                                        echo render_select('clients[]',$clients,array('userid','company'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('lg_filter_by_customer'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false); ?>
                        
                                    </div>

                                    <div class="col-md-2">
                                        <?php echo render_date_input('from_date','','', ['placeholder' => _l('lg_from_date')]); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <?php echo render_date_input('to_date','','', ['placeholder' => _l('lg_to_date')]); ?>
                                    </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php
                        $table_data = [];
                                

                               $table_data = array_merge($table_data, [
                                _l('lg_tracking'),
                                _l('lg_date'),
                                _l('lg_customer'),
                                _l('lg_shipping_company'),
                                _l('lg_store_supplier'),
                                _l('lg_package_description'),
                                _l('lg_delivery_date'),
                                _l('lg_purchase_price'),
                                _l('lg_status'),
                                _l('lg_action'),
                                _l('lg_attach_invoice'),
                              ]);

                           echo render_datatable($table_data, 'pre_alert', [],['id' => 'table-pre_alert']); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php init_tail(); ?>
<?php require 'modules/logistic/assets/js/pre_alert/manage_js.php';?>