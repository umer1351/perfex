<?php
$task_timer_id = $this->uri->segment(6);
$deals_email_details = null;
if ($task_timer_id) {
    $deals_email_details = get_deals_row('tbl_deals_email', ['id' => $task_timer_id]);
}

$edited = has_permission('deals', '', 'edit');
$id = !empty($deals_email_details) ? $deals_email_details->id : null;
$all_deals_email = get_deals_result('tbl_deals_email', ['deals_id' => $deals_details->id]);
$all_deals_email = is_array($all_deals_email) ? $all_deals_email : [];
$emailCount = count($all_deals_email);
$outboundCount = 0;
$inboundCount = 0;
$attentionCount = 0;
$latestEmailDate = null;

foreach ($all_deals_email as $emailItem) {
    $direction = !empty($emailItem->direction) ? $emailItem->direction : 'outbound';
    if ($direction === 'inbound') {
        $inboundCount++;
    } else {
        $outboundCount++;
    }

    if (in_array((string) ($emailItem->delivery_status ?? ''), ['failed', 'bounced'], true)) {
        $attentionCount++;
    }

    if (!empty($emailItem->message_time) && ($latestEmailDate === null || strtotime($emailItem->message_time) > strtotime($latestEmailDate))) {
        $latestEmailDate = $emailItem->message_time;
    }
}
?>

