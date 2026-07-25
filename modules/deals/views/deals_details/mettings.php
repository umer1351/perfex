<?php
$task_timer_id = $this->uri->segment(6);
if ($task_timer_id) {
    $mettings_details = get_deals_row('tbl_deals_mettings', ['mettings_id' => $task_timer_id]);
}
$edited = has_permission('deals', '', 'edit');
$id = !empty($mettings_details) ? $mettings_details->mettings_id : null;
$deals_id = $this->uri->segment(4);
$all_meetings = get_deals_result('tbl_deals_mettings', ['module' => 'deals', 'module_field_id' => $deals_details->id]);
$all_meetings = is_array($all_meetings) ? $all_meetings : [];
$meetingCount = count($all_meetings);
$upcomingCount = 0;
$completedCount = 0;
$latestMeetingDate = null;

foreach ($all_meetings as $meetingItem) {
    $endDate = !empty($meetingItem->end_date) ? strtotime($meetingItem->end_date) : null;
    if ($endDate && $endDate >= time()) {
        $upcomingCount++;
    } else {
        $completedCount++;
    }

    if (!empty($meetingItem->end_date) && ($latestMeetingDate === null || strtotime($meetingItem->end_date) > strtotime($latestMeetingDate))) {
        $latestMeetingDate = $meetingItem->end_date;
    }
}
?>

