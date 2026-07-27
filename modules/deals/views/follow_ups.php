<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">Deal Follow-Ups</h4>
            </div>
        </div>

        <?php echo form_open(admin_url('deals/follow_ups'), ['method' => 'get']); ?>
        <div class="row">
            <div class="col-md-3">
                <?php
                echo render_select('status', [
                    ['id' => '', 'name' => 'All statuses'],
                    ['id' => 'pending', 'name' => 'Pending'],
                    ['id' => 'completed', 'name' => 'Completed'],
                    ['id' => 'cancelled', 'name' => 'Cancelled'],
                ], ['id', 'name'], 'Status', $filters['status']);
                ?>
            </div>
            <div class="col-md-3">
                <?php
                $staff_options = [['staffid' => '', 'firstname' => 'All', 'lastname' => 'Owners']];
                foreach ($staff as $member) {
                    $staff_options[] = is_array($member) ? $member : [
                        'staffid' => $member->staffid,
                        'firstname' => $member->firstname,
                        'lastname' => $member->lastname,
                    ];
                }
                echo render_select('owner_id', $staff_options, ['staffid', ['firstname', 'lastname']], 'Owner', $filters['owner_id']);
                ?>
            </div>
            <div class="col-md-3">
                <?php
                echo render_select('deal_status', [
                    ['id' => '', 'name' => 'All deal statuses'],
                    ['id' => 'open', 'name' => 'Open'],
                    ['id' => 'won', 'name' => 'Won'],
                    ['id' => 'lost', 'name' => 'Lost'],
                ], ['id', 'name'], 'Deal Status', $filters['deal_status']);
                ?>
            </div>
            <div class="col-md-3">
                <div class="tw-mt-8">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="<?= admin_url('deals/follow_ups') ?>" class="btn btn-default">Reset</a>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>

        <div class="panel_s">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Due</th>
                            <th>Deal</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($followups as $followup) { ?>
                            <?php $isOverdue = $followup['status'] === 'pending' && strtotime($followup['follow_up_at']) < time(); ?>
                            <tr>
                                <td>
                                    <span class="<?= $isOverdue ? 'text-danger' : '' ?>"><?= _dt($followup['follow_up_at']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= admin_url('deals/details/' . $followup['deal_id'] . '/followups') ?>"><?= $followup['deal_title'] ?></a>
                                </td>
                                <td><?= $followup['subject'] ?></td>
                                <td><?= ucfirst($followup['type']) ?></td>
                                <td><?= $followup['owner_name'] ?: 'Unassigned' ?></td>
                                <td><?= ucfirst($followup['status']) ?></td>
                                <td class="text-right">
                                    <?php if ($followup['status'] === 'pending') { ?>
                                        <a class="btn btn-success btn-sm" href="<?= admin_url('deals/complete_follow_up/' . $followup['deal_id'] . '/' . $followup['id']) ?>">Complete</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
