<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="general_shipments" class="hide">
   <div class="row">
      
   <div class="clearfix"></div>
</div>
<table class="table table-general_shipments scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('lg_tracking'); ?></th>
         <th><?php echo _l('lg_time'); ?></th>
         <th><?php echo _l('lg_customer'); ?></th>
         <th><?php echo _l('lg_destination'); ?></th>
         <th><?php echo _l('lg_status'); ?></th>
         <th><?php echo _l('lg_weight'); ?></th>
         <th><?php echo _l('lg_subtotal'); ?></th>
         <th><?php echo _l('lg_discount'); ?></th>
         <th><?php echo _l('lg_shipping_insurance'); ?></th>
         <th><?php echo _l('lg_custom_duties'); ?></th>
         <th><?php echo _l('lg_tax'); ?></th>
         <th><?php echo _l('lg_declared_value'); ?></th>
         <th><?php echo _l('lg_total'); ?></th>
      </tr>
   </thead>
   <tbody></tbody>
   <tfoot>
      <tr>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td class="total"></td>
      </tr>
   </tfoot>
</table>
</div>
