<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('staff', '', 'delete');

$aColumns = [
    'number_code',
    'created_at',
    'customer_id',
    'customer_address',
    'courrier_company',
    'store_supplier',
    'tracking_purchase',
    'delivery_status',
    'assign_driver',
    db_prefix().'lg_packages.total as package_total',
    'invoice_id',
    db_prefix().'lg_packages.currency as package_currency',
    ];

$join = [
    'LEFT JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'lg_packages.invoice_id',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lg_packages';

$where  = [];

$user = get_staff_user_id();
if(!has_permission('lg_packages', '', 'view')){
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


$filter = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'lg_packages.id as package_id', 'shipping_prefix', 'number_code', 'tracking_purchase', db_prefix().'invoices.status as invoice_status', 'CONCAT(shipping_prefix, \'\', number_code) as tracking_number'
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $invoice_status_str = '';
    $invoice_status = '';
    if(is_numeric($aRow['invoice_id']) && $aRow['invoice_id'] > 0){
        $invoice_status_str = '<a href="'.admin_url('invoices/list_invoices/'.$aRow['invoice_id']).'">'.format_invoice_number($aRow['invoice_id']).'</a>&nbsp;'.format_invoice_status($aRow['invoice_status']);
    }else{
        $invoice_status = 'pending';
        $invoice_status_str = '<span class="label label-warning">'._l('lg_pending').'</span>';
    }


    $numberOutput = '<a href="'.admin_url('logistic/package_detail/'.$aRow['package_id']).'">'.$aRow['shipping_prefix'].$aRow['number_code'].'</a>';

    $numberOutput .= '<div class="row-options">';

    if (has_permission('lg_packages', '', 'edit') && $invoice_status == 'pending' && !is_driver_staff()) {
        $numberOutput .= ' <a href="' . admin_url('logistic/register_package/0/' . $aRow['package_id']) . '">' . _l('edit') . '</a>';
    }
    if (has_permission('lg_packages', '', 'delete') && $invoice_status == 'pending' && !is_driver_staff()) {
        $numberOutput .= ' | <a href="' . admin_url('logistic/delete_package/' . $aRow['package_id']) . '" class="text-danger _delete">' . _l('lg_delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;
    $row[] = _d($aRow['created_at']);
    $row[] = '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">'.get_company_name($aRow['customer_id']).'</a>';
    $row[] = lg_get_customer_address_str($aRow['customer_address']);
    $row[] = lg_get_shipping_company_name($aRow['courrier_company']);
    $row[] = $aRow['store_supplier'];
    $row[] = $aRow['tracking_purchase'];

    $row[] =  format_lg_package_status($aRow['delivery_status']);

    $row[] = '<a href="'.admin_url('logistic/driver/'.$aRow['package_id']).'">'.get_staff_full_name($aRow['assign_driver']).'</a>';
    $row[] = app_format_money($aRow['package_total'], $aRow['package_currency']);

   
    $row[] = $invoice_status_str;

    $action = '';
   
    if(has_permission('invoices', '', 'create') && $invoice_status == 'pending'){
        $action .= '<a href="'.admin_url('logistic/create_invoice/'.$aRow['package_id']).'" class="btn btn-icon btn-success mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_create_invoice').'"><i class="fa fa-receipt"></i></a>';
    }

    $action .= '<a href="'.admin_url('logistic/export_package_shipment/'.$aRow['package_id']).'?output_type=I" target="_blank" class="btn btn-icon btn-warning mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_export_shipment').'"><i class="fa fa-file-lines"></i></a>';

    $action .= '<a href="'.admin_url('logistic/export_package_label/'.$aRow['package_id']).'?output_type=I" target="_blank"  class="btn btn-icon btn-info mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_export_label').'"><i class="fa fa-file-contract"></i></a>';

    $action .= '<a href="javascript:void(0);" class="btn btn-icon btn-success mleft5" onclick="send_package('.$aRow['package_id'].'); return false;" data-toggle="tooltip" data-placement="top" title="'._l('lg_send_mail').'"><i class="fa fa-envelope"></i></a>';

    $action .= '<a href="javascript:void(0);" onclick="assign_driver(this); return false;" data-package_id="'.$aRow['package_id'].'" data-assign_driver="'.$aRow['assign_driver'].'" class="btn btn-icon btn-primary mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_assign_driver').'"><i class="fa fa-user"></i></a>';

    
    $row[] = $action;


    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
