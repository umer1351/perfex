<?php
$followup_id = $this->uri->segment(6);
$followup_details = null;
if ($followup_id) {
    $followup_details = get_deals_row('tbl_deals_followups', ['id' => $followup_id, 'deal_id' => $deals_details->id]);
}

$followups = is_array($deals_followups ?? null) ? $deals_followups : [];
$followupCount = count($followups);
$pendingCount = 0;
$completedCount = 0;
$overdueCount = 0;
$nextDueAt = null;
$edited = has_permission('deals', '', 'edit');
$saveFollowupId = !empty($followup_details) ? $followup_details->id : null;

foreach ($followups as $followup) {
    if (($followup['status'] ?? '') === 'completed') {
        $completedCount++;
    } elseif (($followup['status'] ?? '') === 'pending') {
        $pendingCount++;
        if (!empty($followup['follow_up_at']) && strtotime($followup['follow_up_at']) < time()) {
            $overdueCount++;
        }
        if (!empty($followup['follow_up_at']) && ($nextDueAt === null || strtotime($followup['follow_up_at']) < strtotime($nextDueAt))) {
            $nextDueAt = $followup['follow_up_at'];
        }
    }
}
?>

<div class="deal-call-shell">
    <div class="deal-panel">
        <div class="deal-panel__header">
            <div>
                <h4 class="deal-panel__title mbot5">Follow-Ups</h4>
                <p class="text-muted mbot0"><?php echo $followupCount; ?> scheduled item<?php echo $followupCount === 1 ? '' : 's'; ?></p>
            </div>
            <?php if ($edited) { ?>
                <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#deal-followup-modal">
                    <i class="fa fa-plus"></i> New Follow-Up
                </a>
            <?php } ?>
        </div>

        <div class="deal-call-stats">
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Total</span>
                <strong><?php echo $followupCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Pending</span>
                <strong><?php echo $pendingCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Completed</span>
                <strong><?php echo $completedCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Overdue</span>
                <strong><?php echo $overdueCount; ?></strong>
            </div>
        </div>

        <?php if (empty($followups)) { ?>
            <div class="deal-empty-state">
                <h5>No follow-ups yet</h5>
                <p>Schedule the next action and keep the deal moving.</p>
            </div>
        <?php } else { ?>
            <div class="deal-followup-grid">
                <?php foreach ($followups as $followup) {
                    $status = $followup['status'] ?? 'pending';
                    $type = $followup['type'] ?? 'call';
                    $isOverdue = $status === 'pending' && !empty($followup['follow_up_at']) && strtotime($followup['follow_up_at']) < time();
                    $notes = trim(strip_tags($followup['description'] ?? ''));
                    $notes = $notes !== '' ? $notes : 'No notes added.';
                    $notesPreview = strlen($notes) > 180 ? substr($notes, 0, 177) . '...' : $notes;
                    $statusClass = $status === 'completed' ? 'inbound' : ($isOverdue ? 'outbound' : 'pending');
                    ?>
                    <div class="deal-followup-card<?php echo $isOverdue ? ' deal-followup-card--overdue' : ''; ?>">
                        <div class="deal-call-card__top">
                            <div>
                                <div class="deal-call-card__date"><?php echo html_escape($followup['subject']); ?></div>
                                <div class="deal-chip-row">
                                    <span class="deal-pill deal-call-pill deal-followup-pill--<?php echo html_escape($type); ?>">
                                        <?php echo ucfirst($type); ?>
                                    </span>
                                    <span class="deal-pill deal-call-pill deal-call-pill--<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="deal-call-card__actions">
                                <?php if ($edited) { ?>
                                    <a href="<?php echo admin_url('deals/details/' . $deals_details->id . '/followups/' . $followup['id']); ?>"
                                       class="btn btn-primary btn-sm deal-card-action-btn"
                                       title="<?php echo _l('edit'); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                <?php } ?>
                                <?php if ($status === 'pending') { ?>
                                    <a href="#"
                                       class="btn btn-success btn-sm"
                                       data-toggle="modal"
                                       data-target="#deal-followup-complete-modal"
                                       data-complete-url="<?php echo admin_url('deals/complete_follow_up/' . $deals_details->id . '/' . $followup['id']); ?>"
                                       data-followup-subject="<?php echo html_escape($followup['subject']); ?>">
                                        Complete
                                    </a>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="deal-call-card__summary">
                            <?php echo html_escape($notesPreview); ?>
                        </div>

                        <div class="deal-call-card__meta">
                            <div class="deal-call-card__meta-item">
                                <span>Due</span>
                                <strong class="<?php echo $isOverdue ? 'text-danger' : ''; ?>">
                                    <?php echo _dt($followup['follow_up_at']); ?>
                                </strong>
                            </div>
                            <div class="deal-call-card__meta-item">
                                <span>Owner</span>
                                <strong><?php echo html_escape($followup['owner_name'] ?: 'Unassigned'); ?></strong>
                            </div>
                            <div class="deal-call-card__meta-item">
                                <span>Next Queue</span>
                                <strong><?php echo !empty($nextDueAt) ? _dt($nextDueAt) : '-'; ?></strong>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<?php if ($edited) { ?>
    <div class="modal fade" id="deal-followup-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content deal-activity-modal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo $saveFollowupId ? 'Edit Follow-Up' : 'Schedule Follow-Up'; ?></h4>
                </div>
                <div class="modal-body">
                    <?php echo form_open(admin_url('deals/save_follow_up/' . $deals_details->id . '/' . $saveFollowupId)); ?>
                    <div class="row">
                        <div class="col-md-6 mtop15">
                            <?php echo render_input('subject', 'Subject', !empty($followup_details->subject) ? $followup_details->subject : $deals_details->title . ' follow-up'); ?>
                        </div>
                        <div class="col-md-6 mtop15">
                            <?php echo render_datetime_input('follow_up_at', 'Follow-Up At', !empty($followup_details->follow_up_at) ? $followup_details->follow_up_at : (!empty($deals_details->next_follow_up_at) ? $deals_details->next_follow_up_at : '')); ?>
                        </div>
                        <div class="col-md-4 mtop15">
                            <?php echo render_select('type', [
                                ['id' => 'call', 'name' => 'Call'],
                                ['id' => 'email', 'name' => 'Email'],
                                ['id' => 'meeting', 'name' => 'Meeting'],
                                ['id' => 'task', 'name' => 'Task'],
                            ], ['id', 'name'], 'Type', !empty($followup_details->type) ? $followup_details->type : 'call'); ?>
                        </div>
                        <div class="col-md-4 mtop15">
                            <?php echo render_select('owner_id', $staff, ['staffid', ['firstname', 'lastname']], 'Owner', !empty($followup_details->owner_id) ? $followup_details->owner_id : $deals_details->default_deal_owner); ?>
                        </div>
                        <div class="col-md-4 mtop15">
                            <?php echo render_select('status', [
                                ['id' => 'pending', 'name' => 'Pending'],
                                ['id' => 'completed', 'name' => 'Completed'],
                                ['id' => 'cancelled', 'name' => 'Cancelled'],
                            ], ['id', 'name'], 'Status', !empty($followup_details->status) ? $followup_details->status : 'pending'); ?>
                        </div>
                        <div class="col-md-12 mtop15">
                            <?php echo render_textarea('description', 'Notes', !empty($followup_details->description) ? $followup_details->description : '', ['rows' => 4]); ?>
                        </div>
                    </div>
                    <div class="deal-form-actions">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo $saveFollowupId ? _l('updates') : 'Save Follow-Up'; ?></button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<div class="modal fade" id="deal-followup-complete-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content deal-activity-modal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Complete Follow-Up</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted mbot20" id="deal-followup-complete-text">This follow-up will be marked as completed.</p>
                <div class="deal-form-actions">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <a href="#" class="btn btn-success" id="deal-followup-complete-confirm">Complete</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('#deal-followup-complete-modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var url = button.data('complete-url');
            var subject = button.data('followup-subject') || 'this follow-up';
            $('#deal-followup-complete-confirm').attr('href', url);
            $('#deal-followup-complete-text').text('Mark "' + subject + '" as completed?');
        });

        <?php if ($edited && $saveFollowupId) { ?>
        $('#deal-followup-modal').modal('show');
        <?php } ?>
    });
</script>
