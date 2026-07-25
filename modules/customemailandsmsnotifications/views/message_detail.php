<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix">
                            <h3 class="pull-left"><?php echo _l('message_history'); ?></h3>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('customemailandsmsnotifications/message_history'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
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
                                    <th><?php echo _l('message_type'); ?></th>
                                    <td><?php echo ucfirst($message->message_type); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('status'); ?></th>
                                    <td><?php echo ucfirst($message->status); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('recipient_name'); ?></th>
                                    <td><?php echo $message->recipient_name; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('recipient_contact'); ?></th>
                                    <td><?php echo $message->recipient_contact; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('subject'); ?></th>
                                    <td><?php echo !empty($message->subject) ? $message->subject : '<span class="text-muted">N/A</span>'; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('message'); ?></th>
                                    <td><?php echo nl2br(htmlspecialchars($message->message_content)); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('gateway'); ?></th>
                                    <td><?php echo $message->gateway; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _l('sent_at'); ?></th>
                                    <td><?php echo !empty($message->sent_at) ? _dt($message->sent_at) : '<span class="text-muted">-</span>'; ?></td>
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
