<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'order_number',
	'process_name',
	'person_in_charge',
	'estimate_time',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wm_warranty_receipt_process_details';

$where = [];
$join= [];

$warranty_process_id = $this->ci->input->post('warranty_process_id');
if($this->ci->input->post('warranty_process_id')){
	$where_routing_id = '';

	if($warranty_process_id != '')
	{
		if($where_routing_id == ''){
			$where_routing_id .= 'AND warranty_receipt_id = "'.$warranty_process_id. '"';
		}else{
			$where_routing_id .= ' or warranty_receipt_id = "' .$warranty_process_id.'"';
		}
	}
	if($where_routing_id != '')
	{
		array_push($where, $where_routing_id);
	}
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
	$row = [];

	for ($i = 0; $i < count($aColumns); $i++) {

		if($aColumns[$i] == 'id') {
			$_data = $aRow['id'];

		}elseif($aColumns[$i] == 'order_number'){
			$_data = round($aRow['order_number'],0);

		}elseif ($aColumns[$i] == 'process_name') {

			$code = $aRow['process_name'] ;
			$code .= '<div class="row-options">';

			if (has_permission('warranty_management', '', 'edit') || is_admin()) {

				$code .= ' <a href="#" onclick="add_process('. $warranty_process_id .','. $aRow['id'] .',\'updated\'); return false;" >' . _l('edit') . '</a>';
			}
			if (has_permission('warranty_management', '', 'delete') || is_admin()) {
				$code .= ' | <a href="' . admin_url('warranty_management/delete_process/' . $aRow['id']) . '/'.$warranty_process_id.'" class="text-danger _delete">' . _l('delete') . '</a>';
			}
			$code .= '</div>';

			$_data = $code;


		}elseif($aColumns[$i] == 'person_in_charge'){

			$staff_name_output = '';
			$staff_ids       = explode(",", $aRow['person_in_charge']);

			$list_staff_name = '';
			$exportjob_positions = '';
			if($staff_ids != null){
				foreach ($staff_ids as $key => $staff_id) {

					$list_staff_name .= '<li class="text-success mbot10 mtop"><a href="#" class="text-left">'.get_staff_full_name($staff_id). '</a></li>';
				}
			}

			if($staff_ids != null){
				$staff_name_output .= '<span class="avatar bg-warning brround avatar-none">+'. (count($staff_ids) ) .'</span>';
			}


			$staff_name_output1 = '<div class="task-info task-watched task-info-watched">
			<h5>
			<div class="btn-group">
			<span class="task-single-menu task-menu-watched">
			<div class="avatar-list avatar-list-stacked" data-toggle="dropdown">'.$staff_name_output.'</div>
			<ul class="dropdown-menu list-staff" role="menu">
			
			'.$list_staff_name.'
			</ul>
			</span>
			</div>
			</h5>
			</div>';

			$data_staff_names = $staff_name_output1;

			$_data =  $data_staff_names;

		}elseif($aColumns[$i] == 'estimate_time'){
				$_data =  $aRow['estimate_time'];

		}


		$row[] = $_data;
	}

	$output['aaData'][] = $row;
}

