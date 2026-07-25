<?php
$probability = deals_probability_percentage($deals_details);
$all_stages = get_deals_order_by('tbl_deals_stages', ['pipeline_id' => $deals_details->pipeline_id], 'stage_order', 'asc');
$intelligence = $deal_intelligence ?? [];
$statusClass = $deals_details->status === 'won' ? 'won' : ($deals_details->status === 'lost' ? 'lost' : 'open');
$pendingApprovals = (int) ($intelligence['pending_approvals'] ?? 0);

$staffOptions = [];
foreach ($staff as $member) {
    $staffOptions[] = is_array($member) ? $member : [
        'staffid' => $member->staffid,
        'full_name' => trim(($member->firstname ?? '') . ' ' . ($member->lastname ?? '')),
    ];
}
?>

<div class="deal-shell">
    <div class="deal-hero">
        <div class="deal-hero__eyebrow">
            Deal #<?php echo $deals_details->id; ?> · <?php echo date('M j, Y', strtotime($deals_details->created_at)); ?>
        </div>
        <div class="deal-hero__title-row">
            <div>
                <h2 class="deal-hero__title"><?php echo html_escape($deals_details->title); ?></h2>
                <div class="deal-chip-row">
                    <span class="deal-pill deal-pill--status-<?php echo $statusClass; ?>"><?php echo ucfirst($deals_details->status); ?></span>
                    <span class="deal-pill deal-pill--priority-<?php echo strtolower($deals_details->priority ?? 'medium'); ?>"><?php echo ucfirst($deals_details->priority ?? 'medium'); ?></span>
                    <span class="deal-pill deal-pill--health-<?php echo strtolower($deals_details->health_status ?? 'on_track'); ?>"><?php echo ucwords(str_replace('_', ' ', $deals_details->health_status ?? 'on_track')); ?></span>
                    <?php if ($pendingApprovals > 0) { ?>
                        <span class="deal-pill deal-pill--approval"><?php echo $pendingApprovals; ?> Pending</span>
                    <?php } ?>
                </div>
            </div>
            <div class="deal-hero__actions">
                <?php if ($deals_details->status == 'won' || $deals_details->status == 'lost') { ?>
                    <a href="<?= admin_url('deals/changedStatus/' . $id . '/open') ?>" class="btn btn-warning btn-sm">
                        <i class="fa fa-repeat"></i> <?= _l('reopen') ?>
                    </a>
                <?php } ?>
                <?php if ($deals_details->status == 'open' || $deals_details->status == 'lost') { ?>
                    <a data-toggle="modal" data-target="#myModal" href="<?= admin_url('deals/changeStatus/' . $id . '/won') ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-check"></i> <?= _l('won') ?>
                    </a>
                <?php } ?>
                <?php if ($deals_details->status == 'open' || $deals_details->status == 'won') { ?>
                    <a data-toggle="modal" data-target="#myModal" href="<?= admin_url('deals/changeStatus/' . $id . '/lost') ?>" class="btn btn-danger btn-sm">
                        <i class="fa fa-times"></i> <?= _l('lost') ?>
                    </a>
                <?php } ?>
                <?php if (has_permission('tasks', '', 'create')) { ?>
                    <a href="#" onclick="new_task_from_relation(undefined,'deals',<?php echo $deals_details->id; ?>); return false;" class="btn btn-primary btn-sm">
                        <i class="fa-regular fa-plus"></i> <?php echo _l('new_task'); ?>
                    </a>
                <?php } ?>
                <a href="<?= admin_url('deals/details/' . $id . '/followups') ?>" class="btn btn-default btn-sm">
                    <i class="fa-regular fa-clock"></i> Follow-Ups
                </a>
                <a href="<?= admin_url('deals/details/' . $id . '/approvals') ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-shield-halved"></i> Approvals
                </a>
            </div>
        </div>

        <div class="deal-summary-grid deal-summary-grid--clean">
            <div class="deal-summary-card">
                <span class="deal-summary-card__label">Pipeline</span>
                <strong><?php echo $deals_details->pipeline_name ?: '-'; ?></strong>
                <span class="deal-summary-card__meta"><?php echo $deals_details->stage_name ?: 'Unassigned'; ?></span>
            </div>
            <div class="deal-summary-card">
                <span class="deal-summary-card__label">Owner</span>
                <strong><?php echo $deals_details->full_name ?: 'Unassigned'; ?></strong>
                <span class="deal-summary-card__meta">
                    <div class="dropdown inline-block">
                        <a href="#" class="dropdown-toggle text-muted" data-toggle="dropdown"><i class="fa-solid fa-chevron-down"></i></a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <?php foreach ($staffOptions as $staffMember) { ?>
                                <?php if ($deals_details->default_deal_owner != $staffMember['staffid']) { ?>
                                    <li>
                                        <a href="#" onclick="change_deal_owner(<?php echo $staffMember['staffid']; ?>,<?php echo $deals_details->id; ?>); return false;">
                                            <?php echo html_escape($staffMember['full_name']); ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                            <li><a href="#" onclick="change_deal_owner(0,<?php echo $deals_details->id; ?>); return false;"><?php echo _l('no_owner'); ?></a></li>
                        </ul>
                    </div>
                </span>
            </div>
            <div class="deal-summary-card">
                <span class="deal-summary-card__label">Deal Value</span>
                <strong><?php echo app_format_money((float) $deals_details->deal_value, get_base_currency()); ?></strong>
            </div>
            <div class="deal-summary-card">
                <span class="deal-summary-card__label">Next Follow-Up</span>
                <strong><?php echo !empty($deals_details->next_follow_up_at) ? _dt($deals_details->next_follow_up_at) : 'Not scheduled'; ?></strong>
            </div>
        </div>
    </div>

    <div class="deal-stage-rail">
        <?php
        $activeStageKey = array_search($deals_details->stage_id, array_column($all_stages, 'stage_id'));
        foreach ($all_stages as $index => $stage) {
            $stageClass = 'pending';
            if ($deals_details->status === 'lost') {
                $stageClass = 'lost';
            } elseif ($index < $activeStageKey) {
                $stageClass = 'completed';
            } elseif ($index == $activeStageKey) {
                $stageClass = 'current';
            }
            ?>
            <a href="<?= admin_url('deals/changeStage/' . $id . '/' . $stage->stage_id) ?>" class="deal-stage-rail__item deal-stage-rail__item--<?php echo $stageClass; ?>">
                <span class="deal-stage-rail__dot"><?php echo $index + 1; ?></span>
                <span class="deal-stage-rail__label"><?php echo html_escape($stage->stage_name); ?></span>
            </a>
        <?php } ?>
    </div>
</div>
<script>
    function change_deal_owner(staffId, dealId) {
        window.location.href = "<?= admin_url('deals/change_deal_owner/') ?>" + staffId + "/" + dealId;
    }
</script>
