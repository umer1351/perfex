<?php

defined('BASEPATH') or exit('No direct script access allowed');
$get_base_currency =  get_base_currency();
if($get_base_currency){
	$base_currency_id = $get_base_currency->id;
}else{
	$base_currency_id = 0;
}


$aColumns = [
	'id',
	'client_id',
	'order_id',
	'invoice_id',
	'item_name',
	'billing_plan_value as rate',
	'quantity',
	'2',
	'3',
	'4',
	'expiration_date as warranty_period',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'sm_service_details';

$where = [];
$join = [];

$client_filter = $this->ci->input->post('client_filter');
$order_filter = $this->ci->input->post('order_filter');
$product_filter = $this->ci->input->post('product_filter');
$delivery_note_filter = $this->ci->input->post('delivery_note_filter');
$order_id_filter = $this->ci->input->post('order_id');



$where[] = 'AND date_format('.db_prefix().'sm_service_details.expiration_date, "%Y-%m-%d") >= "'.date('Y-m-d').'"';

if (isset($delivery_note_filter)) {
	$where[] = 'AND 1=2';
}

if (isset($client_filter)) {
	$where_client_ft = '';
	foreach ($client_filter as $client_id) {
		if ($client_id != '') {
			if ($where_client_ft == '') {
				$where_client_ft .= 'AND ('.db_prefix().'sm_service_details.client_id = "' . $client_id . '"';
			} else {
				$where_client_ft .= 'or '.db_prefix().'sm_service_details.client_id = "' . $client_id . '"';
			}
		}
	}
	if ($where_client_ft != '') {
		$where_client_ft .= ')';
		array_push($where, $where_client_ft);
	}
}

if (isset($order_filter)) {
	$where_order_ft = '';
	foreach ($order_filter as $order_id) {
		if ($order_id != '') {
			if ($where_order_ft == '') {
				$where_order_ft .= 'AND ('.db_prefix().'sm_service_details.order_id = "' . $order_id . '"';
			} else {
				$where_order_ft .= 'or '.db_prefix().'sm_service_details.order_id = "' . $order_id . '"';
			}
		}
	}
	if ($where_order_ft != '') {
		$where_order_ft .= ')';
		array_push($where, $where_order_ft);
	}
}

if (isset($product_filter)) {
	$where_product_ft = '';
	foreach ($product_filter as $item_id) {
		if ($item_id != '') {
			if ($where_product_ft == '') {
				$where_product_ft .= 'AND ('.db_prefix().'sm_service_details.item_id = "' . $item_id . '"';
			} else {
				$where_product_ft .= 'or '.db_prefix().'sm_service_details.item_id = "' . $item_id . '"';
			}
		}
	}
	if ($where_product_ft != '') {
		$where_product_ft .= ')';
		array_push($where, $where_product_ft);
	}
}


if(isset($order_id_filter)){
	$where[] = 'AND '.db_prefix().'sm_service_details.order_id = '.$order_id_filter;
}


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['billing_plan_type', 'billing_plan_rate', 'start_date', 'billing_plan_value', 'order_id', 'tax_rate', 'tax_name']);

$output = $result['output'];
$rResult = $result['rResult'];

if(wm_get_status_modules('warehouse')){
/*get warranty information from inventory deivey details start*/
$aColumns1 = [
	db_prefix().'goods_delivery_detail.id as id',
	db_prefix() . 'goods_delivery.customer_code as client_id',
	db_prefix() . 'goods_delivery_detail.goods_delivery_id as order_id',
	db_prefix() . 'goods_delivery.invoice_id as invoice_id',
	db_prefix() . 'goods_delivery_detail.commodity_name as item_name',
	db_prefix() . 'goods_delivery_detail.unit_price as rate',
	db_prefix() . 'goods_delivery_detail.quantities as quantity',
	db_prefix() . 'goods_delivery_detail.expiry_date as expiry_date',
	db_prefix() . 'goods_delivery_detail.lot_number as lot_number',
	db_prefix() . 'goods_delivery_detail.serial_number as serial_number',
	db_prefix() . 'goods_delivery_detail.guarantee_period as warranty_period',
	'1',
];
$sIndexColumn1 = 'id';
$sTable1 = db_prefix() . 'goods_delivery_detail';

$where = [];
$join = [
	'LEFT JOIN ' . db_prefix() . 'goods_delivery ON ' . db_prefix() . 'goods_delivery.id = ' . db_prefix() . 'goods_delivery_detail.goods_delivery_id',
];

$where[] = 'AND'.db_prefix().'goods_delivery.invoice_id is not null AND '.db_prefix().'goods_delivery.invoice_id != 0 AND '.db_prefix().'goods_delivery.approval = 1 AND '.db_prefix().'goods_delivery.customer_code is not null AND '.db_prefix().'goods_delivery.customer_code != "" AND (date_format('.db_prefix().'goods_delivery_detail.guarantee_period, "%Y-%m-%d") >= "'.date('Y-m-d').'" OR '.db_prefix().'goods_delivery_detail.guarantee_period is NULL OR '.db_prefix().'goods_delivery_detail.guarantee_period = "" )';

if (isset($order_filter)) {
	$where[] = 'AND 1=2';
}

if (isset($client_filter)) {
	$where_client_ft = '';
	foreach ($client_filter as $client_id) {
		if ($client_id != '') {
			if ($where_client_ft == '') {
				$where_client_ft .= 'AND ('.db_prefix().'goods_delivery.customer_code = "' . $client_id . '"';
			} else {
				$where_client_ft .= 'or '.db_prefix().'goods_delivery.customer_code = "' . $client_id . '"';
			}
		}
	}
	if ($where_client_ft != '') {
		$where_client_ft .= ')';
		array_push($where, $where_client_ft);
	}
}

if (isset($delivery_note_filter)) {
	$where_delivery_ft = '';
	foreach ($delivery_note_filter as $delivery_note) {
		if ($delivery_note != '') {
			if ($where_delivery_ft == '') {
				$where_delivery_ft .= 'AND ('.db_prefix().'goods_delivery.id = "' . $delivery_note . '"';
			} else {
				$where_delivery_ft .= 'or '.db_prefix().'goods_delivery.id = "' . $delivery_note . '"';
			}
		}
	}
	if ($where_delivery_ft != '') {
		$where_delivery_ft .= ')';
		array_push($where, $where_delivery_ft);
	}
}

if (isset($product_filter)) {
	$where_product_ft = '';
	foreach ($product_filter as $item_id) {
		if ($item_id != '') {
			if ($where_product_ft == '') {
				$where_product_ft .= 'AND ('.db_prefix().'goods_delivery_detail.commodity_code = "' . $item_id . '"';
			} else {
				$where_product_ft .= 'or '.db_prefix().'goods_delivery_detail.commodity_code = "' . $item_id . '"';
			}
		}
	}
	if ($where_product_ft != '') {
		$where_product_ft .= ')';
		array_push($where, $where_product_ft);
	}
}


$result1 = data_tables_init($aColumns1, $sIndexColumn1, $sTable1, $join, $where, ['date_add', 'commodity_code']);

$output1 = $result1['output'];
$rResult1 = $result1['rResult'];
}else{
	$output1 = [];
	$rResult1 = [];
}
/*get warranty information from inventory deivey details end*/

