<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Class Warranty_management
 */
class Warranty_management extends AdminController
{
	/**
	 * __construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('warranty_management_model');
		$this->load->model('service_management/service_management_model');
		hooks()->do_action('warranty_management_init');

	}

	/**
	 * setting
	 * @return [type] 
	 */
	public function setting()
	{
		if (!has_permission('warranty_management', '', 'edit') && !is_admin() && !has_permission('warranty_management', '', 'create')) {
			access_denied('warranty_management');
		}

		$data['group'] = $this->input->get('group');
		$data['title'] = _l('setting');

		$data['tab'][] = 'general';
		$data['tab'][] = 'warranty_receipt_process';
		$data['tab'][] = 'prefix_number';

		if ($data['group'] == '' || $data['group'] == 'general') {
			$data['tabs']['view'] = 'settings/general/' . $data['group'];
		}elseif($data['group'] == 'warranty_receipt_process'){
			$data['tabs']['view'] = 'settings/warranty_receipt_process/warranty_receipt_process_manage';
		}elseif($data['group'] == 'prefix_number'){
			$data['tabs']['view'] = 'settings/prefixs/prefix_number';
		}

		$this->load->view('settings/manage_setting', $data);
	}

	/**
	 * wm check box setting
	 * @return [type] 
	 */
	public function wm_check_box_setting()
	{
		$data = $this->input->post();

		if (!has_permission('service_management', '', 'edit') && !is_admin()) {
			$success = false;
			$message = _l('Not permission edit');

			echo json_encode([
				'message' => $message,
				'success' => $success,
			]);
			die;
		}

		if($data != 'null'){
			$value = $this->warranty_management_model->change_setting_with_checkbox($data);
			if($value){
				$success = true;
				$message = _l('updated_successfully');
			}else{
				$success = false;
				$message = _l('updated_false');
			}
			echo json_encode([
				'message' => $message,
				'success' => $success,
			]);
			die;
		}
	}

	/**
	 * prefix number
	 * @return [type] 
	 */
	public function prefix_number()
	{
		if (!has_permission('warranty_management', '', 'edit') && !is_admin() && !has_permission('warranty_management', '', 'create')) {
			access_denied('warranty_management');
		}

		$data = $this->input->post();

		if ($data) {

			$success = $this->warranty_management_model->update_prefix_number($data);

			if ($success == true) {

				$message = _l('wm_updated_successfully');
				set_alert('success', $message);
			}

			redirect(admin_url('warranty_management/setting?group=prefix_number'));
		}
	}

	/**
	 * warranty receipt process table
	 * @return [type] 
	 */
	public function warranty_receipt_process_table()
	{
			$this->app->get_table_data(module_views_path('warranty_management', 'settings/warranty_receipt_process/warranty_receipt_process_table'));
	}

	/**
	 * warranty receipt process modal
	 * @return [type] 
	 */
	public function warranty_receipt_process_modal()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$data=[];
		$data['code'] = $this->warranty_management_model->create_code('receipt_process_code');
		$data['product_groups'] = $this->warranty_management_model->get_item_group();

