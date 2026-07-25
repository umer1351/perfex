<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'code',
	'name',
	'product_group_id',
	'description',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wm_warranty_receipt_process';

$where = [];
$join= [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id', 'mark_default']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
	$row = [];

	for ($i = 0; $i < count($aColumns); $i++) {

		if($aColumns[$i] == 'id') {
			$_data = $aRow['id'];

		}elseif ($aColumns[$i] == 'code') {
			$code = '<a href="' . admin_url('warranty_management/process_manage/' . $aRow['id']) . '">' . $aRow['code'] . '</a>';
			$code .= '<div class="row-options">';

			$code .= '<a href="' . admin_url('warranty_management/process_manage/' . $aRow['id']) . '" >' . _l('view') . '</a>';

			if (has_permission('warranty_management', '', 'edit') || is_admin()) {

				$code .= ' | <a href="' . admin_url('warranty_management/process_manage/' . $aRow['id']) . '" >' . _l('edit') . '</a>';
			}
			if (has_permission('warranty_management', '', 'delete') || is_admin()) {
				$code .= ' | <a href="' . admin_url('warranty_management/delete_warranty_receipt_process/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
			}
			$code .= '</div>';

			$_data = $code;


		}elseif($aColumns[$i] == 'name'){
			$_data =  $aRow['name'];

		}elseif($aColumns[$i] == 'product_group_id'){
			$_data =  wm_get_product_group_name($aRow['product_group_id']);

		}elseif($aColumns[$i] == 'description'){
			/*get frist 400 character */

			if(new_strlen($aRow['description']) > 400){
				$description_sub = mb_substr($aRow['description'], 0, 400);
			}else{
				$description_sub = $aRow['description'];
			}

			$_data =   $description_sub;

		}

		$row[] = $_data;
	}

	$output['aaData'][] = $row;
}