$rResult2 = array_merge($rResult, $rResult1);
if(isset($output1['iTotalRecords'])){
	$output['iTotalRecords'] = (int)$output['iTotalRecords'] + (int)$output1['iTotalRecords'];
}else{
	$output['iTotalRecords'] = (int)$output['iTotalRecords'];
}

if(isset($output1['iTotalDisplayRecords'])){
	$output['iTotalDisplayRecords'] = (int)$output['iTotalDisplayRecords'] + (int)$output1['iTotalDisplayRecords'];
}else{
	$output['iTotalDisplayRecords'] = (int)$output['iTotalDisplayRecords'] ;
}

foreach ($rResult2 as $key => $aRow) {
	$row = [];
	$row[] = $aRow['id'];
	$row[] = get_company_name($aRow['client_id']);

	if(!isset($aRow['start_date'])){
		$value = '';
		if(wm_get_status_modules('warehouse')){

			$value = get_goods_delivery_code($aRow['order_id']) != null ? get_goods_delivery_code($aRow['order_id'])->goods_delivery_code : '';
			$row[] = '<a href="' . admin_url('warehouse/manage_delivery/' . $aRow['order_id']) . '" >'. $value.'</a>';
		}else{
			$row[] = '';

		}
	}else{
		$row[] = '<a href="' . admin_url('service_management/order_detail/' . $aRow['order_id'] ).'" >' . sm_order_code($aRow['order_id']) . '</a>';
	}

	$row[] = '<a href="' . admin_url('invoices#' . $aRow['invoice_id'] ).'" >' . format_invoice_number($aRow['invoice_id']) . '</a>';

	if(!isset($aRow['commodity_code'])){
		$row[] = $aRow['item_name'];
	}else{
		if(new_strlen($aRow['item_name']) > 0){
			$row[] = $aRow['item_name'];
		}else{
			$row[] = wm_get_item($aRow['commodity_code']);
		}
	}

	if(!isset($aRow['billing_plan_rate'])){
		$row[] = app_format_money((float)$aRow['rate'], $base_currency_id);
	}else{
		$row[] = app_format_money((float)$aRow['billing_plan_rate'], $base_currency_id).' ('. $aRow['rate'].' '. _l($aRow['billing_plan_type']) . ')';
	}

	$row[] = $aRow['quantity'];
	if(isset($aRow['expiry_date'])){
		$row[] = $aRow['expiry_date'];
	}else{
		$row[] = '...';
	}

	if(isset($aRow['expiry_date'])){
		$row[] = $aRow['lot_number'];
	}else{
		$row[] = '...';
	}
	if(isset($aRow['expiry_date'])){
		$row[] = $aRow['serial_number'];
	}else{
		$row[] = '...';
	}

	if(isset($aRow['start_date'])){
		$row[] = _dt($aRow['start_date']) .' - '. _dt($aRow['warranty_period']);
	}else{
		if($aRow['warranty_period'] != null && new_strlen($aRow['warranty_period']) > 0){
			$row[] = _d($aRow['date_add']) .' - '. _d($aRow['warranty_period']);
		}else{
			$row[] = _d($aRow['date_add']) .' - ...';
		}
	}

	if(isset($aRow['start_date'])){
		$row[] = '';
	}else{
		if($aRow['warranty_period'] != null && new_strlen($aRow['warranty_period']) > 0){
			$row[] = '';
		}else{
			$options = '';
			if ((has_permission('warranty_management', '', 'edit') || has_permission('warranty_management', '', 'create') || is_admin()) &&  wm_get_status_modules('warehouse')) {
				$options .= ' <a href="#" onclick="add_goods_delivery_expiration_date('. $aRow['id'] .'); return false;" title="'._l('wm_add_warranty_expiration_date').'" class="btn btn-sm btn-success text-right mright5">' . _l('wm_expiration_date') . '</a>';

			}

			$row[] = $options;
		}
	}

	$output['aaData'][] = $row;
}

