<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('staff', '', 'delete');

$aColumns = [
    'number_code',
    'created_at',
    'customer_id',
    'recipient_id',
    'recipient_address_id',
    'courrier_company',
    'customer_address',
    'payment_term_id',
    'store_supplier',
    'tracking_purchase',
    'delivery_status',
    
    ];

$join = [
    'LEFT JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'lg_shippings.invoice_id',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lg_shippings';

$where  = [];

$user = get_staff_user_id();
if(!has_permission('lg_shippings', '', 'view')){
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

if(isset($shipping_type) && $shipping_type == 'pickup'){
     array_push($where, 'AND shipping_type = "pickup"');
}else{
    array_push($where, 'AND (shipping_type IS NULL or shipping_type = "shipping")');
}

if (!class_exists('Invoices_model', false)) {
    $this->load->model('invoices_model');
}


$filter = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'lg_shippings.id as shipping_id', 'shipping_prefix', 'number_code', 'tracking_purchase', db_prefix().'invoices.status as invoice_status', 'CONCAT(shipping_prefix, \'\', number_code) as tracking_number, shipping_type','assign_driver',
    db_prefix().'lg_shippings.total as package_total',
    'invoice_id',
    db_prefix().'lg_shippings.currency as package_currency',
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


    $numberOutput = '<a href="'.admin_url('logistic/shipping_detail/'.$aRow['shipping_id']).'">'.$aRow['shipping_prefix'].$aRow['number_code'].'</a>';

    $numberOutput .= '<div class="row-options">';

    if (has_permission('lg_shipping', '', 'edit') && $invoice_status == 'pending' && !is_driver_staff() ) {
        $numberOutput .= ' <a href="' . admin_url('logistic/shipment/0/' . $aRow['shipping_id']) . '">' . _l('edit') . '</a>';
    }
    if (has_permission('lg_shipping', '', 'delete') && $invoice_status == 'pending' && !is_driver_staff()) {
        $numberOutput .= ' | <a href="' . admin_url('logistic/delete_shipping/' . $aRow['shipping_id']) . '" class="text-danger _delete">' . _l('lg_delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;
    $row[] = _d($aRow['created_at']);
    $row[] = '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">'.get_company_name($aRow['customer_id']).'</a>';
    $row[] = lg_get_recipient_name($aRow['recipient_id']);
    $row[] = lg_get_customer_address_str($aRow['customer_address']);
    $row[] = lg_get_recipient_address_str($aRow['recipient_address_id']);

    $row[] = lg_get_payment_term_str($aRow['payment_term_id']);

    $row[] =  format_lg_package_status($aRow['delivery_status']);

    $row[] = app_format_money($aRow['package_total'], $aRow['package_currency']);

   
    $row[] = $invoice_status_str;

    $action = '';
   
    if(has_permission('invoices', '', 'create') && $invoice_status == 'pending' && $aRow['shipping_type'] != 'pickup'){
        $action .= '<a href="'.admin_url('logistic/shipping_create_invoice/'.$aRow['shipping_id']).'" class="btn btn-icon btn-success mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_create_invoice').'"><i class="fa fa-receipt"></i></a>';
    }

    $action .= '<a href="'.admin_url('logistic/export_shipping_shipment/'.$aRow['shipping_id']).'?output_type=I" target="_blank" class="btn btn-icon btn-warning mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_export_shipment').'"><i class="fa fa-file-lines"></i></a>';

    $action .= '<a href="'.admin_url('logistic/export_shipping_label/'.$aRow['shipping_id']).'?output_type=I" target="_blank"  class="btn btn-icon btn-info mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_export_label').'"><i class="fa fa-file-contract"></i></a>';

    $action .= '<a href="javascript:void(0);" class="btn btn-icon btn-success mleft5" onclick="send_shipping('.$aRow['shipping_id'].'); return false;" data-toggle="tooltip" data-placement="top" title="'._l('lg_send_mail').'"><i class="fa fa-envelope"></i></a>';

    $action .= '<a href="javascript:void(0);" onclick="assign_driver(this); return false;" data-shipping_id="'.$aRow['shipping_id'].'" data-assign_driver="'.$aRow['assign_driver'].'" class="btn btn-icon btn-primary mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_assign_driver').'"><i class="fa fa-user"></i></a>';

    if (has_permission('lg_shipping', '', 'edit')  && $aRow['shipping_type'] == 'pickup' ) {
        $action .= '<a href="javascript:void(0);" onclick="approval_pickup(this); return false;" data-shipping_id="'.$aRow['shipping_id'].'" class="btn btn-icon btn-default mleft5" data-toggle="tooltip" data-placement="top" title="'._l('lg_approval').'"><i class="fa fa-check"></i></a>';
    }

    
    $row[] = $action;


    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
