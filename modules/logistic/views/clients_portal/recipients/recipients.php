<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-invoices">
    <?php echo e($title); ?>

</h4>
<div class="panel_s">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <a href="<?php echo site_url('logistic/client/recipient'); ?>" class="btn btn-info"><?php echo _l('lg_create_recipient'); ?></a>
            </div>
        </div>

        <hr />
         <div class="row">
            <div class="col-md-12">
                <table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
                    <thead>
                        <tr>
                            <th ><?php echo _l('lg_recipient'); ?></th>
                            <th ><?php echo _l('lg_phone'); ?></th>
                            <th ><?php echo _l('lg_email'); ?></th>
                            <th><?php echo _l('options') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recipients as $recipient){ ?>
                            <tr>
                                <td><?php echo lg_html_entity_decode($recipient['first_name'].' '.$recipient['last_name']) ?></td>
                                <td><?php echo lg_html_entity_decode($recipient['phone']) ?></td>
                                <td><?php echo lg_html_entity_decode($recipient['email']) ?></td>
                                <td>
                                    <a href="<?php echo site_url('logistic/client/recipient/'.$recipient['id']); ?>" class="btn btn-info btn-icon"><i class="fa fa-pencil"></i></a>
                                    <a href="<?php echo site_url('logistic/client/delete_recipient/'.$recipient['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                   
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>