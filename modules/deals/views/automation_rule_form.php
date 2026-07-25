<?php init_head(); ?>
<?php
$actions = isset($rule) && !empty($rule->actions_json) ? json_decode($rule->actions_json, true) : [];
$action = !empty($actions) ? $actions[0] : [];
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?= isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule' ?></h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open(admin_url('deals/save_automation_rule/' . (!empty($rule) ? $rule->id : ''))); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_input('name', 'Rule Name', $rule->name ?? ''); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('trigger_type', [
                                    ['id' => 'deal_created', 'name' => 'Deal Created'],
                                    ['id' => 'deal_updated', 'name' => 'Deal Updated'],
                                    ['id' => 'stage_changed', 'name' => 'Stage Changed'],
                                    ['id' => 'status_changed', 'name' => 'Status Changed'],
                                    ['id' => 'follow_up_completed', 'name' => 'Follow-Up Completed'],
                                    ['id' => 'follow_up_overdue', 'name' => 'Follow-Up Overdue'],
                                    ['id' => 'inactivity_detected', 'name' => 'Inactivity Detected'],
                                ], ['id', 'name'], 'Trigger', $rule->trigger_type ?? 'deal_created'); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_select('pipeline_id', array_merge([['pipeline_id' => '', 'pipeline_name' => 'Any Pipeline']], $pipelines), ['pipeline_id', 'pipeline_name'], 'Pipeline', $rule->pipeline_id ?? ''); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_select('stage_id', array_merge([['stage_id' => '', 'stage_name' => 'Any Stage']], $stages), ['stage_id', 'stage_name'], 'Stage', $rule->stage_id ?? ''); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_select('deal_status', [
                                    ['id' => '', 'name' => 'Any Status'],
                                    ['id' => 'open', 'name' => 'Open'],
                                    ['id' => 'won', 'name' => 'Won'],
                                    ['id' => 'lost', 'name' => 'Lost'],
                                ], ['id', 'name'], 'Deal Status', $rule->deal_status ?? ''); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_input('offset_value', 'Trigger Offset', $rule->offset_value ?? 0, 'number'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('offset_unit', [
                                    ['id' => 'minutes', 'name' => 'Minutes'],
                                    ['id' => 'hours', 'name' => 'Hours'],
                                    ['id' => 'days', 'name' => 'Days'],
                                ], ['id', 'name'], 'Offset Unit', $rule->offset_unit ?? 'hours'); ?>
                            </div>
                        </div>

                        <hr />
                        <h5 class="tw-font-semibold">Action</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_select('action_type', [
                                    ['id' => 'create_followup', 'name' => 'Create Follow-Up'],
                                    ['id' => 'queue_email', 'name' => 'Send Email'],
                                    ['id' => 'add_activity', 'name' => 'Add Activity'],
                                    ['id' => 'update_health', 'name' => 'Update Health'],
                                ], ['id', 'name'], 'Action Type', $action['type'] ?? 'create_followup'); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_input('action_delay_value', 'Action Delay', $action['delay_value'] ?? 0, 'number'); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('action_delay_unit', [
                                    ['id' => 'minutes', 'name' => 'Minutes'],
                                    ['id' => 'hours', 'name' => 'Hours'],
                                    ['id' => 'days', 'name' => 'Days'],
                                ], ['id', 'name'], 'Action Delay Unit', $action['delay_unit'] ?? 'hours'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_input('action_subject', 'Follow-Up Subject', $action['subject'] ?? ''); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('followup_type', [
                                    ['id' => 'call', 'name' => 'Call'],
                                    ['id' => 'email', 'name' => 'Email'],
                                    ['id' => 'meeting', 'name' => 'Meeting'],
                                    ['id' => 'task', 'name' => 'Task'],
                                ], ['id', 'name'], 'Follow-Up Type', $action['follow_up_type'] ?? 'call'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('action_owner_id', $staff, ['staffid', ['firstname', 'lastname']], 'Action Owner', $action['owner_id'] ?? ''); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_input('action_email_subject', 'Email Subject', $action['email_subject'] ?? ''); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('action_send_to', [
                                    ['id' => 'primary_contact', 'name' => 'Primary Contact'],
                                    ['id' => 'deal_owner', 'name' => 'Deal Owner'],
                                ], ['id', 'name'], 'Send Email To', $action['send_to'] ?? 'primary_contact'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('action_health_status', [
                                    ['id' => 'on_track', 'name' => 'On Track'],
                                    ['id' => 'at_risk', 'name' => 'At Risk'],
                                    ['id' => 'blocked', 'name' => 'Blocked'],
                                ], ['id', 'name'], 'Health Update', $action['health_status'] ?? 'on_track'); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo render_textarea('action_description', 'Follow-Up Notes', $action['description'] ?? '', ['rows' => 3]); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo render_textarea('action_message', 'Activity Message', $action['message'] ?? '', ['rows' => 3]); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo render_textarea('action_email_body', 'Email Body', $action['email_body'] ?? '', ['rows' => 6]); ?>
                            </div>
                            <div class="col-md-12">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" <?= !isset($rule) || !empty($rule->is_active) ? 'checked' : '' ?>>
                                    <label for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="<?= admin_url('deals/automation') ?>" class="btn btn-default">Back</a>
                            <button type="submit" class="btn btn-primary">Save Rule</button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
