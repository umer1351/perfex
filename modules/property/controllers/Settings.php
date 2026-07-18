<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends AdminController {

    private $_module = 'settings';
    private $_title = '';

    public function __construct(){
        parent::__construct();

    }

    public function index(){
        if (!is_admin() && !has_permission('prospector_settings', '', 'edit')) {
            access_denied('Settings');
        }
        $data['title'] = _l('settings');

        if ($this->input->post()) {

            $data = $this->input->post();

            if (isset($data['lead_resource']) && !empty($data['lead_resource'])){
                update_option('lead_resource',$data['lead_resource']);
            }
            if (isset($data['lead_status']) && !empty($data['lead_status'])){
                update_option('lead_status',$data['lead_status']);
            }
            set_alert('success', _l('settings_update'));
            redirect(admin_url('prospector/settings/'));

        }

        $this->load->view('settings/manage', $data);

    }




}