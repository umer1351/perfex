<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">Deals Automation</h4>
            </div>
            <div class="col-md-4 text-right">
                <a href="<?= admin_url('deals/process_automation') ?>" class="btn btn-default">Run Now</a>
                <a href="<?= admin_url('deals/automation_rule') ?>" class="btn btn-primary">New Rule</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Automation Rules</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Trigger</th>
                                    <th>Status</th>
                                    <th>Last Run</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($rules as $rule) { ?>
                                    <tr>
                                        <td><a href="<?= admin_url('deals/automation_rule/' . $rule['id']) ?>"><?= $rule['name'] ?></a></td>
                                        <td><?= ucwords(str_replace('_', ' ', $rule['trigger_type'])) ?></td>
                                        <td><?= !empty($rule['is_active']) ? 'Active' : 'Paused' ?></td>
                                        <td><?= !empty($rule['last_run_at']) ? _dt($rule['last_run_at']) : '-' ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Automation Queue</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Execute At</th>
                                    <th>Deal</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>Source</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($queue as $item) { ?>
                                    <tr>
                                        <td><?= _dt($item['execute_at']) ?></td>
                                        <td><?= $item['deal_title'] ?: ('#' . $item['deal_id']) ?></td>
                                        <td><?= ucwords(str_replace('_', ' ', $item['action_type'])) ?></td>
                                        <td><?= ucfirst($item['status']) ?></td>
                                        <td><?= $item['rule_name'] ?: ($item['campaign_name'] ?: '-') ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