<div class="deal-call-shell">
    <div class="deal-panel">
        <div class="deal-panel__header">
            <div>
                <h4 class="deal-panel__title mbot5">Meetings</h4>
                <p class="text-muted mbot0"><?php echo $meetingCount; ?> scheduled meeting<?php echo $meetingCount === 1 ? '' : 's'; ?></p>
            </div>
            <?php if ($edited) { ?>
                <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#deal-meeting-modal">
                    <i class="fa fa-plus"></i> <?php echo _l('new_metting'); ?>
                </a>
            <?php } ?>
        </div>

        <div class="deal-call-stats">
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Total</span>
                <strong><?php echo $meetingCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Upcoming</span>
                <strong><?php echo $upcomingCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Completed</span>
                <strong><?php echo $completedCount; ?></strong>
            </div>
            <div class="deal-call-stat">
                <span class="deal-stat-card__label">Last Meeting</span>
                <strong><?php echo !empty($latestMeetingDate) ? _dt($latestMeetingDate) : '-'; ?></strong>
            </div>
        </div>
        <div class="deal-activity-list">
            <?php if (empty($all_meetings)) { ?>
                <div class="deal-empty-state">
                    <h5>No meetings yet</h5>
                    <p>Schedule a meeting to keep the deal timeline organized.</p>
                </div>
            <?php } else { ?>
                <div class="deal-meeting-grid">
                    <?php foreach ($all_meetings as $v_mettings) {
                        $responsibleName = $v_mettings->user_id ? get_staff_full_name($v_mettings->user_id) : '-';
                        $attendees = [];
                        if (!empty($v_mettings->attendees)) {
                            $unserialized = @unserialize($v_mettings->attendees);
                            if (is_array($unserialized) && !empty($unserialized['attendees']) && is_array($unserialized['attendees'])) {
                                $attendees = $unserialized['attendees'];
                            }
                        }
                        $attendeeCount = count($attendees);
                        $descriptionText = trim(strip_tags($v_mettings->description));
                        $descriptionText = $descriptionText !== '' ? $descriptionText : 'No meeting notes added.';
                        $descriptionPreview = strlen($descriptionText) > 180 ? substr($descriptionText, 0, 177) . '...' : $descriptionText;
                        $meetingState = !empty($v_mettings->end_date) && strtotime($v_mettings->end_date) < time() ? 'Completed' : 'Upcoming';
                        $meetingStateClass = $meetingState === 'Completed' ? 'outbound' : 'inbound';
                        ?>
                        <div class="deal-meeting-card">
                            <div class="deal-call-card__top">
                                <div>
                                    <div class="deal-call-card__date"><?php echo html_escape($v_mettings->meeting_subject); ?></div>
                                    <div class="deal-chip-row">
                                        <span class="deal-pill deal-call-pill deal-call-pill--<?php echo $meetingStateClass; ?>">
                                            <?php echo $meetingState; ?>
                                        </span>
                                        <span class="deal-pill"><?php echo !empty($v_mettings->location) ? html_escape($v_mettings->location) : _l('location'); ?></span>
                                    </div>
                                </div>
                                <div class="deal-call-card__actions">
                                    <a href="<?php echo base_url('admin/deals/meeting_details/' . $v_mettings->mettings_id); ?>"
                                       class="btn btn-default btn-sm deal-card-action-btn" data-placement="top" data-toggle="modal"
                                       data-target="#myModal" title="<?php echo _l('details'); ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="<?php echo admin_url('deals/details/' . $deals_details->id . '/mettings/' . $v_mettings->mettings_id); ?>"
                                       class="btn btn-primary btn-sm deal-card-action-btn"
                                       title="<?php echo _l('edit'); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="<?php echo base_url('admin/deals/delete_deals_mettings/' . $deals_details->id . '/' . $v_mettings->mettings_id); ?>"
                                       class="btn btn-danger btn-sm deal-card-action-btn" title="<?php echo _l('delete'); ?>">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="deal-call-card__summary">
                                <?php echo html_escape($descriptionPreview); ?>
                            </div>

                            <div class="deal-call-card__meta">
                                <div class="deal-call-card__meta-item">
                                    <span>Starts</span>
                                    <strong><?php echo !empty($v_mettings->start_date) ? _dt($v_mettings->start_date) : '-'; ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Ends</span>
                                    <strong><?php echo !empty($v_mettings->end_date) ? _dt($v_mettings->end_date) : '-'; ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Responsible</span>
                                    <strong><?php echo html_escape($responsibleName); ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Location</span>
                                    <strong><?php echo !empty($v_mettings->location) ? html_escape($v_mettings->location) : '-'; ?></strong>
                                </div>
                                <div class="deal-call-card__meta-item">
                                    <span>Attendees</span>
                                    <strong><?php echo $attendeeCount; ?></strong>
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
    <div class="modal fade" id="deal-meeting-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content deal-activity-modal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo $id ? _l('edit') . ' ' . _l('new_metting') : _l('new_metting'); ?></h4>
                </div>
                <div class="modal-body">
                    <?php echo form_open(base_url('admin/deals/saved_metting/' . $id), ['id' => 'deals_calls_form', 'enctype' => 'multipart/form-data', 'data-parsley-validate' => '', 'role' => 'form']); ?>
                    <input type="hidden" name="deals_id" value="<?php echo $deals_id; ?>" class="form-control">

                    <div class="row">
                        <div class="col-md-6 mtop15">
                            <label for="meeting_subject" class="control-label"><?php echo _l('metting_subject'); ?></label>
                            <input type="text" required name="meeting_subject" class="form-control" value="<?php echo !empty($mettings_details->meeting_subject) ? html_escape($mettings_details->meeting_subject) : ''; ?>">
                        </div>

                        <div class="col-md-6 mtop15">
                            <label for="start_date" class="control-label"><?php echo _l('start_date_time'); ?></label>
                            <div class="input-group">
                                <input type="text" required name="start_date" class="form-control datetimepicker"
                                       value="<?php
                                       if (!empty($mettings_details->start_date)) {
                                           echo date('Y-m-d H:i:s', strtotime($mettings_details->start_date));
                                       } else {
                                           echo date('Y-m-d H:i:s');
                                       }
                                       ?>" data-date-format="yyyy-mm-dd hh:ii:ss">
                                <div class="input-group-addon">
                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mtop15">
                            <label for="end_date" class="control-label"><?php echo _l('end_date_time'); ?></label>
                            <div class="input-group">
                                <input type="text" required name="end_date" class="form-control datetimepicker"
                                       value="<?php
                                       if (!empty($mettings_details->end_date)) {
                                           echo date('Y-m-d H:i:s', strtotime($mettings_details->end_date));
                                       } else {
                                           echo date('Y-m-d H:i:s');
                                       }
                                       ?>" data-date-format="yyyy-mm-dd hh:ii:ss">
                                <div class="input-group-addon">
                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mtop15">
                            <label for="attendees" class="control-label"><?php echo _l('attend_person'); ?></label>
                            <select multiple="multiple" name="attendees[]" data-width="100%" class="selectpicker" required>
                                <option value=""><?php echo _l('select') . _l('attendess'); ?></option>
                                <?php
                                $all_user_attendees = $this->db->get('tblstaff')->result();
                                if (!empty($all_user_attendees)) {
                                    foreach ($all_user_attendees as $v_user_attendees) {
                                        $selectedAttendee = '';
                                        if (!empty($mettings_details->attendees)) {
                                            $staffid = @unserialize($mettings_details->attendees);
                                            if (is_array($staffid) && !empty($staffid['attendees']) && is_array($staffid['attendees'])) {
                                                foreach ($staffid['attendees'] as $assding_id) {
                                                    if ((int) $v_user_attendees->staffid === (int) $assding_id) {
                                                        $selectedAttendee = 'selected';
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                        <option value="<?php echo $v_user_attendees->staffid; ?>" <?php echo $selectedAttendee; ?>>
                                            <?php echo $v_user_attendees->firstname . ' ' . $v_user_attendees->lastname; ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>

                        <div class="col-md-6 mtop15">
                            <label for="user_id" class="control-label"><?php echo _l('responsible'); ?></label>
                            <select name="user_id" class="form-control select_box selectpicker" style="width: 100%" required>
                                <option value=""><?php echo _l('admin_staff'); ?></option>
                                <?php
                                $responsible_user_info = $this->db->where(['role !=' => '2'])->get('tblstaff')->result();
                                if (!empty($responsible_user_info)) {
                                    foreach ($responsible_user_info as $v_responsible_user) { ?>
                                        <option value="<?php echo $v_responsible_user->staffid; ?>" <?php echo !empty($mettings_details) && $mettings_details->user_id == $v_responsible_user->staffid ? 'selected' : ''; ?>>
                                            <?php echo $v_responsible_user->firstname . ' ' . $v_responsible_user->lastname; ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>

                        <div class="col-md-6 mtop15">
                            <label for="location" class="control-label"><?php echo _l('location'); ?></label>
                            <input type="text" required name="location" class="form-control" value="<?php echo !empty($mettings_details->location) ? html_escape($mettings_details->location) : ''; ?>">
                        </div>

                        <div class="col-md-12 mtop15">
                            <label class="control-label"><?php echo _l('description'); ?><span class="text-danger"> *</span></label>
                            <textarea name="description" class="form-control tinymce" rows="5"><?php echo !empty($mettings_details->description) ? $mettings_details->description : ''; ?></textarea>
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
                $('#deal-meeting-modal').modal('show');
            });
        </script>
    <?php } ?>
<?php } ?>
