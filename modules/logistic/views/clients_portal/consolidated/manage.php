<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-invoices">
    <?php echo e($title); ?>

</h4>
<div class="panel_s">
    <div class="panel-body">

        <table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
            <thead>
                <tr>
                    <th ><?php echo _l('lg_tracking'); ?></th>
                    <th ><?php echo _l('lg_date'); ?></th>
                    <th ><?php echo _l('lg_package_type'); ?></th>
                    <th ><?php echo _l('lg_package_details'); ?></th>            
                    <th ><?php echo _l('lg_recipient'); ?></th>
                    <th ><?php echo _l('lg_origin'); ?></th>
                    <th ><?php echo _l('lg_destination'); ?></th>
                    <th ><?php echo _l('lg_payment'); ?></th>
                    <th ><?php echo _l('lg_status'); ?></th>
                    <th ><?php echo _l('lg_total_cost'); ?></th>
                    <th ><?php echo _l('lg_action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consolidated as $consolidation) { ?>
                <tr>
                    <td><a href="<?php echo site_url('logistic/client/consolidated_detail/'.$consolidation['id']) ?>"><?php echo e($consolidation['shipping_prefix'].$consolidation['number_code']); ?></a></td>
                    <td><?php echo _dt($consolidation['created_at']); ?></td>
                    <td><?php echo _l('lg_'.$consolidation['rel_type']); ?></td>
                    <td>
                        <?php
                        $rel_str = '';
                        if($consolidation['rel_type'] == 'locker_packages'){
                            $url = site_url('logistic/client/package_detail/');

                        }else if($consolidation['rel_type'] == 'shipping'){
                            $url = site_url('logistic/client/shipping_detail/');
                        }

                        if($consolidation['rel_id'] != ''){
                            $rel_arr = explode(',', $consolidation['rel_id']);
                            foreach($rel_arr as $key => $rel_id){
                                if($key == 0){
                                    $rel_str .= '<a href="'.$url.$rel_id.'">'.lg_get_tracking_number_by_type($consolidation['rel_type'], $rel_id).'</a>';
                                }else{
                                    $rel_str .= '<br><a href="'.$url.$rel_id.'">'.lg_get_tracking_number_by_type($consolidation['rel_type'], $rel_id).'</a>';
                                }
                            }
                        }

                        echo  lg_html_entity_decode($rel_str);

                        ?>

                    </td>
                    <td><?php echo lg_get_recipient_name($consolidation['recipient_id']); ?></td>
                    <td><?php echo lg_get_customer_address_str($consolidation['customer_address']); ?></td>
                    <td><?php echo lg_get_recipient_address_str($consolidation['recipient_address_id']); ?></td>
                    <td><?php echo lg_get_payment_term_str($consolidation['payment_term_id']); ?></td>
                    <td><?php echo format_lg_package_status($consolidation['delivery_status']); ?></td>
                    <td><?php echo app_format_money($consolidation['total'], $consolidation['currency']); ?></td>
                    <td> 
              
                        <a href="<?php echo site_url('logistic/client/export_consolidation_shipment/'.$consolidation['id'].'?output_type=I'); ?>" class="btn btn-success btn-icon" data-toggle="tooltip" data-placement="top" title="<?php echo _l('lg_export_shipment'); ?>"><i class="fa fa-file-lines"></i></a>

                        <a href="<?php echo site_url('logistic/client/export_consolidation_label/'.$consolidation['id'].'?output_type=I'); ?>" class="btn btn-info btn-icon" data-toggle="tooltip" data-placement="top" title="<?php echo _l('lg_export_label'); ?>"><i class="fa fa-file-contract"></i></a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>