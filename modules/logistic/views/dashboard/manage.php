<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
?>
<div id="wrapper">
  <div class="content">
      <div class="panel_s">
        <div class="panel-body">

          <div class="row">
            <div class="col-md-12">
              <h4 class=""><?php echo lg_html_entity_decode($title); ?></h4>
              <hr class="hr-panel-heading" />
            </div>
          </div>

          <?php
          $total_locker_packages = total_rows(db_prefix().'lg_packages');
          $total_shipping = total_rows(db_prefix().'lg_shippings', '(shipping_type IS NULL or shipping_type = "shipping")');

          $total_pickup = total_rows(db_prefix().'lg_shippings', 'shipping_type = "pickup"');

          $total_consolidated = total_rows(db_prefix().'lg_consolidated');
         
           ?>


          <div class="row">

           <div class="quick-stats-invoices col-xs-12 col-md-3 col-sm-6 mtop15">
             <div class="top_stats_wrapper minheight85">
                 <a class="text-success mbot15">
                 <p class="text-uppercase mtop5 minheight35"><i class="hidden-sm fa fa-truck"></i> <?php echo _l('lg_locker_packages'); ?>
                 </p>
                    <span class="pull-right bold no-mtop fontsize24"><?php echo lg_html_entity_decode($total_locker_packages) ?></span>
                 </a>
                 <div class="clearfix"></div>
                 <div class="progress no-margin progress-bar-mini">
                    <?php 
                    $percentage = 100;
                    
                    ?>
                    <div class="progress-bar progress-bar-success no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo lg_html_entity_decode($total_locker_packages); ?>" aria-valuemin="0" aria-valuemax="<?php echo lg_html_entity_decode($total_locker_packages); ?>" style=" width: <?php echo lg_html_entity_decode($percentage); ?>%" data-percent="<?php echo lg_html_entity_decode($percentage); ?>%">
                    </div>
                 </div>
              </div>
           </div>


           <div class="quick-stats-invoices col-xs-12 col-md-3 col-sm-6 mtop15">
             <div class="top_stats_wrapper minheight85">
                 <a class="text mbot15">
                 <p class="text-uppercase mtop5 minheight35"><i class="hidden-sm fa fa-truck"></i> <?php echo _l('lg_shippings'); ?>
                 </p>
                    <span class="pull-right bold no-mtop fontsize24"><?php echo lg_html_entity_decode($total_shipping) ?></span>
                 </a>
                 <div class="clearfix"></div>
                 <div class="progress no-margin progress-bar-mini">
                    <?php 
                    $percentage = 100;
                    
                    ?>
                    <div class="progress-bar progress-bar no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo lg_html_entity_decode($total_shipping); ?>" aria-valuemin="0" aria-valuemax="<?php echo lg_html_entity_decode($total_shipping); ?>" style=" width: <?php echo lg_html_entity_decode($percentage); ?>%" data-percent="<?php echo lg_html_entity_decode($percentage); ?>%">
                    </div>
                 </div>
              </div>
           </div>

           <div class="quick-stats-invoices col-xs-12 col-md-3 col-sm-6 mtop15">
             <div class="top_stats_wrapper minheight85">
                 <a class="text-warning mbot15">
                 <p class="text-uppercase mtop5 minheight35"><i class="hidden-sm fa-brands fa-slideshare menu-icon"></i> <?php echo _l('lg_pickup'); ?>
                 </p>
                    <span class="pull-right bold no-mtop fontsize24"><?php echo lg_html_entity_decode($total_pickup); ?></span>
                 </a>
                 <div class="clearfix"></div>
                 <div class="progress no-margin progress-bar-mini">
                   <?php 
                    $percentage = 100;
                    
                    ?>
                    <div class="progress-bar progress-bar-warning no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo lg_html_entity_decode($total_pickup); ?>" aria-valuemin="0" aria-valuemax="<?php echo lg_html_entity_decode($total_pickup); ?>" style="width: <?php echo lg_html_entity_decode($percentage); ?>%" data-percent=" <?php echo lg_html_entity_decode($percentage); ?>%">
                    </div>
                 </div>
              </div>
           </div> 
          
            <div class="quick-stats-invoices col-xs-12 col-md-3 col-sm-6 mtop15">
               <div class="top_stats_wrapper minheight85">
                   <a class="text-info mbot15">
                   <p class="text-uppercase mtop5 minheight35"><i class="hidden-sm fa fa-snowflake"></i> <?php echo _l('lg_consolidated'); ?>
                   </p>
                      <span class="pull-right bold no-mtop fontsize24"><?php echo lg_html_entity_decode($total_consolidated); ?></span>
                   </a>
                   <div class="clearfix"></div>
                   <div class="progress no-margin progress-bar-mini">
                    <?php 
                      $percentage = 100;
                      
                      ?>
                      <div class="progress-bar progress-bar-info no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo lg_html_entity_decode($total_consolidated) ?>" aria-valuemin="0" aria-valuemax="<?php echo lg_html_entity_decode($total_consolidated); ?>" style="width: <?php echo lg_html_entity_decode($percentage); ?>%" data-percent=" <?php echo lg_html_entity_decode($percentage); ?>%">
                      </div>
                   </div>
                </div>
             </div>
           </div>
            
            <div class="row mtop15">
              <div class="col-md-6 mtop15">
                <div id="package_by_status" class="minwidth310">
                </div>
                <br>
              </div>

              <div class="col-md-6 mtop15">
                <div id="package_sales_graph" class="minwidth310">
                </div>
                <br>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                
                <hr class="hr-panel-heading" />
              </div>
            </div>

            <div class="row mtop15">
              <div class="col-md-6 mtop15">
                <div id="shipping_by_status" class="minwidth310">
                </div>
                <br>
              </div>

              <div class="col-md-6 mtop15">
                <div id="shipping_sales_graph" class="minwidth310">
                </div>
                <br>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                
                <hr class="hr-panel-heading" />
              </div>
            </div>

            <div class="row mtop15">
              <div class="col-md-6 mtop15">
                <div id="consolidated_by_status" class="minwidth310">
                </div>
                <br>
              </div>

              <div class="col-md-6 mtop15">
                <div id="consolidated_sales_graph" class="minwidth310">
                </div>
                <br>
              </div>
            </div>

         
        </div>
      </div>
  </div>
</div>

<?php init_tail(); ?>
<?php require('modules/logistic/assets/js/dashboard/dashboard_js.php'); ?>