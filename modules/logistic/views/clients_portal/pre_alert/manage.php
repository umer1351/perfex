<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-invoices">
    <?php echo e($title); ?>

</h4>
<div class="panel_s">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <a href="<?php echo site_url('logistic/client/pre_alert'); ?>" class="btn btn-info"><?php echo _l('lg_create_pre_alert'); ?></a>
            </div>
        </div>

        <hr />
         <div class="row">
            <div class="col-md-12">
                <table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
                    <thead>
                        <tr>
                            <th ><?php echo _l('lg_tracking'); ?></th>
                            <th ><?php echo _l('lg_date'); ?></th>
             
                            <th ><?php echo _l('lg_shipping_company'); ?></th>
                            <th ><?php echo _l('lg_store_supplier'); ?></th>
                            <th ><?php echo _l('lg_package_description'); ?></th>
                            <th ><?php echo _l('lg_delivery_date'); ?></th>
                            <th ><?php echo _l('lg_purchase_price'); ?></th>
                            <th ><?php echo _l('lg_status'); ?></th>
                            <th><?php echo _l('options') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pre_alert_list as $pre_alert){ ?>
                            <tr>
                                <td><?php echo lg_html_entity_decode($pre_alert['tracking_purchase']) ?></td>
                                <td><?php echo _dt($pre_alert['created_at']) ?></td>
                                <td><?php echo lg_get_shipping_company_name($pre_alert['courier_company']) ?></td>
                                <td><?php echo lg_html_entity_decode($pre_alert['store_supplier']) ?></td>
                                <td><?php echo lg_html_entity_decode($pre_alert['package_description']) ?></td>
                                <td><?php echo _dt($pre_alert['delivery_date']) ?></td>
                                <td><?php echo app_format_money($pre_alert['purchase_price'], $pre_alert['currency']) ?></td>
                                <td>
                                    <?php
                                    $status = '';
                                    if($pre_alert['status'] == 1){
                                        $status = '<span class="label label-warning">'._l('lg_pending').'</span>';
                                    }else if($pre_alert['status'] == 2){
                                        $status = '<span class="label label-success">'._l('lg_approved').'</span>';
                                    }
                                    echo lg_html_entity_decode($status);
                                    ?>
                                        
                                </td>
                                <td>
                                    <?php if($pre_alert['status'] != 2){ ?>
                                        <a href="<?php echo site_url('logistic/client/pre_alert/'.$pre_alert['id']); ?>" class="btn btn-info btn-icon"><i class="fa fa-pencil"></i></a>
                                        <a href="<?php echo site_url('logistic/client/delete_pre_alert/'.$pre_alert['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                    <?php } ?>    
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>