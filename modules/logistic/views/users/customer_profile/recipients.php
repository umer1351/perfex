<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading"><?php echo _l('lg_recipients'); ?></h4>



<?php $this->load->model('logistic/logistic_model');
$recipients = $this->logistic_model->get_client_recipients('client_id = '.$client->userid); ?>

<table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
    <thead>
        <tr>
            <th ><?php echo _l('lg_recipient'); ?></th>
            <th ><?php echo _l('lg_phone'); ?></th>
            <th ><?php echo _l('lg_email'); ?></th>
        
        </tr>
    </thead>
    <tbody>
        <?php foreach($recipients as $recipient){ ?>
            <tr>
                <td><?php echo lg_html_entity_decode($recipient['first_name'].' '.$recipient['last_name']) ?></td>
                <td><?php echo lg_html_entity_decode($recipient['phone']) ?></td>
                <td><?php echo lg_html_entity_decode($recipient['email']) ?></td>
             
            </tr>
        <?php } ?>
    </tbody>
</table>


<?php } ?>
