<?php init_head(); ?>
<link rel="stylesheet" id="deals-style" href="<?php echo module_dir_url(DEALS_MODULE); ?>assets/css/style.css">
<?php
$editingPolicy = $editing_policy ?? null;
?>
<div id="wrapper">
    <div class="content">
        <div class="deal-page-shell">
            <div class="deal-page-hero">
                <div>
                    <div class="deal-page-hero__eyebrow">Governance</div>
                    <h3 class="deal-page-hero__title">Approval Policies</h3>
                    <p class="deal-page-hero__description">Automate approval gates for high-value, high-risk, or stage-sensitive deals before revenue decisions are finalized.</p>
                </div>
                <div class="deal-page-hero__actions">
                    <a href="<?php echo admin_url('deals/diagnostics'); ?>" class="btn btn-default">Diagnostics</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="deal-panel">
                        <h4 class="deal-panel__title"><?php echo $editingPolicy ? 'Edit Approval Policy' : 'New Approval Policy'; ?></h4>
                        <?php echo form_open(admin_url('deals/save_approval_policy')); ?>
                        <?php echo form_hidden('policy_id', $editingPolicy['id'] ?? ''); ?>
                        <?php echo render_input('name', 'Policy Name', $editingPolicy['name'] ?? ''); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_select('trigger_event', [
                                    ['id' => 'deal_created', 'name' => 'Deal Created'],
                                    ['id' => 'deal_updated', 'name' => 'Deal Updated'],
                                    ['id' => 'stage_changed', 'name' => 'Stage Changed'],
                                    ['id' => 'status_won', 'name' => 'Before Mark Won'],
                                ], ['id', 'name'], 'Trigger Event', $editingPolicy['trigger_event'] ?? 'status_won'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('approval_type', [
                                    ['id' => 'close_won', 'name' => 'Close Won'],
                                    ['id' => 'discount_exception', 'name' => 'Discount Exception'],
                                    ['id' => 'forecast_commit', 'name' => 'Forecast Commit'],
                                    ['id' => 'legal_review', 'name' => 'Legal Review'],
                                    ['id' => 'custom', 'name' => 'Custom'],
                                ], ['id', 'name'], 'Approval Type', $editingPolicy['approval_type'] ?? 'close_won'); ?>
                            </div>
                        </div>
                        <?php echo render_input('title_template', 'Approval Title Template', $editingPolicy['title_template'] ?? 'Approve {deal_title} ({deal_value})'); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_select('assigned_to', $staff, ['staffid', ['firstname', 'lastname']], 'Approver', $editingPolicy['assigned_to'] ?? ''); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('priority', [
                                    ['id' => 'low', 'name' => 'Low'],
                                    ['id' => 'medium', 'name' => 'Medium'],
                                    ['id' => 'high', 'name' => 'High'],
                                ], ['id', 'name'], 'Priority', $editingPolicy['priority'] ?? 'high'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_select('pipeline_id', $pipelines, ['pipeline_id', 'pipeline_name'], 'Pipeline Scope', $editingPolicy['pipeline_id'] ?? '', ['data-none-selected-text' => 'All pipelines']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('stage_id', $stages, ['stage_id', 'stage_name'], 'Stage Scope', $editingPolicy['stage_id'] ?? '', ['data-none-selected-text' => 'Any stage']); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_input('min_deal_value', 'Min Deal Value', $editingPolicy['min_deal_value'] ?? '', 'number', ['step' => '0.01']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_select('health_status', [
                                    ['id' => 'on_track', 'name' => 'On Track'],
                                    ['id' => 'at_risk', 'name' => 'At Risk'],
                                    ['id' => 'off_track', 'name' => 'Off Track'],
                                ], ['id', 'name'], 'Health Scope', $editingPolicy['health_status'] ?? '', ['data-none-selected-text' => 'Any health']); ?>
                            </div>
                        </div>
                        <?php echo render_input('due_in_hours', 'Approval SLA (Hours)', $editingPolicy['due_in_hours'] ?? 24, 'number', ['min' => 1]); ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" id="auto_create" name="auto_create" value="1" <?php echo !isset($editingPolicy['auto_create']) || !empty($editingPolicy['auto_create']) ? 'checked' : ''; ?>>
                            <label for="auto_create">Auto-create approval requests when matched</label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo !isset($editingPolicy['is_active']) || !empty($editingPolicy['is_active']) ? 'checked' : ''; ?>>
                            <label for="is_active">Policy is active</label>
                        </div>
                        <div class="deal-form-actions">
                            <?php if ($editingPolicy) { ?>
                                <a href="<?php echo admin_url('deals/approval_policies'); ?>" class="btn btn-default">Cancel</a>
                            <?php } ?>
                            <button type="submit" class="btn btn-primary"><?php echo $editingPolicy ? 'Update Policy' : 'Save Policy'; ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="deal-panel">
                        <h4 class="deal-panel__title">Active Governance Rules</h4>
                        <div class="table-responsive">
                            <table class="table table-hover deal-data-table">
                                <thead>
                                <tr>
                                    <th>Policy</th>
                                    <th>Trigger</th>
                                    <th>Scope</th>
                                    <th>Approver</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($policies as $policy) { ?>
                                    <tr>
                                        <td>
                                            <div class="deal-table-title"><?php echo html_escape($policy['name']); ?></div>
                                            <div class="text-muted"><?php echo html_escape($policy['title_template']); ?></div>
                                        </td>
                                        <td>
                                            <span class="deal-pill"><?php echo ucwords(str_replace('_', ' ', $policy['trigger_event'])); ?></span>
                                            <div class="text-muted tw-mt-1"><?php echo ucwords(str_replace('_', ' ', $policy['approval_type'])); ?></div>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo $policy['pipeline_name'] ?: 'All pipelines'; ?><br>
                                            <?php echo $policy['stage_name'] ?: 'Any stage'; ?>
                                            <?php if ($policy['min_deal_value'] !== null && $policy['min_deal_value'] !== '') { ?>
                                                <br>Min <?php echo app_format_money((float) $policy['min_deal_value'], get_base_currency()); ?>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo $policy['approver_name'] ?: 'Unassigned'; ?></td>
                                        <td>
                                            <span class="deal-pill <?php echo !empty($policy['is_active']) ? 'deal-pill--status-open' : 'deal-pill--status-lost'; ?>">
                                                <?php echo !empty($policy['is_active']) ? 'Active' : 'Paused'; ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?php echo admin_url('deals/approval_policies?id=' . $policy['id']); ?>" class="btn btn-default btn-sm">Edit</a>
                                            <a href="<?php echo admin_url('deals/delete_approval_policy/' . $policy['id']); ?>" class="btn btn-danger btn-sm _delete">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($policies)) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No approval policies configured yet.</td>
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
