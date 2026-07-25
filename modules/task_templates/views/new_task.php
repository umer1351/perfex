<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open_multipart(admin_url('task_templates/create_task/'), array('id' => 'task-form')); ?>
<div class="modal fade" id="_task_modal" tabindex="-1" role="dialog"
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
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <?php
                        $rel_id = '';
                        $rel_type = '';
                        ?>
                        <?php echo render_select('task_template', $task_templates, ['id', 'name'], 'tt_select_task_template', $id); ?>

                        <div class="row">
                            <div class="col-md-12">
                                <?php if (isset($task)) {
                                    $value = _d($task->startdate);
                                } else if (isset($start_date)) {
                                    $value = $start_date;
                                } else {
                                    $value = _d(date('Y-m-d'));
                                }
                                $date_attrs = array();
                                if (isset($task) && $task->recurring > 0 && $task->last_recurring_date != null) {
                                    $date_attrs['disabled'] = true;
                                }
                                ?>
                                <?php echo render_date_input('startdate', 'task_add_edit_start_date', $value, $date_attrs); ?>
                            </div>

                        </div>


                        <div class="form-group">
                            <label for="rel_type" class="control-label"><?php echo _l('task_related_to'); ?></label>
                            <select name="rel_type" class="selectpicker" id="rel_type" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <option value="project"
                                    <?php if (isset($task) || $this->input->get('rel_type')) {
                                        if ($rel_type == 'project') {
                                            echo 'selected';
                                        }
                                    } ?>><?php echo _l('project'); ?></option>
                                <option value="invoice" <?php if (isset($task) || $this->input->get('rel_type')) {
                                    if ($rel_type == 'invoice') {
                                        echo 'selected';
                                    }
                                } ?>>
                                    <?php echo _l('invoice'); ?>
                                </option>
                                <option value="customer"
                                    <?php if (isset($task) || $this->input->get('rel_type')) {
                                        if ($rel_type == 'customer') {
                                            echo 'selected';
                                        }
                                    } ?>>
                                    <?php echo _l('client'); ?>
                                </option>
                                <option value="estimate" <?php if (isset($task) || $this->input->get('rel_type')) {
                                    if ($rel_type == 'estimate') {
                                        echo 'selected';
                                    }
                                } ?>>
                                    <?php echo _l('estimate'); ?>
                                </option>
                                <option value="contract" <?php if (isset($task) || $this->input->get('rel_type')) {
                                    if ($rel_type == 'contract') {
                                        echo 'selected';
                                    }
                                } ?>>
                                    <?php echo _l('contract'); ?>
                                </option>
                                <option value="ticket" <?php if (isset($task) || $this->input->get('rel_type')) {
                                    if ($rel_type == 'ticket') {
                                        echo 'selected';
                                    }
                                } ?>>
                                    <?php echo _l('ticket'); ?>
                                </option>
                                <option value="expense" <?php if (isset($task) || $this->input->get('rel_type')) {
                                    if ($rel_type == 'expense') {
                                        echo 'selected';
                                    }
                                } ?>>
                                    <?php echo _l('expense'); ?>
                                </option>
                                <option value="lead" <?php if (isset($task) || $this->input->get('rel_type')) {
                                    if ($rel_type == 'lead') {
                                        echo 'selected';
                                    }
                                } ?>>
                                    <?php echo _l('lead'); ?>
                                </option>
                                <option value="proposal" <?php if (isset($task) || $this->input->get('rel_type')) {
                                    if ($rel_type == 'proposal') {
                                        echo 'selected';
                                    }
                                } ?>>
                                    <?php echo _l('proposal'); ?>
                                </option>
                                <?php
                                hooks()->do_action('task_modal_rel_type_select', ['task' => (isset($task) ? $task : 0), 'rel_type' => $rel_type]);
                                ?>
                            </select>
                        </div>

                        <div class="form-group<?php if ($rel_id == '') {
                            echo ' hide';
                        } ?>" id="rel_id_wrapper">
                            <label for="rel_id" class="control-label"><span class="rel_id_label"></span></label>
                            <div id="rel_id_select">
                                <select name="rel_id" id="rel_id" class="ajax-sesarch" data-width="100%"
                                        data-live-search="true"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <?php if ($rel_id != '' && $rel_type != '') {
                                        $rel_data = get_relation_data($rel_type, $rel_id);
                                        $rel_val = get_relation_values($rel_data, $rel_type);
                                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="task-visible-to-customer checkbox checkbox-inline checkbox-primary<?php if ((isset($task) && $task->rel_type != 'project') || !isset($task) || (isset($task) && $task->rel_type == 'project' && total_rows(db_prefix() . 'project_settings', array('project_id' => $task->rel_id, 'name' => 'view_tasks', 'value' => 0)) > 0)) {
                            echo ' hide';
                        } ?>">
                            <input type="checkbox" id="task_visible_to_client"
                                   name="visible_to_client" <?php if (isset($task)) {
                                if ($task->visible_to_client == 1) {
                                    echo 'checked';
                                }
                            } ?>>
                            <label for="task_visible_to_client"><?php echo _l('task_visible_to_client'); ?></label>
                        </div>

                        <div class="project-details<?php if ($rel_type != 'project') {
                            echo ' hide';
                        } ?>">
                            <div class="form-group">
                                <label for="milestone"><?php echo _l('task_milestone'); ?></label>
                                <select name="milestone" id="milestone" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""></option>
                                    <?php foreach ($milestones as $milestone) { ?>
                                        <option value="<?php echo html_escape($milestone['id']); ?>" <?php if (isset($task) && $task->milestone == $milestone['id']) {
                                            echo 'selected';
                                        } ?>><?php echo html_escape($milestone['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

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
        var _rel_id = $('#rel_id'),
            _rel_type = $('#rel_type'),
            _rel_id_wrapper = $('#rel_id_wrapper'),
            data = {};

        var _milestone_selected_data;
        _milestone_selected_data = undefined;

        $(function () {

            $("body").off("change", "#rel_id");

            var inner_popover_template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>';

            $('#_task_modal .task-menu-options .trigger').popover({
                html: true,
                placement: "bottom",
                trigger: 'click',
                title: "<?php echo _l('actions'); ?>",
                content: function () {
                    return $('body').find('#_task_modal .task-menu-options .content-menu').html();
                },
                template: inner_popover_template
            });

            custom_fields_hyperlink();

            appValidateForm($('#task-form'), {
                task_template: 'required',
                startdate: 'required',
            }, task_form_handler);

            $('.rel_id_label').html(_rel_type.find('option:selected').text());

            _rel_type.on('change', function () {

                var clonedSelect = _rel_id.html('').clone();
                _rel_id.selectpicker('destroy').remove();
                _rel_id = clonedSelect;
                $('#rel_id_select').append(clonedSelect);
                $('.rel_id_label').html(_rel_type.find('option:selected').text());

                task_rel_select();
                if ($(this).val() != '') {
                    _rel_id_wrapper.removeClass('hide');
                } else {
                    _rel_id_wrapper.addClass('hide');
                }
                init_project_details(_rel_type.val());
            });

            init_datepicker();
            init_color_pickers();
            init_selectpicker();
            task_rel_select();

            $('body').on('change', '#rel_id', function () {
                if ($(this).val() != '') {
                    if (_rel_type.val() == 'project') {
                        $.get(admin_url + 'projects/get_rel_project_data/' + $(this).val() + '/' + taskid, function (project) {
                            $("select[name='milestone']").html(project.milestones);
                            if (typeof(_milestone_selected_data) != 'undefined') {
                                $("select[name='milestone']").val(_milestone_selected_data.id);
                                $('input[name="duedate"]').val(_milestone_selected_data.due_date)
                            }
                            $("select[name='milestone']").selectpicker('refresh');
                            if (project.billing_type == 3) {
                                $('.task-hours').addClass('project-task-hours');
                            } else {
                                $('.task-hours').removeClass('project-task-hours');
                            }

                            if (project.deadline) {
                                var $duedate = $('#_task_modal #duedate');
                                var currentSelectedTaskDate = $duedate.val();
                                $duedate.attr('data-date-end-date', project.deadline);
                                $duedate.datetimepicker('destroy');
                                init_datepicker($duedate);

                                if (currentSelectedTaskDate) {
                                    var dateTask = new Date(unformat_date(currentSelectedTaskDate));
                                    var projectDeadline = new Date(project.deadline);
                                    if (dateTask > projectDeadline) {
                                        $duedate.val(project.deadline_formatted);
                                    }
                                }
                            } else {
                                reset_task_duedate_input();
                            }
                            init_project_details(_rel_type.val(), project.allow_to_view_tasks);
                        }, 'json');
                    } else {
                        reset_task_duedate_input();
                    }
                }
            });

            <?php if(!isset($task) && $rel_id != ''){ ?>
            _rel_id.change();
            <?php } ?>

        });

        <?php if(isset($_milestone_selected_data)){ ?>
        _milestone_selected_data = '<?php echo json_encode($_milestone_selected_data); ?>';
        _milestone_selected_data = JSON.parse(_milestone_selected_data);
        <?php } ?>

        function task_rel_select() {
            var serverData = {};
            serverData.rel_id = _rel_id.val();
            data.type = _rel_type.val();
            init_ajax_search(_rel_type.val(), _rel_id, serverData);
        }

        function init_project_details(type, tasks_visible_to_customer) {
            var wrap = $('.non-project-details');
            var wrap_task_hours = $('.task-hours');
            if (type == 'project') {
                if (wrap_task_hours.hasClass('project-task-hours') == true) {
                    wrap_task_hours.removeClass('hide');
                } else {
                    wrap_task_hours.addClass('hide');
                }
                wrap.addClass('hide');
                $('.project-details').removeClass('hide');
            } else {
                wrap_task_hours.removeClass('hide');
                wrap.removeClass('hide');
                $('.project-details').addClass('hide');
                $('.task-visible-to-customer').addClass('hide').prop('checked', false);
            }
            if (typeof(tasks_visible_to_customer) != 'undefined') {
                if (tasks_visible_to_customer == 1) {
                    $('.task-visible-to-customer').removeClass('hide');
                    $('.task-visible-to-customer input').prop('checked', true);
                } else {
                    $('.task-visible-to-customer').addClass('hide')
                    $('.task-visible-to-customer input').prop('checked', false);
                }
            }
        }
        function reset_task_duedate_input() {
            var $duedate = $('#_task_modal #duedate');
            $duedate.removeAttr('data-date-end-date');
            $duedate.datetimepicker('destroy');
            init_datepicker($duedate);
        }
    </script>
