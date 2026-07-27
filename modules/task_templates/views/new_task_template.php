<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open_multipart(admin_url('task_templates/task/' . $id), array('id' => 'task-form')); ?>
<div class="modal fade<?php if (isset($task)) {
    echo ' edit';
} ?>" id="_task_modal" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel"<?php if ($this->input->get('opened_from_lead_id')) {
    echo 'data-lead-id=' . $this->input->get('opened_from_lead_id');
} ?>>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo html_escape($title); ?>
                </h4>
                <?php if(isset($project_template)){ ?>
                <small><?php echo _l('tt_related_to', [$rel_type, $project_template->name]); ?></small>
                    <input type="hidden" name="rel_type" value="<?php echo html_escape($rel_type); ?>">
                    <input type="hidden" name="rel_id" value="<?php echo html_escape($rel_id); ?>">
                <?php } ?>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="checkbox checkbox-primary no-mtop checkbox-inline task-add-edit-public">
                            <input type="checkbox" id="task_is_public" name="is_public" <?php if (isset($task)) {
                                if ($task->is_public == 1) {
                                    echo 'checked';
                                }
                            }; ?>>
                            <label for="task_is_public" data-toggle="tooltip" data-placement="bottom"
                                   title="<?php echo _l('task_public_help'); ?>"><?php echo _l('task_public'); ?></label>
                        </div>
                        <div class="checkbox checkbox-primary checkbox-inline task-add-edit-billable">
                            <input type="checkbox" id="task_is_billable" name="billable"
                                <?php if ((isset($task) && $task->billable == 1) || (!isset($task) && get_option('task_biillable_checked_on_creation') == 1)) {
                                    echo ' checked';
                                } ?>>
                            <label for="task_is_billable"><?php echo _l('task_billable'); ?></label>
                        </div>
                            <a href="#" class="pull-right"
                               onclick="slideToggle('#new-task-attachments'); return false;">
                                <?php echo _l('attach_files'); ?>
                            </a>
                            <div id="new-task-attachments" class="hide">
                                <hr/>
                                <div class="row attachments">
                                    <div class="attachment">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="attachment"
                                                       class="control-label"><?php echo _l('add_task_attachments'); ?></label>
                                                <div class="input-group">
                                                    <input type="file"
                                                           extension="<?php echo str_replace('.', '', get_option('allowed_files')); ?>"
                                                           filesize="<?php echo file_upload_max_size(); ?>"
                                                           class="form-control" name="attachments[0]">
                                                    <span class="input-group-btn">
                                 <button class="btn btn-success add_more_attachments p8" type="button"><i
                                             class="fa fa-plus"></i></button>
                                 </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if ($this->input->get('ticket_to_task')) {
                                echo form_hidden('ticket_to_task');
                            }
                        ?>
                        <hr>
                        <?php $value = (isset($task) ? $task->name : ''); ?>
                        <?php echo render_input('name', 'task_add_edit_subject', $value); ?>
                        <div class="task-hours<?php if (isset($rel_type) && $rel_type == 'project' && total_rows(PROJECT_TEMPLATES_TABLE_NAME, ['id' => $rel_id, 'billing_type' => 3]) == 0) {
                            echo ' hide';
                        } ?>">
                            <?php $value = (isset($task) ? $task->hourly_rate : 0); ?>
                            <?php echo render_input('hourly_rate','task_hourly_rate',$value); ?>
                        </div>
                        <div class="project-details<?php if ($rel_type != 'project') { echo ' hide';} ?>">
                            <div class="form-group">
                                <label for="milestone"><?php echo _l('task_milestone'); ?></label>
                                <select name="milestone" id="milestone" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""></option>
                                    <?php foreach ($milestones as $milestone) { ?>
                                        <option value="<?php echo $milestone['id']; ?>" <?php if (isset($milestone_id) && $milestone_id == $milestone['id']) {
                                            echo 'selected';
                                        } ?>><?php echo $milestone['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <?php if(isset($rel_type) && $rel_type == "project"){ ?>
                                <div class="col-md-12">
                                    <div class="mbot15 inline-block task-template-duration"> <?php echo _l('tt_task_start'); ?>: </div>
                                    <div class="radio radio-primary mbot15 radio-inline">
                                        <input type="radio" id="start_type_date" name="start_type" value="project" <?php if (!isset($task) || $task->start_type == "project") {
                                            echo 'checked';
                                        }; ?>>
                                        <label for="start_type_date"><?php echo _l('tt_start_type_date'); ?></label>
                                    </div>
                                    <div class="radio radio-primary mbot15 radio-inline start_type_milestone_wrapper hide">
                                        <input type="radio" id="start_type_milestone" name="start_type" value="milestone" <?php if (isset($task) && $task->start_type == "milestone") {
                                            echo 'checked';
                                        }; ?>>
                                        <label for="start_type_milestone"><?php echo _l('tt_start_type_milestone'); ?></label>
                                    </div>
                                    <div class="radio radio-primary mbot15 radio-inline">
                                        <input type="radio" id="start_type_task" name="start_type" value="task" <?php if (isset($task) && $task->after_task_id > 0) {
                                            echo 'checked';
                                        }; ?>>
                                        <label for="start_type_task"><?php echo _l('tt_start_type_task'); ?></label>
                                    </div>
                                </div>
                            <div class="col-md-12">
                                <?php
                                $value = (isset($task) ? $task->startdate : '');
                                $type_value = (isset($task) ? $task->startdate_type : 'day');
                                ?>
                                <div class="form-group <?php echo (isset($task) && $task->after_task_id > 0) ? 'hide' : ''; ?>" app-field-wrapper="startdate">
                                    <label for="startdate" class="control-label hide project_start">
                                        <?php echo _l('tt_start_date_project'); ?></label>
                                    <label for="startdate" class="control-label hide milestone_start">
                                        <?php echo _l('tt_start_date_milestone'); ?></label>
                                    <div class="input-group">
                                        <input type="text" id="startdate" name="startdate" class="form-control" value="<?php echo $value; ?>">

                                        <div class="input-group-btn dropdown-input-group">
                                            <input type="hidden" name="startdate_type" value="<?php echo $type_value; ?>">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="dropdown-display-text"><?php echo _l('tt_'.$type_value); ?></span> <span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li class="<?php if($type_value == "day") echo 'hide'; ?>"><a href="#"><?php echo _l('tt_day'); ?></a></li>
                                                <li class="<?php if($type_value == "week") echo 'hide'; ?>"><a href="#"><?php echo _l('tt_week'); ?></a></li>
                                                <li class="<?php if($type_value == "month") echo 'hide'; ?>"><a href="#"><?php echo _l('tt_month'); ?></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $value = (isset($task) ? $task->after_task_id : '');
                                ?>
                                <?php echo render_select('after_task_id',$other_tasks, ["id", "name"], 'tt_start_task', $value, [], [], (!isset($task) || empty($task->after_task_id)) ? 'hide' : ''); ?>
                            </div>
                            <?php } ?>
                            <div class="col-md-12">
                                <div class="form-group" app-field-wrapper="duration_value">
                                    <label for="duration_value" class="control-label">
                                        <?php echo _l('tt_duration_text'); ?></label>
                                    <?php
                                    $value = (isset($task) ? $task->duration_value : '');
                                    $type_value = (isset($task) ? $task->duration_type : 'day');
                                    ?>
                                    <div class="input-group">
                                        <input type="text" id="duration_value" name="duration_value" class="form-control" value="<?php echo $value; ?>">
                                        <div class="input-group-btn dropdown-input-group">
                                            <input type="hidden" name="duration_type" value="<?php echo $type_value; ?>">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="dropdown-display-text"><?php echo _l('tt_'.$type_value); ?></span> <span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li class="<?php if($type_value == "day") echo 'hide'; ?>"><a href="#"><?php echo _l('tt_day'); ?></a></li>
                                                <li class="<?php if($type_value == "week") echo 'hide'; ?>"><a href="#"><?php echo _l('tt_week'); ?></a></li>
                                                <li class="<?php if($type_value == "month") echo 'hide'; ?>"><a href="#"><?php echo _l('tt_month'); ?></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority"
                                           class="control-label"><?php echo _l('task_add_edit_priority'); ?></label>
                                    <select name="priority" class="selectpicker" id="priority" data-width="100%"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <?php foreach (get_tasks_priorities() as $priority) { ?>
                                            <option value="<?php echo html_escape($priority['id']); ?>"<?php if (isset($task) && $task->priority == $priority['id'] || !isset($task) && get_option('default_task_priority') == $priority['id']) {
                                                echo ' selected';
                                            } ?>><?php echo html_escape($priority['name']); ?></option>
                                        <?php } ?>
                                        <?php hooks()->do_action('task_priorities_select', (isset($task) ? $task : 0)); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="repeat_every"
                                           class="control-label"><?php echo _l('task_repeat_every'); ?></label>
                                    <select name="repeat_every" id="repeat_every" class="selectpicker" data-width="100%"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <option value="1-week" <?php if (isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'week') {
                                            echo 'selected';
                                        } ?>><?php echo _l('week'); ?></option>
                                        <option value="2-week" <?php if (isset($task) && $task->repeat_every == 2 && $task->recurring_type == 'week') {
                                            echo 'selected';
                                        } ?>>2 <?php echo _l('weeks'); ?></option>
                                        <option value="1-month" <?php if (isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'month') {
                                            echo 'selected';
                                        } ?>>1 <?php echo _l('month'); ?></option>
                                        <option value="2-month" <?php if (isset($task) && $task->repeat_every == 2 && $task->recurring_type == 'month') {
                                            echo 'selected';
                                        } ?>>2 <?php echo _l('months'); ?></option>
                                        <option value="3-month" <?php if (isset($task) && $task->repeat_every == 3 && $task->recurring_type == 'month') {
                                            echo 'selected';
                                        } ?>>3 <?php echo _l('months'); ?></option>
                                        <option value="6-month" <?php if (isset($task) && $task->repeat_every == 6 && $task->recurring_type == 'month') {
                                            echo 'selected';
                                        } ?>>6 <?php echo _l('months'); ?></option>
                                        <option value="1-year" <?php if (isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'year') {
                                            echo 'selected';
                                        } ?>>1 <?php echo _l('year'); ?></option>
                                        <option value="custom" <?php if (isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'custom') {
                                            echo 'selected';
                                        } ?>><?php echo _l('recurring_custom'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="recurring_custom <?php if ((isset($task) && $task->repeat_every == '') || (!isset($task))) {
                            echo 'hide';
                        } ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php $value = (isset($task) && $task->repeat_every_custom == 1 ? $task->repeat_every_custom : 1); ?>
                                    <?php echo render_input('repeat_every_custom', '', $value, 'number', array('min' => 1)); ?>
                                </div>
                                <div class="col-md-6">
                                    <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker"
                                            data-width="100%"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value="day" <?php if (isset($task) && $task->repeat_type_custom == 'day') {
                                            echo 'selected';
                                        } ?>><?php echo _l('task_recurring_days'); ?></option>
                                        <option value="week" <?php if (isset($task) && $task->repeat_type_custom == 'week') {
                                            echo 'selected';
                                        } ?>><?php echo _l('task_recurring_weeks'); ?></option>
                                        <option value="month" <?php if (isset($task) && $task->repeat_type_custom == 'month') {
                                            echo 'selected';
                                        } ?>><?php echo _l('task_recurring_months'); ?></option>
                                        <option value="year" <?php if (isset($task) && $task->repeat_type_custom == 'year') {
                                            echo 'selected';
                                        } ?>><?php echo _l('task_recurring_years'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="cycles_wrapper"
                             class="<?php if (!isset($task) || (isset($task) && $task->repeat_every == '')) {
                                 echo ' hide';
                             } ?>">
                            <?php $value = (isset($task) ? $task->cycles : 0); ?>
                            <div class="form-group recurring-cycles">
                                <label for="cycles"><?php echo _l('recurring_total_cycles'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control"<?php if ($value == 0) {
                                        echo ' disabled';
                                    } ?> name="cycles" id="cycles"
                                           value="<?php echo html_escape($value); ?>">
                                    <div class="input-group-addon">
                                        <div class="checkbox">
                                            <input type="checkbox"<?php if ($value == 0) {
                                                echo ' checked';
                                            } ?> id="unlimited_cycles">
                                            <label for="unlimited_cycles"><?php echo _l('cycles_infinity'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                            echo render_select('staff[]',$assignees,array('staffid',array('firstname','lastname')),'tt_task_assignees',$assignees_ids,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false); ?>
                        </div>
                        <div class="form-group">
                            <?php
                            echo render_select('followers[]',$staff,array('staffid',array('firstname','lastname')),'tt_task_followers',$followers_ids,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false); ?>
                        </div>
                        <div class="form-group <?php if (count($checklistTemplates) == 0) { echo ' hide'; } ?>">
                            <label for="checklist_items"><?php echo _l('insert_checklist_templates'); ?></label>
                            <select id="checklist_items" name="checklist_items[]"
                                    class="selectpicker checklist-items-template-select" multiple="1"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex') ?>"
                                    data-width="100%" data-live-search="true" data-actions-box="true">
                                <option value="" class="hide"></option>
                                <?php foreach ($checklistTemplates as $chkTemplate) { ?>
                                    <option value="<?php echo html_escape($chkTemplate['id']); ?>" <?php if(isset($task) && in_array($chkTemplate['id'], $task->checklist_items)) echo 'selected'; ?>>
                                        <?php echo html_escape($chkTemplate['description']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <div id="inputTagsWrapper">
                                <label for="tags" class="control-label"><i class="fa fa-tag"
                                                                           aria-hidden="true"></i> <?php echo _l('tags'); ?>
                                </label>
                                <input type="text" class="tagsinput" id="tags" name="tags"
                                       value="<?php echo(isset($task) ? prep_tags_input(get_tags_in($task->id, 'task_templates')) : ''); ?>"
                                       data-role="tagsinput">
                            </div>
                        </div>
                        <?php echo render_custom_fields('tasks'); ?>
                        <hr/>
                        <p class="bold"><?php echo _l('task_add_edit_description'); ?></p>
                        <?php
                        // onclick and onfocus used for convert ticket to task too
                        echo render_textarea('description', '', (isset($task) ? $task->description : ''), array('rows' => 6, 'placeholder' => _l('task_add_description'), 'data-task-ae-editor' => true, !is_mobile() ? 'onclick' : 'onfocus' => (!isset($task) || isset($task) && $task->description == '' ? 'init_editor(\'.tinymce-task\', {height:200, auto_focus: true});' : '')), array(), 'no-mbot', 'tinymce-task'); ?>


                        <?php if(isset($task) && count($task->attachments) > 0){ ?>
                            <div class="row task_attachments_wrapper">
                                <div class="col-md-12" id="attachments">
                                    <hr />
                                    <h4 class="th font-medium mbot15"><?php echo _l('task_view_attachments'); ?></h4>
                                    <div class="row">
                                        <?php
                                        $i = 1;
                                        // Store all url related data here
                                        $comments_attachments = array();
                                        $attachments_data = array();
                                        $show_more_link_task_attachments = hooks()->apply_filters('show_more_link_task_attachments', 2);
                                        foreach($task->attachments as $attachment){ ?>
                                            <?php ob_start(); ?>
                                            <div data-num="<?php echo html_escape($i); ?>" data-comment-attachment="<?php echo html_escape($attachment['task_comment_id']); ?>" data-task-attachment-id="<?php echo html_escape($attachment['id']); ?>" class="task-attachment-col col-md-6<?php if($i > $show_more_link_task_attachments){echo ' hide task-attachment-col-more';} ?>">
                                                <ul class="list-unstyled task-attachment-wrapper" data-placement="right" data-toggle="tooltip" data-title="<?php echo html_escape($attachment['file_name']); ?>" >
                                                    <li class="mbot10 task-attachment<?php if(strtotime($attachment['dateadded']) >= strtotime('-16 hours')){echo ' highlight-bg'; } ?>">
                                                        <div class="mbot10 pull-right task-attachment-user">
                                                            <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                                                                <a href="#" class="pull-right" onclick="remove_task_template_attachment(this,<?php echo html_escape($attachment['id']); ?>); return false;">
                                                                    <i class="fa fa fa-times"></i>
                                                                </a>
                                                            <?php }
                                                            $externalPreview = false;
                                                            $is_image = false;
                                                            $path = TASK_TEMPLATES_ATTACHMENTS_FOLDER . $task->id . '/'. $attachment['file_name'];
                                                            $href_url = site_url('download/file/taskattachment/'. $attachment['attachment_key']);
                                                            $isHtml5Video = is_html5_video($path);
                                                            if(empty($attachment['external'])){
                                                                $is_image = is_image($path);
                                                                $img_url = site_url('download/preview_image?path='.protected_file_url_by_path($path,true).'&type='.$attachment['filetype']);
                                                            } else if((!empty($attachment['thumbnail_link']) || !empty($attachment['external']))
                                                                && !empty($attachment['thumbnail_link'])){
                                                                $is_image = true;
                                                                $img_url = optimize_dropbox_thumbnail($attachment['thumbnail_link']);
                                                                $externalPreview = $img_url;
                                                                $href_url = $attachment['external_link'];
                                                            } else if(!empty($attachment['external']) && empty($attachment['thumbnail_link'])) {
                                                                $href_url = $attachment['external_link'];
                                                            }
                                                            ?>
                                                            <?php
                                                            if($attachment['staffid'] != 0){
                                                                echo '<a href="'.admin_url('profile/'.$attachment['staffid']).'" target="_blank">'.get_staff_full_name($attachment['staffid']) .'</a> - ';
                                                            } else if($attachment['contact_id'] != 0) {
                                                                echo '<a href="'.admin_url('clients/client/'.get_user_id_by_contact_id($attachment['contact_id']).'?contactid='.$attachment['contact_id']).'" target="_blank">'.get_contact_full_name($attachment['contact_id']) .'</a> - ';
                                                            }
                                                            echo '<span class="text-has-action" data-toggle="tooltip" data-title="'._dt($attachment['dateadded']).'">'.time_ago($attachment['dateadded']).'</span>';
                                                            ?>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="<?php if($is_image){echo 'preview-image';}else if(!$isHtml5Video){echo 'task-attachment-no-preview';} ?>">
                                                            <?php
                                                            // Not link on video previews because on click on the video is opening new tab
                                                            if(!$isHtml5Video){ ?>
                                                            <a href="<?php echo (!$externalPreview ? $href_url : $externalPreview); ?>" target="_blank"<?php if($is_image){ ?> data-lightbox="task-attachment"<?php } ?> class="<?php if($isHtml5Video){echo 'video-preview';} ?>">
                                                                <?php } ?>
                                                                <?php if($is_image){ ?>
                                                                    <img src="<?php echo html_escape($img_url); ?>" class="img img-responsive">
                                                                <?php } else if($isHtml5Video) { ?>
                                                                    <video width="100%" height="100%" src="<?php echo site_url('download/preview_video?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>" controls>
                                                                        Your browser does not support the video tag.
                                                                    </video>
                                                                <?php } else { ?>
                                                                    <i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i>
                                                                    <?php echo html_escape($attachment['file_name']); ?>
                                                                <?php } ?>
                                                                <?php if(!$isHtml5Video){ ?>
                                                            </a>
                                                        <?php } ?>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php
                                            $attachments_data[$attachment['id']] = ob_get_contents();
                                            if($attachment['task_comment_id'] != 0) {
                                                $comments_attachments[$attachment['task_comment_id']][$attachment['id']] = $attachments_data[$attachment['id']];
                                            }
                                            ob_end_clean();
                                            print( $attachments_data[$attachment['id']]);
                                            ?>
                                            <?php
                                            $i++;
                                        } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <?php if(($i - 1) > $show_more_link_task_attachments){ ?>
                                    <div class="col-md-12" id="show-more-less-task-attachments-col">
                                        <a href="#" class="task-attachments-more" onclick="slideToggle('.task_attachments_wrapper .task-attachment-col-more', task_attachments_toggle); return false;"><?php echo _l('show_more'); ?></a>
                                        <a href="#" class="task-attachments-less hide" onclick="slideToggle('.task_attachments_wrapper .task-attachment-col-more', task_attachments_toggle); return false;"><?php echo _l('show_less'); ?></a>
                                    </div>
                                <?php } ?>
                                <div class="col-md-12 text-center">
                                    <hr />
                                    <a href="<?php echo admin_url('task_templates/download_files/'.$task->id); ?>" class="bold">
                                        <?php echo _l('download_all'); ?> (.zip)
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
    <script>
        $(function () {
            init_new_task_template_form();

            <?php
            if(isset($task)){
                put_custom_field_value_with_js($task->id);
            }
            ?>
        });
    </script>
