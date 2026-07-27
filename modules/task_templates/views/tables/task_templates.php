<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionCreateTask   = staff_can('create', 'tasks');
$hasPermissionCreate   = staff_can('create', 'task_templates');
$hasPermissionEdit   = staff_can('edit', 'task_templates');
$hasPermissionDelete = staff_can('delete', 'task_templates');
$tasksPriorities     = get_tasks_priorities();
$CI = &get_instance();

$aColumns = [
    '1', // bulk actions
    TASK_TEMPLATES_TABLE_NAME . '.id as id',
    TASK_TEMPLATES_TABLE_NAME . '.name as task_name',
    'duration_value',
    get_sql_select_task_template_asignees_full_names() . ' as assignees',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . TASK_TEMPLATES_TABLE_NAME . '.id and rel_type="task_templates" ORDER by tag_order ASC) as tags',
    'priority',
];

$sIndexColumn = 'id';
$sTable       = TASK_TEMPLATES_TABLE_NAME;

$where = [];
$join  = [];

if(!staff_can('view', 'task_templates')){
    $where[] = ' AND added_by='.get_staff_user_id();
}
if($CI->input->get("project_id")){
    $where[] = ' AND rel_type="project" and rel_id='.$CI->input->get("project_id");
}
else{
    $where[] = ' AND (rel_type="" OR rel_type IS NULL) ';
}

$custom_fields = get_table_custom_fields('tasks');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, '(SELECT value FROM ' . TASK_TEMPLATES_CUSTOM_FIELD_VALUES . ' WHERE ' . TASK_TEMPLATES_CUSTOM_FIELD_VALUES . '.relid=' . TASK_TEMPLATES_TABLE_NAME . '.id AND ' . TASK_TEMPLATES_CUSTOM_FIELD_VALUES . '.fieldid=' . $field['id'] . ' AND ' . TASK_TEMPLATES_CUSTOM_FIELD_VALUES . '.fieldto="' . $field['fieldto'] . '" LIMIT 1) as ' . $selectAs);
}

$aColumns = hooks()->apply_filters('task_templates_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [
        'repeat_every',
        'duration_type',
        get_sql_select_task_template_assignees_ids() . ' as assignees_ids',
    ]
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    if ($hasPermissionEdit) {
        $row[] = '<a href="' . admin_url('tasks/view/' . $aRow['id']) . '" onclick="edit_task_template(' . $aRow['id'] . '); return false;">' . $aRow['id'] . '</a>';
    }
    else{
        $row[] = $aRow['id'];
    }

    $outputName = '';

    if ($hasPermissionEdit) {
        $outputName .= '<a href="' . admin_url('tasks/view/' . $aRow['id']) . '" class="display-block main-tasks-table-href-name' . (!empty($aRow['rel_id']) ? ' mbot5' : '') . '" onclick="edit_task_template(' . $aRow['id'] . '); return false;">' . $aRow['task_name'] . '</a>';
    }
    else{
        $outputName = $aRow['task_name'];
    }

    if ($aRow['repeat_every'] != '') {
        $outputName .= '<br /><span class="label label-primary inline-block mtop4"> ' . _l('recurring_task') . '</span>';
    }

    $outputName .= '<div class="row-options">';

    $class = 'text-success bold';
    $style = '';

    $actions = [];
    if ($hasPermissionCreateTask) {
        $actions[] = '<a href="#" onclick="new_task_from_template('.$aRow['id'].'); return false;">' . _l('tt_create_task') . '</a>';
    }

    if ($hasPermissionCreate) {
        $actions[] = '<a href="' . admin_url('task_templates/copy/' . $aRow['id']) . '">' . _l('copy') . '</a>';
    }

    if ($hasPermissionEdit) {
        $actions[] = '<a href="#" onclick="edit_task_template(' . $aRow['id'] . '); return false">' . _l('edit') . '</a>';
    }

    if ($hasPermissionDelete) {
        $actions[] = '<a href="' . admin_url('task_templates/delete_task_template/' . $aRow['id']) . '" class="text-danger _delete task-delete">' . _l('delete') . '</a>';
    }
    $outputName .= implode('<span class="text-dark"> | </span>', $actions);
    $outputName .= '</div>';

    $row[] = $outputName;

    $row[] = $aRow['duration_value'].' '.$aRow['duration_type'];

    $assignees_ids = $aRow['assignees_ids'];
    if(empty($assignees_ids))
        $assignees_ids = "";
    $assignees = $aRow['assignees'];
    if(empty($assignees))
        $assignees = "";
    $row[] = format_members_by_ids_and_names($assignees_ids, $assignees);

    $row[] = render_tags($aRow['tags']);

    $outputPriority = '<span style="color:' . task_priority_color($aRow['priority']) . ';" class="inline-block">' . task_priority($aRow['priority']);

    $outputPriority .= '</span>';
    $row[] = $outputPriority;

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('task_templates_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
