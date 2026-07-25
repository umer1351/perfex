<?php
defined('BASEPATH') or exit('No direct script access allowed');

function add_task_templates_own_scripts($group){
    if($group == "admin") {
        $CI = &get_instance();
        $CI->app_scripts->add('task-templates-js', module_dir_url(TASK_TEMPLATES_MODULE_NAME, 'assets/task-templates.js?v='.TASK_TEMPLATES_MODULE_VERSION));
    }
}

function add_task_templates_global_scripts($group){
    if($group == "admin") {
        $CI = &get_instance();
        $CI->app_scripts->add('task-templates-admin-js', module_dir_url(TASK_TEMPLATES_MODULE_NAME, 'assets/task-templates-global.js?v='.TASK_TEMPLATES_MODULE_VERSION));
    }
}

function add_task_templates_css($group){
    if($group == "admin") {
        $CI = &get_instance();
        $CI->app_css->add('task-templates-css', module_dir_url(TASK_TEMPLATES_MODULE_NAME, 'assets/task-templates.css?v='.TASK_TEMPLATES_MODULE_VERSION));
    }
}

/**
 * Init language editor module menu items in setup in admin_init hook
 * @return null
 */
function add_setup_menu_task_templates_link(){
    if (staff_can('view', 'task_templates') || staff_can('view_own', 'task_templates')) {
        $CI = &get_instance();
        /**
         * If the logged in user is administrator, add custom menu in Setup
         */
        $CI->app_menu->add_setup_menu_item('task_templates', [
            'href'     => admin_url('task_templates'),
            'name'     => _l('tt_module_title'),
            'position' => 300,
        ]);
    }
}

/**
 * Staff permissions for translation module
 * @param $corePermissions array
 * @param $data array
 * @return array
 */
function task_templates_staff_permissions($corePermissions, $data){
    $corePermissions['task_templates'] = [
        'name'         => _l('tt_module_title'),
        'capabilities' => [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'view_own'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit' => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];
    return $corePermissions;
}


/**
 * Task Templates attachments upload array
 * Multiple task template attachments can be upload if input type is array or dropzone plugin is used
 * @param  mixed $taskid     task id
 * @param  string $index_name attachments index, in different forms different index name is used
 * @return mixed
 */
function handle_task_template_attachments_array($taskid, $index_name = 'attachments')
{
    $uploaded_files = [];
    $path           = TASK_TEMPLATES_ATTACHMENTS_FOLDER . $taskid . '/';
    $CI             = &get_instance();

    if (isset($_FILES[$index_name]['name'])
        && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        _file_attachments_index_fix($index_name);
        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            // Get the temp file path
            $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];

            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
                    || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                $newFilePath = $path . $filename;

                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    array_push($uploaded_files, [
                        'file_name' => $filename,
                        'filetype'  => $_FILES[$index_name]['type'][$i],
                    ]);

                    if (is_image($newFilePath)) {
                        create_img_thumb($path, $filename);
                    }
                }
            }
        }
    }

    if (count($uploaded_files) > 0) {
        return $uploaded_files;
    }

    return false;
}

function get_sql_select_task_template_assignees_ids()
{
    return '(SELECT GROUP_CONCAT(staffid SEPARATOR ",") FROM ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . ' WHERE taskid=' . TASK_TEMPLATES_TABLE_NAME . '.id ORDER BY ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . '.staffid)';
}

function get_sql_select_task_template_asignees_full_names()
{
    return '(SELECT GROUP_CONCAT(CONCAT(firstname, \' \', lastname) SEPARATOR ",") FROM ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . ' JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . '.staffid WHERE taskid=' . TASK_TEMPLATES_TABLE_NAME . '.id ORDER BY ' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME . '.staffid)';
}

/**
 * This text is used in WHERE statements for tasks if the staff member don't have permission for tasks VIEW
 * This query will shown only tasks that are created from current user, public tasks or where this user is added is task follower.
 * Other statement will be included the tasks to be visible for this user only if Show All Tasks For Project Members is set to YES
 * @return string
 */
function get_task_templates_where_string($table = true)
{
    $_tasks_where = ' added_by = '.get_staff_user_id();
    if ($table == true) {
        $_tasks_where = 'AND ' . $_tasks_where;
    }

    return $_tasks_where;
}


/**
 * Check for custom fields for , update on $_POST
 * @param  mixed $rel_id        the main ID from the table
 * @param  array $custom_fields all custom fields with id and values
 * @return boolean
 */
