<?php defined('BASEPATH') or exit('No direct script access allowed');

if ($deal['stage_id'] == $stage->stage_id) {
    $statusClass = 'open';
    if (($deal['status'] ?? '') === 'won') {
        $statusClass = 'won';
    } elseif (($deal['status'] ?? '') === 'lost') {
        $statusClass = 'lost';
    }

    $priorityClass = strtolower($deal['priority'] ?? 'medium');
    $healthClass = strtolower($deal['health_status'] ?? 'on_track');
    $probability = (int) round((float) ($deal['probability'] ?? 0));
    $expectedRevenue = app_format_money((float) ($deal['expected_revenue'] ?? 0), $base_currency);
    $dealValue = app_format_money((float) ($deal['deal_value'] ?? 0), $base_currency);
    $nextFollowUp = !empty($deal['next_follow_up_at']) ? strtotime($deal['next_follow_up_at']) : null;
    $followUpOverdue = $nextFollowUp && $nextFollowUp < time() && ($deal['status'] ?? '') === 'open';
    $ownerName = !empty($deal['default_deal_owner']) ? get_staff_full_name($deal['default_deal_owner']) : 'Unassigned';
    ?>
    <li data-lead-id="<?php echo $deal['id']; ?>" class="lead-kan-ban deal-kanban-card<?php if ($deal['default_deal_owner'] == get_staff_user_id()) {
        echo ' current-user-lead';
    } ?>">
        <div class="deal-kanban-card__body">
            <div class="deal-kanban-card__header">
                <div class="deal-kanban-card__heading">
                    <div class="deal-kanban-card__id">#<?php echo $deal['id']; ?></div>
                    <a href="<?php echo admin_url('deals/details/' . $deal['id']); ?>" class="deal-kanban-card__title">
                        <?php echo html_escape($deal['title']); ?>
                    </a>
                </div>
                <div class="deal-kanban-card__probability">
                    <strong><?php echo $probability; ?>%</strong>
                    <span>confidence</span>
                </div>
            </div>

            <div class="deal-kanban-card__chips">
                <span class="deal-pill deal-pill--status-<?php echo $statusClass; ?>"><?php echo ucfirst($deal['status']); ?></span>
                <span class="deal-pill deal-pill--priority-<?php echo $priorityClass; ?>"><?php echo ucfirst($deal['priority'] ?? 'Medium'); ?></span>
                <span class="deal-pill deal-pill--health-<?php echo $healthClass; ?>"><?php echo ucwords(str_replace('_', ' ', $deal['health_status'] ?? 'On Track')); ?></span>
                <?php if (!empty($deal['pending_approvals'])) { ?>
                    <span class="deal-pill deal-pill--approval"><?php echo (int) $deal['pending_approvals']; ?> approval<?php echo (int) $deal['pending_approvals'] > 1 ? 's' : ''; ?></span>
                <?php } ?>
                <?php if ($followUpOverdue) { ?>
                    <span class="deal-pill deal-pill--alert">Follow-up overdue</span>
                <?php } ?>
            </div>

            <div class="deal-kanban-card__values">
                <div>
                    <span class="deal-kanban-card__label">Deal Value</span>
                    <strong><?php echo $dealValue; ?></strong>
                </div>
                <div>
                    <span class="deal-kanban-card__label">Weighted</span>
                    <strong><?php echo $expectedRevenue; ?></strong>
                </div>
            </div>

            <div class="deal-kanban-card__meta">
                <div>
                    <span class="deal-kanban-card__label">Owner</span>
                    <div class="deal-kanban-card__owner">
                        <?php if (!empty($deal['default_deal_owner'])) { ?>
                            <a href="<?php echo admin_url('profile/' . $deal['default_deal_owner']); ?>" data-toggle="tooltip" title="<?php echo html_escape($ownerName); ?>">
                                <?php echo staff_profile_image($deal['default_deal_owner'], ['staff-profile-image-xs']); ?>
                            </a>
                        <?php } ?>
                        <span><?php echo html_escape($ownerName); ?></span>
                    </div>
                </div>
                <div>
                    <span class="deal-kanban-card__label">Next Follow-Up</span>
                    <strong><?php echo !empty($deal['next_follow_up_at']) ? _dt($deal['next_follow_up_at']) : 'Not scheduled'; ?></strong>
                </div>
            </div>

            <div class="deal-kanban-card__stats">
                <span data-toggle="tooltip" title="<?php echo _l('leads_canban_tasks', $deal['total_tasks']); ?>"><i class="fa-regular fa-circle-check"></i> <?php echo (int) $deal['total_tasks']; ?></span>
                <span data-toggle="tooltip" title="<?php echo _l('lead_kan_ban_attachments', $deal['total_files']); ?>"><i class="fa fa-paperclip"></i> <?php echo (int) $deal['total_files']; ?></span>
                <span data-toggle="tooltip" title="<?php echo _l('calls', $deal['total_calls']); ?>"><i class="fa fa-tty"></i> <?php echo (int) $deal['total_calls']; ?></span>
                <span data-toggle="tooltip" title="<?php echo _l('mettings', $deal['total_mettings']); ?>"><i class="fa fa-calendar-check"></i> <?php echo (int) $deal['total_mettings']; ?></span>
                <span data-toggle="tooltip" title="<?php echo _l('comments', $deal['total_comments']); ?>"><i class="fa fa-comments"></i> <?php echo (int) $deal['total_comments']; ?></span>
                <span data-toggle="tooltip" title="<?php echo _l('emails', $deal['total_emails']); ?>"><i class="fa fa-envelope"></i> <?php echo (int) $deal['total_emails']; ?></span>
                <span data-toggle="tooltip" title="<?php echo _l('products', $deal['total_items']); ?>"><i class="fa fa-shopping-cart"></i> <?php echo (int) $deal['total_items']; ?></span>
            </div>

            <div class="deal-kanban-card__footer">
                <div>
                    <span class="deal-kanban-card__label">Source</span>
                    <strong><?php echo $deal['source_name'] ?: 'Unknown'; ?></strong>
                </div>
                <div class="text-right">
                    <span class="deal-kanban-card__label">Created</span>
                    <strong><?php echo time_ago($deal['created_at']); ?></strong>
                </div>
            </div>

            <?php if (!empty($deal['tags'])) { ?>
                <div class="deal-kanban-card__tags">
                    <?php echo render_tags($deal['tags']); ?>
                </div>
            <?php } ?>
        </div>
    </li>
<?php } ?>
