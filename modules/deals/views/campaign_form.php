<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?= isset($campaign) ? 'Edit Campaign' : 'New Campaign' ?></h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open(admin_url('deals/save_campaign/' . (!empty($campaign) ? $campaign->id : ''))); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_input('name', 'Name', $campaign->name ?? ''); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('channel', [
                                    ['id' => 'email', 'name' => 'Email'],
                                    ['id' => 'social', 'name' => 'Social'],
                                    ['id' => 'webinar', 'name' => 'Webinar'],
                                    ['id' => 'paid_ads', 'name' => 'Paid Ads'],
                                ], ['id', 'name'], 'Channel', $campaign->channel ?? 'email'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('status', [
                                    ['id' => 'draft', 'name' => 'Draft'],
                                    ['id' => 'scheduled', 'name' => 'Scheduled'],
                                    ['id' => 'running', 'name' => 'Running'],
                                    ['id' => 'completed', 'name' => 'Completed'],
                                ], ['id', 'name'], 'Status', $campaign->status ?? 'draft'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_input('audience_size', 'Audience Size', $campaign->audience_size ?? 0, 'number'); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('sent_count', 'Sent', $campaign->sent_count ?? 0, 'number'); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('open_count', 'Opens', $campaign->open_count ?? 0, 'number'); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('click_count', 'Clicks', $campaign->click_count ?? 0, 'number'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_input('budget', 'Budget', $campaign->budget ?? '0.00', 'number', ['step' => '0.01']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_datetime_input('started_at', 'Started At', !empty($campaign->started_at) ? $campaign->started_at : ''); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_datetime_input('ended_at', 'Ended At', !empty($campaign->ended_at) ? $campaign->ended_at : ''); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo render_textarea('notes', 'Notes', $campaign->notes ?? '', ['rows' => 6]); ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Save Campaign</button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
                <?php if (!empty($campaign)) { ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="panel_s">
                                <div class="panel-body">
                                    <h5 class="tw-mt-0 text-muted">Sent</h5>
                                    <div class="tw-text-2xl tw-font-semibold"><?= (int) ($campaign->sent_count ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="panel_s">
                                <div class="panel-body">
                                    <h5 class="tw-mt-0 text-muted">Opens</h5>
                                    <div class="tw-text-2xl tw-font-semibold"><?= (int) ($campaign->open_count ?? 0) ?></div>
                                    <div class="text-muted">
                                        <?= (int) ($campaign->sent_count ?? 0) > 0 ? round((((int) $campaign->open_count) / max((int) $campaign->sent_count, 1)) * 100, 1) . '% open rate' : 'No sends yet' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="panel_s">
                                <div class="panel-body">
                                    <h5 class="tw-mt-0 text-muted">Clicks</h5>
                                    <div class="tw-text-2xl tw-font-semibold"><?= (int) ($campaign->click_count ?? 0) ?></div>
                                    <div class="text-muted">
                                        <?= (int) ($campaign->sent_count ?? 0) > 0 ? round((((int) $campaign->click_count) / max((int) $campaign->sent_count, 1)) * 100, 1) . '% click rate' : 'No sends yet' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="tw-mt-0">Campaign Sequence</h4>
                            <?php echo form_open(admin_url('deals/save_campaign_step/' . $campaign->id)); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <?php echo render_input('step_name', 'Step Name', 'Step ' . ((isset($steps) ? count($steps) : 0) + 1)); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_input('delay_amount', 'Delay', 0, 'number'); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_select('delay_unit', [
                                        ['id' => 'minutes', 'name' => 'Minutes'],
                                        ['id' => 'hours', 'name' => 'Hours'],
                                        ['id' => 'days', 'name' => 'Days'],
                                    ], ['id', 'name'], 'Unit', 'days'); ?>
                                </div>
                                <div class="col-md-4">
                                    <?php echo render_select('send_to', [
                                        ['id' => 'primary_contact', 'name' => 'Primary Contact'],
                                        ['id' => 'deal_owner', 'name' => 'Deal Owner'],
                                    ], ['id', 'name'], 'Send To', 'primary_contact'); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('email_subject', 'Email Subject', 'Update on {deal_title}'); ?>
                                </div>
                                <div class="col-md-3">
                                    <?php echo render_select('step_type', [
                                        ['id' => 'email', 'name' => 'Email'],
                                        ['id' => 'add_activity', 'name' => 'Add Activity'],
                                    ], ['id', 'name'], 'Step Type', 'email'); ?>
                                </div>
                                <div class="col-md-3">
                                    <?php echo render_input('sort_order', 'Order', isset($steps) ? count($steps) + 1 : 1, 'number'); ?>
                                </div>
                                <div class="col-md-12">
                                    <?php echo render_textarea('email_body', 'Email Body', 'Hello, this is an automated update for {deal_title}.', ['rows' => 5]); ?>
                                </div>
                                <div class="col-md-12">
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                                        <label for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Add Step</button>
                            </div>
                            <?php echo form_close(); ?>

                            <?php if (!empty($steps)) { ?>
                                <hr />
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Delay</th>
                                            <th>Recipient</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($steps as $step) { ?>
                                            <tr>
                                                <td><?= (int) $step['sort_order'] ?></td>
                                                <td><?= $step['name'] ?></td>
                                                <td><?= ucfirst($step['step_type']) ?></td>
                                                <td><?= (int) $step['delay_amount'] . ' ' . ucfirst($step['delay_unit']) ?></td>
                                                <td><?= ucwords(str_replace('_', ' ', $step['send_to'])) ?></td>
                                                <td><?= !empty($step['is_active']) ? 'Active' : 'Paused' ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="tw-mt-0">Recent Deliveries</h4>
                            <?php if (!empty($messages)) { ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>Sent At</th>
                                            <th>Deal</th>
                                            <th>Step</th>
                                            <th>Recipient</th>
                                            <th>Status</th>
                                            <th>Opens</th>
                                            <th>Clicks</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($messages as $message) { ?>
                                            <tr>
                                                <td><?= !empty($message['delivered_at']) ? _dt($message['delivered_at']) : _dt($message['created_at']) ?></td>
                                                <td><?= $message['deal_title'] ?: ('#' . $message['deal_id']) ?></td>
                                                <td><?= $message['step_name'] ?: '-' ?></td>
                                                <td>
                                                    <?= $message['recipient_email'] ?>
                                                    <?php if (!empty($message['is_unsubscribed'])) { ?>
                                                        <span class="label label-warning mleft5">Unsubscribed</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?= ucfirst($message['status']) ?></td>
                                                <td>
                                                    <?= (int) $message['open_count'] ?>
                                                    <?php if (!empty($message['first_opened_at'])) { ?>
                                                        <span class="text-muted">at <?= _dt($message['first_opened_at']) ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?= (int) $message['click_count'] ?>
                                                    <?php if (!empty($message['first_clicked_at'])) { ?>
                                                        <span class="text-muted">at <?= _dt($message['first_clicked_at']) ?></span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <p class="text-muted mbot0">No campaign messages have been sent yet.</p>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
