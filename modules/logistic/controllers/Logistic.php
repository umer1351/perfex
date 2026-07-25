<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Logistic Controller
 */
class logistic extends AdminController {

	/**
	 * Constructs a new instance.
	 */
	public function __construct() {
		parent::__construct();
        hooks()->do_action('logistic_init'); 
		$this->load->model('logistic_model');
        $this->load->model('staff_model');
	}

	public function settings($value='')
	{
		if (!is_admin() && !has_permission('purchase_settings', '', 'edit')) {
            access_denied('purchase');
        }

        $data['group'] = $this->input->get('group');
        $data['unit_tab'] = $this->input->get('tab');

        $data['title']                 = _l('setting');
       
		$this->db->where('module_name','warehouse');
        $module = $this->db->get(db_prefix().'modules')->row();

        // Logistics
        $data['tab'][] = 'office_group';
        $data['tab'][] = 'agency_group';
        $data['tab'][] = 'shipping_companies';	
        $data['tab'][] = 'type_of_packages';	
        $data['tab'][] = 'shipping_modes';
        $data['tab'][] = 'shipping_time';
        $data['tab'][] = 'styles_and_states';
        $data['tab'][] = 'logistics_service';

        // Locations
        $data['tab'][] = 'countries';
        $data['tab'][] = 'states';
        $data['tab'][] = 'cities';

        //Payment terms
        $data['tab'][] = 'payment_terms';

        // Shipping Setting
        $data['tab'][] = 'taxes';
        //$data['tab'][] = 'shipping_rates_list';
        $data['tab'][] = 'tracking_and_invoice';
        $data['tab'][] = 'default_shipping_info';

        // Currency rate
        $data['tab'][] = 'currency_rates';
        

        if($data['group'] == ''){
            $data['group'] = 'office_group';
        }

        if($data['group'] == 'office_group'){
            $data['offices'] = $this->logistic_model->get_offices();
        }else if($data['group'] == 'agency_group'){
            $data['agencys'] = $this->logistic_model->get_agencys();
        }else if($data['group'] == 'shipping_companies'){
             $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
            $data['shipping_companys'] = $this->logistic_model->get_shipping_companies();
        }else if($data['group'] == 'type_of_packages'){
            $data['type_of_packages'] = $this->logistic_model->get_type_of_packages();
        }else if($data['group'] == 'shipping_modes'){
            $data['shipping_modes'] = $this->logistic_model->get_shipping_modes();
        }else if($data['group'] == 'shipping_time'){
            $data['shipping_times'] = $this->logistic_model->get_shipping_times();
        }else if($data['group'] == 'styles_and_states'){
            $data['style_and_states'] = $this->logistic_model->get_style_and_states();
        }else if($data['group'] == 'logistics_service'){
            $data['logistics_services'] = $this->logistic_model->get_logistics_services();
        }else if($data['group'] == 'countries'){
            $data['countries'] = $this->logistic_model->get_logistics_countries();
        }else if($data['group'] == 'states'){
            $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
            $data['states'] = $this->logistic_model->get_logistics_states();
        }else if($data['group'] == 'cities'){
            $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
            $data['states'] = [];//$this->logistic_model->get_logistics_states();
            $data['cities'] = $this->logistic_model->get_logistics_cities();
        }else if($data['group'] == 'shipping_rates_list'){
            $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
            $data['states'] = [];
            $data['cities'] = [];
            $data['shipping_rates_lists'] = $this->logistic_model->get_shipping_rates_lists();
        }else if($data['group'] == 'payment_terms'){
            $data['payment_terms'] = $this->logistic_model->get_logistics_payment_terms();
        }else if($data['group'] == 'default_shipping_info'){
            $data['logistics_services'] = $this->logistic_model->get_logistics_services();
            $data['type_of_packages'] = $this->logistic_model->get_type_of_packages();
            $data['shipping_companys'] = $this->logistic_model->get_shipping_companies();
            $data['shipping_modes'] = $this->logistic_model->get_shipping_modes();
            $data['shipping_times'] = $this->logistic_model->get_shipping_times();
            $data['payment_terms'] = $this->logistic_model->get_logistics_payment_terms();
            $data['style_and_states'] = $this->logistic_model->get_style_and_states();

            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get();

        }

        if($data['group'] == 'currency_rates'){
            $this->load->model('currencies_model');
            $this->logistic_model->check_auto_create_currency_rate();

            $data['currencies'] = $this->currencies_model->get();
            if($data['unit_tab'] == ''){
                $data['unit_tab'] = 'general';
            }
        }


        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['tabs']['view'] = 'settings/includes/'.$data['group'];

        $this->load->view('settings/manage_setting', $data);

	}

