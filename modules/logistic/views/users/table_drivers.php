<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('staff', '', 'delete');

$aColumns = [
    'firstname',
    'lastname',
    'email',
    'office_group',
    'active',
    'last_login',
    'vehicle_license_plate',
    'vehicle_code',
    ];

$join = [
    
];

$sIndexColumn = 'staffid';
$sTable       = db_prefix() . 'staff';

$where  = [];

$where[] = 'AND staff_type = "driver"';

if ($this->ci->input->post('office_group') && $this->ci->input->post('office_group') != '') {
    array_push($where, 'AND office_group = '. $this->ci->input->post('office_group'));
}

$filter = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['staffid'
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = get_office_group_name_by_id($aRow['office_group']);


    $name_str = '<a href="' . admin_url('logistic/driver/' . $aRow['staffid']) . '">' . staff_profile_image($aRow['staffid'], [
                'staff-profile-image-small',
                ]) . '</a>';
            $name_str .= ' <a href="' . admin_url('logistic/driver/' . $aRow['staffid']) . '">' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>';

            $name_str .= '<div class="row-options">';
            $name_str .= '<a href="' . admin_url('logistic/driver/' . $aRow['staffid']) . '">' . _l('view') . '</a>';

            if (($has_permission_delete && ($has_permission_delete && !is_admin($aRow['staffid']))) || is_admin()) {
                if ($has_permission_delete && $aRow['staffid'] != get_staff_user_id()) {
                    $name_str .= ' | <a href="#" onclick="delete_staff_member(' . $aRow['staffid'] . '); return false;" class="text-danger">' . _l('delete') . '</a>';
                }
            }

            $name_str .= '</div>';

    $row[] = $name_str;

    $row[] = '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>';

    $checked = '';
    if ($aRow['active'] == 1) {
        $checked = 'checked';
    }

    $active_str = '<div class="onoffswitch">
        <input type="checkbox" ' . (($aRow['staffid'] == get_staff_user_id() || (is_admin($aRow['staffid']) || !has_permission('staff', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'staff/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['staffid'] . '" data-id="' . $aRow['staffid'] . '" ' . $checked . '>
        <label class="onoffswitch-label" for="c_' . $aRow['staffid'] . '"></label>
    </div>';

    // For exporting
    $active_str .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';


    $row[] = $active_str;

    if($aRow['last_login'] != null){
        $row[] = '<span class="text-has-action is-date" data-toggle="tooltip" data-title="' . _dt($aRow['last_login']) . '">' . time_ago($aRow['last_login']) . '</span>';
    }else{
        $row[] = 'Never';
    }

    $row[] = $aRow['vehicle_license_plate'];

    $row[] = $aRow['vehicle_code'];


    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
