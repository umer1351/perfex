<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * This class describes a warranty client.
 */
class Warranty_management_client extends ClientsController
{

	/**
	 * __construct description
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('warranty_management_model');

		if(get_option('warranty_management_display_on_portal') != 1){
			set_alert('warning', _l('access_denied'));
			redirect(site_url('clients'));
		}

		if(!is_client_logged_in()){ 
			redirect_after_login_to_current_url();
			redirect(site_url('authentication/login'));
		}
	}

	/**
	 * warranty informations
	 * @param  boolean $status 
	 * @return [type]          
	 */
	public function warranty_informations($status = false)
	{
		if(!is_client_logged_in()){
			redirect(site_url('authentication/login'));
		}

		if($status){
			$data['warranty_informations'] = $this->warranty_management_model->get_list_item_warranty(get_client_user_id());
		}else{
			$data['warranty_informations'] = $this->warranty_management_model->get_list_item_warranty(get_client_user_id());
		}

		$get_base_currency =  get_base_currency();
		if($get_base_currency){
			$base_currency_id = $get_base_currency->id;
		}else{
			$base_currency_id = 0;
		}
		$data['base_currency_id'] = $base_currency_id;
		$data['client_warranty_claim_process'] = get_client_warranty_claim_process(get_client_user_id());
		$data['title']    = _l('wm_warranty_informations');
		$this->data($data);
		$this->view('client_portals/warranty_informations/warranty_information');

		$this->layout();
	}

	/**
	 * warranty claims
	 * @param  boolean $status 
	 * @return [type]          
	 */
	public function warranty_claims($status = false)
	{

		if($status){
			$data['warranty_claims'] = $this->warranty_management_model->get_warranty_claim(false, 'client_id = '.get_client_user_id().' AND status = "'.$status.'"');
		}else{
			$data['warranty_claims'] = $this->warranty_management_model->get_warranty_claim(false, 'client_id = '.get_client_user_id());
		}

		$data['warranty_claim_status'] = $this->warranty_management_model->count_warranty_claim_by_status(get_client_user_id());

		$get_base_currency =  get_base_currency();
		if($get_base_currency){
			$base_currency_id = $get_base_currency->id;
		}else{
			$base_currency_id = 0;
		}
		$data['base_currency_id'] = $base_currency_id;


		$data['title']    = _l('sm_services_management');
		$this->data($data);
		$this->view('client_portals/warranty_claims/warranty_claims');

		$this->layout();
	}

	/**
	 * warranty claim detai
	 * @param  string $id                        
	 * @param  string $warranty_process_detail_id
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

		$this->data($data);
		$this->view('client_portals/warranty_claims/details/warranty_claim_detail');
		$this->layout();
	}

	/**
	 * add warranty claim
	 * @param [type] $invoice_id 
	 * @param [type] $item_id    
	 */
	public function add_warranty_claim($invoice_id, $item_id)
	{
		
		if ($this->input->post()) {
			
			$data = $this->input->post();
			$id = $this->input->post('id');

			if ($id == '') {

				$insert_id = $this->warranty_management_model->add_warranty_claim($data);
				if ($insert_id) {
					set_alert('success', _l('wm_added_successfully'));
				}
				redirect(site_url('warranty_management/warranty_management_client/warranty_claim_detail/'.$insert_id));

			} else {
				redirect(site_url('warranty_management/warranty_management_client/warranty_claims'));
			}
		}

		/*check warranty claim process exist*/
		$this->load->model('invoice_items_model');
		$check_receipt_process = false;
		$product = $this->invoice_items_model->get($item_id);
		$warranty_receipt_process_option = '';

		if($product){
			if($product->group_id){
				$this->db->where('product_group_id', $product->group_id);
				$this->db->order_by('mark_default', 'asc');
				$this->db->limit(1);
				$warranty_receipt_processes = $this->db->get(db_prefix() . 'wm_warranty_receipt_process')->row();
				if($warranty_receipt_processes){
					$check_receipt_process = true;

				}
			}
		}

		if(!$check_receipt_process){
			set_alert('warning', _l('The_warranty_procedure_for_the_product_you_requested_could_not_be_found_please_contact_your_system_administrator'));
			redirect(site_url('warranty_management/warranty_management_client/warranty_informations'));
		}

		$get_warranty_receipt_process = $this->warranty_management_model->get_warranty_receipt_process($warranty_receipt_processes->id);
		
		$data=[];
		$data['client_id'] = get_client_user_id();
		$data['invoice_id'] = $invoice_id;
		$data['item_id'] = $item_id;
		$data['warranty_receipt_process_id'] = $warranty_receipt_processes->id;

		$data['title'] = _l('wm_add_warranty_claim');
		$warranty_claim_row_template = '';

		$warranty_claim_index = 0;
		foreach ($get_warranty_receipt_process->details as $key => $warranty_process) {
			$warranty_claim_index++;

			$warranty_claim_row_template .= $this->warranty_management_model->create_warranty_receipt_process_row_template('newitems[' . $warranty_claim_index . ']', '', '', $warranty_process['process_name'], $warranty_process['order_number'], $warranty_process['person_in_charge'], $warranty_process['estimate_time'], $warranty_process['description'], $warranty_process['step_icon'], $warranty_claim_index, $warranty_process['id'], false);

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

		$this->data($data);
		$this->view('client_portals/warranty_claims/add_edit_warranty_claim');
		$this->layout();
	}



}