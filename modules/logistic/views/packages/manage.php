<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s panel-table-full">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold"><i class="fa fa-box" aria-hidden="true"></i> <?php echo lg_html_entity_decode($title); ?></h4>
                        <hr class="hr-panel-heading" />


                        <div class="row mbot15"> 
                            <div class="col-md-12"> 

                                <?php if(has_permission('lg_packages', '', 'create')){ ?>
                                    <a href="<?php echo admin_url('logistic/register_package/0'); ?>" class="btn btn-primary pull-left mright5"><?php echo _l('lg_register_package'); ?></a>
                                    <a href="<?php echo admin_url('logistic/register_package/1'); ?>" class="btn btn-primary pull-left mright5"><?php echo _l('lg_register_multiple_package'); ?></a>
                                    
                                <?php } ?>
                                    
                            </div>
                        </div>

                        <div class="row mbot15"> 
                            <div class="col-md-3">
                                           
                                        <?php 
                                       

                                        echo render_select('status[]',$statuses,array('id','style_name'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('lg_filter_by_status'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false); ?>
                        
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
                                _l('lg_time'),
                                _l('lg_customer'),
                                _l('lg_destination'),
                                _l('lg_shipping_company'),
                                _l('lg_store_supplier'),
                                _l('lg_tracking_purchase'),
                                _l('lg_status'),
                                _l('lg_driver'),
                                _l('lg_total_cost'),
                                _l('lg_invoice_status'),
                                _l('lg_action'),
                              ]);

                           echo render_datatable($table_data, 'packages', [],['id' => 'table-packages']); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="send_mail_modal_container"></div>
<?php $this->load->view('packages/assign_driver_modal'); ?>

<?php init_tail(); ?>
<?php require 'modules/logistic/assets/js/packages/manage_js.php';?>