<div class="deal-call-shell">
    <div class="deal-panel">
        <div class="deal-panel__header">
            <div>
                <h4 class="deal-panel__title mbot5">Emails</h4>
                <p class="text-muted mbot0"><?php echo $emailCount; ?> conversation<?php echo $emailCount === 1 ? '' : 's'; ?></p>
            </div>
            <?php if ($edited) { ?>
                <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#deal-email-modal">
                    <i class="fa fa-plus"></i> <?php echo _l('new_email'); ?>
                </a>
            <?php } ?>
        </div>

        <div class="deal-call-stats">
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Total</span>
                <strong><?php echo $emailCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Outbound</span>
                <strong><?php echo $outboundCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Inbound</span>
                <strong><?php echo $inboundCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Attention</span>
                <strong><?php echo $attentionCount; ?></strong>
            </div>
        </div>

        <div class="deal-activity-list">
            <?php if (empty($all_deals_email)) { ?>
                <div class="deal-empty-state">
                    <h5>No emails yet</h5>
                    <p>Send the first deal email to start the communication timeline.</p>
                </div>
            <?php } else { ?>
                <div class="deal-email-grid">
                    <?php foreach ($all_deals_email as $v_emails) {
                        $direction = !empty($v_emails->direction) ? $v_emails->direction : 'outbound';
                        $deliveryStatus = !empty($v_emails->delivery_status) ? $v_emails->delivery_status : ($direction === 'inbound' ? 'received' : 'sent');
                        $statusClass = $deliveryStatus === 'failed' || $deliveryStatus === 'bounced'
                            ? 'outbound'
                            : ($direction === 'inbound' ? 'inbound' : 'pending');
                        $bodyText = trim(strip_tags((string) ($v_emails->message_body ?? '')));
                        $bodyText = $bodyText !== '' ? $bodyText : 'No message preview available.';
                        $bodyPreview = strlen($bodyText) > 220 ? substr($bodyText, 0, 217) . '...' : $bodyText;
                        $recipientList = array_values(array_filter(array_map('trim', explode(';', (string) ($v_emails->email_to ?? '')))));
                        $recipientPreview = !empty($recipientList) ? implode(', ', array_slice($recipientList, 0, 2)) : 'No recipient';
                        if (count($recipientList) > 2) {
                            $recipientPreview .= ' +' . (count($recipientList) - 2);
                        }
                        $attachmentCount = 0;
                        if (!empty($v_emails->attach_file)) {
                            $attachments = json_decode($v_emails->attach_file);
                            $attachmentCount = is_array($attachments) ? count($attachments) : 0;
                        }
                        ?>
                        <div class="deal-email-card">
                            <div class="deal-call-card__top">
                                <div>
                                    <div class="deal-call-card__date"><?php echo html_escape($v_emails->subject ?: '(No subject)'); ?></div>
                                    <div class="deal-chip-row">
                                        <span class="deal-pill deal-call-pill deal-email-pill--<?php echo html_escape($direction); ?>">
                                            <?php echo ucfirst($direction); ?>
                                        </span>
                                        <span class="deal-pill deal-call-pill deal-call-pill--<?php echo $statusClass; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $deliveryStatus)); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="deal-call-card__actions">
                                    <a href="<?php echo base_url('admin/deals/email_details/' . $v_emails->id); ?>"
                                       class="btn btn-default btn-sm deal-card-action-btn"
                                       data-placement="top"
                                       data-toggle="modal"
                                       data-target="#myModal"
                                       title="<?php echo _l('details'); ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                        <?php if ($edited) { ?>
                                            <a href="<?php echo admin_url('deals/details/' . $deals_details->id . '/email/' . $v_emails->id); ?>"
                                           class="btn btn-primary btn-sm deal-card-action-btn"
                                           title="<?php echo _l('edit'); ?>">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/deals/delete_deals_email/' . $deals_details->id . '/' . $v_emails->id); ?>"
                                           class="btn btn-danger btn-sm deal-card-action-btn"
                                           title="<?php echo _l('delete'); ?>">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="deal-email-card__to">
                                <span>To</span>
                                <strong><?php echo html_escape($recipientPreview); ?></strong>
                            </div>

                            <div class="deal-call-card__summary">
                                <?php echo html_escape($bodyPreview); ?>
                            </div>

                            <div class="deal-call-card__meta">
                                <div class="deal-call-card__meta-item">
                                    <span>From</span>
                                    <strong><?php echo html_escape($v_emails->email_from ?: '-'); ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Sent</span>
                                    <strong><?php echo !empty($v_emails->message_time) ? _dt($v_emails->message_time) : '-'; ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Attachments</span>
                                    <strong><?php echo $attachmentCount; ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>CC</span>
                                    <strong><?php echo !empty($v_emails->email_cc) ? html_escape($v_emails->email_cc) : '-'; ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Latest Activity</span>
                                    <strong><?php echo !empty($latestEmailDate) ? _dt($latestEmailDate) : '-'; ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php if ($edited) { ?>
    <div class="modal fade" id="deal-email-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content deal-activity-modal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo $id ? _l('edit') . ' ' . _l('new_email') : _l('new_email'); ?></h4>
                </div>
                <div class="modal-body">
                    <?php echo form_open(base_url('admin/deals/send_mail/' . $id), ['id' => 'deals_email_form', 'enctype' => 'multipart/form-data', 'data-parsley-validate' => '', 'role' => 'form']); ?>
                    <input type="hidden" name="deals_id" value="<?php echo $deals_details->id; ?>">

                    <div class="row">
                        <div class="col-md-12 mtop15">
                            <label class="control-label"><?php echo _l('mail_to'); ?><span class="text-danger"> *</span></label>
                            <input class="form-control"
                                   value="<?php echo !empty($deals_email_details->email_to) ? html_escape($deals_email_details->email_to) : ''; ?>"
                                   type="text"
                                   required
                                   name="email_to"
                                   placeholder="<?php echo _l('you_can_sent_multiple_mail_semicolon_separated'); ?>"/>
                        </div>

                        <div class="col-md-12 mtop15">
                            <label class="control-label"><?php echo _l('add_cc'); ?></label>
                            <input class="form-control"
                                   value="<?php echo !empty($deals_email_details->email_cc) ? html_escape($deals_email_details->email_cc) : ''; ?>"
                                   type="text"
                                   name="email_cc"
                                   placeholder="<?php echo _l('add_cc'); ?>"/>
                        </div>

                        <div class="col-md-12 mtop15">
                            <label class="control-label"><?php echo _l('subject'); ?><span class="text-danger"> *</span></label>
                            <input class="form-control"
                                   value="<?php echo !empty($deals_email_details->subject) ? html_escape($deals_email_details->subject) : ''; ?>"
                                   type="text"
                                   required
                                   name="subject"
                                   placeholder="<?php echo _l('subject'); ?>"/>
                        </div>

                        <div class="col-md-12 mtop15">
                            <label class="control-label"><?php echo _l('message_body'); ?></label>
                            <textarea name="message_body" class="form-control tinymce" rows="6"><?php
                                echo !empty($deals_email_details->message_body) ? html_escape($deals_email_details->message_body) : '';
                                ?></textarea>
                        </div>

                        <div class="col-md-12 mtop15">
                            <div class="attachments_area">
                                <div class="row attachments">
                                    <div class="attachment">
                                        <div class="col-md-6 mtop10">
                                            <div class="form-group">
                                                <label for="attachment" class="control-label"><?php echo _l('ticket_add_attachments'); ?></label>
                                                <div class="input-group">
                                                    <input type="file"
                                                           extension="<?php echo str_replace(['.', ' '], '', get_option('ticket_attachments_file_extensions')); ?>"
                                                           filesize="<?php echo file_upload_max_size(); ?>"
                                                           class="form-control"
                                                           name="attachments[0]"
                                                           accept="<?php echo get_ticket_form_accepted_mimes(); ?>">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default add_more_attachments"
                                                                data-max="<?php echo get_option('maximum_allowed_ticket_attachments'); ?>"
                                                                type="button">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="deal-form-actions">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-envelope-o"></i> <?php echo $id ? _l('updates') : _l('send'); ?>
                        </button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($id) { ?>
        <script>
            $(function () {
                $('#deal-email-modal').modal('show');
            });
        </script>
    <?php } ?>
<?php } ?>
