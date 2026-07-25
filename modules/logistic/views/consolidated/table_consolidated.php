<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('staff', '', 'delete');

$aColumns = [
    'number_code',
    'created_at',
    'customer_id',
    'recipient_id',
    'recipient_address_id',
    'rel_type',
    'rel_id',
    'courrier_company',
    'customer_address',
    'payment_term_id',
    'store_supplier',
    'tracking_purchase',
    ];

$join = [

];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lg_consolidated';

$where  = [];

$user = get_staff_user_id();
if(!has_permission('lg_consolidated', '', 'view')){
    array_push($where, 'AND (created_by = '.$user.' OR assign_driver = '.$user.')');
}

if ($this->ci->input->post('status') && count($this->ci->input->post('status')) > 0) {
    array_push($where, 'AND delivery_status IN (' . implode(',', $this->ci->input->post('status')) . ')');
}

if ($this->ci->input->post('clients') && count($this->ci->input->post('clients')) > 0) {
    array_push($where, 'AND customer_id IN (' . implode(',', $this->ci->input->post('clients')) . ')');
}

if ($this->ci->input->post('from_date')
    && $this->ci->input->post('from_date') != '') {
    array_push($where, 'AND date(created_at) >= "'.to_sql_date($this->ci->input->post('from_date')).'"');
}

if ($this->ci->input->post('to_date')
    && $this->ci->input->post('to_date') != '') {
    array_push($where, 'AND date(created_at) <= "'.to_sql_date($this->ci->input->post('to_date')).'"');
}



if (!class_exists('Invoices_model', false)) {
    $this->load->model('invoices_model');
}


$filter = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id', 'shipping_prefix', 'number_code', 'tracking_purchase', 'CONCAT(shipping_prefix, \'\', number_code) as tracking_number', 'total as package_total', 'currency as package_currency', 'assign_driver', 'delivery_status'
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];



    $numberOutput = '<a href="'.admin_url('logistic/consolidated_detail/'.$aRow['id']).'">'.$aRow['shipping_prefix'].$aRow['number_code'].'</a>';

    $numberOutput .= '<div class="row-options">';

    if (has_permission('lg_consolidated', '', 'edit') && !is_driver_staff()) {
        $numberOutput .= ' <a href="' . admin_url('logistic/consolidation/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    if (has_permission('lg_consolidated', '', 'delete') && !is_driver_staff()) {
        $numberOutput .= ' | <a href="' . admin_url('logistic/delete_consolidation/' . $aRow['id']) . '" class="text-danger _delete">' . _l('lg_delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;
    $row[] = _d($aRow['created_at']);
    $row[] = '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">'.get_company_name($aRow['customer_id']).'</a>';
    $row[] = _l('lg_'.$aRow['rel_type']);

    $rel_str = '';
    if($aRow['rel_type'] == 'locker_packages'){
        $url = admin_url('logistic/package_detail/');

    }else if($aRow['rel_type'] == 'shipping'){
        $url = admin_url('logistic/shipping_detail/');
    }

    if($aRow['rel_id'] != ''){
        $rel_arr = explode(',', $aRow['rel_id']);
        foreach($rel_arr as $key => $rel_id){
            if($key == 0){
                $rel_str .= '<a href="'.$url.$rel_id.'">'.lg_get_tracking_number_by_type($aRow['rel_type'], $rel_id).'</a>';
            }else{
                $rel_str .= '<br><a href="'.$url.$rel_id.'">'.lg_get_tracking_number_by_type($aRow['rel_type'], $rel_id).'</a>';
            }
        }
    }

    $row[] = $rel_str;

    $row[] = lg_get_recipient_name($aRow['recipient_id']);
    $row[] = lg_get_customer_address_str($aRow['customer_address']);
    $row[] = lg_get_recipient_address_str($aRow['recipient_address_id']);

    $row[] = lg_get_payment_term_str($aRow['payment_term_id']);

    $row[] = format_lg_package_status($aRow['delivery_status']);

    $row[] = app_format_money($aRow['package_total'], $aRow['package_currency']);


    $action = '';


    $action .= '<a href="'.admin_url('logistic/export_consolidation_shipment/'.$aRow['id']).'?output_type=I" target="_blank" class="btn btn-icon btn-warning mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_export_shipment').'"><i class="fa fa-file-lines"></i></a>';

    $action .= '<a href="'.admin_url('logistic/export_consolidation_label/'.$aRow['id']).'?output_type=I" target="_blank"  class="btn btn-icon btn-info mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_export_label').'"><i class="fa fa-file-contract"></i></a>';

    $action .= '<a href="javascript:void(0);" class="btn btn-icon btn-success mleft5" onclick="send_consolidation('.$aRow['id'].'); return false;" data-toggle="tooltip" data-placement="top" title="'._l('lg_send_mail').'"><i class="fa fa-envelope"></i></a>';

    $action .= '<a href="javascript:void(0);" onclick="assign_driver(this); return false;" data-consolidation_id="'.$aRow['id'].'" data-assign_driver="'.$aRow['assign_driver'].'" class="btn btn-icon btn-primary mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_assign_driver').'"><i class="fa fa-user"></i></a>';

    $row[] = $action;


    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