    /**
     * 
     * { office form }
     * @return redirect
     */
    public function office_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('office_group_id') == '') {
                unset($data['office_group_id']);
                $id = $this->logistic_model->add_office($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('office'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=office_group'));
            } else {
                $id = $data['office_group_id'];
                unset($data['office_group_id']);
                $success = $this->logistic_model->update_office($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('office'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=office_group'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_office($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=office_group'));
        }
        $response = $this->logistic_model->delete_office($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('office')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('office')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('office')));
        }
        redirect(admin_url('logistic/settings?group=office_group'));
    }

    /**
     * 
     * { agency form }
     * @return redirect
     */
    public function agency_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('agency_group_id') == '') {
                unset($data['agency_group_id']);
                $id = $this->logistic_model->add_agency($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('agency'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=agency_group'));
            } else {
                $id = $data['agency_group_id'];
                unset($data['agency_group_id']);
                $success = $this->logistic_model->update_agency($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('agency'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=agency_group'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_agency($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=agency_group'));
        }
        $response = $this->logistic_model->delete_agency($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('agency')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('agency')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('agency')));
        }
        redirect(admin_url('logistic/settings?group=agency_group'));
    }

     /**
     * 
     * { shipping_company form }
     * @return redirect
     */
    public function shipping_company_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('shipping_company_id') == '') {
                unset($data['shipping_company_id']);
                $id = $this->logistic_model->add_shipping_company($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_shipping_company'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=shipping_companies'));
            } else {
                $id = $data['shipping_company_id'];
                unset($data['shipping_company_id']);
                $success = $this->logistic_model->update_shipping_company($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_shipping_company'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=shipping_companies'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_shipping_company($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=shipping_companies'));
        }
        $response = $this->logistic_model->delete_shipping_company($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_shipping_company')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_shipping_company')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_shipping_company')));
        }
        redirect(admin_url('logistic/settings?group=shipping_companies'));
    }


    /**
     * 
     * { type_of_package_form  }
     * @return redirect
     */
    public function type_of_package_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('type_of_package_id') == '') {
                unset($data['type_of_package_id']);
                $id = $this->logistic_model->add_type_of_package($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_type_of_packages'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=type_of_packages'));
            } else {
                $id = $data['type_of_package_id'];
                unset($data['type_of_package_id']);
                $success = $this->logistic_model->update_type_of_package($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_type_of_packages'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=type_of_packages'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_type_of_package($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=type_of_packages'));
        }
        $response = $this->logistic_model->delete_type_of_package($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_type_of_packages')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_type_of_packages')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_type_of_packages')));
        }
        redirect(admin_url('logistic/settings?group=type_of_packages'));
    }

     /**
     * 
     * { shipping_mode_form  }
     * @return redirect
     */
    public function shipping_mode_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('shipping_mode_id') == '') {
                unset($data['shipping_mode_id']);
                $id = $this->logistic_model->add_shipping_mode($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_shipping_modes'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=shipping_modes'));
            } else {
                $id = $data['shipping_mode_id'];
                unset($data['shipping_mode_id']);
                $success = $this->logistic_model->update_shipping_mode($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_shipping_modes'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=shipping_modes'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_shipping_mode($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=shipping_modes'));
        }
        $response = $this->logistic_model->delete_shipping_mode($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_shipping_modes')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_shipping_modes')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_shipping_modes')));
        }
        redirect(admin_url('logistic/settings?group=shipping_modes'));
    }

    /**
     * 
     * { shipping_time_form  }
     * @return redirect
     */
    public function shipping_time_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('shipping_time_id') == '') {
                unset($data['shipping_time_id']);
                $id = $this->logistic_model->add_shipping_time($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_shipping_times'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=shipping_time'));
            } else {
                $id = $data['shipping_time_id'];
                unset($data['shipping_time_id']);
                $success = $this->logistic_model->update_shipping_time($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_shipping_times'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=shipping_time'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_shipping_time($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=shipping_time'));
        }
        $response = $this->logistic_model->delete_shipping_time($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_shipping_time')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_shipping_time')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_shipping_time')));
        }
        redirect(admin_url('logistic/settings?group=shipping_time'));
    }

    /**
     * 
     * { style_and_state_form  }
     * @return redirect
     */
    public function style_and_state_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('style_and_state_id') == '') {
                unset($data['style_and_state_id']);
                $id = $this->logistic_model->add_style_and_state($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('style'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=styles_and_states'));
            } else {
                $id = $data['style_and_state_id'];
                unset($data['style_and_state_id']);
                $success = $this->logistic_model->update_style_and_state($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('style'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=styles_and_states'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_style_and_state($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=styles_and_states'));
        }
        $response = $this->logistic_model->delete_style_and_state($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('style')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_style_and_state')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('style')));
        }
        redirect(admin_url('logistic/settings?group=styles_and_states'));
    }

    /**
     * 
     * { logistics_service_form  }
     * @return redirect
     */
    public function logistics_service_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('logistics_service_id') == '') {
                unset($data['logistics_service_id']);
                $id = $this->logistic_model->add_logistics_service($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_logistics_services'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=logistics_service'));
            } else {
                $id = $data['logistics_service_id'];
                unset($data['logistics_service_id']);
                $success = $this->logistic_model->update_logistics_service($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_logistics_services'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=logistics_service'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_logistics_service($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=logistics_service'));
        }
        $response = $this->logistic_model->delete_logistics_service($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_logistics_service')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_logistics_service')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_logistics_service')));
        }
        redirect(admin_url('logistic/settings?group=logistics_service'));
    }

    /**
     * [taxes_setting_form description]
     * @return [type] [description]
     */
    public function taxes_setting_form()
    {
        if($this->input->post()){
            $data = $this->input->post();

            $success = $this->logistic_model->update_taxes_setting($data);

            if($success){
                set_alert('success', _l('updated_successfully'));
            }

            redirect(admin_url('logistic/settings?group=taxes'));
        }
    }

    /**
     * 
     * { country_form  }
     * @return redirect
     */
    public function country_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('country_id') == '') {
                unset($data['country_id']);
                $id = $this->logistic_model->add_country($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_country'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=countries'));
            } else {
                $id = $data['country_id'];
                unset($data['country_id']);
                $success = $this->logistic_model->update_country($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_country'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=countries'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_country($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=countries'));
        }
        $response = $this->logistic_model->delete_country($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_country')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_country')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_country')));
        }
        redirect(admin_url('logistic/settings?group=countries'));
    }

    /**
     * change settings status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_logistic_status($id, $status)
    {
        if (has_permission('logistic_settings', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->logistic_model->change_logistic_country_status($id, $status);
            }
        }
    }

    /**
     * 
     * { state_form  }
     * @return redirect
     */
    public function state_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('state_id') == '') {
                unset($data['state_id']);
                $id = $this->logistic_model->add_state($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_state'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=states'));
            } else {
                $id = $data['state_id'];
                unset($data['state_id']);
                $success = $this->logistic_model->update_state($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_state'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=states'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_state($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=states'));
        }
        $response = $this->logistic_model->delete_state($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_state')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_state')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_state')));
        }
        redirect(admin_url('logistic/settings?group=states'));
    }

    /**
     * [get_state_by_country description]
     * @return [type] [description]
     */
    public function get_state_by_country($country_id){
        $html = '';
        
        $this->db->where('country', $country_id);
        $states_list = $this->db->get(db_prefix().'lg_states')->result_array();
        $html = '<option value=""></option>';
        foreach($states_list as $state){
            $html .= '<option value="'.$state['id'].'">'.$state['state_name'].'</option>';
        }
        

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * 
     * { city_form  }
     * @return redirect
     */
    public function city_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('city_id') == '') {
                unset($data['city_id']);
                $id = $this->logistic_model->add_city($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_city'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=cities'));
            } else {
                $id = $data['city_id'];
                unset($data['city_id']);
                $success = $this->logistic_model->update_city($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_city'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=cities'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_city($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=cities'));
        }
        $response = $this->logistic_model->delete_city($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_city')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_city')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_city')));
        }
        redirect(admin_url('logistic/settings?group=cities'));
    }

    /**
     * [get_city_by_state description]
     * @return [type] [description]
     */
    public function get_city_by_state($state_id){
        $html = '';
        
        $this->db->where('state', $state_id);
        $cities_list = $this->db->get(db_prefix().'lg_cities')->result_array();
        $html = '<option value=""></option>';
        foreach($cities_list as $city){
            $html .= '<option value="'.$city['id'].'">'.$city['city_name'].'</option>';
        }
        

        echo json_encode([
            'html' => $html,
        ]);
    }

     /**
     * 
     * { shipping_rates_list_form  }
     * @return redirect
     */
    public function shipping_rates_list_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('shipping_rates_list_id') == '') {
                unset($data['shipping_rates_list_id']);
                $id = $this->logistic_model->add_shipping_rates_list($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_shipping_rates_list'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=shipping_rates_list'));
            } else {
                $id = $data['shipping_rates_list_id'];
                unset($data['shipping_rates_list_id']);
                $success = $this->logistic_model->update_shipping_rates_list($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_shipping_rates_list'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=shipping_rates_list'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_shipping_rates_list($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=shipping_rates_list'));
        }
        $response = $this->logistic_model->delete_shipping_rates_list($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_shipping_rates_list')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_shipping_rates_list')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_shipping_rates_list')));
        }
        redirect(admin_url('logistic/settings?group=shipping_rates_list'));
    }


    /**
     * change settings status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_logistic_rate_list_status($id, $status)
    {
        if (has_permission('logistic_settings', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->logistic_model->change_logistic_rate_list_status($id, $status);
            }
        }
    }

    /**
     * [tracking_and_invoice_form description]
     * @return [type] [description]
     */
    public function tracking_and_invoice_form()
    {
        if($this->input->post()){
            $data = $this->input->post();

            $success = $this->logistic_model->update_tracking_and_invoice_setting($data);

            if($success){
                set_alert('success', _l('updated_successfully'));
            }

            redirect(admin_url('logistic/settings?group=tracking_and_invoice'));
        }
    }

     /**
     * 
     * { payment_term_form  }
     * @return redirect
     */
    public function payment_term_form(){
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('payment_term_id') == '') {
                unset($data['payment_term_id']);
                $id = $this->logistic_model->add_payment_term($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_payment_term'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=payment_terms'));
            } else {
                $id = $data['payment_term_id'];
                unset($data['payment_term_id']);
                $success = $this->logistic_model->update_payment_term($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_payment_term'));
                    set_alert('success', $message);
                }
                redirect(admin_url('logistic/settings?group=payment_terms'));
            }
            die;
        }
    }

    /**
     * delete job_position
     * @param  integer $id
     * @return redirect
     */
    public function delete_payment_term($id) {
        if (!$id) {
            redirect(admin_url('logistic/settings?group=payment_terms'));
        }
        $response = $this->logistic_model->delete_payment_term($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_payment_term')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lg_payment_term')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_payment_term')));
        }
        redirect(admin_url('logistic/settings?group=payment_terms'));
    }

    /**
     * [default_shipping_info_form description]
     * @return [type] [description]
     */
    public function default_shipping_info_form()
    {
        if($this->input->post()){
            $data = $this->input->post();

            $success = $this->logistic_model->update_tracking_and_invoice_setting($data);

            if($success){
                set_alert('success', _l('updated_successfully'));
            }

            redirect(admin_url('logistic/settings?group=default_shipping_info'));
        }
    }

    /**
     * [users description]
     * @return [type] [description]
     */
    public function users(){
        if(!has_permission('lg_users', '', 'view')){
            access_denied('users');
        }

        $data['title'] = _l('lg_users');

        $data['group'] = $this->input->get('group');
        if($data['group'] == ''){
            $data['group'] = 'employee';
        }

        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }

        if($data['group'] == 'employee'){
            if (!has_permission('staff', '', 'view')) {
                access_denied('staff');
            }
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data('staff');
            }
        }else if($data['group'] == 'customers'){
            if(!has_permission('customers', '', 'view')){
                access_denied('users');
            }

            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data('all_contacts');
            }

            $this->load->model('contracts_model');
            $data['contract_types'] = $this->contracts_model->get_contract_types();
            $data['groups']         = $this->clients_model->get_groups();
            $data['title']          = _l('clients');

            $this->load->model('proposals_model');
            $data['proposal_statuses'] = $this->proposals_model->get_statuses();

            $this->load->model('invoices_model');
            $data['invoice_statuses'] = $this->invoices_model->get_statuses();

            $this->load->model('estimates_model');
            $data['estimate_statuses'] = $this->estimates_model->get_statuses();

            $this->load->model('projects_model');
            $data['project_statuses'] = $this->projects_model->get_project_statuses();

            $data['customer_admins'] = $this->clients_model->get_customers_admin_unique_ids();

            $whereContactsLoggedIn = '';
            if (staff_cant('view', 'customers')) {
                $whereContactsLoggedIn = ' AND userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
            }

            $data['contacts_logged_in_today'] = $this->clients_model->get_contacts('', 'last_login LIKE "' . date('Y-m-d') . '%"' . $whereContactsLoggedIn);

            $data['countries'] = $this->clients_model->get_clients_distinct_countries();
            $data['table'] = App_table::find('clients');

        }else if($data['group'] == 'drivers'){
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('logistic', 'users/table_drivers'));
            }
        }

        $data['office_groups'] = $this->logistic_model->get_offices();
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);

        $this->load->view('users/manage', $data);
    }

    /**
     * [driver description]
     * @return [type] [description]
     */
    public function driver($id = ''){

        if($id == ''){
            $data['title'] = _l('lg_add_drivers');
        }else{
            $data['title'] = _l('lg_edit_drivers');
        }

        $this->load->model('departments_model');
        if ($this->input->post()) {
            $data = $this->input->post();
            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $data['password'] = $this->input->post('password', false);

            if ($id == '') {
                if (!has_permission('staff', '', 'create')) {
                    access_denied('staff');
                }
                $id = $this->staff_model->add($data);
                if ($id) {
                    handle_staff_profile_image_upload($id);
                    set_alert('success', _l('added_successfully', _l('staff_member')));
                    redirect(admin_url('logistic/users?group=drivers'));
                }
            } else {
                if (!has_permission('staff', '', 'edit')) {
                    access_denied('staff');
                }
                handle_staff_profile_image_upload($id);
                $response = $this->staff_model->update($data, $id);
                if (is_array($response)) {
                    if (isset($response['cant_remove_main_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_main_admin'));
                    } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
                    }
                } elseif ($response == true) {
                    set_alert('success', _l('updated_successfully', _l('staff_member')));
                }
                redirect(admin_url('logistic/users?group=drivers'));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('staff_member_lowercase'));
        } else {
            $member = $this->staff_model->get($id);
            if (!$member) {
                blank_page('Staff Member Not Found', 'danger');
            }
            $data['member']            = $member;
            $title                     = $member->firstname . ' ' . $member->lastname;
            $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);

            $ts_filter_data = [];
            if ($this->input->get('filter')) {
                if ($this->input->get('range') != 'period') {
                    $ts_filter_data[$this->input->get('range')] = true;
                } else {
                    $ts_filter_data['period-from'] = $this->input->get('period-from');
                    $ts_filter_data['period-to']   = $this->input->get('period-to');
                }
            } else {
                $ts_filter_data['this_month'] = true;
            }

            $data['logged_time'] = $this->staff_model->get_logged_time_data($id, $ts_filter_data);
            $data['timesheets']  = $data['logged_time']['timesheets'];
        }
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['roles']         = $this->roles_model->get();
        $data['user_notes']    = $this->misc_model->get_notes($id, 'staff');
        $data['departments']   = $this->departments_model->get();


        $data['office_groups'] = $this->logistic_model->get_offices();

        $this->load->view('users/driver', $data);
    }

    /**
     * [driver_email_exists description]
     * @return [type] [description]
     */
    public function driver_email_exists(){
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if the email is the same
                $driverid = $this->input->post('driverid');
                if ($driverid != '') {
                    $this->db->where('id', $driverid);
                    $_current_email = $this->db->get(db_prefix() . 'lg_drivers')->row();
                    if ($_current_email->email == $this->input->post('email')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('email', $this->input->post('email'));
                $total_rows = $this->db->count_all_results(db_prefix() . 'lg_drivers');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }


    /**
     * [driver_username_exists description]
     * @return [type] [description]
     */
    public function driver_username_exists(){
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if the username is the same
                $driverid = $this->input->post('driverid');
                if ($driverid != '') {
                    $this->db->where('id', $driverid);
                    $_current_username = $this->db->get(db_prefix() . 'lg_drivers')->row();
                    if ($_current_username->username == $this->input->post('username')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('username', $this->input->post('username'));
                $total_rows = $this->db->count_all_results(db_prefix() . 'lg_drivers');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * [staff description]
     * @return [type] [description]
     */
    public function staff(){
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('logistic', 'users/table_staff'));
        }
    }

    /**
     * [delete_driver description]
     * @return [type] [description]
     */
    public function delete_driver()
    {
        if (!is_admin() && is_admin($this->input->post('id'))) {
            die('Busted, you can\'t delete administrators');
        }

        if (staff_can('delete',  'staff')) {
            $success = $this->staff_model->delete($this->input->post('id'), $this->input->post('transfer_data_to'));
            if ($success) {
                set_alert('success', _l('deleted', _l('staff_member')));
            }
        }

        redirect(admin_url('logistic/users?group=drivers'));
    }

    /**
     * [packages description]
     * @return [type] [description]
     */
    public function packages(){
        $data['title']                 = _l('lg_packages');
        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();

        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);

        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('logistic', 'packages/table_packages'));
        }

        $this->load->view('packages/manage', $data);
    }

    /**
     * [register_package description]
     * @return [type] [description]
     */
    public function register_package($multiple, $id = '', $pre_alert_id =  ''){

        if ( (!has_permission('lg_packages', '', 'edit') && !has_permission('lg_packages', '', 'create')) || is_driver_staff()) {
            access_denied('register_package');
        }

        if($id == '' || $id == 0){
            $data['title'] = _l('add_package_shipment');
        }else{
            $data['package'] = $this->logistic_model->get_package($id);

            $data['package_attachments'] = $this->logistic_model->get_package_attachments($id);
            $data['title']                 = _l('update_package_shipment');
        }

        if($this->input->post()){
            $package_data = $this->input->post();
            if($package_data['id'] == ''){

                if ( is_driver_staff() || !has_permission('lg_packages', '', 'create') ) {
                    access_denied('register_package');
                }


                unset($package_data['id']);
                $package_id = $this->logistic_model->add_package($multiple, $package_data);

                if($package_id){
                    

                    handle_upload_lg_package_files($package_id);
                  
                    set_alert('success', _l('added_successfully'));
                }

            }else{
                if ( is_driver_staff() || !has_permission('lg_packages', '', 'edit') ) {
                    access_denied('register_package');
                }


                $package_id = $package_data['id'];
                unset($package_data['id']);

                $success = $this->logistic_model->update_package($package_data, $package_id);

                $success_upload = handle_upload_lg_package_files($package_id);

                if($success || $success_upload){
                    set_alert('success', _l('updated_successfully'));
                }
            }

            redirect(admin_url('logistic/packages'));
        }


        $this->load->model('clients_model');
        $this->load->model('invoices_model');

        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['multiple'] = $multiple;
        $data['countries'] = $this->logistic_model->get_logistics_countries('iso_code IS NOT NULL AND active = 1');
        $data['agencies'] = $this->logistic_model->get_agencys();
        $data['office_groups'] = $this->logistic_model->get_offices();
        $data['logistics_services'] = $this->logistic_model->get_logistics_services();
        $data['type_of_packages'] = $this->logistic_model->get_type_of_packages();
        $data['shipping_companies'] = $this->logistic_model->get_shipping_companies();
        $data['shipping_modes'] = $this->logistic_model->get_shipping_modes();
        $data['shipping_times'] = $this->logistic_model->get_shipping_times();
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');

        if($pre_alert_id != ''){
            $data['pre_alert'] = $this->logistic_model->get_pre_alert($pre_alert_id);
        }

        $data['invoices'] = [];

        $this->load->view('packages/package', $data);

    }

    /**
     * [address_form description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function address_form()
    {
       if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();
            if ($this->input->post('address_book_id') == '') {
                unset($data['address_book_id']);

                $data['created_by_type'] = 'staff';
                $data['created_by'] = get_staff_user_id();
                $id = $this->logistic_model->add_client_address($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('lg_address'));
                    set_alert('success', $message);
                }
                redirect(admin_url('clients/client/'.$data['client_id'].'?group=address_book'));
            } else {
                $id = $data['address_book_id'];
                unset($data['address_book_id']);
                $success = $this->logistic_model->update_client_address($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('lg_address'));
                    set_alert('success', $message);
                }
                redirect(admin_url('clients/client/'.$data['client_id'].'?group=address_book'));
            }
            die;
        }
    }


    /**
     * delete address
     * @param  integer $id
     * @return redirect
     */
    public function delete_address($id, $client_id) {
        if (!$id) {
            redirect(admin_url('clients/client/'.$client_id.'?group=address_book'));
        }
        $response = $this->logistic_model->delete_address($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lg_address')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('office')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lg_address')));
        }
        redirect(admin_url('clients/client/'.$client_id.'?group=address_book'));
    }

    /**
     * [add_package_row description]
     */
    public function add_package_row($key){

        $html = '';

        $data['key'] = $key;

        $html .= $this->load->view('packages/package_row', $data, true);

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_client_address_option description]
     * @return [type] [description]
     */
    public function get_client_address_option($client_id, $currency = ''){

        $html = '<option value=""></option>';
        $invoice_html = '<option value=""></option>';
        $recipient_html = '<option value=""></option>';

        $list_address = lg_get_client_address_list($client_id);

        $list_invoice = $this->logistic_model->get_invoices_for_package($client_id, $currency);

        $list_recipients = $this->logistic_model->get_client_recipients($client_id);


        foreach($list_address as $address){
            $html .= '<option value="'.$address['id'].'">'.$address['address'].'</option>';
        }

        foreach($list_invoice as $inv){
            if(total_rows(db_prefix().'lg_packages', ['invoice_id' => $inv['id']]) == 0){
                $invoice_html .= '<option value="'.$inv['id'].'">'.format_invoice_number($inv['id']).'</option>';
            }
        }


        foreach($list_recipients as $recipient){
            $recipient_html .= '<option value="'.$recipient['id'].'">'.$recipient['first_name'].' '.$recipient['last_name'].'</option>';
        }


        echo json_encode([
            'html' => $html,
            'invoice_html' => $invoice_html,
            'recipient_html' => $recipient_html,
        ]);
    }

    /**
     * currency rate table
     * @return [type] 
     */
    public function currency_rate_table(){
        $this->app->get_table_data(module_views_path('logistic', 'settings/currencies/currency_rate_table'));
    }

    /**
     * update automatic conversion
     */
    public function update_setting_currency_rate(){
        $data = $this->input->post();
        $success = $this->logistic_model->update_setting_currency_rate($data);
        if($success == true){
            $message = _l('updated_successfully', _l('setting'));
            set_alert('success', $message);
        }
        redirect(admin_url('logistic/settings?group=currency_rates'));
    }

    /**
     * Gets all currency rate online.
     */
    public function get_all_currency_rate_online()
    {
        $result = $this->logistic_model->get_all_currency_rate_online();
        if($result){
            set_alert('success', _l('updated_successfully', _l('lg_currency_rates')));
        }
        else{
            set_alert('warning', _l('no_data_changes', _l('lg_currency_rates')));                  
        }

        redirect(admin_url('logistic/settings?group=currency_rates'));
    }

    /**
     * update currency rate
     * @return [type] 
     */
    public function update_currency_rate($id)
    {
        if($this->input->post()){
            $data = $this->input->post();

            $result =  $this->logistic_model->update_currency_rate($data, $id);
            if($result){
                set_alert('success', _l('updated_successfully', _l('currency_rates')));
            }
            else{
                set_alert('warning', _l('no_data_changes', _l('currency_rates')));                  
            }
        }

        redirect(admin_url('logistic/settings?group=currency_rates'));
    }

    /**
     * Gets the currency rate online.
     *
     * @param        $id     The identifier
     */
    public function get_currency_rate_online($id)
    {
            $result =  $this->logistic_model->get_currency_rate_online($id);
            echo json_encode(['value' => $result]);
            die;
    }


    /**
     * delete currency
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_currency_rate($id){
        if($id != ''){
            $result =  $this->logistic_model->delete_currency_rate($id);
            if($result){
                set_alert('success', _l('deleted_successfully', _l('currency_rate')));
            }
            else{
                set_alert('danger', _l('deleted_failure', _l('currency_rate')));                   
            }
        }
        redirect(admin_url('logistic/settings?group=currency_rates'));
    }

    /**
     * currency rate modal
     * @return [type] 
     */
    public function currency_rate_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id=$this->input->post('id');

        $data=[];
        $data['currency_rate'] = $this->logistic_model->get_currency_rate($id);

        $this->load->view('settings/currencies/currency_rate_modal', $data);
    }

    /**
     * currency rate table
     * @return [type] 
     */
    public function currency_rate_logs_table(){
        $this->app->get_table_data(module_views_path('logistic', 'settings/currencies/currency_rate_logs_table'));
    }

    /**
     * [create_invoice description]
     * @return [type] [description]
     */
    public function create_invoice($package_id){
        $invoice_id = $this->logistic_model->create_invoice_for_package($package_id);
        if($invoice_id){
            set_alert('successs', _l('lg_create_invoice_successfully'));
            redirect(admin_url('invoices/invoice/'.$invoice_id));
        }

        
    }
    /**
     * [assign_driver description]
     * @return [type] [description]
     */
    public function assign_driver(){
        if($this->input->post()){
            $data = $this->input->post();
            if(isset($data['package_id'])){
                $this->db->where('id', $data['package_id']);
                $this->db->update(db_prefix().'lg_packages', ['assign_driver' => $data['assign_driver']]);
                if($this->db->affected_rows() > 0){



                    if(is_numeric($data['assign_driver']) && $data['assign_driver'] > 0){
                        $this->load->model('staff_model');

                        $package = $this->logistic_model->get_package($data['package_id']);
                        $staff = $this->staff_model->get($data['assign_driver']);

                        $template = mail_template('Logistic_package_assign_driver', 'logistic', $package, $staff);
                        $template->send();
                        
                        $notified = add_notification([
                        'description'     => _l('lg_assign_driver'),
                        'link'            => 'logistic/package_detail/'.$data['package_id'],
                        'touserid'  => $data['assign_driver'],
                        'fromcompany' => '',
                        'additional_data' => serialize([
                            $package->shipping_prefix.$package->number_code,
                        ]),
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$data['assign_driver']]);
                        }
                    }

                    $action_data = [];
                    $action_data['rel_id'] = $data['package_id'];
                    $action_data['rel_type'] = 'package';
                    $action_data['time_update'] = date('Y-m-d H:i:s');
                    $action_data['user'] = get_staff_user_id();
                    $action_data['action'] = _l('lg_assign_driver');
                    $action_data['created_at'] = date('Y-m-d H:i:s');
                    $action_data['created_by'] = get_staff_user_id();
                    $this->db->insert(db_prefix().'lg_action_history', $action_data);

                    set_alert('success', _l('updated_successfully'));
                }
            }

            if(isset($data['redirect_url'])){
                redirect($data['redirect_url']);
            }
        }

        redirect(admin_url('logistic/packages'));
    }


    /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_package($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('packages/_file', $data);
    }

    /**
     * { delete purchase order attachment }
     *
     * @param      <type>  $id     The identifier
     */
    public function delete_package_attachment($id)
    {
        $this->load->model('misc_model');
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo lg_html_entity_decode($this->logistic_model->delete_package_attachment($id));
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /**
     * Gets the currency rate.
     *
     * @param        $currency_id  The currency identifier
     */
    public function get_currency_rate($currency_id){
        $base_currency = get_base_currency();

        $pr_currency = lg_get_currency_by_id($currency_id);

        $currency_rate = 1;
        $convert_str = ' ('.$base_currency->name.' => '.$base_currency->name.')'; 
        $currency_name = '('.$base_currency->name.')';
        if($base_currency->id != $pr_currency->id){
            $currency_rate = lg_get_currency_rate($base_currency->name, $pr_currency->name);
            $convert_str = ' ('.$base_currency->name.' => '.$pr_currency->name.')'; 
            $currency_name = '('.$pr_currency->name.')';
        }

        echo json_encode([
            'currency_rate' => $currency_rate,
            'convert_str' => $convert_str,
            'currency_name' => $currency_name,
        ]);

    }

    /**
     * [package_detail description]
     * @return [type] [description]
     */
    public function package_detail($id){

        if(!has_permission('lg_packages', '', 'view') && !has_permission('lg_packages', '', 'view_own')){
            access_denied('packages');
        }

        $this->load->model('clients_model');



        $template_name = 'logistic_package_send_to_customer';
        $package = $this->logistic_model->get_package($id);
        $data = [];
        $data = lg_prepare_mail_preview_data($template_name, $package->customer_id, ['logistic'] );

        $data['package'] = $package;
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');
        $data['package_attachments'] = $this->logistic_model->get_package_attachments($id);
        $data['delivery_shipments'] = $this->logistic_model->get_delivery_shipments($id);
        $data['shipment_attachments'] = $this->logistic_model->get_package_shipment_attachments($id);

        $data['client'] = $this->clients_model->get($data['package']->customer_id);

        $data['client_address'] = $this->logistic_model->get_client_address($data['package']->customer_address);

        $data['tracking_histories'] = $this->logistic_model->get_tracking_histories_package($id);
        $data['action_histories'] = $this->logistic_model->get_action_histories_package($id);

        

        $data['title'] = _l('lg_package_details');

        $this->load->view('packages/package_detail', $data);
    }

    /**
     * [export_package_shipment description]
     * @return [type] [description]
     */
    public function export_package_shipment($id){
         if (!$id) {
            redirect(admin_url('logistic/packages'));
        }

        $package_data = $this->logistic_model->get_package($id);

        $package = $this->logistic_model->get_package_pdf_html($package_data);

        try {
            $pdf = $this->logistic_model->package_pdf($package);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $package_data->shipping_prefix.$package_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * [export_package_label description]
     * @return [type] [description]
     */
    public function export_package_label($id){
         if (!$id) {
            redirect(admin_url('logistic/packages'));
        }

        $package_data = $this->logistic_model->get_package($id);

        $package = $this->logistic_model->get_package_label_pdf_html($package_data);

        try {
            $pdf = $this->logistic_model->package_label_pdf($package);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $package_data->shipping_prefix.$package_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * [shipment_tracking description]
     * @return [type] [description]
     */
    public function shipment_tracking($package_id){

        $package = $this->logistic_model->get_package($package_id);

        if(!has_permission('lg_packages','','edit') || !lg_format_invoice_status($package->invoice_id, 1) == 2){
            access_denied('shipment_tracking');
        }


        if($this->input->post()){
            $data_shipment = $this->input->post();
            $data_shipment['rel_type'] = 'package';

            $tracking_id = $this->logistic_model->add_shipment_tracking($data_shipment);

            if($tracking_id){
                set_alert('success', _l('added_successfully'));
            }

            redirect(admin_url('logistic/package_detail/'.$package_id));
        }

        $data['title'] = _l('lg_shipment_tracking');

        $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
        $data['package'] = $package;
        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();
        $data['office_groups'] = $this->logistic_model->get_offices();


        $this->load->view('packages/shipment_tracking', $data);
    }

    /**
     * [create_delivery_shipment description]
     * @param  [type] $package_id [description]
     * @return [type]             [description]
     */
    public function create_delivery_shipment($package_id){

        $package = $this->logistic_model->get_package($package_id);
        $delivery_shipments = $this->logistic_model->get_delivery_shipments($package_id);

        if(!has_permission('lg_packages','','edit') || !lg_format_invoice_status($package->invoice_id, 1) == 2 || isset($delivery_shipments->id)){
            access_denied('delivery_shipment');
        }

        if($this->input->post()){
            $data_shipment = $this->input->post();
            
            $data_shipment['note'] = $this->input->post('note', false);

            $tracking_id = $this->logistic_model->delivery_shipment($data_shipment);

            lg_handle_delivery_shipment_attachment($package_id);

            if($tracking_id){
                set_alert('success', _l('lg_the_shipment_has_been_delivered'));
            }

            redirect(admin_url('logistic/package_detail/'.$package_id));
        }

        $data['package'] = $package;
        $data['title'] = _l('lg_delivery_shipment_str');
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');
        $data['shipment_sign'] = $this->logistic_model->get_delivery_shipment($package_id);


        $this->load->view('packages/delivery_shipment', $data);

    }

    /**
     * [upload_sign description]
     * @return [type] [description]
     */
    public function upload_sign($package_id){
        if($this->input->post()){


            $data = $this->input->post();
            $signature = $data['signature'];

            $package = $this->logistic_model->get_package($package_id);

            $path = LOGISTIC_MODULE_UPLOAD_FOLDER .'/delivery_shipment/sign/' .$package_id;

            $success = lg_process_digital_signature_image($package_id, $signature, $path, 'signature_'.$package_id);

            $data_sign = [];
            $data_sign['package'] = $package;
            $data_sign['shipment_sign'] = $this->logistic_model->get_delivery_shipment($package_id);


            $html = $this->load->view('packages/package_shipment_sign', $data_sign, true);

            echo json_encode([
                'success' => $success,
                'html' => $html,
            ]);
        }

    }

    /**
     * [remove_sign description]
     * @return [type] [description]
     */
    public function remove_sign($package_id){
        

            $package = $this->logistic_model->get_package($package_id);

            $success = $this->logistic_model->remove_package_shipment_sign($package_id);

            $data_sign = [];
            $data_sign['package'] = $package;
            $data_sign['shipment_sign'] = $this->logistic_model->get_delivery_shipment($package_id);


            $html = $this->load->view('packages/package_shipment_sign', $data_sign, true);

            echo json_encode([
                'success' => $success,
                'html' => $html,
            ]);

        
    }

    /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipment_package($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('packages/_file_sm', $data);
    }

    /**
     * 
     */
    public function send_package_to_email($id){

        try {
            

            $success = $this->logistic_model->send_package_to_client(
                $id,
                '',
                $this->input->post('attach_pdf'),
                $this->input->post('cc'),
                false,
                
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo lg_html_entity_decode($message);
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('package_sent_to_client_success'));
        } else {
            set_alert('danger', _l('package_sent_to_client_fail'));
        }

        if($this->input->post('redirect_url') != ''){
            redirect($this->input->post('redirect_url'));
        }

        redirect(admin_url('logistic/package_detail/' . $id));
    }

    /**
     * [send_package_modal description]
     * @return [type] [description]
     */
    public function send_package_modal($package_id){

        $html = '';

        $template_name = 'logistic_package_send_to_customer';
        $package = $this->logistic_model->get_package($package_id);
        $data = [];
        $data = lg_prepare_mail_preview_data($template_name, $package->customer_id, ['logistic'] );

        $data['package'] = $package;


        $this->load->view('packages/package_send_to_client', $data);

        
    }

    /**
     * [pre_alert_list description]
     * @return [type] [description]
     */
    public function pre_alert_list(){
        $data['title'] = _l('lg_pre_alert_list');

        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('logistic', 'pre_alert/table_pre_alert'));
        }

        $this->load->view('pre_alert/manage', $data);
    }

    /**
     * [get_package_info_by_invoice description]
     * @return [type] [description]
     */
    public function get_package_info_by_invoice($invoice_id){

        $items = get_items_by_type('invoice', $invoice_id);
        $html = '';
        foreach($items as $key => $item){
            $data['key'] = $key-1;
            $data['default_description'] = $item['description'];

            $html .= $this->load->view('packages/package_row', $data, true);
        }


        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [recipients description]
     * @return [type] [description]
     */
    public function recipients(){
        $data['title'] = _l('lg_recipients');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('logistic', 'recipients/table_recipients'));
        }
        
        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);

        $this->load->view('recipients/manage', $data);

    }

    /**
     * [shipping description]
     * @return [type] [description]
     */
    public function shipping(){
        if(!has_permission('lg_shipping', '', 'view') && !has_permission('lg_shipping', '', 'view_own')){
            access_denied('shipping');
        }

        $data['title'] = _l('shipping');        

        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();

        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);

        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');

        $data['shipping_type'] = (($this->input->get('shipping_type') != '') ? $this->input->get('shipping_type') : 'shipping');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('logistic', 'shipping/table_shipping'), ['shipping_type' => $data['shipping_type']]);
        }

        $data['title'] = _l($data['shipping_type']);


        $this->load->view('shipping/manage', $data);

    }

    /**
     * [shipment description]
     * @return [type] [description]
     */
    public function shipment($multiple, $id = ''){
        if((!has_permission('lg_shipping', '', 'create') && !has_permission('lg_shipping', '', 'edit')) || is_driver_staff()){
            access_denied('shipping');
        }

        if($id == ''){
            if($multiple == 0){
                $data['title'] = _l('lg_create_shipment');
            }else{
                $data['title'] = _l('lg_create_multiple_shipment');
            }

        }else{

            $data['shipment'] = $this->logistic_model->get_shipping($id);

            $data['shipment_attachments'] = $this->logistic_model->get_shipping_attachments($id);
            $data['title'] = _l('lg_update_shipment');
        }

        if($this->input->post()){
            $shipping_data = $this->input->post();
            if($shipping_data['id'] == ''){

                if(!has_permission('lg_shipping', '', 'create') || is_driver_staff()){
                    access_denied('shipping');
                }

                unset($shipping_data['id']);
                $shipping_data['created_from'] = 'admin';
                $shipping_data['shipping_type'] = 'shipping';
                $shipping_id = $this->logistic_model->add_shipping($multiple, $shipping_data);

                if($shipping_id){
                    

                    handle_upload_lg_shipping_files($shipping_id);
                  
                    set_alert('success', _l('added_successfully'));
                }

            }else{
                if(!has_permission('lg_shipping', '', 'edit') || is_driver_staff()){
                    access_denied('shipping');
                }

                $shipping_id = $shipping_data['id'];
                unset($shipping_data['id']);
                $shipping_data['created_from'] = 'admin';
                $success = $this->logistic_model->update_shipping($shipping_data, $shipping_id);

                $success_upload = handle_upload_lg_shipping_files($shipping_id);

                if($success || $success_upload){
                    set_alert('success', _l('updated_successfully'));
                }
            }

            redirect(admin_url('logistic/shipping'));
        }


        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['multiple'] = $multiple;
        $data['countries'] = $this->logistic_model->get_logistics_countries('iso_code IS NOT NULL AND active = 1');
        $data['agencies'] = $this->logistic_model->get_agencys();
        $data['office_groups'] = $this->logistic_model->get_offices();
        $data['logistics_services'] = $this->logistic_model->get_logistics_services();
        $data['type_of_packages'] = $this->logistic_model->get_type_of_packages();
        $data['shipping_companies'] = $this->logistic_model->get_shipping_companies();
        $data['shipping_modes'] = $this->logistic_model->get_shipping_modes();
        $data['shipping_times'] = $this->logistic_model->get_shipping_times();
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');
        $data['payment_terms'] = $this->logistic_model->get_logistics_payment_terms();

        $this->load->view('shipping/shipment', $data);
    }

    /**
     * [add_shipment_row description]
     */
    public function add_shipment_row($key){

        $html = '';

        $data['key'] = $key;

        $html .= $this->load->view('shipping/package_row', $data, true);

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * { preview shipping file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipment($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('shipping/_file', $data);
    }

    /**
     * { delete purchase order attachment }
     *
     * @param      <type>  $id     The identifier
     */
    public function delete_shipment_attachment($id)
    {
        $this->load->model('misc_model');
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo lg_html_entity_decode($this->logistic_model->delete_shipment_attachment($id));
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /**
     * [delete_package description]
     * @return [type] [description]
     */
    public function delete_package($package_id){

        if(!has_permission('lg_packages', '', 'delete')){
            access_denied('packages');
        }

        $success = $this->logistic_model->delete_package($package_id);
        if($success){
            set_alert('success', _l('deleted_successfully'));
        }

        redirect(admin_url('logistic/packages'));

    }

    /**
     * [get_client_recipient_address description]
     * @return [type] [description]
     */
    public function get_client_recipient_address($recipient_id){
        $html = '<option value=""></option>';

        $recipient_address = $this->logistic_model->get_recipient($recipient_id);

        
        if(isset($recipient_address->address)){
            foreach($recipient_address->address as $address){
                $html .= '<option value="'.$address['id'].'">'.$address['address'].'</option>';
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
        
    }

    /**
     * [export_shipping_shipment description]
     * @return [type] [description]
     */
    public function export_shipping_shipment($id){
         if (!$id) {
            redirect(admin_url('logistic/shipping'));
        }

        $shipping_data = $this->logistic_model->get_shipping($id);

        $shipping = $this->logistic_model->get_shipping_pdf_html($shipping_data);

        try {
            $pdf = $this->logistic_model->shipping_pdf($shipping);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $shipping_data->shipping_prefix.$shipping_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * [export_shipping_label description]
     * @return [type] [description]
     */
    public function export_shipping_label($id){
         if (!$id) {
            redirect(admin_url('logistic/shipping'));
        }

        $shipping_data = $this->logistic_model->get_shipping($id);

        $shipping = $this->logistic_model->get_shipping_label_pdf_html($shipping_data);

        try {
            $pdf = $this->logistic_model->shipping_label_pdf($shipping);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $shipping_data->shipping_prefix.$shipping_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * [shipping_detail description]
     * @return [type] [description]
     */
    public function shipping_detail($id){

        if(!has_permission('lg_shipping', '', 'view') && !has_permission('lg_shipping', '', 'view_own')){
            access_denied('shippings');
        }

        $this->load->model('clients_model');

        $template_name = 'logistic_shipping_send_to_customer';
        $shipping = $this->logistic_model->get_shipping($id);
        $data = [];
        $data = lg_prepare_mail_preview_data($template_name, $shipping->customer_id, ['logistic'] );

        $data['shipping'] = $shipping;
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');
        $data['shipping_attachments'] = $this->logistic_model->get_shipping_attachments($id);
        $data['delivery_shipments'] = $this->logistic_model->get_shipping_delivery_shipments($id);
        $data['shipment_attachments'] = $this->logistic_model->get_shipping_shipment_attachments($id);

        $data['client'] = $this->clients_model->get($data['shipping']->customer_id);

        $data['client_address'] = $this->logistic_model->get_client_address($data['shipping']->customer_address);

        $data['tracking_histories'] = $this->logistic_model->get_tracking_histories_shipping($id);
        $data['action_histories'] = $this->logistic_model->get_action_histories_shipping($id);

        $data['recipient'] = $this->logistic_model->get_recipient($shipping->recipient_id);
        $data['recipient_address'] = $this->logistic_model->get_recipient_address($shipping->recipient_address_id);
        

        $data['title'] = _l('lg_shipping_details');

        $this->load->view('shipping/shipping_detail', $data);
    }

    /**
     * [assign_driver description]
     * @return [type] [description]
     */
    public function shipping_assign_driver(){
        if($this->input->post()){
            $data = $this->input->post();
            if(isset($data['shipping_id'])){
                $this->db->where('id', $data['shipping_id']);
                $this->db->update(db_prefix().'lg_shippings', ['assign_driver' => $data['assign_driver']]);
                if($this->db->affected_rows() > 0){

                    if(is_numeric($data['assign_driver']) && $data['assign_driver'] > 0){
                        $shipping = $this->logistic_model->get_shipping($data['shipping_id']);

                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get($data['assign_driver']);

                        $template = mail_template('Logistic_shipping_assign_driver', 'logistic', $shipping, $staff);
                        $template->send();
                        

                        $notified = add_notification([
                        'description'     => _l('lg_assign_driver'),
                        'link'            => 'logistic/shipping_detail/'.$data['shipping_id'],
                        'touserid'  => $data['assign_driver'],
                        'fromcompany' => '',
                        'additional_data' => serialize([
                            $shipping->shipping_prefix.$shipping->number_code,
                        ]),
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$data['assign_driver']]);
                        }
                    }


                    $action_data = [];
                    $action_data['rel_id'] = $data['shipping_id'];
                    $action_data['rel_type'] = 'shipping';
                    $action_data['time_update'] = date('Y-m-d H:i:s');
                    $action_data['user'] = get_staff_user_id();
                    $action_data['action'] = _l('lg_assign_driver');
                    $action_data['created_at'] = date('Y-m-d H:i:s');
                    $action_data['created_by'] = get_staff_user_id();
                    $this->db->insert(db_prefix().'lg_action_history', $action_data);

                    set_alert('success', _l('updated_successfully'));
                }
            }

            if(isset($data['redirect_url'])){
                redirect($data['redirect_url']);
            }
        }

        redirect(admin_url('logistic/shipping'));
    }


    /**
     * [send_shipping_modal description]
     * @return [type] [description]
     */
    public function send_shipping_modal($shipping_id){

        $html = '';

        $template_name = 'logistic_shipping_send_to_customer';
        $shipping = $this->logistic_model->get_shipping($shipping_id);
        $data = [];
        $data = lg_prepare_mail_preview_data($template_name, $shipping->customer_id, ['logistic'] );

        $data['shipping'] = $shipping;


        $this->load->view('shipping/shipping_send_to_client', $data);

        
    }


    /**
     * 
     */
    public function send_shipping_to_email($id){

        try {
            

            $success = $this->logistic_model->send_shipping_to_client(
                $id,
                '',
                $this->input->post('attach_pdf'),
                $this->input->post('cc'),
                false,
                
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo lg_html_entity_decode($message);
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('shipping_sent_to_client_success'));
        } else {
            set_alert('danger', _l('shipping_sent_to_client_fail'));
        }

        if($this->input->post('redirect_url') != ''){
            redirect($this->input->post('redirect_url'));
        }

        redirect(admin_url('logistic/shipping_detail/' . $id));
    }


    /**
     * [shipping_create_invoice description]
     * @return [type] [description]
     */
    public function shipping_create_invoice($shipping_id){
        $invoice_id = $this->logistic_model->create_invoice_for_shipping($shipping_id);
        if($invoice_id){
            set_alert('successs', _l('lg_create_invoice_successfully'));
            redirect(admin_url('invoices/invoice/'.$invoice_id));
        }

        
    }


    /**
     * [shipment_tracking description]
     * @return [type] [description]
     */
    public function shipping_shipment_tracking($shipping_id){

        $shipping = $this->logistic_model->get_shipping($shipping_id);

        if(!has_permission('lg_shipping','','edit') || !lg_format_invoice_status($shipping->invoice_id, 1) == 2){
            access_denied('shipment_tracking');
        }


        if($this->input->post()){
            $data_shipment = $this->input->post();
            $data_shipment['rel_type'] = 'shipping';

            $tracking_id = $this->logistic_model->add_shipping_shipment_tracking($data_shipment);

            if($tracking_id){
                set_alert('success', _l('added_successfully'));
            }

            redirect(admin_url('logistic/shipping_detail/'.$shipping_id));
        }

        $data['title'] = _l('lg_shipment_tracking');

        $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
        $data['shipping'] = $shipping;
        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();
        $data['office_groups'] = $this->logistic_model->get_offices();


        $this->load->view('shipping/shipment_tracking', $data);
    }

    /**
     * [create_delivery_shipment description]
     * @param  [type] $shipping_id [description]
     * @return [type]             [description]
     */
    public function shipping_create_delivery_shipment($shipping_id){

        $shipping = $this->logistic_model->get_shipping($shipping_id);
        $delivery_shipments = $this->logistic_model->get_shipping_delivery_shipments($shipping_id);

        if(!has_permission('lg_shipping','','edit') || !lg_format_invoice_status($shipping->invoice_id, 1) == 2 || isset($delivery_shipments->id)){
            access_denied('delivery_shipment');
        }

        if($this->input->post()){
            $data_shipment = $this->input->post();
            
            $data_shipment['note'] = $this->input->post('note', false);

            $tracking_id = $this->logistic_model->shipping_delivery_shipment($data_shipment);

            lg_handle_shipping_delivery_shipment_attachment($shipping_id);

            if($tracking_id){
                set_alert('success', _l('lg_the_shipment_has_been_delivered'));
            }

            redirect(admin_url('logistic/shipping_detail/'.$shipping_id));
        }

        $data['shipping'] = $shipping;
        $data['title'] = _l('lg_delivery_shipment_str');
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');
        $data['shipment_sign'] = $this->logistic_model->get_shipping_delivery_shipment($shipping_id);


        $this->load->view('shipping/delivery_shipment', $data);

    }

    /**
     * [upload_sign description]
     * @return [type] [description]
     */
    public function shipping_upload_sign($shipping_id){
        if($this->input->post()){


            $data = $this->input->post();
            $signature = $data['signature'];

            $shipping = $this->logistic_model->get_shipping($shipping_id);

            $path = LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_delivery_shipment/sign/' .$shipping_id;

            $success = lg_process_digital_signature_image_shipping($shipping_id, $signature, $path, 'signature_'.$shipping_id);

            $data_sign = [];
            $data_sign['shipping'] = $shipping;
            $data_sign['shipment_sign'] = $this->logistic_model->get_shipping_delivery_shipment($shipping_id);


            $html = $this->load->view('shipping/shipping_shipment_sign', $data_sign, true);

            echo json_encode([
                'success' => $success,
                'html' => $html,
            ]);
        }

    }


    /**
     * [shipping_remove_sign description]
     * @return [type] [description]
     */
    public function shipping_remove_sign($shipping_id){
        

            $shipping = $this->logistic_model->get_shipping($shipping_id);

            $success = $this->logistic_model->remove_shipping_shipment_sign($shipping_id);

            $data_sign = [];
            $data_sign['shipping'] = $shipping;
            $data_sign['shipment_sign'] = $this->logistic_model->get_shipping_delivery_shipment($shipping_id);


            $html = $this->load->view('shipping/shipping_shipment_sign', $data_sign, true);

            echo json_encode([
                'success' => $success,
                'html' => $html,
            ]);

        
    }

    /**
     * [delete_package description]
     * @return [type] [description]
     */
    public function delete_shipping($shipping_id){

        if(!has_permission('lg_shipping', '', 'delete')){
            access_denied('shipping');
        }

        $success = $this->logistic_model->delete_shipping($shipping_id);
        if($success){
            set_alert('success', _l('deleted_successfully'));
        }

        redirect(admin_url('logistic/shipping'));

    }

    /**
     * { preview shipping file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipping($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('shipping/_file', $data);
    }

    /**
     * { delete shipping attachment }
     *
     * @param      <type>  $id     The identifier
     */
    public function delete_shipping_attachment($id)
    {
        $this->load->model('misc_model');
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo lg_html_entity_decode($this->logistic_model->delete_shipping_attachment($id));
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipment_shipping($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('shipping/_file_sm', $data);
    }

    /**
     * [approve_pickup description]
     * @return [type] [description]
     */
    public function approve_pickup(){
        if (!has_permission('lg_shipping', '', 'edit') ) {
            access_denied('shipping');
        }

        if($this->input->post()){
            $data = $this->input->post();

            $success = $this->logistic_model->approve_pickup($data);
            if($success){
                $message = _l('approved_pickup_successfully');
                if($data['approval_status'] == 'rejected'){
                    $message = _l('rejected_pickup_successfully');
                }

                set_alert('success', $message);
            }

            if(isset($data['redirect_url'])){
                redirect($data['redirect_url']);
            }

        }

        redirect(admin_url('logistic/shipping?shipping_type=pickup'));
    }

    /**
     * [consolidated description]
     * @return [type] [description]
     */
    public function consolidated(){
        if (!has_permission('lg_consolidated', '', 'view') && !has_permission('lg_consolidated', '', 'view_own') ) {
            access_denied('shipping');
        }

        $data['title'] = _l('consolidated');        

        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();

        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);

        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('logistic', 'consolidated/table_consolidated'));
        }

        $this->load->view('consolidated/manage', $data);

    }

    /**
     * [get_package_info_by_invoice description]
     * @return [type] [description]
     */
    public function get_package_info_by_invoice_for_shipping($invoice_id){

        $items = get_items_by_type('invoice', $invoice_id);
        $html = '';
        foreach($items as $key => $item){
            $data['key'] = $key-1;
            $data['default_description'] = $item['description'];

            $html .= $this->load->view('shipping/package_row', $data, true);
        }


        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [consolidation description]
     * @return [type] [description]
     */
    public function consolidation($id = ''){

        if((!has_permission('lg_consolidated', '', 'create') && !has_permission('lg_consolidated', '', 'edit')) || is_driver_staff()){
            access_denied('consolidated');
        }

        if($id == ''){
           
            $data['title'] = _l('lg_create_consolidation');
           

        }else{

            $data['consolidation'] = $this->logistic_model->get_consolidation($id);

            $data['consolidation_attachments'] = $this->logistic_model->get_consolidation_attachments($id);
            $data['title'] = _l('lg_update_consolidation');
        }

        if($this->input->post()){
            $consolidation_data = $this->input->post();
            if($consolidation_data['id'] == ''){

                if(!has_permission('lg_consolidated', '', 'create') || is_driver_staff()){
                    access_denied('consolidated');
                }
                unset($consolidation_data['id']);
                $consolidation_id = $this->logistic_model->add_consolidation($consolidation_data);

                if($consolidation_id){
                    

                    handle_upload_lg_consolidation_files($consolidation_id);
                  
                    set_alert('success', _l('added_successfully'));
                }

            }else{
                if(!has_permission('lg_consolidated', '', 'edit') || is_driver_staff()){
                    access_denied('consolidated');
                }

                $consolidation_id = $consolidation_data['id'];
                unset($consolidation_data['id']);
             
                $success = $this->logistic_model->update_consolidation($consolidation_data, $consolidation_id);

                $success_upload = handle_upload_lg_consolidation_files($consolidation_id);

                if($success || $success_upload){
                    set_alert('success', _l('updated_successfully'));
                }
            }

            redirect(admin_url('logistic/consolidated'));
        }


        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
    
        $data['countries'] = $this->logistic_model->get_logistics_countries('iso_code IS NOT NULL AND active = 1');
        $data['agencies'] = $this->logistic_model->get_agencys();
        $data['office_groups'] = $this->logistic_model->get_offices();
        $data['logistics_services'] = $this->logistic_model->get_logistics_services();
        $data['type_of_packages'] = $this->logistic_model->get_type_of_packages();
        $data['shipping_companies'] = $this->logistic_model->get_shipping_companies();
        $data['shipping_modes'] = $this->logistic_model->get_shipping_modes();
        $data['shipping_times'] = $this->logistic_model->get_shipping_times();
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');
        $data['payment_terms'] = $this->logistic_model->get_logistics_payment_terms();

        $this->load->view('consolidated/consolidation', $data);
    }

    /**
     * [load_packages_for_consolidation description]
     * @return [type] [description]
     */
    public function load_packages_for_consolidation(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();
            if(isset($data['rel_type']) && $data['rel_type'] != '' && isset($data['customer_id']) && $data['customer_id'] != ''  && isset($data['currency_id']) && $data['currency_id'] != ''){

                $not_allow_status = [];
                $this->db->where('style_name IN ("delivered", "consolidate", "cancelled")');
                $this->db->where('is_default_status', 1);
                $statuses = $this->db->get(db_prefix().'lg_style_and_states')->result_array();
                foreach($statuses as $status){
                    $not_allow_status[] = $status['id'];
                }

                if($data['rel_type'] == 'locker_packages'){
                    $this->db->where('currency', $data['currency_id']);
                    $this->db->where('customer_id', $data['customer_id']);
                    $packages = $this->db->get(db_prefix().'lg_packages')->result_array();

                    foreach($packages as $package){
                        if(!in_array($package['delivery_status'], $not_allow_status)){
                            $html .= '<option value="'.$package['id'].'">'.$package['shipping_prefix'].$package['number_code'].'</option>';
                        }
                    }


                }else if($data['rel_type'] == 'shipping'){
                    $this->db->where('currency', $data['currency_id']);
                    $this->db->where('customer_id', $data['customer_id']);
                    $this->db->where('(shipping_type IS NULL or shipping_type = "shipping")');
                    $packages = $this->db->get(db_prefix().'lg_shippings')->result_array();

                    foreach($packages as $package){
                        if(!in_array($package['delivery_status'], $not_allow_status)){
                            $html .= '<option value="'.$package['id'].'">'.$package['shipping_prefix'].$package['number_code'].'</option>';
                        }
                    }
                }
               
            }

        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [load_package_row_for_consolidation description]
     * @return [type] [description]
     */
    public function load_package_row_for_consolidation(){
        $html = '';

        if($this->input->post()){
            $data = $this->input->post();

            if(is_array($data['rel_ids']) &&  $data['rel_type'] != ''){
                $key = 0;
                foreach($data['rel_ids'] as $rel_id){
                    if($data['rel_type'] == 'locker_packages'){

                        $packages = $this->logistic_model->get_package($rel_id);
                        if(isset($packages->package_detail)){
                            foreach($packages->package_detail as $k => $detail){

                                $detail['key'] = $key-1;

                                $html .= $this->load->view('consolidated/package_row', $detail, true);
                                $key++;
                            }
                            
                        }

                    }else if($data['rel_type'] == 'shipping'){
                        $packages = $this->logistic_model->get_shipping($rel_id);

                        if(isset($packages->shipment_detail)){
                            foreach($packages->shipment_detail as $k => $detail){

                                $detail['key'] = $key-1;

                                $html .= $this->load->view('consolidated/package_row', $detail, true);
                                $key++;
                                
                            }
                        }
                    }
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * { preview consolidated file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_consolidated($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('consolidated/_file', $data);
    }

    /**
     * { delete consolidated attachment }
     *
     * @param      <type>  $id     The identifier
     */
    public function delete_consolidation_attachment($id)
    {
        $this->load->model('misc_model');
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo lg_html_entity_decode($this->logistic_model->delete_consolidated_attachment($id));
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /**
     * [send_consolidation_modal description]
     * @return [type] [description]
     */
    public function send_consolidation_modal($consolidation_id){

        $html = '';

        $template_name = 'logistic_consolidation_send_to_customer';
        $consolidation = $this->logistic_model->get_consolidation($consolidation_id);
        $data = [];
        $data = lg_prepare_mail_preview_data($template_name, $consolidation->customer_id, ['logistic'] );

        $data['consolidation'] = $consolidation;


        $this->load->view('consolidated/consolidated_send_to_client', $data);

        
    }


    /**
     * 
     */
    public function send_consolidation_to_email($id){

        try {
            

            $success = $this->logistic_model->send_consolidation_to_client(
                $id,
                '',
                $this->input->post('attach_pdf'),
                $this->input->post('cc'),
                false,
                
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo lg_html_entity_decode($message);
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('consolidation_sent_to_client_success'));
        } else {
            set_alert('danger', _l('consolidation_sent_to_client_fail'));
        }

        if($this->input->post('redirect_url') != ''){
            redirect($this->input->post('redirect_url'));
        }

        redirect(admin_url('logistic/consolidated_detail/' . $id));
    }

    /**
     * [consolidated_assign_driver description]
     * @return [type] [description]
     */
    public function consolidated_assign_driver(){
        if($this->input->post()){
            $data = $this->input->post();
            if(isset($data['consolidation_id'])){
                $this->db->where('id', $data['consolidation_id']);
                $this->db->update(db_prefix().'lg_consolidated', ['assign_driver' => $data['assign_driver']]);
                if($this->db->affected_rows() > 0){

                    if(is_numeric($data['assign_driver']) && $data['assign_driver'] > 0){
                        $consolidation = $this->logistic_model->get_consolidation($data['consolidation_id']);

                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get($data['assign_driver']);

                        $template = mail_template('Logistic_consolidated_assign_driver', 'logistic', $consolidation, $staff);
                        $template->send();

                        $notified = add_notification([
                        'description'     => _l('lg_assign_driver'),
                        'link'            => 'logistic/consolidated_detail/'.$data['consolidation_id'],
                        'touserid'  => $data['assign_driver'],
                        'fromcompany' => '',
                        'additional_data' => serialize([
                            $consolidation->shipping_prefix.$consolidation->number_code,
                        ]),
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$data['assign_driver']]);
                        }
                    }

                    $action_data = [];
                    $action_data['rel_id'] = $data['consolidation_id'];
                    $action_data['rel_type'] = 'consolidated';
                    $action_data['time_update'] = date('Y-m-d H:i:s');
                    $action_data['user'] = get_staff_user_id();
                    $action_data['action'] = _l('lg_assign_driver');
                    $action_data['created_at'] = date('Y-m-d H:i:s');
                    $action_data['created_by'] = get_staff_user_id();
                    $this->db->insert(db_prefix().'lg_action_history', $action_data);

                    set_alert('success', _l('updated_successfully'));
                }
            }

            if(isset($data['redirect_url'])){
                redirect($data['redirect_url']);
            }
        }

        redirect(admin_url('logistic/consolidated'));
    }


    /**
     * [export_consolidation_shipment description]
     * @return [type] [description]
     */
    public function export_consolidation_shipment($id){
         if (!$id) {
            redirect(admin_url('logistic/consolidated'));
        }

        $consolidation_data = $this->logistic_model->get_consolidation($id);

        $consolidation = $this->logistic_model->get_consolidation_pdf_html($consolidation_data);

        try {
            $pdf = $this->logistic_model->consolidation_pdf($consolidation);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $consolidation_data->shipping_prefix.$consolidation_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * [export_consolidation_label description]
     * @return [type] [description]
     */
    public function export_consolidation_label($id){
         if (!$id) {
            redirect(admin_url('logistic//consolidated'));
        }

        $consolidation_data = $this->logistic_model->get_consolidation($id);

        $consolidation = $this->logistic_model->get_consolidation_label_pdf_html($consolidation_data);

        try {
            $pdf = $this->logistic_model->consolidation_label_pdf($consolidation);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $consolidation_data->shipping_prefix.$consolidation_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }


     /**
     * [consolidated_detail description]
     * @return [type] [description]
     */
    public function consolidated_detail($id){

        if(!has_permission('lg_consolidated', '', 'view') && !has_permission('lg_consolidated', '', 'view_own')){
            access_denied('consolidateds');
        }

        $this->load->model('clients_model');

        $template_name = 'logistic_consolidation_send_to_customer';
        $consolidation = $this->logistic_model->get_consolidation($id);
        $data = [];
        $data = lg_prepare_mail_preview_data($template_name, $consolidation->customer_id, ['logistic'] );

        $data['consolidation'] = $consolidation;
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');
        $data['consolidation_attachments'] = $this->logistic_model->get_consolidation_attachments($id);
        $data['delivery_shipments'] = $this->logistic_model->get_consolidation_delivery_shipments($id);
        $data['shipment_attachments'] = $this->logistic_model->get_consolidation_shipment_attachments($id);

        $data['client'] = $this->clients_model->get($data['consolidation']->customer_id);

        $data['client_address'] = $this->logistic_model->get_client_address($data['consolidation']->customer_address);

        $data['tracking_histories'] = $this->logistic_model->get_tracking_histories_consolidation($id);
        $data['action_histories'] = $this->logistic_model->get_action_histories_consolidation($id);

        $data['recipient'] = $this->logistic_model->get_recipient($consolidation->recipient_id);
        $data['recipient_address'] = $this->logistic_model->get_recipient_address($consolidation->recipient_address_id);
        

        $data['title'] = _l('lg_consolidation_details');

        $this->load->view('consolidated/consolidated_detail', $data);
    }

    /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipment_consolidation($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('consolidated/_file_sm', $data);
    }

    /**
     * [shipment_tracking description]
     * @return [type] [description]
     */
    public function consolidation_shipment_tracking($consolidation_id){

        $consolidation = $this->logistic_model->get_consolidation($consolidation_id);

        if(!has_permission('lg_consolidated','','edit')){
            access_denied('shipment_tracking');
        }


        if($this->input->post()){
            $data_shipment = $this->input->post();
            $data_shipment['rel_type'] = 'consolidated';

            $tracking_id = $this->logistic_model->add_consolidation_shipment_tracking($data_shipment);

            if($tracking_id){
                set_alert('success', _l('added_successfully'));
            }

            redirect(admin_url('logistic/consolidated_detail/'.$consolidation_id));
        }

        $data['title'] = _l('lg_shipment_tracking');

        $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
        $data['consolidation'] = $consolidation;
        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();
        $data['office_groups'] = $this->logistic_model->get_offices();


        $this->load->view('consolidated/shipment_tracking', $data);
    }

    /**
     * [create_delivery_shipment description]
     * @param  [type] $shipping_id [description]
     * @return [type]             [description]
     */
    public function consolidation_create_delivery_shipment($consolidation_id){

        $consolidation = $this->logistic_model->get_consolidation($consolidation_id);
        $delivery_shipments = $this->logistic_model->get_consolidation_delivery_shipments($consolidation_id);

        if(!has_permission('lg_consolidated','','edit')){
            access_denied('delivery_shipment');
        }

        if($this->input->post()){
            $data_shipment = $this->input->post();
            
            $data_shipment['note'] = $this->input->post('note', false);

            $tracking_id = $this->logistic_model->consolidation_delivery_shipment($data_shipment);

            lg_handle_consolidation_delivery_shipment_attachment($consolidation_id);

            if($tracking_id){
                set_alert('success', _l('lg_the_shipment_has_been_delivered'));
            }

            redirect(admin_url('logistic/consolidated_detail/'.$consolidation_id));
        }

        $data['consolidation'] = $consolidation;
        $data['title'] = _l('lg_delivery_shipment_str');
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver" and active = 1');
        $data['shipment_sign'] = $this->logistic_model->get_consolidated_delivery_shipment($consolidation_id);

        $this->load->view('consolidated/delivery_shipment', $data);

    }


    /**
     * [upload_sign description]
     * @return [type] [description]
     */
    public function consolidation_upload_sign($consolidation_id){
        if($this->input->post()){


            $data = $this->input->post();
            $signature = $data['signature'];

            $consolidation = $this->logistic_model->get_consolidation($consolidation_id);

            $path = LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_delivery_shipment/sign/' .$consolidation_id;

            $success = lg_process_digital_signature_image_consolidated($consolidation_id, $signature, $path, 'signature_'.$consolidation_id);

            $data_sign = [];
            $data_sign['consolidation'] = $consolidation;
            $data_sign['shipment_sign'] = $this->logistic_model->get_consolidated_delivery_shipment($consolidation_id);


            $html = $this->load->view('consolidated/consolidation_shipment_sign', $data_sign, true);

            echo json_encode([
                'success' => $success,
                'html' => $html,
            ]);
        }

    }


    /**
     * [consolidation_remove_sign description]
     * @return [type] [description]
     */
    public function consolidation_remove_sign($consolidation_id){
        

            $consolidation = $this->logistic_model->get_consolidation($consolidation_id);

            $success = $this->logistic_model->remove_consolidation_shipment_sign($consolidation_id);

            $data_sign = [];
            $data_sign['consolidation'] = $consolidation;
            $data_sign['shipment_sign'] = $this->logistic_model->get_consolidated_delivery_shipment($consolidation_id);


            $html = $this->load->view('consolidated/consolidation_shipment_sign', $data_sign, true);

            echo json_encode([
                'success' => $success,
                'html' => $html,
            ]);

        
    }


    /**
     * [delete_package description]
     * @return [type] [description]
     */
    public function delete_consolidation($consolidated_id){

        if(!has_permission('lg_consolidated', '', 'delete')){
            access_denied('consolidated');
        }

        $success = $this->logistic_model->delete_consolidated($consolidated_id);
        if($success){
            set_alert('success', _l('deleted_successfully'));
        }

        redirect(admin_url('logistic/consolidated'));

    }

    /**
     * [reports description]
     * @return [type] [description]
     */
    public function reports(){
        if(!has_permission('lg_reports', '', 'view')){
            access_denied('reports');
        }

        $data['title'] = _l('lg_reports');
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $this->load->view('reports/manage_report', $data);
    }

    /**
     * Gets the where report period.
     *
     * @param      string  $field  The field
     *
     * @return     string  The where report period.
     */
    private function get_where_report_period($field = 'date')
    {
        $months_report      = $this->input->post('report_months');
        $custom_date_select = '';
        if ($months_report != '') {
            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $custom_date_select = 'AND (DATE(' . $field . ') BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = 'AND (DATE(' . $field . ') BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = 'AND (DATE(' . $field . ') BETWEEN "' .
                date('Y-m-d', strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = 'AND (DATE(' . $field . ') BETWEEN "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = 'AND DATE(' . $field . ') = "' . $from_date . '"';
                } else {
                    $custom_date_select = 'AND (DATE(' . $field . ') BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
        }

        return $custom_date_select;
    }

    /**
     * [general_package_log_report description]
     * @return [type] [description]
     */
    public function general_package_log_report(){
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');


            $where =[];
            $custom_date_select = $this->get_where_report_period(db_prefix() . 'lg_packages.created_at');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }
            $currency = $this->currencies_model->get_base_currency();

            if($this->input->post('report_currency')){
                $report_currency = $this->input->post('report_currency');
                $base_currency = get_base_currency();

                if($report_currency == $base_currency->id){
                    array_push($where, 'AND '.db_prefix().'lg_packages.currency IN (0, '.$report_currency.')');
                }else{
                    array_push($where, 'AND '.db_prefix().'lg_packages.currency = '.$report_currency);
                }

                $currency = lg_get_currency_by_id($report_currency);
            }

            $aColumns     = [
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
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'lg_packages';
            $join         = [
               
            ];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
               db_prefix().'lg_packages.id as package_id', 'shipping_prefix', 'number_code', 'tracking_purchase', 'CONCAT(shipping_prefix, \'\', number_code) as tracking_number', 'subtotal', 'discount','shipping_insurance', 'custom_duties', 'tax', 'declared_value',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'           => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] =  '<a href="'.admin_url('logistic/package_detail/'.$aRow['package_id']).'">'.$aRow['shipping_prefix'].$aRow['number_code'].'</a>';

                $row[] = _dt($aRow['created_at']);

                $row[] = '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">'.get_company_name($aRow['customer_id']).'</a>';

                $row[] =  lg_get_customer_address_str($aRow['customer_address']);

                $row[] = format_lg_package_status($aRow['delivery_status']);

                 $weight = 0;
                $volumetric_weight = 0;
                $total_dec = 0;
                $package = $this->logistic_model->get_package($aRow['package_id']);
                if(isset($package->package_detail)){
                    foreach($package->package_detail as $key => $detail){ 
                        
                        $weight += $detail['weight'];
                        

                        $total_dec += $detail['dec_value'];
                    }
                }

                $row[] = $weight;

                $row[] = app_format_money($aRow['subtotal'], $currency->name);

                $row[] = app_format_money($aRow['discount'], $currency->name);

                $row[] = app_format_money($aRow['shipping_insurance'], $currency->name);

                $row[] = app_format_money($aRow['custom_duties'], $currency->name);

                $row[] = app_format_money($aRow['tax'], $currency->name);

                $row[] = app_format_money($aRow['declared_value'], $currency->name);

                $row[] = app_format_money($aRow['package_total'], $currency->name);

                $footer_data['total'] += $aRow['package_total'];

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();


        }
    }

    /**
     * [general_package_log_report description]
     * @return [type] [description]
     */
    public function general_shipments_reports(){
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');


            $where =[];
            $custom_date_select = $this->get_where_report_period(db_prefix() . 'lg_shippings.created_at');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }
            $currency = $this->currencies_model->get_base_currency();

            if($this->input->post('report_currency')){
                $report_currency = $this->input->post('report_currency');
                $base_currency = get_base_currency();

                if($report_currency == $base_currency->id){
                    array_push($where, 'AND '.db_prefix().'lg_shippings.currency IN (0, '.$report_currency.')');
                }else{
                    array_push($where, 'AND '.db_prefix().'lg_shippings.currency = '.$report_currency);
                }

                $currency = lg_get_currency_by_id($report_currency);
            }

            $aColumns     = [
                'number_code',
                'created_at',
                'customer_id',
                'customer_address',
                'courrier_company',
                'store_supplier',
                'tracking_purchase',
                'delivery_status',
                'assign_driver',
                db_prefix().'lg_shippings.total as package_total',
                'invoice_id',
                db_prefix().'lg_shippings.currency as package_currency',
            ];
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'lg_shippings';
            $join         = [
               
            ];

            array_push($where, 'AND (shipping_type IS NULL or shipping_type = "shipping")');

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
               db_prefix().'lg_shippings.id as shipping_id', 'shipping_prefix', 'number_code', 'tracking_purchase', 'CONCAT(shipping_prefix, \'\', number_code) as tracking_number', 'subtotal', 'discount','shipping_insurance', 'custom_duties', 'tax', 'declared_value',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'           => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] =  '<a href="'.admin_url('logistic/shipping_detail/'.$aRow['shipping_id']).'">'.$aRow['shipping_prefix'].$aRow['number_code'].'</a>';

                $row[] = _dt($aRow['created_at']);

                $row[] = '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">'.get_company_name($aRow['customer_id']).'</a>';

                $row[] =  lg_get_customer_address_str($aRow['customer_address']);

                $row[] = format_lg_package_status($aRow['delivery_status']);

                 $weight = 0;
                $volumetric_weight = 0;
                $total_dec = 0;
                $package = $this->logistic_model->get_shipping($aRow['shipping_id']);
                if(isset($package->shipment_detail)){
                    foreach($package->shipment_detail as $key => $detail){ 
                        
                        $weight += $detail['weight'];
                        

                        $total_dec += $detail['dec_value'];
                    }
                }

                $row[] = $weight;

                $row[] = app_format_money($aRow['subtotal'], $currency->name);

                $row[] = app_format_money($aRow['discount'], $currency->name);

                $row[] = app_format_money($aRow['shipping_insurance'], $currency->name);

                $row[] = app_format_money($aRow['custom_duties'], $currency->name);

                $row[] = app_format_money($aRow['tax'], $currency->name);

                $row[] = app_format_money($aRow['declared_value'], $currency->name);

                $row[] = app_format_money($aRow['package_total'], $currency->name);

                $footer_data['total'] += $aRow['package_total'];

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();


        }
    }


    /**
     * [general_pickups_and_shipments_reports description]
     * @return [type] [description]
     */
    public function general_pickups_and_shipments_reports(){
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');


            $where =[];
            $custom_date_select = $this->get_where_report_period(db_prefix() . 'lg_shippings.created_at');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }
            $currency = $this->currencies_model->get_base_currency();

            if($this->input->post('report_currency')){
                $report_currency = $this->input->post('report_currency');
                $base_currency = get_base_currency();

                if($report_currency == $base_currency->id){
                    array_push($where, 'AND '.db_prefix().'lg_shippings.currency IN (0, '.$report_currency.')');
                }else{
                    array_push($where, 'AND '.db_prefix().'lg_shippings.currency = '.$report_currency);
                }

                $currency = lg_get_currency_by_id($report_currency);
            }

            $aColumns     = [
                'number_code',
                'created_at',
                'customer_id',
                'customer_address',
                'courrier_company',
                'store_supplier',
                'tracking_purchase',
                'delivery_status',
                'assign_driver',
                db_prefix().'lg_shippings.total as package_total',
                'invoice_id',
                db_prefix().'lg_shippings.currency as package_currency',
            ];
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'lg_shippings';
            $join         = [
               
            ];

            array_push($where, 'AND shipping_type = "pickup"');

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
               db_prefix().'lg_shippings.id as shipping_id', 'shipping_prefix', 'number_code', 'tracking_purchase', 'CONCAT(shipping_prefix, \'\', number_code) as tracking_number', 'subtotal', 'discount','shipping_insurance', 'custom_duties', 'tax', 'declared_value',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'           => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] =  '<a href="'.admin_url('logistic/shipping_detail/'.$aRow['shipping_id']).'">'.$aRow['shipping_prefix'].$aRow['number_code'].'</a>';

                $row[] = _dt($aRow['created_at']);

                $row[] = '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">'.get_company_name($aRow['customer_id']).'</a>';

                $row[] =  lg_get_customer_address_str($aRow['customer_address']);

                $row[] = format_lg_package_status($aRow['delivery_status']);

                 $weight = 0;
                $volumetric_weight = 0;
                $total_dec = 0;
                $package = $this->logistic_model->get_shipping($aRow['shipping_id']);
                if(isset($package->shipment_detail)){
                    foreach($package->shipment_detail as $key => $detail){ 
                        
                        $weight += $detail['weight'];
                        

                        $total_dec += $detail['dec_value'];
                    }
                }

                $row[] = $weight;

                $row[] = app_format_money($aRow['subtotal'], $currency->name);

                $row[] = app_format_money($aRow['discount'], $currency->name);

                $row[] = app_format_money($aRow['shipping_insurance'], $currency->name);

                $row[] = app_format_money($aRow['custom_duties'], $currency->name);

                $row[] = app_format_money($aRow['tax'], $currency->name);

                $row[] = app_format_money($aRow['declared_value'], $currency->name);

                $row[] = app_format_money($aRow['package_total'], $currency->name);

                $footer_data['total'] += $aRow['package_total'];

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();


        }
    }

     /**
     * [general_consolidated_shipment_reports description]
     * @return [type] [description]
     */
    public function general_consolidated_shipment_reports(){
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');


            $where =[];
            $custom_date_select = $this->get_where_report_period(db_prefix() . 'lg_consolidated.created_at');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }
            $currency = $this->currencies_model->get_base_currency();

            if($this->input->post('report_currency')){
                $report_currency = $this->input->post('report_currency');
                $base_currency = get_base_currency();

                if($report_currency == $base_currency->id){
                    array_push($where, 'AND '.db_prefix().'lg_consolidated.currency IN (0, '.$report_currency.')');
                }else{
                    array_push($where, 'AND '.db_prefix().'lg_consolidated.currency = '.$report_currency);
                }

                $currency = lg_get_currency_by_id($report_currency);
            }

            $aColumns     = [
                'number_code',
                'created_at',
                'customer_id',
                'customer_address',
                'courrier_company',
                'store_supplier',
                'tracking_purchase',
                'delivery_status',
                'assign_driver',
                db_prefix().'lg_consolidated.total as package_total',
                'invoice_id',
                db_prefix().'lg_consolidated.currency as package_currency',
            ];
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'lg_consolidated';
            $join         = [
               
            ];

            array_push($where, 'AND rel_type = "shipping"');

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
               db_prefix().'lg_consolidated.id as shipping_id', 'shipping_prefix', 'number_code', 'tracking_purchase', 'CONCAT(shipping_prefix, \'\', number_code) as tracking_number', 'subtotal', 'discount','shipping_insurance', 'custom_duties', 'tax', 'declared_value',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'           => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] =  '<a href="'.admin_url('logistic/consolidated_detail/'.$aRow['shipping_id']).'">'.$aRow['shipping_prefix'].$aRow['number_code'].'</a>';

                $row[] = _dt($aRow['created_at']);

                $row[] = '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">'.get_company_name($aRow['customer_id']).'</a>';

                $row[] =  lg_get_customer_address_str($aRow['customer_address']);

                $row[] = format_lg_package_status($aRow['delivery_status']);

                 $weight = 0;
                $volumetric_weight = 0;
                $total_dec = 0;
                $package = $this->logistic_model->get_consolidation($aRow['shipping_id']);
                if(isset($package->shipment_detail)){
                    foreach($package->shipment_detail as $key => $detail){ 
                        
                        $weight += $detail['weight'];
                        

                        $total_dec += $detail['dec_value'];
                    }
                }

                $row[] = $weight;

                $row[] = app_format_money($aRow['subtotal'], $currency->name);

                $row[] = app_format_money($aRow['discount'], $currency->name);

                $row[] = app_format_money($aRow['shipping_insurance'], $currency->name);

                $row[] = app_format_money($aRow['custom_duties'], $currency->name);

                $row[] = app_format_money($aRow['tax'], $currency->name);

                $row[] = app_format_money($aRow['declared_value'], $currency->name);

                $row[] = app_format_money($aRow['package_total'], $currency->name);

                $footer_data['total'] += $aRow['package_total'];

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();


        }
    }

    /**
     * [general_consolidated_package_reports description]
     * @return [type] [description]
     */
    public function general_consolidated_package_reports(){
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');


            $where =[];
            $custom_date_select = $this->get_where_report_period(db_prefix() . 'lg_consolidated.created_at');
            if ($custom_date_select != '') {
                array_push($where, $custom_date_select);
            }
            $currency = $this->currencies_model->get_base_currency();

            if($this->input->post('report_currency')){
                $report_currency = $this->input->post('report_currency');
                $base_currency = get_base_currency();

                if($report_currency == $base_currency->id){
                    array_push($where, 'AND '.db_prefix().'lg_consolidated.currency IN (0, '.$report_currency.')');
                }else{
                    array_push($where, 'AND '.db_prefix().'lg_consolidated.currency = '.$report_currency);
                }

                $currency = lg_get_currency_by_id($report_currency);
            }

            $aColumns     = [
                'number_code',
                'created_at',
                'customer_id',
                'customer_address',
                'courrier_company',
                'store_supplier',
                'tracking_purchase',
                'delivery_status',
                'assign_driver',
                db_prefix().'lg_consolidated.total as package_total',
                'invoice_id',
                db_prefix().'lg_consolidated.currency as package_currency',
            ];
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'lg_consolidated';
            $join         = [
               
            ];

            array_push($where, 'AND rel_type = "locker_packages"');

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
               db_prefix().'lg_consolidated.id as shipping_id', 'shipping_prefix', 'number_code', 'tracking_purchase', 'CONCAT(shipping_prefix, \'\', number_code) as tracking_number', 'subtotal', 'discount','shipping_insurance', 'custom_duties', 'tax', 'declared_value',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $footer_data = [
                'total'           => 0,
            ];

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] =  '<a href="'.admin_url('logistic/consolidated_detail/'.$aRow['shipping_id']).'">'.$aRow['shipping_prefix'].$aRow['number_code'].'</a>';

                $row[] = _dt($aRow['created_at']);

                $row[] = '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">'.get_company_name($aRow['customer_id']).'</a>';

                $row[] =  lg_get_customer_address_str($aRow['customer_address']);

                $row[] = format_lg_package_status($aRow['delivery_status']);

                 $weight = 0;
                $volumetric_weight = 0;
                $total_dec = 0;
                $package = $this->logistic_model->get_consolidation($aRow['shipping_id']);
                if(isset($package->shipment_detail)){
                    foreach($package->shipment_detail as $key => $detail){ 
                        
                        $weight += $detail['weight'];
                        

                        $total_dec += $detail['dec_value'];
                    }
                }

                $row[] = $weight;

                $row[] = app_format_money($aRow['subtotal'], $currency->name);

                $row[] = app_format_money($aRow['discount'], $currency->name);

                $row[] = app_format_money($aRow['shipping_insurance'], $currency->name);

                $row[] = app_format_money($aRow['custom_duties'], $currency->name);

                $row[] = app_format_money($aRow['tax'], $currency->name);

                $row[] = app_format_money($aRow['declared_value'], $currency->name);

                $row[] = app_format_money($aRow['package_total'], $currency->name);

                $footer_data['total'] += $aRow['package_total'];

                $output['aaData'][] = $row;
            }

            foreach ($footer_data as $key => $total) {
                $footer_data[$key] = app_format_money($total, $currency->name);
            }

            $output['sums'] = $footer_data;
            echo json_encode($output);
            die();


        }
    }

    /**
     * [dashboard description]
     * @return [type] [description]
     */
    public function dashboard(){

        $data['title'] = _l('lg_dashboard');
        $data['package_by_status'] = json_encode($this->logistic_model->count_package_by_status());

        $data['package_sales_graph'] = json_encode($this->logistic_model->package_sales_graph_data());

        $data['shipping_by_status'] = json_encode($this->logistic_model->count_shipping_by_status());

        $data['shipping_sales_graph'] = json_encode($this->logistic_model->shipping_sales_graph_data());


        $data['consolidated_by_status'] = json_encode($this->logistic_model->count_consolidated_by_status());

        $data['consolidated_sales_graph'] = json_encode($this->logistic_model->consolidated_sales_graph_data());

        $this->load->view('dashboard/manage', $data);

    }
}