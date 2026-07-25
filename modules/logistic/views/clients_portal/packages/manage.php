<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-invoices">
    <?php echo e($title); ?>

</h4>
<div class="panel_s">
    <div class="panel-body">

        <hr />
        <table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
            <thead>
                <tr>
                    <th ><?php echo _l('lg_tracking'); ?></th>
                    <th ><?php echo _l('lg_time'); ?></th>
                    <th ><?php echo _l('lg_destination'); ?></th>
                    <th ><?php echo _l('lg_shipping_company'); ?></th>
                    <th ><?php echo _l('lg_store_supplier'); ?></th>
                    <th ><?php echo _l('lg_tracking_purchase'); ?></th>
                    <th ><?php echo _l('lg_status'); ?></th>
                    <th ><?php echo _l('lg_driver'); ?></th>
                    <th ><?php echo _l('lg_total_cost'); ?></th>
                    <th ><?php echo _l('lg_invoice_status'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package) { ?>

                    <?php 
                        $invoice_status_str = '';
                        $invoice_status = '';


                        if(is_numeric($package['invoice_id']) && $package['invoice_id'] > 0){
                            $invoice = $this->invoices_model->get($package['invoice_id']);
                            $invoice_status_str = '<a href="'.site_url('invoice/'.$package['invoice_id'].'/'.$invoice->hash).'">'.format_invoice_number($package['invoice_id']).'</a>&nbsp;'.format_invoice_status($invoice->status);
                        }else{
                            $invoice_status = 'pending';
                            $invoice_status_str = '<span class="label label-warning">'._l('lg_pending').'</span>';
                        }
                    ?>

                <tr>
                    <td><a href="<?php echo site_url('logistic/client/package_detail/'.$package['id']) ?>"><?php echo e($package['shipping_prefix'].$package['number_code']); ?></a></td>
                    <td><?php echo _dt($package['created_at']); ?></td>
                    <td><?php echo lg_get_customer_address_str($package['customer_address']); ?></td>
                    <td><?php echo lg_get_shipping_company_name($package['courrier_company']); ?></td>
                    <td><?php echo e($package['store_supplier']); ?></td>
                    <td><?php echo e($package['tracking_purchase']); ?></td>
                    <td><?php echo format_lg_package_status($package['delivery_status']); ?></td>
                    <td><?php echo get_staff_full_name($package['assign_driver']); ?></td>
                    <td><?php echo app_format_money($package['total'], $package['currency']); ?></td>
                    <td><?php echo lg_html_entity_decode($invoice_status_str); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>