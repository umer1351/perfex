<?php
$edited = has_permission('deals', '', 'edit');
$tags = get_tags_in($deals_details->id, 'deal');
$customFields = get_custom_fields('deals');
$probability = deals_probability_percentage($deals_details);
$weightedRevenue = app_format_money((float) ($deals_details->expected_revenue ?? 0), get_base_currency());
$dealValue = app_format_money((float) ($deals_details->deal_value ?? 0), get_base_currency());
$nextFollowUp = !empty($deals_details->next_follow_up_at) ? _dt($deals_details->next_follow_up_at) : 'Not scheduled';
$lastActivity = !empty($deals_details->last_activity_at) ? _dt($deals_details->last_activity_at) : 'No activity yet';
$lastContact = !empty($deals_details->last_contacted_at) ? _dt($deals_details->last_contacted_at) : 'No contact logged';
$closeTarget = !empty($deals_details->days_to_close) ? _d($deals_details->days_to_close) : '-';
$campaignLabel = !empty($deals_details->campaign_id) ? '#' . (int) $deals_details->campaign_id : 'None';
$sourceName = $deals_details->source_name ?: 'Unknown';
$pipelineName = $deals_details->pipeline_name ?: '-';
$stageName = $deals_details->stage_name ?: '-';
$ownerName = $deals_details->full_name ?: 'Unassigned';
$forecastLabel = ucwords(str_replace('_', ' ', $deals_details->forecast_category ?? 'pipeline'));
$healthLabel = ucwords(str_replace('_', ' ', $deals_details->health_status ?? 'on_track'));
$statusLabel = ucfirst($deals_details->status);
$createdAt = _dt($deals_details->created_at);
$hasNotes = !empty($deals_details->notes);
$hasTags = count($tags) > 0;
$hasApprovals = !empty($deal_approvals);
$relationLink = null;
$dealAssignees = is_array($deals_details->assignees ?? null) ? $deals_details->assignees : [];

if (!empty($deals_details->rel_id) && !empty($deals_details->rel_type)) {
    $task_rel_data = get_relation_data($deals_details->rel_type, $deals_details->rel_id);
    $task_rel_value = get_relation_values($task_rel_data, $deals_details->rel_type);
    $relationLink = [
        'type' => _l($deals_details->rel_type),
        'link' => $task_rel_value['link'],
        'name' => $task_rel_value['name'],
    ];
}

$_assignees = '';
foreach ($dealAssignees as $assignee) {
    $_remove_assigne = '';
    if (staff_can('edit', 'deals') || ($deals_details->current_user_is_creator && staff_can('create', 'deals'))) {
        $_remove_assigne = ' <a href="#" class="deal-assignee-remove" onclick="remove_deal_assignee(' . $assignee['staffid'] . ',' . $deals_details->id . '); return false;"><i class="fa fa-remove"></i></a>';
    }
    $_assignees .= '<div class="deal-avatar-row__item" data-toggle="tooltip" data-title="' . html_escape($assignee['full_name']) . '">
        <a href="' . admin_url('profile/' . $assignee['staffid']) . '" target="_blank">' . staff_profile_image($assignee['staffid'], ['staff-profile-image-small']) . '</a>
        <span>' . html_escape($assignee['full_name']) . '</span>' . $_remove_assigne . '
    </div>';
}

if ($_assignees === '') {
    $_assignees = '<div class="text-muted">No assignees added yet.</div>';
}

$user_id = $deals_details->user_id ?? [];
if (is_string($user_id) && $user_id !== '') {
    $decodedUserIds = json_decode($user_id, true);
    $user_id = is_array($decodedUserIds) ? $decodedUserIds : [];
} elseif (!is_array($user_id)) {
    $user_id = [];
}

$selected = [];
foreach ($user_id as $memberId) {
    $selected[] = $memberId;
}
?>

