<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading"><?php echo _l('lg_pre_alert'); ?></h4>



<?php $this->load->model('logistic/logistic_model');
$pre_alert_list = $this->logistic_model->get_pre_alert_list('client_id = '.$client->userid); ?>

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
        	<th> <?php echo _l('lg_action') ?></th>
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
                	<?php $action = '';
                	if($pre_alert['status'] == 1){
				        $action = '<a href="'.admin_url('logistic/register_package/0/0/'.$pre_alert['id']).'" class="btn btn-success btn-icon">'._l('lg_convert_to_package').'</a>';
				    }

				    echo lg_html_entity_decode($action);
                	?>
                </td>
               
            </tr>
        <?php } ?>
    </tbody>
</table>


<?php } ?>
