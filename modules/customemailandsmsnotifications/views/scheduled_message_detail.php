<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix">
                            <h3 class="pull-left"><?php echo _l('scheduled_messages'); ?></h3>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('customemailandsmsnotifications/scheduled_messages'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                                <a href="<?php echo admin_url('customemailandsmsnotifications/scheduled_messages/edit/'.$message->id); ?>" class="btn btn-info">
                                    <i class="fa fa-pencil"></i> <?php echo _l('edit'); ?>
                                </a>
                                <a href="<?php echo admin_url('customemailandsmsnotifications/scheduled_messages/send_now/'.$message->id); ?>" class="btn btn-success">
                                    <i class="fa fa-paper-plane"></i> <?php echo _l('send'); ?>
                                </a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">
                        
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th><?php echo _l('id'); ?></th>
                                    <td>#<?php echo $message->id; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('recipient_type'); ?></th>
                                    <td><?php echo ucfirst($message->customer_or_leads); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('message_type'); ?></th>
                                    <td><?php echo ucfirst($message->mail_or_sms); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('subject'); ?></th>
                                    <td><?php echo !empty($message->subject) ? $message->subject : '<span class="text-muted">-</span>'; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('message'); ?></th>
                                    <td><?php echo nl2br(htmlspecialchars($message->message)); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('scheduled_for'); ?></th>
                                    <td><?php echo $message->custom_date . (!empty($message->custom_time) ? ' ' . $message->custom_time : ''); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('status'); ?></th>
                                    <td><?php echo $message->is_delivered == 1 ? _l('sent') : _l('scheduled'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
