<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">


            <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'shipment_tracking_package-form')); ?>

            <?php $package_id = $package->id;
            echo form_hidden('package_id', $package_id); ?>

            <div class="col-md-12">
                <div class="panel_s panel-table-full">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold"><?php echo '<span class="text-danger">'._l('lg_delivery_shipment_str').'</span> | '.$package->shipping_prefix.$package->number_code; ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="col-md-6">
                            <label for="delivery_date"><span class="text-danger">* </span><?php echo _l('lg_delivery_time'); ?></label>
                            <?php echo render_datetime_input('delivery_date', '', _dt(date('Y-m-d H:i:s')), ['required' => 'true']); ?>
                        </div>

                        <div class="col-md-6">
                            <label for="delivered_by"><span class="text-danger">* </span><?php echo _l('lg_driver'); ?></label>
                            <?php $assign_driver = '';
                            echo render_select('delivered_by', $drivers, array('staffid', 'full_name'), '', $assign_driver, ['required' => 'true']); ?>
                        </div>

                        <div class="col-md-6">
                            <label for="receive_by"><span class="text-danger">* </span><?php echo _l('lg_receive_by'); ?></label>
                            <?php echo render_input('receive_by', '', '', 'text', ['required' => 'true']); ?>
                        </div>

                        <div class="col-md-6">
                            <label for="file"><span class="text-danger">* </span><?php echo _l('lg_attachment'); ?></label>
                            <?php echo render_input('file', '', '', 'file', ['required' => 'true']); ?>
                        </div>

                        <div id="sign_div" class="col-md-6 text-center mtop15">
                            <?php $this->load->view('packages/package_shipment_sign'); ?>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_textarea('note', 'lg_note'); ?>
                        </div>

                    </div>
                </div>
            </div>


            <div class="btn-bottom-toolbar text-right">
                <button type="button" onclick="submit_shipment();" class="btn btn-primary"><?php echo _l('submit') ?></button>
            </div>
            <?php echo form_close(); ?>


        </div>
    </div>
</div>

    
<div class="modal fade" id="add_action" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <div class="modal-content">
         
        <div class="modal-body">
         <p class="bold" id="signatureLabel"><?php echo _l('signature'); ?></p>


            <div id="sign_pad" >
               <div class="signature-pad--body">
                 <canvas id="signature" height="130" width="550"></canvas>
               </div>
               <input type="text" class="ip_style" tabindex="-1" name="signature" id="signatureInput">

               <div class="dispay-block">
                 <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" onclick="signature_clear();"><?php echo _l('clear'); ?></button>
               
               </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
           <button id="sign_button" onclick="sign_request(<?php echo lg_html_entity_decode($package->id); ?>);" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" class="btn btn-success"><?php echo _l('e_signature_sign'); ?></button>
          </div>

      </div><!-- /.modal-content -->
   </div><!-- /.modal-dialog -->
</div><!-- /.modal -->            

<?php init_tail(); ?>
<?php require 'modules/logistic/assets/js/packages/delivery_shipment_js.php';?>