		$this->load->view('settings/warranty_receipt_process/add_warranty_receipt_process_modal', $data);
	}


	/**
	 * add routing modal
	 * @param string $id 
	 */
	public function add_warranty_receipt_process_modal($id='')
	{

		if (!has_permission('warranty_management', '', 'view')  && !is_admin()) {
			access_denied('wm_warranty_receipt_process');
		}
		
		if ($this->input->post()) {
			$data = $this->input->post();
			$data['description']     = $this->input->post('description', false);

			if ($id == '') {
				if (!has_permission('warranty_management', '', 'create') && !is_admin()) {
					access_denied('wm_warranty_receipt_process');
				}

				$id = $this->warranty_management_model->add_warranty_receipt_process($data);
				if ($id) {
					set_alert('success', _l('wm_added_successfully', _l('routing')));
					redirect(admin_url('warranty_management/process_manage/'.$id));
				}

			} else {
				if (!has_permission('warranty_management', '', 'edit') && !is_admin()) {
					access_denied('wm_warranty_receipt_process');
				}

				$response = $this->warranty_management_model->update_warranty_receipt_process($data, $id);

				if (is_array($response)) {
					if (isset($response['cant_remove_main_admin'])) {
						set_alert('warning', _l('staff_cant_remove_main_admin'));
					} elseif (isset($response['cant_remove_yourself_from_admin'])) {
						set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
					}
				} elseif ($response == true) {
					set_alert('success', _l('wm_updated_successfully', _l('routing')));
				}
				redirect(admin_url('warranty_management/process_manage/'.$id));
			}
		}

	}

	/**
	 * delete routing
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_warranty_receipt_process($id)
	{
	    if (!has_permission('warranty_management', '', 'delete')  && !is_admin()) {
			access_denied('wm_warranty_receipt_process');
		}

		$success = $this->warranty_management_model->delete_warranty_receipt_process($id);
		if ($success) {
			set_alert('success', _l('wm_deleted'));
		} else {
			set_alert('warning', _l('problem_deleting'));
		}
		redirect(admin_url('warranty_management/setting?group=warranty_receipt_process'));

	}


	/**
	 * operation manage
	 * @return [type] 
	 */
	public function process_manage($id='')
	{
	    if (!has_permission('warranty_management', '', 'view') ) {
			access_denied('work_center');
		}

		$data['title'] = _l('operation');
		if($id != ''){
			$data['warranty_receipt_process'] = $this->warranty_management_model->get_warranty_receipt_process($id);
		}
		$data['product_groups'] = $this->warranty_management_model->get_item_group();

		$this->load->view('settings/warranty_receipt_process/process_details/process_manage', $data);
	}

	/**
	 * process table
	 * @return [type] 
	 */
	public function process_table()
	{
		$this->app->get_table_data(module_views_path('warranty_management', 'settings/warranty_receipt_process/process_details/process_table'));
	}

	/**
	 * process modal
	 * @return [type] 
	 */
	public function process_modal()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$this->load->model('staff_model');
		$data=[];
		$data = $this->input->post();
		if($data['process_id'] != 0){
			$data['process'] = $this->warranty_management_model->get_process($data['process_id']);
			$data['process_attachment'] = $this->warranty_management_model->wm_get_attachments_file($data['process_id'], 'wm_process');
		}
		$data['person_in_charges'] = $this->staff_model->get();
		$data['get_order_number'] = $this->warranty_management_model->get_max_process_order_number($data['warranty_process_id']);

		$this->load->view('settings/warranty_receipt_process/process_details/add_edit_process_modal', $data);
	}

	/**
	 * add edit process
	 * @param [type] $process_id 
	 */
	public function add_edit_process($id='')
	{
	    if (!has_permission('warranty_management', '', 'view')  && !is_admin()) {
			access_denied('process');
		}
		
		if ($this->input->post()) {
			$data = $this->input->post();
			$data['description']     = $this->input->post('description', false);
			$warranty_receipt_id = $data['warranty_receipt_id'];

			if ($id == '') {
				if (!has_permission('warranty_management', '', 'create') && !is_admin()) {
					access_denied('operation');
				}

				$id = $this->warranty_management_model->add_process($data);
				if ($id) {
					$uploadedFiles = handle_wm_process_attachments_array($id,'file');

					set_alert('success', _l('wm_added_successfully', _l('operation')));
					redirect(admin_url('warranty_management/process_manage/'.$warranty_receipt_id));
				}

			} else {
				if (!has_permission('warranty_management', '', 'edit') && !is_admin()) {
					access_denied('operation');
				}

				$response = $this->warranty_management_model->update_process($data, $id);

				$uploadedFiles = handle_wm_process_attachments_array($id,'file');

				if (is_array($response)) {
					if (isset($response['cant_remove_main_admin'])) {
						set_alert('warning', _l('staff_cant_remove_main_admin'));
					} elseif (isset($response['cant_remove_yourself_from_admin'])) {
						set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
					}
				} elseif ($response == true) {
					set_alert('success', _l('wm_updated_successfully', _l('operation')));
				}
				redirect(admin_url('warranty_management/process_manage/'.$warranty_receipt_id));
			
			}
		}

	}


	/**
	 * delete operation
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_process($id, $warranty_receipt_id)
	{
	    if (!has_permission('warranty_management', '', 'delete')  && !is_admin()) {
			access_denied('work_center');
		}

		$success = $this->warranty_management_model->delete_process($id);
		if ($success) {
			set_alert('success', _l('wm_deleted'));
		} else {
			set_alert('warning', _l('problem_deleting'));
		}
		redirect(admin_url('warranty_management/process_manage/'.$warranty_receipt_id));


	}

	/**
	 * wm view attachment file
	 * @param  [type] $id       
	 * @param  [type] $rel_id   
	 * @param  [type] $rel_type 
	 * @return [type]           
	 */
	public function wm_view_attachment_file($id, $rel_id, $rel_type)
	{
		$data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
		$data['current_user_is_admin']             = is_admin();
		$data['file'] = $this->misc_model->get_file($id);

		if (!$data['file']) {
			header('HTTP/1.0 404 Not Found');
			die;
		}
		switch ($rel_type) {
			case 'wm_process':
				$folder_link = 'warranty_management/settings/warranty_receipt_process/process_details/view_process_file';
				break;
			
			default:
				# code...
				break;
		}

		$this->load->view($folder_link, $data);
	}

	/**
	 * delete process attachment file
	 * @param  [type] $attachment_id 
	 * @return [type]                
	 */
	public function delete_process_attachment_file($attachment_id)
	{
		if (!has_permission('warranty_management', '', 'delete') && !is_admin()) {
			access_denied('operation');
		}

		echo json_encode([
			'success' => $this->warranty_management_model->delete_wm_attachment_file($attachment_id, WARRANTY_MANAGEMENT_PROCESS_UPLOAD),
		]);
	}

	/**
	 * warranty information
	 * @return [type] 
	 */
	public function warranty_information()
	{
		$data['title'] = _l('wm_warranty_informations');

		$this->load->model('invoice_items_model');
		$this->load->model('service_management/service_management_model');

		$data['clients'] = $this->clients_model->get();
		$data['orders'] = $this->service_management_model->get_order();
		$data['orders'] = $this->service_management_model->get_order();
		$data['products'] = $this->invoice_items_model->get();
		$data['goods_deliveries'] = [];
		if(wm_get_status_modules('warehouse')){
			$this->load->model('warehouse/warehouse_model');
			$data['goods_deliveries'] = $this->warehouse_model->get_goods_delivery(false);
		}

		$this->load->view('warranty_management/warranty_informations/warranty_information_management', $data);
	}

	/**
	 * warranty information table
	 * @return [type] 
	 */
	public function warranty_information_table()
	{
		$this->app->get_table_data(module_views_path('warranty_management', 'warranty_informations/warranty_information_table'));
	}

	/**
	 * goods delivery expiration modal
	 * @return [type] 
	 */
	public function goods_delivery_expiration_modal()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$data=[];
		$data = $this->input->post();

		$product_name = '';
		$warranty_month = '';
		$warranty_start_date = '';
		$warranty_end_date = '';

		if(wm_get_status_modules('warehouse')){
			$this->load->model('warehouse/warehouse_model');
			$this->db->where('id', $data['goods_delivery_detail_id']);
			$goods_delivery_detail = $this->db->get(db_prefix() . 'goods_delivery_detail')->row();
			if($goods_delivery_detail){
				$this->load->model('invoice_items_model');
				$item = $this->warehouse_model->get_commodity($goods_delivery_detail->commodity_code);
				$goods_delivery = $this->warehouse_model->get_goods_delivery($goods_delivery_detail->goods_delivery_id);
				if($goods_delivery){
					$warranty_start_date = $goods_delivery->date_add;
				}

				if($item){
					$product_name = $item->description;
					$warranty_month = $item->guarantee;

					if(($item->guarantee != '') && (($item->guarantee != null))){
						$warranty_end_date = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$item->guarantee.' months'));
					}
				}
			}
		}
		$data['product_name'] = $product_name;
		$data['warranty_month'] = $warranty_month;
		$data['warranty_start_date'] = $warranty_start_date;
		$data['warranty_end_date'] = $warranty_end_date;

		$data['title'] = _l('wm_add_warranty_expiration_date');


		$this->load->view('warranty_informations/goods_delivery_expiration_modal', $data);
	}

	/**
	 * add goods delivery warranty period
	 */
	public function add_goods_delivery_warranty_period()
	{
		if (!has_permission('warranty_management', '', 'edit') && !is_admin() && !has_permission('warranty_management', '', 'create')) {
			access_denied('wm_warranty_informations');
		}
		$data = $this->input->post();
		if(wm_get_status_modules('warehouse')){
			$this->db->where('id', $data['goods_delivery_detail_id']);
			$this->db->update(db_prefix().'goods_delivery_detail', ['guarantee_period' => to_sql_date($data['warranty_period'])]);
		}

		set_alert('success', _l('wm_updated_successfully'));
		redirect(admin_url('warranty_management/warranty_information'));
	}

	/**
	 * warranty claim information
	 * @return [type] 
	 */
	public function warranty_claim_information()
	{
		$data['title'] = _l('wm_warranty_claims');

		$this->load->model('invoice_items_model');
		$this->load->model('service_management/service_management_model');

		$data['clients'] = $this->clients_model->get();
		$data['orders'] = $this->service_management_model->get_order();
		$data['orders'] = $this->service_management_model->get_order();
		$data['products'] = [];

		$this->load->view('warranty_management/warranty_claims/warranty_claim_management', $data);
	}

	/**
	 * warranty claim table
	 * @return [type] 
	 */
	public function warranty_claim_table()
	{
		$this->app->get_table_data(module_views_path('warranty_management', 'warranty_claims/warranty_claim_table'));
	}

	/**
	 * add edit warranty claim
	 * @param string $id 
	 */
	public function add_edit_warranty_claim($id = '')
	{
		if (!has_permission('warranty_management', '', 'view')  && !is_admin()) {
			access_denied('warranty_management');
		}
		
		if ($this->input->post()) {
			$data = $this->input->post();
			$id = $this->input->post('id');

			if ($id == '') {
				if (!has_permission('warranty_management', '', 'create') && !is_admin()) {
					access_denied('warranty_management');
				}

				$insert_id = $this->warranty_management_model->add_warranty_claim($data);
				if ($insert_id) {
					set_alert('success', _l('wm_added_successfully'));
				}
				redirect(admin_url('warranty_management/warranty_claim_information'));

			} else {
				if (!has_permission('warranty_management', '', 'edit') && !is_admin()) {
					access_denied('warranty_management');
				}
				$success = $this->warranty_management_model->update_warranty_claim($data, $id);
				set_alert('success', _l('wm_updated_successfully'));
				redirect(admin_url('warranty_management/warranty_claim_information'));
			}
		}
		
		$data=[];
		$data['title'] = _l('wm_add_warranty_claim');
		$warranty_claim_row_template = '';

		if ($id != ''){

			$warranty_claim = $this->warranty_management_model->get_warranty_claim($id);
			$data['warranty_claim'] = $warranty_claim;
			$data['warranty_claim_information_details'] = $warranty_claim->warranty_claim_information_details;
			$data['warranty_process_details'] = $warranty_claim->warranty_process_details;

			$data['title'] = _l('wm_update_warranty_claim');

			$warranty_claim_index = 0;
			foreach ($warranty_claim->warranty_process_details as $key => $warranty_process) {
				$warranty_claim_index++;

				$warranty_claim_row_template .= $this->warranty_management_model->create_warranty_receipt_process_row_template('items[' . $warranty_claim_index . ']', $warranty_process['warranty_claim'], $warranty_process['warranty_claim_detail_id'], $warranty_process['process_name'], $warranty_process['order_number'], $warranty_process['person_in_charge'], $warranty_process['estimate_time'], $warranty_process['description'], $warranty_process['step_icon'], $warranty_process['id'], $warranty_process['warranty_receipt_process_detail_id'], true);

			}

			$this->load->model('invoices_model');
			$data['invoices'] = $this->invoices_model->get(false, db_prefix().'invoices.clientid = '.$warranty_claim->client_id.' AND '.db_prefix().'invoices.status = 2');
			if(count($data['warranty_claim_information_details']) > 0){
				$data['list_item_warranty'] = $this->warranty_management_model->get_list_item_warranty_by_invoice($data['warranty_claim_information_details'][0]['invoice_id']);

				$this->load->model('invoice_items_model');
				$product = $this->invoice_items_model->get($data['warranty_claim_information_details'][0]['item_id']);

				$this->db->where('product_group_id', $product->group_id);
				$data['warranty_receipt_processes'] = $this->db->get(db_prefix() . 'wm_warranty_receipt_process')->result_array();
			}

		}

		$data['clients'] = $this->clients_model->get();

		$data['warranty_claim_row_template'] = $warranty_claim_row_template;
		$data['claim_code'] = $this->warranty_management_model->create_code('warranty_claim_code');

		$get_base_currency =  get_base_currency();
		if($get_base_currency){
			$data['base_currency_id'] = $get_base_currency->id;
		}else{
			$data['base_currency_id'] = 0;
		}

		$this->load->view('warranty_management/warranty_claims/add_edit_warranty_claim', $data);
	}

	/**
	 * get list item warranty by invoice
	 * @param  string $client_id 
	 * @return [type]            
	 */
	public function get_list_item_warranty_by_invoice($invoice_id = '')
	{

		$item_warranty_option = '';
		$list_item_warranty = $this->warranty_management_model->get_list_item_warranty_by_invoice($invoice_id);

		$get_base_currency =  get_base_currency();
		if($get_base_currency){
			$base_currency_id = $get_base_currency->id;
		}else{
			$base_currency_id = 0;
		}

		$item_warranty_option .=' <option value=""></option>';
		foreach ($list_item_warranty as $item) {

			$select='';

			$item_name = $item['item_name'];
			if(new_strlen($item_name) == 0){
				$item_name = wm_get_item($item['item_id']);
			}

			if(!isset($item['billing_plan_rate'])){
				$data_sub_text = app_format_money((float)$item['rate'], $base_currency_id);
			}else{
				$data_sub_text = app_format_money((float)$item['billing_plan_rate'], $base_currency_id).' ('. $item['rate'].' '. _l($item['billing_plan_type']) . ')';
			}

			if(isset($item['start_date'])){
				$data_sub_text .= ' - '._dt($item['start_date']) .' - '. _dt($item['warranty_period']);
			}else{
				if($item['warranty_period'] != null && new_strlen($item['warranty_period']) > 0){
					$data_sub_text .= ' - '._d($item['date_add']) .' - '. _d($item['warranty_period']);
				}else{
					$data_sub_text .= ' - '._d($item['date_add']) .' - ...';
				}
			}


			$item_warranty_option .= '<option value="' . $item['item_id'] . '" '.$select.' data-subtext="'. strip_tags($data_sub_text).'">' . $item_name . '</option>';
		}
		echo json_encode([
			'item_warranty_option' => $item_warranty_option,
		]);
	}

	/**
	 * get invoice by client
	 * @param  string $client_id 
	 * @return [type]            
	 */
	public function get_invoice_by_client($client_id = '')
	{
		$this->load->model('invoices_model');
		$invoices = $this->warranty_management_model->get_list_item_warranty($client_id);

		$invoice_option = '';
		$invoice_option .=' <option value=""></option>';
		$arr_temp_invoice_ids = [];
		foreach ($invoices as $invoice) {

			$select='';

			if(!in_array($invoice['invoice_id'], $arr_temp_invoice_ids)){

				$invoice_option .= '<option value="' .$invoice['invoice_id'] . '" '.$select.'>' . format_invoice_number($invoice['invoice_id']) . '</option>';
			}
			$arr_temp_invoice_ids[] = $invoice['invoice_id'];
		}
		echo json_encode([
			'invoice_option' => $invoice_option,
		]);
	}

	/**
	 * get list warranty receipt process
	 * @param  string $item_id 
	 * @return [type]          
	 */
	public function get_list_warranty_receipt_process($item_id = '')
	{

		$this->load->model('invoice_items_model');
		$product = $this->invoice_items_model->get($item_id);

		$warranty_receipt_process_option = '';

		if($product){
			if($product->group_id){
				$this->db->where('product_group_id', $product->group_id);
				$warranty_receipt_processes = $this->db->get(db_prefix() . 'wm_warranty_receipt_process')->result_array();

				$warranty_receipt_process_option .=' <option value=""></option>';
				foreach ($warranty_receipt_processes as $warranty_receipt_process) {

					$select='';
					$warranty_receipt_process_option .= '<option value="' .$warranty_receipt_process['id'] . '" '.$select.'>' . $warranty_receipt_process['code'].' '.$warranty_receipt_process['name'] . '</option>';
				}
			}
		}

		echo json_encode([
			'warranty_receipt_process_option' => $warranty_receipt_process_option,
		]);
	}

	/**
	 * get list warranty receipt process detail
	 * @param  string $warranty_receipt_process_id 
	 * @return [type]                              
	 */
	public function get_list_warranty_receipt_process_detail($warranty_receipt_process_id = '')
	{
		$warranty_receipt_process = $this->warranty_management_model->get_warranty_receipt_process($warranty_receipt_process_id);
		$warranty_receipt_process_detail_option = '';

		if($warranty_receipt_process){
			if($warranty_receipt_process->details){
				foreach ($warranty_receipt_process->details as $order_index => $detail) {
					$warranty_receipt_process_detail_option .= $this->warranty_management_model->create_warranty_receipt_process_row_template('newitems[' . $order_index . ']', '', '', $detail['process_name'], $detail['order_number'], $detail['person_in_charge'], $detail['estimate_time'], $detail['description'], $detail['step_icon'], $order_index, $detail['id'], false);

				}
			}
		}

		echo json_encode([
			'warranty_receipt_process_detail_option' => $warranty_receipt_process_detail_option,
		]);
	}

	/**
	 * delete warranty claim
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_warranty_claim($id)
	{

		if(!has_permission('warranty_management', '', 'delete')  &&  !is_admin()) {
			access_denied('warranty_management');
		}

		$response = $this->warranty_management_model->delete_warranty_claim($id);
		if ($response == true) {
			set_alert('success', _l('deleted'));
		} else {
			set_alert('warning', _l('problem_deleting'));
		}
		redirect(admin_url('warranty_management/warranty_claim_information'));
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
		$success = $this->warranty_management_model->warranty_status_mark_as($status, $id, $type);
		$message = '';

		if ($success) {
			$message = _l('wm_change_warranty_claim_status_successfully');
		}
		echo json_encode([
			'success'  => $success,
			'message'  => $message
		]);
	}

	/**
	 * warranty claim detail
	 * @param  string $id 
	 * @return [type]     
	 */
	public function warranty_claim_detail($id = '', $warranty_process_detail_id = '')
	{

		$warranty_claim = $this->warranty_management_model->get_warranty_claim($id);
		if (!$warranty_claim) {
			blank_page(_l('warranty_claim_not_found'));
		}
		
		$data = [];

		$data['warranty_claim'] = $warranty_claim;
		$data['warranty_claim_information_details'] = $warranty_claim->warranty_claim_information_details;
		$data['warranty_process_details'] = $warranty_claim->warranty_process_details;

		$data['title']          = $data['warranty_claim']->claim_code;
		 
		//get activity log
		$data['arr_activity_logs'] = $this->warranty_management_model->get_warranty_claim_activity_log($id);

		/*process detail*/
		if(count($data['warranty_process_details']) > 0){
			if(!is_numeric($warranty_process_detail_id) || $warranty_process_detail_id == 0){
				$warranty_process_detail_id = $data['warranty_process_details'][0]['id'];
			}

			$data['warranty_process_detail_id'] = $warranty_process_detail_id;
			$data['one_warranty_process_detail'] = $this->warranty_management_model->get_warranty_process_detail($warranty_process_detail_id);

			$data['warranty_process_detail_file'] = $this->warranty_management_model->wm_get_attachments_file($warranty_process_detail_id, 'wm_claim_process');

			$work_order_prev_next = $this->warranty_management_model->get_warranty_process_previous_next($warranty_process_detail_id, $id);
			$data['prev_id'] = $work_order_prev_next['prev_id'];
			$data['next_id'] = $work_order_prev_next['next_id'];
			$data['pager_value'] = $work_order_prev_next['pager_value'];
			$data['pager_limit'] = $work_order_prev_next['pager_limit'];
			$data['warranty_claim_id'] = $id;

			$data['header'] = $data['one_warranty_process_detail']->process_name;
			$time_tracking_details = $this->warranty_management_model->get_time_tracking_details($warranty_process_detail_id);

			$data['time_tracking_details'] = $time_tracking_details['time_trackings'];
			$data['rows'] = $time_tracking_details['rows'];

			$check_mo_cancelled= false;
			if($data['warranty_claim']){
				if($data['warranty_claim']->status == 'cancelled'){
					$check_mo_cancelled= true;
				}
			}
			$data['check_mo_cancelled'] = $check_mo_cancelled;
		}

		$this->load->view('warranty_claims/details/warranty_claim_detail', $data);
	}

	/**
	 * warranty claim activity log modal
	 * @return [type] 
	 */
	public function warranty_claim_activity_log_modal()
	{
		if ($this->input->is_ajax_request()) {
			$request_data = $this->input->get();

			$data=[];
			$data['warranty_claim_id'] = $request_data['warranty_claim_id'];
			$data['id'] = $request_data['id'];
			$data['cart_id'] = $request_data['cart_id'];
			$allow_attachment = false;

			$data['title'] = _l('wm_add_warranty_log');
			if($request_data['id'] != ''){
				$data['activity_log'] = $this->warranty_management_model->wm_get_one_timeline_log($request_data['id'], 'warranty_claim');
				$data['title'] = _l('wm_update_warranty_log');
			}
			$data['allow_attachment'] = $allow_attachment;

			$response = $this->load->view('warranty_claims/details/modals/add_edit_activity_log_modal', $data, true);
			echo json_encode([
				'data' => $response,
			]);
		}
	}

	/**
	 * shipment add edit activity log
	 * @return [type] 
	 */
	public function warranty_claim_add_edit_activity_log()
	{
		if($this->input->post()){
			$data = $this->input->post();
			if (!has_permission('warranty_management', '', 'edit') && !is_admin() && !has_permission('warranty_management', '', 'create')) {
				access_denied('warranty_management');
			}

			$cart_id = '';
			if($data['id'] == ''){
				unset($data['id']);
				$cart_id = $data['cart_id'];
				unset($data['cart_id']);
				$date = to_sql_date($data['date'], true);
				$result =  $this->warranty_management_model->wm_add_timeline_log($data['rel_id'], 'warranty_claim', $data['description'], $date);

				if($result){
					echo json_encode([
						'url'       => admin_url('warranty_management/warranty_claim_detail/' . $data['rel_id']),
						'shipment_log_id' => $result,
						'cart_id' => $cart_id,
					]);
					die;
				}

				echo json_encode([
					'url' => admin_url('warranty_management/warranty_claim_detail/'.$data['rel_id']),
				]);
				die;
			}
			else{
				$cart_id = $data['cart_id'];
				unset($data['cart_id']);
				$data['date'] = to_sql_date($data['date'], true);
				$result =  $this->warranty_management_model->update_warranty_claim_activity_log($data['id'], $data);

				echo json_encode([
					'url' => admin_url('warranty_management/warranty_claim_detail/'.$data['rel_id']),
					'shipment_log_id' => $data['id'],
					'cart_id' => $cart_id,
				]);
				die;

				if($result){
					set_alert('success', _l('updated_successfully'));
				}
				redirect(admin_url('warranty_management/warranty_claim_detail/'.$data['rel_id']));
			}
		}
	}

	/**
	 * delete activitylog
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_activitylog($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$delete = $this->warranty_management_model->delete_timeline_log($id);
		if($delete){
			$status = true;
		}else{
			$status = false;
		}

		echo json_encode([
			'success' => $status,
		]);
	}

	/**
	 * warranty process detail mark as start working
	 * @param  [type] $warranty_process_detail_id 
	 * @param  [type] $warranty_claim_id          
	 * @return [type]                             
	 */
	public function warranty_process_detail_mark_as_start_working($warranty_process_detail_id, $warranty_claim_id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!has_permission('warranty_management', '', 'create')  && !has_permission('warranty_management', '', 'edit')  && !is_admin()) {
			access_denied('warranty_management');
		}

		$current_time=date('Y-m-d H:i:s');

		$mo_mark_as_start_working = $this->warranty_management_model->update_warranty_claim_process_detail_status($warranty_process_detail_id, ['status' => 'processing']);
		//update MO to in process
		$this->warranty_management_model->update_warranty_claim_information_status($warranty_claim_id, ['status' => 'processing']);
		
		//Add time tracking
		$data_tracking=[
			'warranty_process_detail_id' => $warranty_process_detail_id,
			'from_date' => $current_time,
			'staff_id' => get_staff_user_id(),
		];
		$this->warranty_management_model->add_time_tracking($data_tracking);


		if($mo_mark_as_start_working){
			$status='success';
			$message = _l('wm_updated_successfully');
		}else{
			$status='warning';
			$message = _l('wm_updated_failed');
		}

		echo json_encode([
			'status' => $status,
			'message' => $message,
		]);
	}

	/**
	 * mo mark as mark pause
	 * @param  [type] $work_order_id 
	 * @return [type]                
	 */
	public function warranty_process_detail_mark_as_mark_pause($warranty_process_detail_id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!has_permission('warranty_management', '', 'create')  && !has_permission('warranty_management', '', 'edit')  && !is_admin()) {
			access_denied('warranty_management');
		}

		$warranty_process_detail_mark_as_start_working = $this->warranty_management_model->update_warranty_claim_process_detail_status($warranty_process_detail_id, ['status' => 'pause']);

		$current_time=date('Y-m-d H:i:s');

		//Update time tracking
		$data_update=[
			'warranty_process_detail_id' => $warranty_process_detail_id,
			'to_date' => $current_time,
			'staff_id' => get_staff_user_id(),
		];
		$update_time_tracking = $this->warranty_management_model->update_time_tracking($warranty_process_detail_id, $data_update);

		if($update_time_tracking){
			$status='success';
			$message = _l('wm_updated_successfully');
		}else{
			$status='warning';
			$message = _l('wm_updated_failed');
		}

		echo json_encode([
			'status' => $status,
			'message' => $message,
		]);
	}

	/**
	 * warranty process detail mark as mark_done
	 * @param  [type] $warranty_process_detail_id 
	 * @param  [type] $warranty_claim_id          
	 * @return [type]                             
	 */
	public function warranty_process_detail_mark_as_mark_done($warranty_process_detail_id, $warranty_claim_id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!has_permission('warranty_management', '', 'create')  && !has_permission('warranty_management', '', 'edit')  && !is_admin()) {
			access_denied('warranty_management');
		}

		$warranty_process_detail_mark_as_done = $this->warranty_management_model->warranty_process_detail_mark_as_done($warranty_process_detail_id, $warranty_claim_id);

		if($warranty_process_detail_mark_as_done){
			$status='success';
			$message = _l('wm_updated_successfully');
		}else{
			$status='warning';
			$message = _l('wm_updated_failed');
		}

		echo json_encode([
			'status' => $status,
			'message' => $message,
		]);
	}

	/*end file*/
}