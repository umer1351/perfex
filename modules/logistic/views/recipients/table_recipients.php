<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('staff', '', 'delete');

$aColumns = [
    'first_name',
    'last_name',
    'client_id',
    'email',
    
    ];

$join = [
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lg_recipients';

$where  = [];

$user = get_staff_user_id();
if(!has_permission('lg_recipient', '', 'view')){
    array_push($where, 'AND client_id IN (SELECT customer_id FROM '.db_prefix().'customer_admins WHERE staff_id = '.$user.')');
}


if ($this->ci->input->post('clients') && count($this->ci->input->post('clients')) > 0) {
    array_push($where, 'AND client_id IN (' . implode(',', $this->ci->input->post('clients')) . ')');
}




$filter = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id' ,'phone'
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];


    $row[] = get_company_name($aRow['client_id']);

    $row[] = $aRow['first_name'].' '.$aRow['last_name'];

    $row[] = $aRow['email'];

    $row[] = $aRow['phone'];


    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
