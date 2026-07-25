<?php
$approval_id = $this->uri->segment(6);
$approval_details = null;
if ($approval_id) {
    $approval_details = $this->deals_enterprise_model->get_approval_request($deals_details->id, $approval_id);
}

$approvals = is_array($deal_approvals ?? null) ? $deal_approvals : [];
$approvalCount = count($approvals);
$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;
$highPriorityCount = 0;
$edited = is_admin() || staff_can('govern', 'deals') || staff_can('edit', 'deals');
$saveApprovalId = !empty($approval_details) ? (int) $approval_details['id'] : null;

foreach ($approvals as $approval) {
    $status = $approval['status'] ?? 'pending';
    if ($status === 'approved') {
        $approvedCount++;
    } elseif ($status === 'rejected') {
        $rejectedCount++;
    } else {
        $pendingCount++;
    }

    if (($approval['priority'] ?? 'medium') === 'high') {
        $highPriorityCount++;
    }
}
?>

<div class="deal-call-shell">
    <div class="deal-panel">
        <div class="deal-panel__header">
            <div>
                <h4 class="deal-panel__title mbot5">Approvals</h4>
                <p class="text-muted mbot0"><?php echo $approvalCount; ?> approval request<?php echo $approvalCount === 1 ? '' : 's'; ?></p>
            </div>
            <?php if ($edited) { ?>
                <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#deal-approval-request-modal">
                    <i class="fa fa-plus"></i> Request Approval
                </a>
            <?php } ?>
        </div>

        <div class="deal-call-stats">
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Total</span>
                <strong><?php echo $approvalCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Pending</span>
                <strong><?php echo $pendingCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Approved</span>
                <strong><?php echo $approvedCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">High Priority</span>
                <strong><?php echo $highPriorityCount; ?></strong>
            </div>
        </div>

        <?php if (empty($approvals)) { ?>
            <div class="deal-empty-state">
                <h5>No approval requests yet</h5>
                <p>Create the first approval request for this deal when governance sign-off is needed.</p>
            </div>
        <?php } else { ?>
            <div class="deal-approval-grid">
                <?php foreach ($approvals as $approval) {
                    $status = $approval['status'] ?? 'pending';
                    $priority = $approval['priority'] ?? 'medium';
                    $statusClass = $status === 'approved' ? 'inbound' : ($status === 'rejected' ? 'outbound' : 'pending');
                    $priorityClass = $priority === 'high' ? 'approval' : ($priority === 'low' ? 'priority-low' : '');
                    $requestedBy = !empty($approval['requested_by_name']) ? $approval['requested_by_name'] : 'System';
                    ?>
                    <div class="deal-approval-card">
                        <div class="deal-call-card__top">
                            <div>
                                <div class="deal-call-card__date"><?php echo html_escape($approval['title']); ?></div>
                                <div class="deal-chip-row">
                                    <span class="deal-pill"><?php echo ucwords(str_replace('_', ' ', $approval['approval_type'])); ?></span>
                                    <span class="deal-pill deal-call-pill deal-call-pill--<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                    <span class="deal-pill<?php echo $priorityClass ? ' deal-pill--' . $priorityClass : ''; ?>">
                                        <?php echo ucfirst($priority); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ($status === 'pending') { ?>
                                <div class="deal-call-card__actions">
                                    <?php if ($edited) { ?>
                                        <a href="<?php echo admin_url('deals/details/' . $deals_details->id . '/approvals/' . $approval['id']); ?>"
                                           class="btn btn-primary btn-sm deal-card-action-btn"
                                           title="<?php echo _l('edit'); ?>">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    <?php } ?>
                                    <a href="#"
                                       class="btn btn-success btn-sm deal-card-action-btn"
                                       data-toggle="modal"
                                       data-target="#deal-approval-decision-modal"
                                       data-action="approve"
                                       data-url="<?php echo admin_url('deals/approve_request/' . $deals_details->id . '/' . $approval['id']); ?>"
                                       data-title="<?php echo html_escape($approval['title']); ?>"
                                       title="Approve">
                                        <i class="fa fa-check"></i>
                                    </a>
                                    <a href="#"
                                       class="btn btn-danger btn-sm deal-card-action-btn"
                                       data-toggle="modal"
                                       data-target="#deal-approval-decision-modal"
                                       data-action="reject"
                                       data-url="<?php echo admin_url('deals/reject_request/' . $deals_details->id . '/' . $approval['id']); ?>"
                                       data-title="<?php echo html_escape($approval['title']); ?>"
                                       title="Reject">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="deal-call-card__summary">
                            <?php if (!empty($approval['notes'])) { ?>
                                <?php echo nl2br(html_escape($approval['notes'])); ?>
                            <?php } else { ?>
                                No approval notes added.
                            <?php } ?>
                        </div>

                        <div class="deal-call-card__meta">
                            <div class="deal-call-card__meta-item">
                                <span>Requested By</span>
                                <strong><?php echo html_escape($requestedBy); ?></strong>
                            </div>
                            <div class="deal-call-card__meta-item">
                                <span>Approver</span>
                                <strong><?php echo html_escape($approval['approver_name'] ?: 'Unassigned'); ?></strong>
                            </div>
                            <div class="deal-call-card__meta-item">
                                <span>Due</span>
                                <strong><?php echo !empty($approval['due_at']) ? _dt($approval['due_at']) : '-'; ?></strong>
                            </div>
                            <div class="deal-call-card__meta-item">
                                <span>Requested</span>
                                <strong><?php echo !empty($approval['requested_at']) ? _dt($approval['requested_at']) : '-'; ?></strong>
                            </div>
                            <div class="deal-call-card__meta-item">
                                <span>Decided</span>
                                <strong><?php echo !empty($approval['decided_at']) ? _dt($approval['decided_at']) : '-'; ?></strong>
                            </div>
                        </div>

                        <?php if (!empty($approval['decision_notes'])) { ?>
                            <div class="deal-approval-card__decision">
                                <span class="deal-stat-card__label">Decision Notes</span>
                                <p class="mbot0"><?php echo nl2br(html_escape($approval['decision_notes'])); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<?php if ($edited) { ?>
    <div class="modal fade" id="deal-approval-request-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content deal-activity-modal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo $saveApprovalId ? 'Edit Approval Request' : 'Request Approval'; ?></h4>
                </div>
                <div class="modal-body">
                    <?php echo form_open(admin_url('deals/request_approval/' . $deals_details->id . '/' . $saveApprovalId)); ?>
                    <div class="row">
                        <div class="col-md-6 mtop15">
                            <?php echo render_select('approval_type', [
                                ['id' => 'close_won', 'name' => 'Close Won'],
                                ['id' => 'discount_exception', 'name' => 'Discount Exception'],
                                ['id' => 'forecast_commit', 'name' => 'Forecast Commit'],
                                ['id' => 'custom', 'name' => 'Custom'],
                            ], ['id', 'name'], 'Approval Type', !empty($approval_details['approval_type']) ? $approval_details['approval_type'] : 'custom'); ?>
                        </div>
                        <div class="col-md-6 mtop15">
                            <?php echo render_select('priority', [
                                ['id' => 'low', 'name' => 'Low'],
                                ['id' => 'medium', 'name' => 'Medium'],
                                ['id' => 'high', 'name' => 'High'],
                            ], ['id', 'name'], 'Priority', !empty($approval_details['priority']) ? $approval_details['priority'] : 'medium'); ?>
                        </div>
                        <div class="col-md-12 mtop15">
                            <?php echo render_input('title', 'Title', !empty($approval_details['title']) ? $approval_details['title'] : $deals_details->title . ' approval'); ?>
                        </div>
                        <div class="col-md-6 mtop15">
                            <?php echo render_select('assigned_to', $staff, ['staffid', ['firstname', 'lastname']], 'Approver', !empty($approval_details['assigned_to']) ? $approval_details['assigned_to'] : $deals_details->default_deal_owner); ?>
                        </div>
                        <div class="col-md-6 mtop15">
                            <?php echo render_datetime_input('due_at', 'Due At', !empty($approval_details['due_at']) ? $approval_details['due_at'] : ''); ?>
                        </div>
                        <div class="col-md-12 mtop15">
                            <?php echo render_textarea('notes', 'Approval Notes', !empty($approval_details['notes']) ? $approval_details['notes'] : '', ['rows' => 5]); ?>
                        </div>
                    </div>
                    <div class="deal-form-actions">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo $saveApprovalId ? _l('updates') : 'Request Approval'; ?></button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<div class="modal fade" id="deal-approval-decision-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content deal-activity-modal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="deal-approval-decision-title">Approval Decision</h4>
            </div>
            <div class="modal-body">
                <?php echo form_open('', ['id' => 'deal-approval-decision-form']); ?>
                <p class="text-muted" id="deal-approval-decision-text">Confirm this approval decision.</p>
                <?php echo render_textarea('decision_notes', 'Decision Notes', '', ['rows' => 4]); ?>
                <div class="deal-form-actions">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-primary" id="deal-approval-decision-submit">Confirm</button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('#deal-approval-decision-modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var action = button.data('action') || 'approve';
            var url = button.data('url') || '#';
            var title = button.data('title') || 'approval request';
            var verb = action === 'reject' ? 'Reject' : 'Approve';
            var submitClass = action === 'reject' ? 'btn-danger' : 'btn-success';

            $('#deal-approval-decision-title').text(verb + ' Request');
            $('#deal-approval-decision-text').text(verb + ' "' + title + '"?');
            $('#deal-approval-decision-form').attr('action', url);
            $('#deal-approval-decision-submit')
                .text(verb)
                .removeClass('btn-success btn-danger btn-primary')
                .addClass(submitClass);
        });

        <?php if ($edited && $saveApprovalId) { ?>
        $('#deal-approval-request-modal').modal('show');
        <?php } ?>
    });
</script>
