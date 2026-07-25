<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Scheduled_messages extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customemailandsmsnotifications/customemailandsmsnotifications_model', 'scheduled_model');
        
        if (!has_permission('customemailandsmsnotifications', '', 'view')) {
            access_denied(_l('sms_title'));
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE, 'tables/scheduled_messages'));
        }
        
        $data['scheduled_count'] = $this->scheduled_model->get_scheduled_message_count();
        $data['upcoming_24h'] = $this->scheduled_model->get_upcoming_messages(24);
        
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/scheduled_messages', $data);
    }
    
    public function view($id)
    {
        if (!$id) {
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        $data['message'] = $this->scheduled_model->get_scheduled_message($id);
        
        if (!$data['message']) {
            set_alert('warning', _l('message_not_found'));
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/scheduled_message_detail', $data);
    }
    
    public function edit($id)
    {
        if (!$id || !has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }
        
        $data['message'] = $this->scheduled_model->get_scheduled_message($id);
        
        if (!$data['message']) {
            set_alert('warning', _l('message_not_found'));
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        if ($data['message']->is_delivered == 1) {
            set_alert('warning', _l('message_already_sent'));
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        // Get clients and leads for the form
        $data['clients'] = $this->db->select('*')->from('tblclients')->get()->result();
        $data['leads'] = $this->db->select('*')->from('tblleads')->get()->result();
        
        $where = ['staff_id' => $this->session->userdata('staff_user_id')];
        $data['templates'] = $this->scheduled_model->get('staff_id', $where);
        
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/edit_scheduled_message', $data);
    }
    
    public function update()
    {
        if (!$this->input->is_ajax_request() || !has_permission('customemailandsmsnotifications', '', 'create')) {
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        $data = $this->input->post();
        $id = $data['id'];
        unset($data['id']);
        
        // Check if message is already delivered
        $message = $this->scheduled_model->get_scheduled_message($id);
        if ($message && $message->is_delivered == 1) {
            echo json_encode([
                'success' => false,
                'message' => _l('message_already_sent')
            ]);
            return;
        }
        
        // Prepare data
        if (isset($data['customer_or_leads']) && $data['customer_or_leads'] == 'leads') {
            if (isset($data['select_lead']) && is_array($data['select_lead'])) {
                $data['select_customer'] = json_encode($data['select_lead']);
            } else {
                $data['select_customer'] = json_encode([]);
            }
            unset($data['select_lead']);
        } else {
            if (isset($data['select_customer']) && is_array($data['select_customer'])) {
                $data['select_customer'] = json_encode($data['select_customer']);
            } else {
                $data['select_customer'] = json_encode([]);
            }
        }
        
        $success = $this->scheduled_model->update_scheduled_message($id, $data);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? _l('updated_successfully', _l('scheduled_message')) : _l('update_failed')
        ]);
    }
    
    public function cancel($id)
    {
        if (!$id || !has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }
        
        $message = $this->scheduled_model->get_scheduled_message($id);
        
        if (!$message) {
            set_alert('warning', _l('message_not_found'));
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        if ($message->is_delivered == 1) {
            set_alert('warning', _l('message_already_sent'));
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        // Delete the scheduled message
        $response = $this->scheduled_model->delete_scheduled_message($id);
        
        if ($response) {
            set_alert('success', _l('scheduled_message_cancelled'));
        } else {
            set_alert('warning', _l('problem_cancelling_message'));
        }
        
        redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
    }
    
    public function duplicate($id)
    {
        if (!$id || !has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }
        
        $message = $this->scheduled_model->get_scheduled_message($id);
        
        if (!$message) {
            set_alert('warning', _l('message_not_found'));
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        // Create a duplicate
        $duplicate_data = [
            'customer_or_leads' => $message->customer_or_leads,
            'select_customer' => $message->select_customer,
            'template' => $message->template,
            'subject' => $message->subject,
            'message' => $message->message,
            'mail_or_sms' => $message->mail_or_sms,
            'custom_date' => date('Y-m-d'),
            'custom_time' => '',
            'is_delivered' => 0
        ];
        
        if ($this->db->insert('tblcustom_email_sms', $duplicate_data)) {
            set_alert('success', _l('scheduled_message_duplicated'));
        } else {
            set_alert('warning', _l('problem_duplicating_message'));
        }
        
        redirect(admin_url('customemailandsmsnotifications/scheduled_messages/edit/' . $this->db->insert_id()));
    }
    
    public function send_now($id)
    {
        if (!$id || !has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }
        
        $message = $this->scheduled_model->get_scheduled_message($id);
        
        if (!$message) {
            set_alert('warning', _l('message_not_found'));
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        if ($message->is_delivered == 1) {
            set_alert('warning', _l('message_already_sent'));
            redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
        }
        
        // Send the message immediately
        $this->load->model('customemailandsmsnotifications/customemailandsmsnotifications_model');
        
        $arrayData = json_decode(json_encode($message), true);
        $arrayData['select_customer'] = json_decode($arrayData['select_customer'], true);
        if ($arrayData['customer_or_leads'] == 'leads' && !isset($arrayData['select_lead'])) {
            $arrayData['select_lead'] = $arrayData['select_customer'];
        }
        
        if ($arrayData['mail_or_sms'] == 'mail') {
            if (!empty($arrayData['file_mail'])) {
                $file_data = json_decode($arrayData['file_mail'], true);
                if ($file_data) {
                    $_FILES['file_mail']['tmp_name'] = $file_data['tmp_name'];
                    $_FILES['file_mail']['name'] = $file_data['name'];
                }
            }
            $this->customemailandsmsnotifications_model->sendMail($arrayData);
        } else {
            $this->customemailandsmsnotifications_model->sendSMS($arrayData);
        }

        // Mark as delivered after sending
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'custom_email_sms', [
            'is_delivered' => 1,
            'delivered_at' => date('Y-m-d H:i:s'),
        ]);
        
        set_alert('success', _l('message_sent_successfully'));
        redirect(admin_url('customemailandsmsnotifications/scheduled_messages'));
    }
    
    public function calendar_view()
    {
        $data['scheduled_messages'] = $this->scheduled_model->get_all_scheduled_messages_for_calendar();
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/scheduled_calendar', $data);
    }
}
