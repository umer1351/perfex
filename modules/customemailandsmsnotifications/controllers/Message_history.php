<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Message_history extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customemailandsmsnotifications/customemailandsmsnotifications_model', 'history_model');
        
        if (!has_permission('customemailandsmsnotifications', '', 'view')) {
            access_denied(_l('sms_title'));
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE, 'tables/message_history'));
        }
        
        // Get statistics
        $data['total_sent'] = $this->history_model->get_message_count('sent');
        $data['total_failed'] = $this->history_model->get_message_count('failed');
        $data['total_scheduled'] = $this->history_model->get_message_count('scheduled');
        $data['total_pending'] = $this->history_model->get_message_count('pending');
        
        // Get data for charts
        $data['messages_by_type'] = $this->history_model->get_messages_by_type();
        $data['messages_by_date'] = $this->history_model->get_messages_by_date(30); // Last 30 days
        $data['delivery_rate'] = $this->history_model->get_delivery_rate();
        
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/message_history', $data);
    }
    
    public function view($id)
    {
        if (!$id) {
            redirect(admin_url('customemailandsmsnotifications/message_history'));
        }
        
        $data['message'] = $this->history_model->get_message_history($id);
        
        if (!$data['message']) {
            set_alert('warning', _l('message_not_found'));
            redirect(admin_url('customemailandsmsnotifications/message_history'));
        }
        
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/message_detail', $data);
    }
    
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('customemailandsmsnotifications/message_history'));
        }
        
        $response = $this->history_model->delete_message_history($id);
        
        if ($response) {
            set_alert('success', _l('deleted', _l('message')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('message')));
        }
        
        redirect(admin_url('customemailandsmsnotifications/message_history'));
    }
    
    public function export()
    {
        if (!has_permission('customemailandsmsnotifications', '', 'view')) {
            access_denied(_l('sms_title'));
        }
        
        $filters = [
            'type' => $this->input->get('type'),
            'status' => $this->input->get('status'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'staff_id' => $this->input->get('staff_id')
        ];
        
        $messages = $this->history_model->get_messages_for_export($filters);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=message_history_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, [
            'ID',
            'Date/Time',
            'Type',
            'Status',
            'Recipient Name',
            'Recipient Contact',
            'Subject',
            'Message',
            'Staff Member',
            'Gateway',
            'Sent At'
        ]);
        
        // Data rows
        foreach ($messages as $message) {
            $message_export = $message['message_content'];
            // Minify HTML to a single line for CSV export
            $message_export = preg_replace('/<!--.*?-->/s', '', $message_export);
            $message_export = preg_replace('/\s+/', ' ', $message_export);
            $message_export = trim($message_export);

            fputcsv($output, [
                $message['id'],
                $message['created_at'],
                $message['message_type'],
                $message['status'],
                $message['recipient_name'],
                $message['recipient_contact'],
                $message['subject'],
                $message_export,
                get_staff_full_name($message['staff_id']),
                $message['gateway'],
                $message['sent_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function get_statistics()
    {
        if ($this->input->is_ajax_request()) {
            $days = $this->input->post('days') ?: 30;
            
            $stats = [
                'total_sent' => $this->history_model->get_message_count('sent', $days),
                'total_failed' => $this->history_model->get_message_count('failed', $days),
                'total_scheduled' => $this->history_model->get_message_count('scheduled', $days),
                'messages_by_type' => $this->history_model->get_messages_by_type($days),
                'messages_by_date' => $this->history_model->get_messages_by_date($days),
                'delivery_rate' => $this->history_model->get_delivery_rate($days)
            ];
            
            echo json_encode($stats);
        }
    }
    
    public function resend($id)
    {
        if (!$id || !has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }
        
        $message = $this->history_model->get_message_history($id);
        
        if (!$message) {
            set_alert('warning', _l('message_not_found'));
            redirect(admin_url('customemailandsmsnotifications/message_history'));
        }
        
        // Resend the message
        $result = $this->history_model->resend_message($message);
        
        if ($result) {
            set_alert('success', _l('message_resent_successfully'));
        } else {
            set_alert('warning', _l('message_resend_failed'));
        }
        
        redirect(admin_url('customemailandsmsnotifications/message_history'));
    }
    
    public function clear_logs()
    {
        if (!has_permission('customemailandsmsnotifications', '', 'delete')) {
            access_denied(_l('sms_title'));
        }
        
        // Clear all message history logs
        $this->db->truncate(db_prefix().'custom_message_history');
        
        set_alert('success', _l('logs_cleared_successfully'));
        redirect(admin_url('customemailandsmsnotifications/message_history'));
    }
}
