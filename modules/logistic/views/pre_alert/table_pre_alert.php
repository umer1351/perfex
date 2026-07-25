<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('staff', '', 'delete');

$aColumns = [
    'tracking_purchase',
    'created_at',
    'client_id',
    'courier_company',
    'store_supplier',
    'package_description',
    'status',
    'currency',
    'purchase_price',
    'delivery_date',
    'id'
    ];

$join = [
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lg_pre_alert';

$where  = [];

$user = get_staff_user_id();
if(!has_permission('lg_pre_alert', '', 'view')){
    array_push($where, 'AND client_id IN (SELECT customer_id FROM '.db_prefix().'customer_admins WHERE staff_id = '.$user.')');
}

if ($this->ci->input->post('status') && count($this->ci->input->post('status')) > 0) {
    array_push($where, 'AND status IN (' . implode(',', $this->ci->input->post('status')) . ')');
}

if ($this->ci->input->post('clients') && count($this->ci->input->post('clients')) > 0) {
    array_push($where, 'AND client_id IN (' . implode(',', $this->ci->input->post('clients')) . ')');
}

if ($this->ci->input->post('from_date')
    && $this->ci->input->post('from_date') != '') {
    array_push($where, 'AND date(created_at) >= "'.to_sql_date($this->ci->input->post('from_date')).'"');
}

if ($this->ci->input->post('to_date')
    && $this->ci->input->post('to_date') != '') {
    array_push($where, 'AND date(created_at) <= "'.to_sql_date($this->ci->input->post('to_date')).'"');
}

$this->ci->load->model('logistic/logistic_model');

$filter = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['tracking_purchase'];

    $row[] = _dt($aRow['created_at']);

    $row[] = get_company_name($aRow['client_id']);

    $row[] = lg_get_shipping_company_name($aRow['courier_company']);

    $row[] = $aRow['store_supplier'];

    $row[] = $aRow['package_description'];

    $row[] = _dt($aRow['delivery_date']);

    $row[] = app_format_money($aRow['purchase_price'], $aRow['currency']);

    $status = '';
    if($aRow['status'] == 1){
        $status = '<span class="label label-warning">'._l('lg_pending').'</span>';
    }else if($aRow['status'] == 2){
        $status = '<span class="label label-success">'._l('lg_approved').'</span>';
    }

    $row[] = $status;
    
    $action = '';

    if($aRow['status'] == 1){
        $action = '<a href="'.admin_url('logistic/register_package/0/0/'.$aRow['id']).'" class="btn btn-success btn-icon">'._l('lg_convert_to_package').'</a>';
    }

    $row[] = $action;

    $pre_alert_attachment = $this->ci->logistic_model->get_pre_alert_attachment($aRow['id']);

    $preview_str = '';
    foreach ($pre_alert_attachment as $f) {
        $preview_str .= '<a href="'.site_url(LOGISTIC_PATH.'pre_alert/'.$f['rel_id'].'/'.$f['file_name']).'">'._l('lg_attach_invoice').'</a>';
    }

    $row[] = $preview_str;
 
    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
