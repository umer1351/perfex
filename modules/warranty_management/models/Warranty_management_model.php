<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Warranty management model
 */
class Warranty_management_model extends App_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * change setting with checkbox
	 * @param  [type] $data 
	 * @return [type]       
	 */
	public function change_setting_with_checkbox($data)
	{

		$val = $data['input_name_status'] == 'true' ? 1 : 0;

		$this->db->where('name',$data['input_name']);
		$this->db->update(db_prefix() . 'options', [
			'value' => $val,
		]);
		if ($this->db->affected_rows() > 0) {
			return true;
		}else{
			return false;
		}
	}

	/**
	 * update prefix number
	 * @param  [type] $data 
	 * @return [type]       
	 */
	public function update_prefix_number($data)
	{
		$affected_rows=0;
		foreach ($data as $key => $value) {

			if (update_option($key, $value)){
				$affected_rows++;
			}
		}

		if($affected_rows > 0){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * create code
	 * @param  [type] $rel_type 
	 * @return [type]           
	 */
	public function create_code($rel_type) {
		$str_result ='';
		$prefix_str ='';
		switch ($rel_type) {
			case 'receipt_process_code':
			$prefix_str .= get_option('wm_warranty_receipt_process_prefix');
			$next_number = (int) get_option('wm_warranty_receipt_process_number');
			$str_result .= $prefix_str.str_pad($next_number,5,'0',STR_PAD_LEFT);
			break;

			case 'warranty_claim_code':
			$prefix_str .= get_option('wm_warranty_claim_prefix');
			$next_number = (int) get_option('wm_warranty_claim_number');
			$str_result .= $prefix_str.str_pad($next_number,5,'0',STR_PAD_LEFT);
			break;


			default:
				# code...
			break;
		}

		return $str_result;
	}

	/**
	 * get item group
	 * @param  boolean $id     
	 * @param  boolean $active 
	 * @return [type]          
	 */
	public function get_item_group($id = false, $active = false)
	{

		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'items_groups')->row();
		}
		if ($id == false) {
			return $this->db->get(db_prefix() . 'items_groups')->result_array();
		}
	}

	/**
	 * get warranty receipt process
	 * @param  boolean $id 
	 * @return [type]      
	 */
	public function get_warranty_receipt_process($id = false)
	{
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			$warranty_receipt_process = $this->db->get(db_prefix() . 'wm_warranty_receipt_process')->row();

			$this->db->where('warranty_receipt_id', $id);
			$warranty_receipt_process->details = $this->db->get(db_prefix() . 'wm_warranty_receipt_process_details')->result_array();

			return $warranty_receipt_process;
		}
		if ($id == false) {
			return $this->db->query('select * from ' . db_prefix() . 'wm_warranty_receipt_process')->result_array();
		}
	}

	/**
	 * add warranty receipt process
	 * @param [type] $data 
	 */
	public function add_warranty_receipt_process($data)
	{
		$data['datecreated'] = date("Y-m-d H:i:s");
		$data['staffid'] = get_staff_user_id();

		$this->db->insert(db_prefix().'wm_warranty_receipt_process',$data);
		$insert_id = $this->db->insert_id();

		if ($insert_id) {
			update_option('wm_warranty_receipt_process_number', get_option('wm_warranty_receipt_process_number')+1);
			return $insert_id;
		}
		return false;
	}

	/**
	 * update warranty receipt process
	 * @param  [type] $data 
	 * @param  [type] $id   
	 * @return [type]       
	 */
	public function update_warranty_receipt_process($data, $id)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'wm_warranty_receipt_process', $data);
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}

		if($affected_rows > 0){
			return true;
		}
		return false;  
	}
	
	/**
	 * delete warranty receipt process
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_warranty_receipt_process($id)
	{	
		$affected_rows = 0;
		//get operations by routing id
		$operations = $this->get_process('', $id);
		foreach ($operations as $value) {
			$delete_result = $this->delete_process($value['id']);
			if($delete_result){
				$affected_rows++;
			}
		}

		//delete data
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'wm_warranty_receipt_process');
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}

		if($affected_rows > 0){
			return true;
		}
		return false;
	}

	/**
	 * get process
	 * @param  boolean $id         
	 * @param  boolean $routing id 
	 * @return [type]              
	 */
	public function get_process($id=false, $warranty_receipt_id = false)
	{
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'wm_warranty_receipt_process_details')->row();
		}

		if($warranty_receipt_id != false){
			$this->db->where('warranty_receipt_id', $warranty_receipt_id);
			$this->db->order_by('order_number', 'asc');

			return $this->db->get(db_prefix() . 'wm_warranty_receipt_process_details')->result_array();
		}

		if ($id == false) {
			return $this->db->query('select * from ' . db_prefix() . 'wm_warranty_receipt_process_details')->result_array();
		}
	}

	/**
	 * add process
	 * @param [type] $data 
	 */
	public function add_process($data)
	{

		if(isset($data['person_in_charge'])){
			$data['person_in_charge'] = implode(",", $data['person_in_charge']);
		}else{
			$data['person_in_charge'] = null;
		}

		if(isset($data['file'])){
			unset($data['file']);
		}
		$data['datecreated'] = date("Y-m-d H:i:s");

		$this->db->insert(db_prefix().'wm_warranty_receipt_process_details',$data);
		$insert_id = $this->db->insert_id();

		if ($insert_id) {
			return $insert_id;
		}
		return false;
	}

	/**
	 * update process
	 * @param  [type] $data 
	 * @param  [type] $id   
	 * @return [type]       
	 */
	public function update_process($data, $id)
	{
		$affected_rows=0;

		if(isset($data['person_in_charge'])){
			$data['person_in_charge'] = implode(",", $data['person_in_charge']);
		}else{
			$data['person_in_charge'] = null;
		}

		if(isset($data['file'])){
			unset($data['file']);
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'wm_warranty_receipt_process_details', $data);
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}

		if($affected_rows > 0){
			return true;
		}
		return false;   
	}

	/**
	 * delete process
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_process($id)
	{	
		//delete attachment file
		$files = $this->wm_get_attachments_file($id, 'wm_process');
		foreach ($files as $file_key => $file_value) {
			$this->delete_wm_attachment_file($file_value['id'], WARRANTY_MANAGEMENT_PROCESS_UPLOAD);
		}

		//delete data
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'wm_warranty_receipt_process_details');
		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * wm get attachments file
	 * @param  [type] $rel_id   
	 * @param  [type] $rel_type 
	 * @return [type]           
	 */
	public function wm_get_attachments_file($rel_id, $rel_type)
	{
		//rel_id = id, rel_type = 'mrp_operation'
		$this->db->order_by('dateadded', 'desc');
		$this->db->where('rel_id', $rel_id);
		$this->db->where('rel_type', $rel_type);

		return $this->db->get(db_prefix() . 'files')->result_array();

	}

	/**
	 * delete wm attachment file
	 * @param  [type] $attachment_id 
	 * @param  [type] $folder_name   
	 * @return [type]                
	 */
	public function delete_wm_attachment_file($attachment_id, $folder_name)
	{
		$deleted    = false;
		$attachment = $this->misc_model->get_file($attachment_id);
		if ($attachment) {
			if (empty($attachment->external)) {
				unlink($folder_name .$attachment->rel_id.'/'.$attachment->file_name);
			}
			$this->db->where('id', $attachment->id);
			$this->db->delete(db_prefix() . 'files');
			if ($this->db->affected_rows() > 0) {
				$deleted = true;
				log_activity('WM Attachment Deleted [ID: ' . $attachment->rel_id . '] folder name: '.$folder_name);
			}

			if (is_dir($folder_name .$attachment->rel_id)) {
				// Check if no attachments left, so we can delete the folder also
				$other_attachments = list_files($folder_name .$attachment->rel_id);
				if (count($other_attachments) == 0) {
					// okey only index.html so we can delete the folder also
					delete_dir($folder_name .$attachment->rel_id);
				}
			}
		}

		return $deleted;
	}

	/**
	 * get max process order number
	 * @param  [type] $warranty_receipt_id 
	 * @return [type]                      
	 */
	public function get_max_process_order_number($warranty_receipt_id)
	{
		$sql_where = "SELECT MAX(order_number) as current_order FROM ".db_prefix()."wm_warranty_receipt_process_details
		WHERE warranty_receipt_id = ".$warranty_receipt_id;
		$current_order = $this->db->query($sql_where)->row();
		if($current_order){
			return (int)$current_order->current_order+1;
		}
		return 1;
	}

	/**
	 * get list item warranty
	 * @param  [type] $client_id 
	 * @return [type]            
	 */
	public function get_list_item_warranty($client_id)
	{

		$sql_where = db_prefix().'sm_service_details.client_id = '.$client_id.' AND date_format('.db_prefix().'sm_service_details.expiration_date, "%Y-%m-%d") >= "'.date('Y-m-d').'"';
		$this->db->select('id, client_id, order_id, invoice_id, item_name, billing_plan_rate, billing_plan_type, start_date, billing_plan_value as rate, quantity, expiration_date as warranty_period, item_id');
		$this->db->where($sql_where);
		$service_details = $this->db->get(db_prefix().'sm_service_details')->result_array();


		$goods_delivery_details = [];
		if(wm_get_status_modules('warehouse')){

			$sql_where = db_prefix().'goods_delivery.invoice_id is not null AND '.db_prefix().'goods_delivery.invoice_id != 0 AND '.db_prefix().'goods_delivery.customer_code = '.$client_id.' AND '.db_prefix().'goods_delivery.approval = 1 AND '.db_prefix().'goods_delivery.customer_code is not null AND '.db_prefix().'goods_delivery.customer_code != "" AND (date_format('.db_prefix().'goods_delivery_detail.guarantee_period, "%Y-%m-%d") >= "'.date('Y-m-d').'" OR '.db_prefix().'goods_delivery_detail.guarantee_period is NULL OR '.db_prefix().'goods_delivery_detail.guarantee_period = "" )';

			$this->db->select(db_prefix().'goods_delivery_detail.id as id, '.db_prefix() . 'goods_delivery.customer_code as client_id, '.db_prefix() . 'goods_delivery.date_add as date_add,'. db_prefix() . 'goods_delivery_detail.goods_delivery_id as order_id, '.db_prefix() . 'goods_delivery.invoice_id as invoice_id, '.db_prefix() . 'goods_delivery_detail.commodity_name as item_name, '.db_prefix() . 'goods_delivery_detail.unit_price as rate, '.db_prefix() . 'goods_delivery_detail.quantities as quantity, '.db_prefix() . 'goods_delivery_detail.expiry_date as expiry_date, '.db_prefix() . 'goods_delivery_detail.lot_number as lot_number, '.db_prefix() . 'goods_delivery_detail.serial_number as serial_number, '.db_prefix() . 'goods_delivery_detail.guarantee_period as warranty_period, '.db_prefix() . 'goods_delivery_detail.commodity_code as item_id');

			$this->db->join(db_prefix() . 'goods_delivery', '' . db_prefix() . 'goods_delivery.id = ' . db_prefix() . 'goods_delivery_detail.goods_delivery_id', 'left');

			$this->db->where($sql_where);
			$goods_delivery_details = $this->db->get(db_prefix().'goods_delivery_detail')->result_array();
		}

		return array_merge($service_details, $goods_delivery_details);
	}

	/**
	 * get list item warranty by_invoice
	 * @param  [type] $invoice_id 
	 * @return [type]             
	 */
	public function get_list_item_warranty_by_invoice($invoice_id = '', $item_id = '')
	{
		$service_details = [];
		$invoice_items = [];
		if(wm_get_status_modules('service_management')){
			$sql_where = db_prefix().'sm_service_details.invoice_id = '.$invoice_id.' AND date_format('.db_prefix().'sm_service_details.expiration_date, "%Y-%m-%d") >= "'.date('Y-m-d').'"';
			$this->db->select('id, client_id, order_id, invoice_id, item_name, billing_plan_rate, billing_plan_type, start_date, billing_plan_value as rate, quantity, expiration_date as warranty_period, item_id');
			$this->db->where($sql_where);
			if(new_strlen($item_id) > 0){
				$this->db->where(db_prefix().'sm_service_details.item_id = '.$item_id);
			}
			$service_details = $this->db->get(db_prefix().'sm_service_details')->result_array();
		}else{
			$this->load->model('invoices_model');
			$invoice = $this->invoices_model->get($invoice_id);
			if($invoice){
				if($invoice->items){
					foreach ($invoice as $key => $value) {
						$value['client_id'] = $value['userid'];
						$value['order_id'] = $invoice_id;
						$value['invoice_id'] = $invoice_id;
						$value['item_name'] = $value['description'];
						$value['quantity'] = $value['qty'];
						$value['warranty_period'] = 0;
						$value['item_id'] = $this->cs_get_itemid_from_name($value['description']);
						$invoice_items[] = $value;
					}
				}
			}

		}

		$goods_delivery_details = [];
		if(wm_get_status_modules('warehouse')){
			$sql_where = db_prefix().'goods_delivery.invoice_id = '.$invoice_id.' AND '.db_prefix().'goods_delivery.approval = 1 AND '.db_prefix().'goods_delivery.customer_code is not null AND '.db_prefix().'goods_delivery.customer_code != "" AND (date_format('.db_prefix().'goods_delivery_detail.guarantee_period, "%Y-%m-%d") >= "'.date('Y-m-d').'" OR '.db_prefix().'goods_delivery_detail.guarantee_period is NULL OR '.db_prefix().'goods_delivery_detail.guarantee_period = "" )';

			$this->db->select(db_prefix().'goods_delivery_detail.id as id, '.db_prefix() . 'goods_delivery.customer_code as client_id, '.db_prefix() . 'goods_delivery.date_add as date_add,'. db_prefix() . 'goods_delivery_detail.goods_delivery_id as order_id, '.db_prefix() . 'goods_delivery.invoice_id as invoice_id, '.db_prefix() . 'goods_delivery_detail.commodity_name as item_name, '.db_prefix() . 'goods_delivery_detail.unit_price as rate, '.db_prefix() . 'goods_delivery_detail.quantities as quantity, '.db_prefix() . 'goods_delivery_detail.expiry_date as expiry_date, '.db_prefix() . 'goods_delivery_detail.lot_number as lot_number, '.db_prefix() . 'goods_delivery_detail.serial_number as serial_number, '.db_prefix() . 'goods_delivery_detail.guarantee_period as warranty_period, '.db_prefix() . 'goods_delivery_detail.commodity_code as item_id');

			$this->db->join(db_prefix() . 'goods_delivery', '' . db_prefix() . 'goods_delivery.id = ' . db_prefix() . 'goods_delivery_detail.goods_delivery_id', 'left');

			$this->db->where($sql_where);
			if(new_strlen($item_id) > 0){
				$this->db->where(db_prefix().'goods_delivery_detail.commodity_code = '.$item_id);
			}
			$goods_delivery_details = $this->db->get(db_prefix().'goods_delivery_detail')->result_array();
		}

		return array_merge($service_details, $goods_delivery_details, $invoice_items);
	}

	/**
	 * create warranty receipt process row template
	 * @param  string  $name                     
	 * @param  string  $warranty_claim           
	 * @param  string  $warranty_claim_detail_id 
	 * @param  string  $process_name             
	 * @param  string  $order_number             
	 * @param  string  $person_in_charge         
	 * @param  string  $estimate_time            
	 * @param  string  $description              
	 * @param  string  $step_icon                
	 * @param  string  $item_key                 
	 * @param  boolean $is_edit                  
	 * @return [type]                            
	 */
	public function create_warranty_receipt_process_row_template($name = '', $warranty_claim = '',  $warranty_claim_detail_id = '', $process_name = '', $order_number = '', $person_in_charge = '', $estimate_time = '', $description = '', $step_icon = '', $item_key = '', $warranty_receipt_process_detail_id = '', $is_edit = false ) {

		$this->load->model('staff_model');
		$staffs = $this->staff_model->get();
		
		$row = '';

		$name_warranty_claim = 'warranty_claim';
		$name_warranty_claim_detail_id = 'warranty_claim_detail_id';
		$name_process_name = 'process_name';
		$name_order_number = 'order_number';
		$name_person_in_charge = 'person_in_charge';
		$name_estimate_time = 'estimate_time';
		$name_description = 'description';
		$name_step_icon = 'step_icon';
		$name_warranty_receipt_process_detail_id = 'warranty_receipt_process_detail_id';


		if ($name == '') {
			$row .= '<tr class="main">
			<td></td>';

		} else {
			$row .= '<tr class="sortable item">
			<td class="dragger"><input type="hidden" class="order" name="' . $name . '[order]"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '"></td>';
			$name_warranty_claim = $name . '[warranty_claim]';
			$name_warranty_claim_detail_id = $name . '[warranty_claim_detail_id]';
			$name_process_name = $name . '[process_name]';
			$name_order_number = $name . '[order_number]';
			$name_person_in_charge = $name . '[person_in_charge][]';
			$name_estimate_time = $name . '[estimate_time]';
			$name_description = $name . '[description]';
			$name_step_icon = $name . '[step_icon]';
			$name_warranty_receipt_process_detail_id = $name . '[warranty_receipt_process_detail_id]';

		}

		$row .= '<td class="hide">' . render_input($name_warranty_claim, '', $warranty_claim) . '</td>';
		$row .= '<td class="hide">' . render_input($name_warranty_claim_detail_id, '', $warranty_claim_detail_id) . '</td>';
		$row .= '<td class="hide">' . render_textarea($name_process_name, '', $process_name, ['rows' => 2, 'placeholder' => _l('item_description_placeholder'), 'readonly' => true] ) . '</td>';
		$row .= '<td class="hide order_number">' . render_input($name_order_number, '', $order_number, 'text') . '</td>';
		$row .= '<td class="estimate_time hide">' . render_input($name_estimate_time, '', $estimate_time, 'number') . '</td>';
		$row .= '<td class="person_in_charge hide">' .
		render_select($name_person_in_charge, $staffs,array('staffid', array('firstname', 'lastname')),'',explode(",", $person_in_charge), ['multiple' => true], [], '', '', false).
		'</td>';
		$row .= '<td class="hide">' . render_textarea($name_description, '', $description, ['readonly' => true] ) . '</td>';
		$row .= '<td class="hide">' . render_input($name_step_icon, '', $step_icon ) . '</td>';
		$row .= '<td class="hide">' . render_input($name_warranty_receipt_process_detail_id, '', $warranty_receipt_process_detail_id ) . '</td>';

		$staff_name_output = '';
		$staff_ids       = explode(",", $person_in_charge);

		$list_staff_name = '';
		$exportjob_positions = '';
		if($staff_ids != null){
			foreach ($staff_ids as $key => $staff_id) {

				$list_staff_name .= '<li class="text-success mbot10 mtop"><a href="#"  class="text-left">'.get_staff_full_name($staff_id). '</a></li>';
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


		$row .= '<td class="order_number text-left">' .$order_number.	'</td>';
		$row .= '<td class="process_name text-left">' .$process_name.	'</td>';
		$row .= '<td class="process_name text-right">' .$staff_name_output1.	'</td>';
		$row .= '<td class="process_name text-right">' .$estimate_time.	'</td>';
		$row .= '<td class="process_name text-right">' .$description.	'</td>';

		$row .= '</tr>';
		return $row;
	}

	/**
	 * add warranty claim
	 * @param [type]  $data 
	 * @param boolean $id   
	 */
	public function add_warranty_claim($data, $id = false) {

		$warranty_claims = [];
		if (isset($data['newitems'])) {
			$warranty_claims = $data['newitems'];
			unset($data['newitems']);
		}

		$claim_informations = [];
		$claim_informations['claim_code'] = $data['claim_code'];
		$claim_informations['created_id'] = $data['created_id'];
		$claim_informations['created_type'] = $data['created_type'];
		$claim_informations['client_id'] = $data['client_id'];
		$claim_informations['client_note'] = $data['client_note'];
		$claim_informations['admin_note'] = $data['admin_note'];
		$claim_informations['description'] = $data['description'];
		$claim_informations['status'] = $data['created_type'] == 'client' ? 'submitted' : 'approved';
		$claim_informations['datecreated'] = to_sql_date($data['datecreated'], true);

		if(isset($data['invoice_id'])){
			$claim_informations['invoice_id'] = $data['invoice_id'];
		}

		$this->db->insert(db_prefix() . 'wm_warranty_claim_informations', $claim_informations);
		$insert_id = $this->db->insert_id();

		/*update save note*/

		if (isset($insert_id)) {
			update_option('wm_warranty_claim_number', get_option('wm_warranty_claim_number')+1);

			/*add warranty claim detail*/
			$warranty_claim_information_details = [];
			$get_order_delivery_from_invoice = $this->get_order_delivery_from_invoice($data['invoice_id']);
			$get_item_warranty_by_invoice = $this->get_list_item_warranty_by_invoice($data['invoice_id'], $data['item_id']);
			$start_date = null;
			$expiration_date = null;
			$order_id = null;

			if(count($get_item_warranty_by_invoice) > 0){
				if(isset($get_item_warranty_by_invoice[0]['start_date'])){
					$start_date = $get_item_warranty_by_invoice[0]['start_date'];
					$expiration_date = $get_item_warranty_by_invoice[0]['warranty_period'];
					$order_id = $get_item_warranty_by_invoice[0]['order_id'];
				}

				if(isset($get_item_warranty_by_invoice[0]['date_add'])){
					$start_date = $get_item_warranty_by_invoice[0]['start_date'];
					$expiration_date = $get_item_warranty_by_invoice[0]['warranty_period'];
				}

			}

			$warranty_claim_information_details['warranty_claim'] = $insert_id;
			$warranty_claim_information_details['warranty_receipt_process_id'] = $data['warranty_receipt_process_id'];
			$warranty_claim_information_details['item_id'] = $data['item_id'];
			$warranty_claim_information_details['items_name'] = wm_get_item($data['item_id']);
			$warranty_claim_information_details['rel_id'] = $get_order_delivery_from_invoice['rel_id'];
			$warranty_claim_information_details['rel_type'] = $get_order_delivery_from_invoice['rel_type'];
			$warranty_claim_information_details['invoice_id'] = $data['invoice_id'];
			$warranty_claim_information_details['order_id'] = $order_id;
			$warranty_claim_information_details['start_date'] = $start_date;
			$warranty_claim_information_details['expiration_date'] = $expiration_date;
			$warranty_claim_information_details['datecreated'] = date('Y-m-d H:i:s');
			$this->db->insert(db_prefix() . 'wm_warranty_claim_information_details', $warranty_claim_information_details);
			$warranty_claim_detail_id = $this->db->insert_id();


			foreach ($warranty_claims as $key => $warranty_claim) {
				$warranty_claim['warranty_claim'] = $insert_id;
				$warranty_claim['warranty_claim_detail_id'] = $warranty_claim_detail_id;
				$warranty_claim['person_in_charge'] = implode(",", $warranty_claim['person_in_charge']);
				$warranty_claim['datecreated'] = date('Y-m-d H:i:s');
				if($key == 0){
					$warranty_claim['status'] = 'pendding';
				}else{
					$warranty_claim['status'] = null;
				}

				unset($warranty_claim['order']);
				unset($warranty_claim['id']);

				$this->db->insert(db_prefix() . 'wm_warranty_process_details', $warranty_claim);
				$warranty_process_detail_id = $this->db->insert_id();

				$this->copy_warranty_process_attachment($warranty_claim['warranty_receipt_process_detail_id'], $warranty_process_detail_id);
			}
		}

		if (isset($insert_id)) {
			return $insert_id;
		}
		return false;
	}

	/**
	 * update warranty claim
	 * @param  [type]  $data 
	 * @param  boolean $id   
	 * @return [type]        
	 */
	public function update_warranty_claim($data, $id = false) {


		$results=0;

		$warranty_claims = [];
		$update_warranty_claims = [];
		$remove_warranty_claims = [];
		if(isset($data['isedit'])){
			unset($data['isedit']);
		}

		if (isset($data['newitems'])) {
			$warranty_claims = $data['newitems'];
			unset($data['newitems']);
		}

		if (isset($data['items'])) {
			$update_warranty_claims = $data['items'];
			unset($data['items']);
		}
		if (isset($data['removed_items'])) {
			$remove_warranty_claims = $data['removed_items'];
			unset($data['removed_items']);
		}


		$claim_informations = [];
		$claim_informations['client_id'] = $data['client_id'];
		$claim_informations['client_note'] = $data['client_note'];
		$claim_informations['admin_note'] = $data['admin_note'];
		$claim_informations['description'] = $data['description'];

		if(isset($data['invoice_id'])){
			$claim_informations['invoice_id'] = $data['invoice_id'];
		}

		$warranty_claim_id = $data['id'];
		unset($data['id']);

		$this->db->where('id', $warranty_claim_id);
		$this->db->update(db_prefix() . 'wm_warranty_claim_informations', $claim_informations);
		if ($this->db->affected_rows() > 0) {
			$results++;
		}

		/*add warranty claim detail*/
		$warranty_claim_information_details = [];
		$get_order_delivery_from_invoice = $this->get_order_delivery_from_invoice($data['invoice_id']);
		$get_item_warranty_by_invoice = $this->get_list_item_warranty_by_invoice($data['invoice_id'], $data['item_id']);
		$start_date = null;
		$expiration_date = null;
		$order_id = null;

		if(count($get_item_warranty_by_invoice) > 0){
			if(isset($get_item_warranty_by_invoice[0]['start_date'])){
				$start_date = $get_item_warranty_by_invoice[0]['start_date'];
				$expiration_date = $get_item_warranty_by_invoice[0]['warranty_period'];
				$order_id = $get_item_warranty_by_invoice[0]['order_id'];
			}

			if(isset($get_item_warranty_by_invoice[0]['date_add'])){
				$start_date = $get_item_warranty_by_invoice[0]['start_date'];
				$expiration_date = $get_item_warranty_by_invoice[0]['warranty_period'];
			}
		}

		$warranty_claim_information_details['warranty_receipt_process_id'] = $data['warranty_receipt_process_id'];
		$warranty_claim_information_details['item_id'] = $data['item_id'];
		$warranty_claim_information_details['items_name'] = wm_get_item($data['item_id']);
		$warranty_claim_information_details['rel_id'] = $get_order_delivery_from_invoice['rel_id'];
		$warranty_claim_information_details['rel_type'] = $get_order_delivery_from_invoice['rel_type'];
		$warranty_claim_information_details['invoice_id'] = $data['invoice_id'];
		$warranty_claim_information_details['order_id'] = $order_id;
		$warranty_claim_information_details['start_date'] = $start_date;
		$warranty_claim_information_details['expiration_date'] = $expiration_date;
		
		$this->db->where('id', $data['claim_information_detail_id']);
		$this->db->update(db_prefix() . 'wm_warranty_claim_information_details', $warranty_claim_information_details);

		$arr_warranty_process_detail_id = [];
		foreach ($update_warranty_claims as $warranty_claim) {
			$arr_warranty_process_detail_id[] = $warranty_claim['id'];

			$warranty_claim['person_in_charge'] = implode(",", $warranty_claim['person_in_charge']);

			unset($warranty_claim['order']);

			$this->db->where('id', $warranty_claim['id']);
			if ($this->db->update(db_prefix() . 'wm_warranty_process_details', $warranty_claim)) {
				$results++;
			}
		}

		if(count($arr_warranty_process_detail_id) > 0){
			$this->db->where('warranty_claim', $warranty_claim_id);
			$this->db->where('id NOT IN ('.implode(",", $arr_warranty_process_detail_id).')');
			if ($this->db->delete(db_prefix() . 'wm_warranty_process_details')) {
				$results++;
			}
		}else{
			$this->db->where('warranty_claim', $warranty_claim_id);
			if ($this->db->delete(db_prefix() . 'wm_warranty_process_details')) {
				$results++;
			}
		}


		foreach ($warranty_claims as $warranty_claim) {
			$warranty_claim['warranty_claim'] = $warranty_claim_id;
			$warranty_claim['warranty_claim_detail_id'] = $data['claim_information_detail_id'];
			$warranty_claim['person_in_charge'] = implode(",", $warranty_claim['person_in_charge']);
			$warranty_claim['datecreated'] = date('Y-m-d H:i:s');

			unset($warranty_claim['order']);
			unset($warranty_claim['id']);

			$this->db->insert(db_prefix() . 'wm_warranty_process_details', $warranty_claim);
			$warranty_process_detail_id = $this->db->insert_id();

			if($warranty_process_detail_id){
				$this->copy_warranty_process_attachment($warranty_claim['warranty_receipt_process_detail_id'], $warranty_process_detail_id);
				$results++;
			}
		}


		hooks()->do_action('wm_after_warranty_claim_updated', $order_id);

		return $results > 0 ? true : false;

	}

	/**
	 * get warranty claim
	 * @param  boolean $id    
	 * @param  string  $where 
	 * @return [type]         
	 */
	public function get_warranty_claim($id = false, $where = '')
	{

		if (is_numeric($id)) {
			$this->db->where('id', $id);
			$warranty_claim = $this->db->get(db_prefix() . 'wm_warranty_claim_informations')->row();

			$this->db->where('warranty_claim', $id);
			$warranty_claim->warranty_claim_information_details = $this->db->get(db_prefix().'wm_warranty_claim_information_details')->result_array();

			$this->db->where('warranty_claim', $id);
			$warranty_claim->warranty_process_details = $this->db->get(db_prefix().'wm_warranty_process_details')->result_array();

			return $warranty_claim;
		}
		if ($id == false) {
			if(new_strlen($where) > 0){
				$this->db->where($where);
			}
			return $this->db->get(db_prefix() . 'wm_warranty_claim_informations')->result_array();
		}

	}

	/**
	 * delete warranty claim
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_warranty_claim($id)
	{

		hooks()->do_action('sm_before_warranty_claim_informations_deleted', $id);
		$affected_rows = 0;

		$this->db->where('warranty_claim', $id);
		$this->db->delete(db_prefix() . 'wm_warranty_claim_information_details');
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}

		$this->db->where('warranty_claim', $id);
		$this->db->delete(db_prefix() . 'wm_warranty_process_details');
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}
		

		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'wm_warranty_claim_informations');
		if ($this->db->affected_rows() > 0) {

			$affected_rows++;
		}

		if ($affected_rows > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get order delivery from invoice
	 * @param  [type] $invoice_id 
	 * @return [type]             
	 */
	public function get_order_delivery_from_invoice($invoice_id)
	{
		$this->db->where('invoice_id', $invoice_id);
		$service_detail = $this->db->get(db_prefix().'sm_service_details')->row();
		if($service_detail){
			return ['rel_type' => 'sm_order', 'rel_id' => $service_detail->order_id];
		}

		if(wm_get_status_modules('warehouse')){
			$this->db->where('invoice_id', $invoice_id);
			$goods_delivery = $this->db->get(db_prefix().'goods_delivery')->row();
			if($goods_delivery){
				return ['rel_type' => 'delivery', 'rel_id' => $goods_delivery->id];
			}
		}

		return ['rel_type' => '', 'rel_id' => ''];
	}

	/**
	 * warranty status mark as
	 * @param  [type] $status 
	 * @param  [type] $id     
	 * @param  [type] $type   
	 * @return [type]         
	 */
	public function warranty_status_mark_as($status, $id, $type)
	{

		$status_f = false;
		if($type == 'warranty_claim'){
			$this->db->where('id', $id);
			$this->db->update(db_prefix() . 'wm_warranty_claim_informations', ['status' => $status]);
			if ($this->db->affected_rows() > 0) {
				$status_f = true;
				//write log
				$this->wm_add_timeline_log($id, 'warranty_claim', _l('wm_change_status_to').': '. _l('wm_'.$status));

			}
		}elseif($type == 'warranty_claim_process'){
			$this->db->where('id', $id);
			$this->db->update(db_prefix() . 'wm_warranty_process_details', ['status' => $status]);
			if ($this->db->affected_rows() > 0) {
				$status_f = true;
				//write log
				$this->wm_add_timeline_log($id, 'warranty_claim_process', _l('wm_change_status_to').': '. _l('wm_'.$status));

			}
		}
		return $status_f;
	}

	/**
	 * wm get timeline log
	 * @param  [type] $id       
	 * @param  [type] $rel_type 
	 * @return [type]           
	 */
	public function wm_get_timeline_log($id, $rel_type)
	{
		$this->db->where('rel_id', $id);
		$this->db->where('rel_type', $rel_type);
		$this->db->order_by('date', 'ASC');

		return $this->db->get(db_prefix() . 'wm_timeline_logs')->result_array();
	}

	/**
	 * wm get one timeline log
	 * @param  [type] $id       
	 * @param  [type] $rel_type 
	 * @return [type]           
	 */
	public function wm_get_one_timeline_log($id, $rel_type)
	{
		$this->db->where('id', $id);
		$this->db->order_by('date', 'ASC');

		return $this->db->get(db_prefix() . 'wm_timeline_logs')->row();
	}

	/**
	 * wm add timeline log
	 * @param  [type] $id          
	 * @param  [type] $rel_type    
	 * @param  [type] $description 
	 * @param  string $date        
	 * @return [type]              
	 */
	public function wm_add_timeline_log($id, $rel_type, $description, $date = '')
	{
		if(new_strlen($date) == 0){
			$date = date('Y-m-d H:i:s');
		}
		$log = [
			'date'            => $date,
			'description'     => $description,
			'rel_id'          => $id,
			'rel_type'          => $rel_type,
			'staffid'         => get_staff_user_id(),
			'full_name'       => get_staff_full_name(get_staff_user_id()),
		];

		$this->db->insert(db_prefix() . 'wm_timeline_logs', $log);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			
			hooks()->do_action('affter_wm_timeline_log', $insert_id);
			return $insert_id;
		}
		return false;
	}

	/**
	 * delete timeline log
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_timeline_log($id)
	{
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'wm_timeline_logs');

		if ($this->db->affected_rows() > 0) {
			return true;
		}

		return false;
	}

	/**
	 * get warranty claim activity log
	 * @param  [type] $claim_id 
	 * @return [type]           
	 */
	public function get_warranty_claim_activity_log($claim_id)
	{
		$warranty_process_detail_ids = [];
		$arr_activity_log = [];

		$this->db->where('warranty_claim', $claim_id);
		$warranty_process_details = $this->db->get(db_prefix() . 'wm_warranty_process_details')->result_array();
		if(count($warranty_process_details) > 0){
			foreach ($warranty_process_details as $warranty_process_detail) {
				$warranty_process_detail_ids[]  = $warranty_process_detail['id'];
			}
		}

		$this->db->or_group_start();
		$this->db->where('rel_id', $claim_id);
		$this->db->where('rel_type', 'warranty_claim');
		$this->db->group_end();

		if(count($warranty_process_detail_ids) > 0){
			$this->db->or_group_start();
			$this->db->where('rel_id IN ('.implode(',', $warranty_process_detail_ids).')');
			$this->db->where('rel_type', 'warranty_claim_process');
			$this->db->group_end();
		}

		$this->db->order_by('date', 'desc');
		$shipment_activity_log = $this->db->get(db_prefix().'wm_timeline_logs')->result_array();

		return $shipment_activity_log;
	}
	
	/**
	 * update warranty claim activity log
	 * @param  [type] $id   
	 * @param  [type] $data 
	 * @return [type]       
	 */
	public function update_warranty_claim_activity_log($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wm_timeline_logs', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get warranty process detail
	 * @param  boolean $id    
	 * @param  string  $where 
	 * @return [type]         
	 */
	public function get_warranty_process_detail($id = false)
	{
		$this->db->where('id', $id);
		return $this->db->get(db_prefix().'wm_warranty_process_details')->row();
	}

	/**
	 * get_warranty_process_previous_next
	 * @param  [type] $warranty_process_detail_id 
	 * @param  [type] $warranty_claim_id          
	 * @return [type]                             
	 */
	public function get_warranty_process_previous_next($warranty_process_detail_id, $warranty_claim_id)
	{
		$prev_id='';
		$next_id='';
		$pager_value=0;
		$pager_limit=0;

		$warranty_processs=[];
		$this->db->where('warranty_claim', $warranty_claim_id);
		$this->db->order_by('id', 'asc');
		$warranty_process_details= $this->db->get(db_prefix() . 'wm_warranty_process_details')->result_array();
		foreach ($warranty_process_details as $key => $value) {
			$value['index'] = $key+1;
		    $warranty_processs[$value['id']] =  $value;
		}

		$pager_value = $warranty_processs[(int)$warranty_process_detail_id]['index'];
		$pager_limit = count($warranty_process_details);
		//prev_id
		if(count($warranty_processs) > 0){
			if(isset($warranty_processs[(int)$warranty_process_detail_id+1])){
				$prev_id = (int)$warranty_process_detail_id+1;
			}else{
				$prev_id = $warranty_process_details[count($warranty_processs)-1]['id'];
			}
		}else{
			$prev_id = (int)$warranty_process_detail_id;

		}

		//next_id
		if(count($warranty_processs) > 0){
			if(isset($warranty_processs[(int)$warranty_process_detail_id-1])){
				$next_id = (int)$warranty_process_detail_id-1;

			}else{
				$next_id = $warranty_process_details[0]['id'];

			}
		}else{
			$next_id = (int)$warranty_process_detail_id;

		}

		$data=[];
		$data['pager_value']= $pager_value;
		$data['pager_limit']= $pager_limit;
		$data['prev_id']= $prev_id;
		$data['next_id']= $next_id;

		return $data;
	}

	/**
	 * get time tracking details
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function get_time_tracking_details($id)
	{
		$this->db->select(db_prefix().'wm_warranty_process_time_trackings.id , wm_warranty_process_time_trackings.warranty_process_detail_id, from_date, to_date, duration, CONCAT(firstname," ",lastname) as full_name');
		$this->db->from(db_prefix() . 'wm_warranty_process_time_trackings');
		$this->db->join(db_prefix() . 'staff', db_prefix() . 'wm_warranty_process_time_trackings.staff_id = ' . db_prefix() . 'staff.staffid', 'left');
		$this->db->where('warranty_process_detail_id', $id);
		$this->db->where('to_date is not null');
		$time_trackings = $this->db->get()->result_array();

		$row = count($time_trackings);

		$data=[];
		$data['rows'] = $row;
		$data['time_trackings'] = $time_trackings;

		return $data;
	}

	/**
	 * copy warranty process attachment
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function copy_warranty_process_attachment($id, $claim_process_id)
	{	
		$attachments = $this->wm_get_attachments_file($id, 'wm_process');


		if (is_dir(WARRANTY_MANAGEMENT_PROCESS_UPLOAD . $id)) {
			xcopy(WARRANTY_MANAGEMENT_PROCESS_UPLOAD . $id, WARRANTY_CLAIM_PROCESS_UPLOAD . $claim_process_id);
		}
		foreach ($attachments as $at) {

			$_at      = [];
			$_at[]    = $at;
			$external = false;
			if (!empty($at['external'])) {
				$external       = $at['external'];
				$_at[0]['name'] = $at['file_name'];
				$_at[0]['link'] = $at['external_link'];
				if (!empty($at['thumbnail_link'])) {
					$_at[0]['thumbnailLink'] = $at['thumbnail_link'];
				}
			}

			$this->misc_model->add_attachment_to_database($claim_process_id,'wm_claim_process', $_at, $external);
		}   

		return true;
	}

	/**
	 * update warranty claim process detail status
	 * @param  [type] $id   
	 * @param  [type] $data 
	 * @return [type]       
	 */
	public function update_warranty_claim_process_detail_status($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wm_warranty_process_details', $data);
		if($this->db->affected_rows() > 0){
			return true;
		}
		return false;
	}

	/**
	 * update warranty claim information status
	 * @param  [type] $id   
	 * @param  [type] $data 
	 * @return [type]       
	 */
	public function update_warranty_claim_information_status($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wm_warranty_claim_informations', $data);
		if($this->db->affected_rows() > 0){
            hooks()->do_action('warranty_claim_information_status_changed', ['id' => $id, 'data' => $data]);

			return true;
		}
		return false;
	}

	/**
	 * add time tracking
	 * @param [type] $data 
	 */
	public function add_time_tracking($data)
	{
		$insert_id = $this->db->insert(db_prefix().'wm_warranty_process_time_trackings', $data);
		if($insert_id ){
			return true;
		}
		return false;
	}

	/**
	 * update time tracking
	 * @param  [type] $warranty_process_detail_id 
	 * @param  [type] $data          
	 * @return [type]                
	 */
	public function update_time_tracking($warranty_process_detail_id, $data)
	{
		$this->db->where('warranty_process_detail_id', $warranty_process_detail_id);
		$this->db->order_by('id', 'desc');
		$time_tracking = $this->db->get(db_prefix() . 'wm_warranty_process_time_trackings')->row();

		if($time_tracking){
			$start_date = strtotime($time_tracking->from_date);
			$to_date = strtotime($data['to_date']);
			$duration = abs($to_date - $start_date)/(60);

			$data['duration'] = $duration;

			$this->db->where('id', $time_tracking->id);
			$this->db->update(db_prefix().'wm_warranty_process_time_trackings', $data);
			if($this->db->affected_rows() > 0){
				return true;
			}
		}

		return false;
	}

	/**
	 * get total duration
	 * @param  [type] $warranty_process_detail_id 
	 * @return [type]                             
	 */
	public function get_total_duration($warranty_process_detail_id)
	{
		$duration = 0;
		$this->db->select('sum(duration) as duration');
	    $this->db->where('warranty_process_detail_id', $warranty_process_detail_id);
	    $time_tracking = $this->db->get(db_prefix() . 'wm_warranty_process_time_trackings')->row();
	    if($time_tracking){
	    	$duration = $time_tracking->duration;
	    }
	    
	    return $duration;
	}

	/**
	 * warranty process detail mark as_done
	 * @param  [type] $warranty_process_detail_id 
	 * @param  [type] $warranty_claim_id          
	 * @return [type]                             
	 */
	public function  warranty_process_detail_mark_as_done($warranty_process_detail_id, $warranty_claim_id)
	{
		$affected_rows=0;
		$current_time=date('Y-m-d H:i:s');


		//Update time tracking
		$data_update=[
			'warranty_process_detail_id' => $warranty_process_detail_id,
			'to_date' => $current_time,
			'staff_id' => get_staff_user_id(),
		];
		$update_time_tracking = $this->update_time_tracking($warranty_process_detail_id, $data_update);
		if($update_time_tracking){
			$affected_rows++;
		}

		//update consumed quantity
		$duration = $this->get_total_duration($warranty_process_detail_id);

		$warranty_process_detail=[];
		$warranty_process_detail['status'] = 'complete';
		$warranty_process_detail['actual_time'] = $duration/60;

		$update_warranty_process = $this->update_warranty_process_detail_status($warranty_process_detail_id, $warranty_process_detail);
		if($update_warranty_process){
			$affected_rows++;
		}

		$this->wm_add_timeline_log($warranty_process_detail_id, 'warranty_claim_process', _l('wm_warranty_claim').' '._l('wm_change_status_to').': '. _l('wm_complete'));

		/*check update warranty claim status*/
		$this->db->where('warranty_claim', $warranty_claim_id);
		$this->db->where('status != "complete"');
		$warranty_process_detail_status = $this->db->get(db_prefix().'wm_warranty_process_details')->result_array();
		if(count($warranty_process_detail_status) == 0){
			$this->db->where('id', $warranty_claim_id);
			$this->db->update(db_prefix().'wm_warranty_claim_informations', ['status' => 'complete']);
			if($this->db->affected_rows() > 0){
				$this->wm_add_timeline_log($warranty_claim_id, 'warranty_claim', _l('wm_warranty_claim_process').' '._l('wm_change_status_to').': '. _l('wm_complete'));
				$affected_rows++;
			}

		}

		if($affected_rows > 0){
			return true;
		}
		return false;
	}

	/**
	 * update warranty process detail status
	 * @param  [type] $id   
	 * @param  [type] $data 
	 * @return [type]       
	 */
	public function update_warranty_process_detail_status($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'wm_warranty_process_details', $data);
		if($this->db->affected_rows() > 0){
			return true;
		}
		return false;
	}

	/**
	 * count warranty claim by status
	 * @param  string $client_id 
	 * @return [type]            
	 */
	public function count_warranty_claim_by_status($client_id = '')
	{
		$status = [];
		$sql_where = "SELECT count(id) as total, status, client_id FROM ".db_prefix()."wm_warranty_claim_informations
		WHERE client_id = '".$client_id."'
		GROUP BY client_id, ".db_prefix()."wm_warranty_claim_informations.status;";
		$service_detail = $this->db->query($sql_where)->result_array();
		foreach ($service_detail as $value) {
		    $status[$value['status']] = $value['total'];
		}

		return $status;
	}


	/*end file*/
}