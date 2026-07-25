<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">Deal Campaigns</h4>
            </div>
            <div class="col-md-4 text-right">
                <a href="<?= admin_url('deals/campaign') ?>" class="btn btn-primary">New Campaign</a>
            </div>
        </div>

        <div class="panel_s">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Audience</th>
                            <th>Sent</th>
                            <th>Opens</th>
                            <th>Clicks</th>
                            <th>Deals</th>
                            <th>Pipeline</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($campaigns as $campaign) { ?>
                            <tr>
                                <td><?= $campaign['name'] ?></td>
                                <td><?= ucfirst($campaign['channel']) ?></td>
                                <td><?= ucfirst($campaign['status']) ?></td>
                                <td><?= (int) $campaign['audience_size'] ?></td>
                                <td><?= (int) $campaign['sent_count'] ?></td>
                                <td>
                                    <?= (int) $campaign['open_count'] ?>
                                    <?php if ((int) $campaign['sent_count'] > 0) { ?>
                                        <span class="text-muted">(<?= round(((int) $campaign['open_count'] / max((int) $campaign['sent_count'], 1)) * 100, 1) ?>%)</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?= (int) $campaign['click_count'] ?>
                                    <?php if ((int) $campaign['sent_count'] > 0) { ?>
                                        <span class="text-muted">(<?= round(((int) $campaign['click_count'] / max((int) $campaign['sent_count'], 1)) * 100, 1) ?>%)</span>
                                    <?php } ?>
                                </td>
                                <td><?= (int) $campaign['attributed_deals'] ?></td>
                                <td><?= app_format_money($campaign['attributed_value'], get_base_currency()) ?></td>
                                <td class="text-right">
                                    <a href="<?= admin_url('deals/campaign/' . $campaign['id']) ?>" class="btn btn-default btn-sm">Open</a>
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