<div class="deal-overview-stack">
    <div class="deal-detail-stats">
        <div class="deal-stat-icon-card">
            <span class="deal-stat-icon-card__icon"><i class="fa fa-money"></i></span>
            <div class="deal-stat-icon-card__body">
                <span class="deal-stat-card__label">Deal Value</span>
                <strong><?php echo $dealValue; ?></strong>
            </div>
        </div>
        <div class="deal-stat-icon-card">
            <span class="deal-stat-icon-card__icon"><i class="fa fa-bullseye"></i></span>
            <div class="deal-stat-icon-card__body">
                <span class="deal-stat-card__label">Win Probability</span>
                <strong><?php echo (int) $probability; ?>%</strong>
            </div>
        </div>
        <div class="deal-stat-icon-card">
            <span class="deal-stat-icon-card__icon"><i class="fa fa-calendar"></i></span>
            <div class="deal-stat-icon-card__body">
                <span class="deal-stat-card__label">Next Follow-Up</span>
                <strong><?php echo $nextFollowUp; ?></strong>
            </div>
        </div>
        <div class="deal-stat-icon-card">
            <span class="deal-stat-icon-card__icon"><i class="fa fa-clock-o"></i></span>
            <div class="deal-stat-icon-card__body">
                <span class="deal-stat-card__label">Last Activity</span>
                <strong><?php echo $lastActivity; ?></strong>
            </div>
        </div>
    </div>

    <div class="deal-overview-grid deal-overview-grid--compact">
        <div class="deal-panel">
            <div class="deal-panel__header">
                <h4 class="deal-panel__title mbot0">Details</h4>
                <span class="deal-pill"><?php echo $statusLabel; ?></span>
            </div>
            <div class="deal-definition-list deal-definition-list--compact">
                <div class="deal-definition-list__item">
                    <span>Source</span>
                    <strong><?php echo $sourceName; ?></strong>
                </div>
                <div class="deal-definition-list__item">
                    <span>Pipeline</span>
                    <strong><?php echo $pipelineName; ?></strong>
                </div>
                <div class="deal-definition-list__item">
                    <span>Stage</span>
                    <strong><?php echo $stageName; ?></strong>
                </div>
                <div class="deal-definition-list__item">
                    <span>Created</span>
                    <strong><?php echo $createdAt; ?></strong>
                </div>
                <div class="deal-definition-list__item">
                    <span>Owner</span>
                    <strong><?php echo $ownerName; ?></strong>
                </div>
                <div class="deal-definition-list__item">
                    <span>Health</span>
                    <strong><?php echo $healthLabel; ?></strong>
                </div>
            </div>
        </div>

        <div class="deal-panel">
            <div class="deal-panel__header">
                <h4 class="deal-panel__title mbot0">Team</h4>
                <a href="#" class="btn btn-default btn-sm" data-target="#add-edit-members" data-toggle="modal">Manage Team</a>
            </div>
            <div class="deal-inline-metrics">
                <div class="deal-inline-metric">
                    <span><i class="fa fa-user"></i> Owner</span>
                    <strong><?php echo $ownerName; ?></strong>
                </div>
                <div class="deal-inline-metric">
                    <span><i class="fa fa-refresh"></i> Next Action</span>
                    <strong><?php echo $nextFollowUp; ?></strong>
                </div>
                <div class="deal-inline-metric">
                    <span><i class="fa fa-phone"></i> Last Contact</span>
                    <strong><?php echo $lastContact; ?></strong>
                </div>
                <div class="deal-inline-metric">
                    <span><i class="fa fa-users"></i> Assignees</span>
                    <strong><?php echo count($dealAssignees); ?></strong>
                </div>
            </div>

            <div class="deal-assignee-block deal-assignee-block--compact">
                <div class="deal-panel__subheading">
                    <span>Assigned Team</span>
                    <a href="#" data-target="#add-edit-members" data-toggle="modal">Manage</a>
                </div>
                <div class="deal-avatar-row">
                    <?php echo $_assignees; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="deal-detail-accordion">
        <div class="deal-config-section deal-detail-section">
            <button type="button" class="deal-config-section__toggle" data-target="deal-commercial-detail">
                <span>Commercial</span>
            </button>
            <div id="deal-commercial-detail" class="deal-config-section__content">
                <div class="deal-definition-list">
                    <div class="deal-definition-list__item">
                        <span>Source</span>
                        <strong><?php echo $sourceName; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Pipeline</span>
                        <strong><?php echo $pipelineName; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Stage</span>
                        <strong><?php echo $stageName; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Campaign</span>
                        <strong><?php echo $campaignLabel; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Close Target</span>
                        <strong><?php echo $closeTarget; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Created</span>
                        <strong><?php echo $createdAt; ?></strong>
                    </div>
                    <?php if ($relationLink) { ?>
                        <div class="deal-definition-list__item deal-definition-list__item--full">
                            <span><?php echo _l('task_related_to') . ' ' . $relationLink['type']; ?></span>
                            <strong><a class="text-muted" href="<?php echo $relationLink['link']; ?>"><?php echo html_escape($relationLink['name']); ?></a></strong>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="deal-config-section deal-detail-section">
            <button type="button" class="deal-config-section__toggle" data-target="deal-collaboration-detail">
                <span>Timeline</span>
            </button>
            <div id="deal-collaboration-detail" class="deal-config-section__content">
                <div class="deal-definition-list">
                    <div class="deal-definition-list__item">
                        <span>Deal Owner</span>
                        <strong><?php echo $ownerName; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Next Follow-Up</span>
                        <strong><?php echo $nextFollowUp; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Last Contact</span>
                        <strong><?php echo $lastContact; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Last Activity</span>
                        <strong><?php echo $lastActivity; ?></strong>
                    </div>
                </div>

                <div class="deal-assignee-block">
                    <div class="deal-panel__subheading">
                        <span>Assignees</span>
                        <a href="#" data-target="#add-edit-members" data-toggle="modal">Manage</a>
                    </div>
                    <div class="deal-avatar-row">
                        <?php echo $_assignees; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="deal-config-section deal-detail-section">
            <button type="button" class="deal-config-section__toggle" data-target="deal-governance-detail">
                <span>Governance</span>
            </button>
            <div id="deal-governance-detail" class="deal-config-section__content">
                <div class="deal-definition-list">
                    <div class="deal-definition-list__item">
                        <span>Forecast Category</span>
                        <strong><?php echo $forecastLabel; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Health Status</span>
                        <strong><?php echo $healthLabel; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Status</span>
                        <strong><?php echo $statusLabel; ?></strong>
                    </div>
                    <div class="deal-definition-list__item">
                        <span>Campaign</span>
                        <strong><?php echo $campaignLabel; ?></strong>
                    </div>
                </div>

                <?php if ($hasApprovals) { ?>
                    <div class="deal-inline-table">
                        <div class="deal-panel__subheading">
                            <span>Latest Approvals</span>
                            <a href="<?php echo admin_url('deals/details/' . $deals_details->id . '/approvals'); ?>">Open queue</a>
                        </div>
                        <table class="table table-hover deal-data-table mbot0">
                            <thead>
                            <tr>
                                <th>Title</th>
                                <th>Approver</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach (array_slice($deal_approvals, 0, 3) as $approval) { ?>
                                <tr>
                                    <td><?php echo html_escape($approval['title']); ?></td>
                                    <td><?php echo $approval['approver_name'] ?: 'Unassigned'; ?></td>
                                    <td><?php echo ucfirst($approval['status']); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p class="text-muted mbot0 deal-detail-empty">No approval items on this deal right now.</p>
                <?php } ?>
            </div>
        </div>

        <div class="deal-config-section deal-detail-section">
            <button type="button" class="deal-config-section__toggle" data-target="deal-notes-detail">
                <span>Notes And Custom Data</span>
            </button>
            <div id="deal-notes-detail" class="deal-config-section__content">
                <?php if ($hasNotes) { ?>
                    <div class="deal-note-block deal-note-block--compact">
                        <span class="deal-stat-card__label">Internal Notes</span>
                        <p class="mbot0"><?php echo nl2br(html_escape($deals_details->notes)); ?></p>
                    </div>
                <?php } ?>

                <?php if ($hasTags) { ?>
                    <div class="deal-tag-wrap">
                        <input type="text" class="tagsinput read-only" id="tags" name="tags"
                               value="<?php echo prep_tags_input($tags); ?>" data-role="tagsinput">
                    </div>
                <?php } elseif (!$hasNotes) { ?>
                    <p class="text-muted">No tags added yet.</p>
                <?php } ?>

                <?php
                $hasCustomFields = false;
                if (!empty($customFields)) {
                    echo '<div class="deal-definition-list">';
                    foreach ($customFields as $field) {
                        $value = get_custom_field_value($deals_details->id, $field['id'], 'deals');
                        if ($value == '') {
                            continue;
                        }
                        $hasCustomFields = true;
                        echo '<div class="deal-definition-list__item"><span>' . html_escape($field['name']) . '</span><strong>' . $value . '</strong></div>';
                    }
                    echo '</div>';
                }
                if (!$hasCustomFields && !$hasTags && !$hasNotes) {
                    echo '<p class="text-muted mbot0">No notes, tags, or custom fields populated for this deal.</p>';
                } elseif (!$hasCustomFields) {
                    echo '<p class="text-muted mbot0">No custom fields populated for this deal.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add-edit-members" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('deals/add_deals_assignees/' . $deals_details->id)); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('assigne'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_select('assignee[]', $staff, ['staffid', ['firstname', 'lastname']], 'assigne', $selected, ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script type="text/javascript">
    'use strict';

    $(function () {
        function toggleDetailSection($section, forceOpen) {
            var shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !$section.hasClass('is-open');
            $section.toggleClass('is-open', shouldOpen);
            $section.find('.deal-config-section__content').stop(true, true)[shouldOpen ? 'slideDown' : 'slideUp'](160);
        }

        $('.deal-detail-section').each(function () {
            var $section = $(this);
            toggleDetailSection($section, $section.hasClass('is-open'));
        });

        $(document).on('click', '.deal-detail-section .deal-config-section__toggle', function () {
            toggleDetailSection($(this).closest('.deal-detail-section'));
        });
    });

    function remove_deal_assignee(id, deal_id) {
        if (confirm_delete()) {
            requestGetJSON("deals/remove_assignee/" + id + "/" + deal_id).done(function (response) {
                if (response.success === true || response.success === "true") {
                    alert_float("success", response.message);
                    location.reload();
                }
            });
        }
    }
</script>
