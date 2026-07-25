<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * wm warranty claim status
 * @return [type] 
 */
function wm_warranty_claim_status()
{

	$statuses = [

		[
			'id'             => 'submitted',
			'color'          => '#9e9e9e',
			'name'           => _l('wm_submitted'),
			'order'          => 1,
			'filter_default' => true,
		],
		[
			'id'             => 'approved',
			'color'          => '#3db8da',
			'name'           => _l('wm_approved'),
			'order'          => 2,
			'filter_default' => true,
		],
		[
			'id'             => 'processing',
			'color'          => '#2196f3',
			'name'           => _l('wm_processing'),
			'order'          => 3,
			'filter_default' => true,
		],
		[
			'id'             => 'complete',
			'color'          => '#84c529',
			'name'           => _l('wm_complete'),
			'order'          => 4,
			'filter_default' => false,
		],
		[
			'id'             => 'closed',
			'color'          => '#84c529',
			'name'           => _l('wm_closed'),
			'order'          => 5,
			'filter_default' => false,
		],
		
		[
			'id'             => 'cancelled',
			'color'          => '#d71a1a',
			'name'           => _l('wm_cancelled'),
			'order'          => 6,
			'filter_default' => false,
		],

	];

	usort($statuses, function ($a, $b) {
		return $a['order'] - $b['order'];
	});

	return $statuses;
}

/**
 * wm_warranty_receipt_process_status
 * @return [type] 
 */
function wm_warranty_receipt_process_status()
{

	$statuses = [
		[
			'id'             => 'pendding',
			'color'          => '#9e9e9e',
			'name'           => _l('wm_pendding'),
			'order'          => 1,
			'filter_default' => true,
		],
		[
			'id'             => 'processing',
			'color'          => '#2196f3',
			'name'           => _l('wm_processing'),
			'order'          => 2,
			'filter_default' => true,
		],
		[
			'id'             => 'complete',
			'color'          => '#84c529',
			'name'           => _l('wm_complete'),
			'order'          => 3,
			'filter_default' => false,
		],
	];

	usort($statuses, function ($a, $b) {
		return $a['order'] - $b['order'];
	});

	return $statuses;
}

/**
 * wm get product group name
 * @param  [type] $id 
 * @return [type]     
 */
function wm_get_product_group_name($id)
{
	$CI   = & get_instance();
	$CI->db->where('id', $id);
	$items_group = $CI->db->get(db_prefix() . 'items_groups')->row();

	$name='';
	if($items_group){
		$name .= $items_group->name;
	}

	return $name;
}

/**
 * handle wm process attachments array
 * @param  [type] $process_id 
 * @param  string $index_name   
 * @return [type]               
 */
function handle_wm_process_attachments_array($process_id, $index_name = 'attachments')
{
	$uploaded_files = [];
	$path           = WARRANTY_MANAGEMENT_PROCESS_UPLOAD.$process_id .'/';
	$CI             = &get_instance();
	if (isset($_FILES[$index_name]['name'])
		&& ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
		
		if (!is_array($_FILES[$index_name]['name'])) {
			$_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
			$_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
			$_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
			$_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
			$_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
		}

		_file_attachments_index_fix($index_name);
		for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
			$tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
			if (!empty($tmpFilePath) && $tmpFilePath != '') {
				if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
					|| !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
					continue;
			}

			_maybe_create_upload_path($path);
			$filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
			$newFilePath = $path . $filename;
			if (move_uploaded_file($tmpFilePath, $newFilePath)) {
				array_push($uploaded_files, [
					'file_name' => $filename,
					'filetype'  => $_FILES[$index_name]['type'][$i],
				]);

				$attachment   = [];
				$attachment[] = [
					'file_name' => $filename,
					'filetype'  => $_FILES[$index_name]['type'][$i],
				];

				$CI->misc_model->add_attachment_to_database($process_id, 'wm_process', $attachment);

				if (is_image($newFilePath)) {
					create_img_thumb($path, $filename);
				}
			}
		}
	}
}
if (count($uploaded_files) > 0) {
	return $uploaded_files;
}
return false;
}

