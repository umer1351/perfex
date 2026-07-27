<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customemailandsmsnotifications/Customemailandsmsnotifications_model', 'custom_model');
        
        if (!has_permission('customemailandsmsnotifications', '', 'view')) {
            access_denied(_l('sms_title'));
        }
    }

    public function index()
    {
        $data['whatsapp_active'] = (int) get_option('sms_whatsapp_active');
        $data['whatsapp_badges'] = (int) get_option('sms_whatsapp_delivery_badges');
        $data['whatsapp_account_sid'] = get_option('sms_whatsapp_account_sid');
        $data['whatsapp_auth_token'] = get_option('sms_whatsapp_auth_token');
        $data['whatsapp_phone_number'] = get_option('sms_whatsapp_phone_number');
        $batch_size = (int) get_option('customemailandsmsnotifications_cron_batch_size');
        $data['cron_batch_size'] = $batch_size > 0 ? $batch_size : 75;

        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/settings', $data);
    }

    public function save()
    {
        if (!has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }
        
        $active = $this->input->post('sms_whatsapp_active') ? 1 : 0;
        $badges = $this->input->post('sms_whatsapp_delivery_badges') ? 1 : 0;
        update_option('sms_whatsapp_active', $active);
        update_option('sms_whatsapp_delivery_badges', $badges);
        update_option('sms_whatsapp_account_sid', trim($this->input->post('sms_whatsapp_account_sid')));
        update_option('sms_whatsapp_auth_token', trim($this->input->post('sms_whatsapp_auth_token')));
        update_option('sms_whatsapp_phone_number', trim($this->input->post('sms_whatsapp_phone_number')));

        $batch_size = (int) $this->input->post('customemailandsmsnotifications_cron_batch_size');
        if ($batch_size < 1) {
            $batch_size = 75;
        } elseif ($batch_size > 500) {
            $batch_size = 500; // hard ceiling so one cron run cannot stall on a huge batch
        }
        update_option('customemailandsmsnotifications_cron_batch_size', $batch_size);

        set_alert('success', _l('settings_updated_successfully'));
        redirect(admin_url('customemailandsmsnotifications/settings'));
    }
    
    public function test_whatsapp()
    {
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url('customemailandsmsnotifications/settings'));
        }
        
        if (!has_permission('customemailandsmsnotifications', '', 'create')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }
        
        if (get_option('sms_whatsapp_active') != 1) {
            echo json_encode(['success' => false, 'message' => _l('whatsapp_not_enabled')]);
            return;
        }
        
        $to_number = trim($this->input->post('test_to'));
        $message = trim($this->input->post('test_message'));
        
        if ($to_number === '' || $message === '') {
            echo json_encode(['success' => false, 'message' => _l('whatsapp_test_missing_fields')]);
            return;
        }
        
        $to = [];
        $to[] = (object) [
            'phonenumber' => $to_number,
            'firstname' => 'Test',
            'lastname' => 'Recipient',
        ];
        
        $request = [
            'message' => $message,
            'template' => null,
        ];
        
        $result = $this->custom_model->sendWhatsApp($request, $to);
        
        if ($result === false) {
            echo json_encode(['success' => false, 'message' => _l('whatsapp_test_failed')]);
            return;
        }
        
        echo json_encode(['success' => true, 'message' => _l('whatsapp_test_sent')]);
    }
}