function handle_custom_fields_post_for_task_templates($rel_id, $custom_fields, $is_cf_items = false)
{
    $affectedRows = 0;
    $CI           = & get_instance();

    foreach ($custom_fields as $key => $fields) {
        foreach ($fields as $field_id => $field_value) {
            $CI->db->where('relid', $rel_id);
            $CI->db->where('fieldid', $field_id);
            $CI->db->where('fieldto', ($is_cf_items ? 'items_pr' : $key));
            $row = $CI->db->get(TASK_TEMPLATES_CUSTOM_FIELD_VALUES)->row();
            if (!is_array($field_value)) {
                $field_value = trim($field_value);
            }
            // Make necessary checkings for fields
            if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                $CI->db->where('id', $field_id);
                $field_checker = $CI->db->get(db_prefix() . 'customfields')->row();
                if ($field_checker->type == 'date_picker') {
                    $field_value = to_sql_date($field_value);
                } elseif ($field_checker->type == 'date_picker_time') {
                    $field_value = to_sql_date($field_value, true);
                } elseif ($field_checker->type == 'textarea') {
                    $field_value = nl2br($field_value);
                } elseif ($field_checker->type == 'checkbox' || $field_checker->type == 'multiselect') {
                    if ($field_checker->disalow_client_to_edit == 1 && is_client_logged_in()) {
                        continue;
                    }
                    if (is_array($field_value)) {
                        $v = 0;
                        foreach ($field_value as $chk) {
                            if ($chk == 'cfk_hidden') {
                                unset($field_value[$v]);
                            }
                            $v++;
                        }
                        $field_value = implode(', ', $field_value);
                    }
                }
            }
            if ($row) {
                $CI->db->where('id', $row->id);
                $CI->db->update(TASK_TEMPLATES_CUSTOM_FIELD_VALUES, [
                    'value' => $field_value,
                ]);
                if ($CI->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            } else {
                if ($field_value != '') {
                    $CI->db->insert(TASK_TEMPLATES_CUSTOM_FIELD_VALUES, [
                        'relid'   => $rel_id,
                        'fieldid' => $field_id,
                        'fieldto' => $is_cf_items ? 'items_pr' : $key,
                        'value'   => $field_value,
                    ]);
                    $insert_id = $CI->db->insert_id();
                    if ($insert_id) {
                        $affectedRows++;
                    }
                }
            }
        }
    }
    if ($affectedRows > 0) {
        return true;
    }

    return false;
}

function get_task_template_custom_field_value($rel_id, $field_id, $format = true)
{
    $CI = & get_instance();

    $CI->db->select(TASK_TEMPLATES_CUSTOM_FIELD_VALUES . '.value,' . db_prefix() . 'customfields.type');
    $CI->db->join(db_prefix() . 'customfields', db_prefix() . 'customfields.id=' . TASK_TEMPLATES_CUSTOM_FIELD_VALUES . '.fieldid');
    $CI->db->where(TASK_TEMPLATES_CUSTOM_FIELD_VALUES . '.relid', $rel_id);
    if (is_numeric($field_id)) {
        $CI->db->where(TASK_TEMPLATES_CUSTOM_FIELD_VALUES . '.fieldid', $field_id);
    }

    $row = $CI->db->get(TASK_TEMPLATES_CUSTOM_FIELD_VALUES)->row();

    $result = '';
    if ($row) {
        $result = $row->value;
        if ($format == true) {
            if ($row->type == 'date_picker') {
                $result = _d($result);
            } elseif ($row->type == 'date_picker_time') {
                $result = _dt($result);
            }
        }
    }

    return $result;
}

function put_custom_field_value_with_js($rel_id)
{
    $fields = get_custom_fields('tasks');

    foreach ($fields as $field){
        $result = get_task_template_custom_field_value($rel_id, $field['id']);
        $name = 'custom_fields[tasks]['.$field['id'].']';
        $js = '$(\'[name="'.$name.'"]\').val(\''.$result.'\');';
        echo ($js);
    }

}


/**
 * Check if a module is active or not. If active return version of module
 * @param $module String
 * @return boolean|string
 */
if(!function_exists('module_is_active')){

    function module_is_active($module){
        $CI =& get_instance();

        $active = $CI->app_object_cache->get($module.'_is_active');

        if (!$active || empty($active)) {
            $active = $CI->db->where("module_name", $module)->get("tblmodules")->row_array();
            $CI->app_object_cache->add($module.'_is_active', $active);
        }

        if(!empty($active) && $active['active'] == "1"){
            return $active['installed_version'];
        }
        return false;
    }
}

function tt_task_status_changed($data){
    $CI =& get_instance();
    if ($data['status'] == Tasks_model::STATUS_COMPLETE) {
        $CI->load->model("tasks_model");
        $CI->load->model("task_templates/task_templates_model");
        $que_tasks = $CI->db->select([
            TASK_TEMPLATES_TABLE_NAME.".*",
            TASK_TEMPLATES_QUE_TABLE_NAME.".id AS que_id",
            TASK_TEMPLATES_QUE_TABLE_NAME.".real_task_id",
        ])
            ->where("real_task_id", $data['task_id'])
            ->join(TASK_TEMPLATES_TABLE_NAME, TASK_TEMPLATES_TABLE_NAME.".id=".TASK_TEMPLATES_QUE_TABLE_NAME.".next_task_template_id")
            ->get(TASK_TEMPLATES_QUE_TABLE_NAME)
            ->result_array();
        foreach ($que_tasks as $task){
            $current_task = $CI->tasks_model->get($task['real_task_id']);
            $task_start_date = date("Y-m-d");
            $new_task_data['task_template'] = $task['id'];
            $new_task_data['startdate'] = $task_start_date;
            $new_task_data['rel_type'] = 'project';
            $new_task_data['rel_id'] = $current_task->rel_id;
            $new_task_data['milestone'] = $current_task->milestone;
            $CI->task_templates_model->create_task($new_task_data);

            $CI->db->where("id", $task['que_id'])->delete(TASK_TEMPLATES_QUE_TABLE_NAME);
        }
    }
}