function get_warranty_status_by_id($id, $type)
{
	$CI       = &get_instance();

	if($type == 'warranty_claim'){
		$statuses = wm_warranty_claim_status();
		$status = [
			'id'             => 'submitted',
			'color'          => '#9e9e9e',
			'name'           => _l('wm_submitted'),
			'order'          => 1,
			'filter_default' => true,
		];
	}else{
		$statuses = wm_warranty_receipt_process_status();
		$status = [
			'id'             => 'pendding',
			'color'          => '#9e9e9e',
			'name'           => _l('wm_pendding'),
			'order'          => 1,
			'filter_default' => true,
		];
	}

	foreach ($statuses as $s) {
		if ($s['id'] == $id) {
			$status = $s;

			break;
		}
	}

	return $status;
}


function render_warranty_status_html($id, $type, $status_value = '', $ChangeStatus = true)
{
	$status          = get_warranty_status_by_id($status_value, $type);

	if($type == 'warranty_claim'){
		$task_statuses = wm_warranty_claim_status();
	}else{
		$task_statuses = sm_service_status();
	}

	$outputStatus    = '';

	$outputStatus .= '<span class="inline-block label" style="color:' . $status['color'] . ';border:1px solid ' . $status['color'] . '" task-status-table="' . $status_value . '">';
	$outputStatus .= $status['name'];
	$canChangeStatus = (has_permission('warranty_management', '', 'edit') || is_admin());

	if ($canChangeStatus && $ChangeStatus) {
		$outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
		$outputStatus .= '<a href="#" class="dropdown-toggle text-dark dropdown-font-size" id="tableTaskStatus-' . $id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
		$outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
		$outputStatus .= '</a>';

		$outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskStatus-' . $id . '">';
		foreach ($task_statuses as $taskChangeStatus) {
			if ($status_value != $taskChangeStatus['id']) {
				$outputStatus .= '<li>
				<a href="#" onclick="warranty_status_mark_as(\'' . $taskChangeStatus['id'] . '\',' . $id . ',\'' . $type . '\'); return false;">
				' . _l('task_mark_as', $taskChangeStatus['name']) . '
				</a>
				</li>';
			}
		}
		$outputStatus .= '</ul>';
		$outputStatus .= '</div>';
	}

	$outputStatus .= '</span>';

	return $outputStatus;
}

/**
 * wm get status modules
 * @param  [type] $module_name 
 * @return [type]              
 */
function wm_get_status_modules($module_name){
	$CI             = &get_instance();

	$sql = 'select * from '.db_prefix().'modules where module_name = "'.$module_name.'" AND active =1 ';
	$module = $CI->db->query($sql)->row();
	if($module){
		return true;
	}else{
		return false;
	}
}

/**
 * get client warranty claim process
 * @param  [type] $client_id 
 * @return [type]            
 */
function get_client_warranty_claim_process($client_id)
{
	$arr_warranty_claim_process = [];
	$CI             = &get_instance();

	$sql = "SELECT * FROM `tblwm_warranty_claim_informations` 
	LEFT JOIN tblwm_warranty_claim_information_details on tblwm_warranty_claim_informations.id = tblwm_warranty_claim_information_details.warranty_claim
	WHERE tblwm_warranty_claim_informations.client_id = ".$client_id." AND status NOT IN ('complete', 'closed', 'cancelled')";

	$warranty_claim_process = $CI->db->query($sql)->result_array();
	foreach ($warranty_claim_process as $value) {
	    $arr_warranty_claim_process[$value['invoice_id'].'_'.$value['item_id']] = $value['id'];
	}

	return $arr_warranty_claim_process;
}


/**
 * new html entity decode
 * @param  [type] $str 
 * @return [type]      
 */
if (!function_exists('new_html_entity_decode')) {
	function new_html_entity_decode($str){
	return html_entity_decode($str ?? '');
	}
}

if (!function_exists('new_strlen')) {
    
    function new_strlen($str){
        return strlen($str ?? '');
    }
}

if (!function_exists('new_str_replace')) {
    
    function new_str_replace($search, $replace, $subject){
        return str_replace($search, $replace, $subject ?? '');
    }
}

if (!function_exists('new_explode')) {

	function new_explode($delimiter, $string){
		return explode($delimiter, $string ?? '');
	}
}

function wm_get_item($id)
{
	$CI           = & get_instance();

	$CI->db->where('id', $id);
	$item_value = $CI->db->get(db_prefix() . 'items')->row();

	$name = '';
	if($item_value){
		if(wm_get_status_modules('warehouse')){
			$CI->load->model('warehouse/warehouse_model');
			$new_item_value = $CI->warehouse_model->row_item_to_variation($item_value);
			$name .= $item_value->commodity_code.'_'.$new_item_value->new_description;
		}else{
			$name .= $item_value->description;
		}

	}

	return $name;
}