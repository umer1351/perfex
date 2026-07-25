<?php
$sub_active = 1;
$calls_id = $this->uri->segment(6);
if ($calls_id) {
    $sub_active = 2;
    $call_info = get_deals_row('tbl_deal_calls', ['calls_id' => $calls_id]);
}
$edited = has_permission('deals', '', 'edit');
$id = !empty($call_info) ? $call_info->calls_id : null;
$all_calls_info = get_deals_result('tbl_deal_calls', ['module_field_id' => $deals_details->id]);
$all_calls_info = is_array($all_calls_info) ? $all_calls_info : [];
$callCount = count($all_calls_info);
$inboundCount = 0;
$outboundCount = 0;
$latestCallDate = null;

foreach ($all_calls_info as $callItem) {
    if (($callItem->call_type ?? '') === 'inbound') {
        $inboundCount++;
    } else {
        $outboundCount++;
    }

    if (!empty($callItem->date) && ($latestCallDate === null || strtotime($callItem->date) > strtotime($latestCallDate))) {
        $latestCallDate = $callItem->date;
    }
}
?>

<div class="deal-call-shell">
    <div class="deal-panel">
        <div class="deal-panel__header">
            <div>
                <h4 class="deal-panel__title mbot5">Calls</h4>
                <p class="text-muted mbot0"><?php echo $callCount; ?> logged call<?php echo $callCount === 1 ? '' : 's'; ?></p>
            </div>
            <?php if ($edited) { ?>
                <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#deal-call-modal">
                    <i class="fa fa-plus"></i> <?php echo _l('new_call'); ?>
                </a>
            <?php } ?>
        </div>

        <div class="deal-call-stats">
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Total</span>
                <strong><?php echo $callCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Inbound</span>
                <strong><?php echo $inboundCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Outbound</span>
                <strong><?php echo $outboundCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Last Call</span>
                <strong><?php echo !empty($latestCallDate) ? _dt($latestCallDate) : '-'; ?></strong>
            </div>
        </div>
        <div class="deal-activity-list">
            <?php if (empty($all_calls_info)) { ?>
                <div class="deal-empty-state">
                    <h5>No calls yet</h5>
                    <p>Log your first deal call to start tracking conversations here.</p>
                </div>
            <?php } else { ?>
                <div class="deal-call-grid">
                    <?php foreach ($all_calls_info as $v_calls) {
                        $user = $this->deals_model->check_deals_by(['staffid' => $v_calls->user_id], 'tblstaff');
                        $callType = $v_calls->call_type ?: 'outbound';
                        $outcome = $v_calls->outcome ?: 'other';
                        $contactName = !empty($deals_details->customers['name']) ? $deals_details->customers['name'] : '-';
                        $responsibleName = !empty($user) ? trim($user->firstname . ' ' . $user->lastname) : '-';
                        $summaryText = trim(strip_tags($v_calls->call_summary));
                        $summaryText = $summaryText !== '' ? $summaryText : 'No summary added.';
                        $summaryPreview = strlen($summaryText) > 180 ? substr($summaryText, 0, 177) . '...' : $summaryText;
                        ?>
                        <div class="deal-call-card">
                            <div class="deal-call-card__top">
                                <div>
                                    <div class="deal-call-card__date"><?php echo _dt($v_calls->date); ?></div>
                                    <div class="deal-chip-row">
                                        <span class="deal-pill deal-call-pill deal-call-pill--<?php echo html_escape($callType); ?>">
                                            <?php echo ucfirst($callType); ?>
                                        </span>
                                        <span class="deal-pill"><?php echo ucwords(str_replace('_', ' ', $outcome)); ?></span>
                                    </div>
                                </div>
                                <div class="deal-call-card__actions">
                                    <a href="<?php echo base_url('admin/deals/call_details/' . $v_calls->calls_id); ?>"
                                       class="btn btn-default btn-sm deal-card-action-btn" data-placement="top" data-toggle="modal"
                                       data-target="#myModal" title="<?php echo _l('details'); ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="<?php echo admin_url('deals/details/' . $deals_details->id . '/call/' . $v_calls->calls_id); ?>"
                                       class="btn btn-primary btn-sm deal-card-action-btn"
                                       title="<?php echo _l('edit'); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="<?php echo base_url('admin/deals/delete_deals_call/' . $deals_details->id . '/' . $v_calls->calls_id); ?>"
                                       class="btn btn-danger btn-sm deal-card-action-btn" title="<?php echo _l('delete'); ?>">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="deal-call-card__summary">
                                <?php echo html_escape($summaryPreview); ?>
                            </div>

                            <div class="deal-call-card__meta">
                                <div class="deal-call-card__meta-item">
                                    <span>Contact</span>
                                    <strong><?php echo html_escape($contactName); ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Responsible</span>
                                    <strong><?php echo html_escape($responsibleName); ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Duration</span>
                                    <strong><?php echo !empty($v_calls->duration) ? html_escape($v_calls->duration) : '-'; ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php if (!empty($edited)) { ?>
    <div class="modal fade" id="deal-call-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content deal-activity-modal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo $id ? _l('edit') . ' ' . _l('call') : _l('new_call'); ?></h4>
                </div>
                <div class="modal-body">
                    <?php echo form_open(base_url('admin/deals/saved_call/' . $deals_details->id . '/' . $id), ['id' => 'deals_calls_form', 'enctype' => 'multipart/form-data', 'data-parsley-validate' => '', 'role' => 'form']); ?>

                    <div class="row">
                        <div class="col-md-6 mtop15">
                            <label class="control-label"><?php echo _l('date'); ?><span class="text-danger"> *</span></label>
                            <div class="input-group">
                                <input type="text" required name="date" class="form-control datepicker" value="<?php
                                if (!empty($call_info->date)) {
                                    echo $call_info->date;
                                } else {
                                    echo date('Y-m-d');
                                }
                                ?>" data-date-format="<?php echo config_item('date_picker_format'); ?>">
                                <div class="input-group-addon">
                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mtop15">
                            <label class="control-label"><?php echo _l('call_type'); ?></label>
                            <select name="call_type" class="form-control select_box" style="width: 100%">
                                <option value="outbound" <?php echo !empty($call_info->call_type) && $call_info->call_type == 'outbound' ? 'selected' : ''; ?>><?php echo _l('outbound'); ?></option>
                                <option value="inbound" <?php echo !empty($call_info->call_type) && $call_info->call_type == 'inbound' ? 'selected' : ''; ?>><?php echo _l('inbound'); ?></option>
                            </select>
                        </div>

                        <div class="col-md-6 mtop15">
                            <label class="control-label"><?php echo _l('outcome'); ?><span class="text-danger">*</span></label>
                            <?php
                            $all_outcomes = [
                                'left_voice_message' => _l('left_voice_message'),
                                'moved_conversion_forward' => _l('moved_conversion_forward'),
                                'no_answer' => _l('no_answer'),
                                'not_interested' => _l('not_interested'),
                                'busy' => _l('busy'),
                                'wrong_number' => _l('wrong_number'),
                                'switched_off' => _l('switched_off'),
                                'call_back' => _l('call_back'),
                                'other' => _l('other'),
                            ];
                            ?>
                            <select name="outcome" class="form-control selectpicker" style="width: 100%">
                                <?php foreach ($all_outcomes as $key => $value) { ?>
                                    <option value="<?php echo $key; ?>" <?php echo !empty($call_info->outcome) && $call_info->outcome == $key ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6 mtop15">
                            <label class="control-label"><?php echo _l('call_duration'); ?></label>
                            <input type="text" name="duration" class="form-control" id="duration" placeholder="00:35:20"
                                   value="<?php echo !empty($call_info->duration) ? html_escape($call_info->duration) : ''; ?>">
                        </div>

                        <div class="col-md-6 mtop15">
                            <label class="control-label"><?php echo _l('contact'); ?></label>
                            <select name="client_id" class="form-control selectpicker" style="width: 100%">
                                <?php if (!empty($deals_details->customers)) { ?>
                                    <option value="<?php echo $deals_details->customers['id']; ?>" <?php echo !empty($call_info) && $call_info->client_id == $deals_details->customers['id'] ? 'selected' : ''; ?>>
                                        <?php echo $deals_details->customers['name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6 mtop15">
                            <label class="control-label"><?php echo _l('responsible'); ?><span class="text-danger"> *</span></label>
                            <select name="user_id" class="form-control selectpicker" style="width: 100%" required>
                                <option value=""><?php echo _l('responsible'); ?></option>
                                <?php if (!empty($staff)) {
                                    foreach ($staff as $v_user) { ?>
                                        <option value="<?php echo $v_user['staffid']; ?>" <?php echo !empty($call_info) && $call_info->user_id == $v_user['staffid'] ? 'selected' : ''; ?>>
                                            <?php echo $v_user['firstname'] . ' ' . $v_user['lastname']; ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>

                        <div class="col-md-12 mtop15">
                            <label class="control-label"><?php echo _l('call_summary'); ?><span class="text-danger"> *</span></label>
                            <textarea name="call_summary" class="form-control tinymce" rows="5"><?php echo !empty($call_info->call_summary) ? $call_info->call_summary : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="deal-form-actions">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo _l('updates'); ?></button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($id) { ?>
        <script>
            $(function () {
                $('#deal-call-modal').modal('show');
            });
        </script>
    <?php } ?>
<?php } ?>
