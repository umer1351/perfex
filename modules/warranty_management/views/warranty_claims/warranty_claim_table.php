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
	'claim_code',
	'created_id',
	'created_type',
	'client_id',
	'description',
	'datecreated',
	'status',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wm_warranty_claim_informations';

$where = [];
$join = [];

$client_filter = $this->ci->input->post('client_filter');
$order_filter = $this->ci->input->post('order_filter');
$product_filter = $this->ci->input->post('product_filter');
$delivery_note_filter = $this->ci->input->post('delivery_note_filter');
$order_id_filter = $this->ci->input->post('order_id');
$claim_status_filter = $this->ci->input->post('claim_status_filter');




if (isset($client_filter)) {
	$where_client_ft = '';
	foreach ($client_filter as $client_id) {
		if ($client_id != '') {
			if ($where_client_ft == '') {
				$where_client_ft .= 'AND ('.db_prefix().'wm_warranty_claim_informations.client_id = "' . $client_id . '"';
			} else {
				$where_client_ft .= 'or '.db_prefix().'wm_warranty_claim_informations.client_id = "' . $client_id . '"';
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

if (isset($claim_status_filter)) {
	$where_claim_status_ft = '';
	foreach ($claim_status_filter as $status) {
		if ($status != '') {
			if ($where_claim_status_ft == '') {
				$where_claim_status_ft .= 'AND ('.db_prefix().'wm_warranty_claim_informations.status = "' . $status . '"';
			} else {
				$where_claim_status_ft .= 'or '.db_prefix().'wm_warranty_claim_informations.status = "' . $status . '"';
			}
		}
	}
	if ($where_claim_status_ft != '') {
		$where_claim_status_ft .= ')';
		array_push($where, $where_claim_status_ft);
	}
}


if(isset($order_id_filter)){
	$where[] = 'AND '.db_prefix().'sm_service_details.order_id = '.$order_id_filter;
}


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['created_type', 'invoice_id', 'client_note', 'admin_note','claim_code','description','datecreated','status' ]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
	$row = [];

	$row[] = $aRow['id'];

	$name = '<a href="' . admin_url('warranty_management/warranty_claim_detail/' . $aRow['id'] ).'" >' . $aRow['claim_code'] . '</a>';
	$name .= '<div class="row-options">';
	$name .= '<a href="' . admin_url('warranty_management/warranty_claim_detail/' . $aRow['id'] ).'" >' . _l('view') . '</a>';

	if((has_permission('warranty_management', '', 'edit') || is_admin())){
		if($aRow['status'] == 'submitted'){
			$name .= ' | <a href="' . admin_url('warranty_management/add_edit_warranty_claim/' . $aRow['id'] ).'" >' . _l('edit') . '</a>';
		}
	}

	if ((has_permission('warranty_management', '', 'delete') || is_admin()) ) {
		$name .= ' | <a href="' . admin_url('warranty_management/delete_warranty_claim/' . $aRow['id'] ).'" class="text-danger _delete" >' . _l('delete') . '</a>';
	}

	$name .= '</div>';

	$row[] = $name;
	if($aRow['created_type'] == 'staff'){
		$row[] = get_staff_full_name($aRow['created_id']);
	}else{
		$row[] = get_contact_full_name($aRow['created_id']);
	}

	$row[] = $aRow['created_type'];
	$row[] = get_company_name($aRow['client_id']);
	$row[] = $aRow['description'];
	$row[] = _dt($aRow['datecreated']);
	$row[] = render_warranty_status_html($aRow['id'], 'warranty_claim', $aRow['status']);

	
	$output['aaData'][] = $row;
}

