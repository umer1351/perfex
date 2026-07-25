<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-invoices">
    <?php echo e($title); ?>

</h4>
<div class="panel_s">
    <div class="panel-body">

        <div class="row mbot15"> 
            <div class="col-md-12"> 
                <a href="<?php echo site_url('logistic/client/shipment/0'); ?>" class="btn btn-primary pull-left mright5"><?php echo _l('lg_create_pickup'); ?></a>

            </div>
        </div>

        <hr class="hr-panel-heading" />
        <table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
            <thead>
                <tr>
                    <th ><?php echo _l('lg_tracking'); ?></th>
                    <th ><?php echo _l('lg_date'); ?></th>
                    <th ><?php echo _l('lg_recipient'); ?></th>
                    <th ><?php echo _l('lg_origin'); ?></th>
                    <th ><?php echo _l('lg_destination'); ?></th>
                    <th ><?php echo _l('lg_payment'); ?></th>
                    <th ><?php echo _l('lg_status'); ?></th>
                    <th ><?php echo _l('lg_total_cost'); ?></th>
                    <th ><?php echo _l('lg_invoice_status'); ?></th>
                    <th ><?php echo _l('lg_action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shippings as $shipping) { ?>

                    <?php 
                        $invoice_status_str = '';
                        $invoice_status = '';


                        if(is_numeric($shipping['invoice_id']) && $shipping['invoice_id'] > 0){
                            $invoice = $this->invoices_model->get($shipping['invoice_id']);
                            $invoice_status_str = '<a href="'.site_url('invoice/'.$shipping['invoice_id'].'/'.$invoice->hash).'">'.format_invoice_number($shipping['invoice_id']).'</a>&nbsp;'.format_invoice_status($invoice->status);
                        }else{
                            $invoice_status = 'pending';
                            $invoice_status_str = '<span class="label label-warning">'._l('lg_pending').'</span>';
                        }
                    ?>

                <tr>
                    <td><a href="<?php echo site_url('logistic/client/shipping_detail/'.$shipping['id']) ?>"><?php echo e($shipping['shipping_prefix'].$shipping['number_code']); ?></a></td>
                    <td><?php echo _dt($shipping['created_at']); ?></td>
                    <td><?php echo lg_get_recipient_name($shipping['recipient_id']); ?></td>
                    <td><?php echo lg_get_customer_address_str($shipping['customer_address']); ?></td>
                    <td><?php echo lg_get_recipient_address_str($shipping['recipient_address_id']); ?></td>
                    <td><?php echo lg_get_payment_term_str($shipping['payment_term_id']); ?></td>
                    <td><?php echo format_lg_package_status($shipping['delivery_status']); ?></td>
                    <td><?php echo app_format_money($shipping['total'], $shipping['currency']); ?></td>
                    <td><?php echo lg_html_entity_decode($invoice_status_str); ?></td>
                    <td> 
                        <?php if($shipping['created_from'] == 'client' && $shipping['shipping_type'] == 'pickup' && $shipping['approve_status'] != 'approved'){ ?>

                            <a href="<?php echo site_url('logistic/client/shipment/0/'.$shipping['id']); ?>" class="btn btn-warning btn-icon"><i class="fa fa-pencil"></i></a>
                        <?php } ?>

                        <a href="<?php echo site_url('logistic/client/export_shipping_shipment/'.$shipping['id'].'?output_type=I'); ?>" class="btn btn-success btn-icon" data-toggle="tooltip" data-placement="top" title="<?php echo _l('lg_export_shipment'); ?>"><i class="fa fa-file-lines"></i></a>

                        <a href="<?php echo site_url('logistic/client/export_shipping_label/'.$shipping['id'].'?output_type=I'); ?>" class="btn btn-info btn-icon" data-toggle="tooltip" data-placement="top" title="<?php echo _l('lg_export_label'); ?>"><i class="fa fa-file-contract"></i></a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>