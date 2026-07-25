<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>

<h4 class="customer-profile-group-heading"><?php echo _l('lg_shippings'); ?></h4>

<?php $this->load->model('logistic/logistic_model');
$shippings = $this->logistic_model->get_client_shippings($client->userid); ?>

<table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
    <thead>
        <tr>
            <th ><?php echo _l('lg_tracking'); ?></th>
            <th ><?php echo _l('lg_time'); ?></th>
            <th ><?php echo _l('lg_destination'); ?></th>
            <th ><?php echo _l('lg_shipping_company'); ?></th>
 
            <th ><?php echo _l('lg_status'); ?></th>
            <th ><?php echo _l('lg_driver'); ?></th>
            <th ><?php echo _l('lg_total_cost'); ?></th>
            <th ><?php echo _l('lg_invoice_status'); ?></th>
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
            <td><?php echo lg_get_customer_address_str($shipping['customer_address']); ?></td>
            <td><?php echo lg_get_shipping_company_name($shipping['courrier_company']); ?></td>
    
            <td><?php echo format_lg_package_status($shipping['delivery_status']); ?></td>
            <td><?php echo get_staff_full_name($shipping['assign_driver']); ?></td>
            <td><?php echo app_format_money($shipping['total'], $shipping['currency']); ?></td>
            <td><?php echo lg_html_entity_decode($invoice_status_str); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php } ?>