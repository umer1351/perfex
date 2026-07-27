<?php

$table_data = [
    _l('the_number_sign'),
    _l('tasks_dt_name'),
    [
        'name'     => _l('tt_duration_text'),
        'th_attrs' => ['class' => 'duedate'],
    ],
    _l('task_assigned'),
    _l('tags'),
    _l('tasks_list_priority'),
];

array_unshift($table_data, [
    'name'     => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="tasks"><label></label></div>',
    'th_attrs' => ['class' => (isset($bulk_actions) ? '' : 'not_visible')],
]);

$custom_fields = get_custom_fields('tasks', [
    'show_on_table' => 1,
]);

foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = hooks()->apply_filters('task_template_table_columns', $table_data);

render_datatable($table_data, 'task-templates');

?>