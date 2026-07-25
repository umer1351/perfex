<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="panel_s panel-table-full">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold"><i class="fa fa-user" aria-hidden="true"></i> <?php echo lg_html_entity_decode($title); ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="row mbot15"> 
                         
                            <div class="col-md-3">
                                   
                                <?php 
                               

                                echo render_select('clients[]',$clients,array('userid','company'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('lg_filter_by_customer'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false); ?>
                
                            </div>

                        </div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php
                        $table_data = [];
                                

                               $table_data = array_merge($table_data, [
                                _l('lg_customer'),
                                _l('lg_recipient'),
                                _l('lg_email'),
                                _l('lg_phone'),
                              ]);

                           echo render_datatable($table_data, 'recipients', [],['id' => 'table-recipients']); ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php init_tail(); ?>
<?php require 'modules/logistic/assets/js/recipients/manage_js.php';?>