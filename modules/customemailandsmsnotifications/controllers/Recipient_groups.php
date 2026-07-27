<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recipient_groups extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customemailandsmsnotifications/customemailandsmsnotifications_model', 'groups_model');
        
        if (!has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE, 'tables/recipient_groups'));
        }
        
        $data['groups'] = $this->groups_model->get_recipient_groups();
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/recipient_groups', $data);
    }
    
    public function manage($id = '')
    {
        // Get all customers and leads for the form
        $data['clients'] = $this->db->select('userid as id, company')
                                    ->from('tblclients')
                                    ->get()
                                    ->result_array();
        
        $data['leads'] = $this->db->select('id, name')
                                  ->from('tblleads')
                                  ->get()
                                  ->result_array();
        
        if ($id) {
            $data['group'] = $this->groups_model->get_recipient_group($id);
            
            if (!$data['group']) {
                set_alert('warning', _l('group_not_found'));
                redirect(admin_url('customemailandsmsnotifications/recipient_groups'));
            }
        }
        
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/manage_recipient_group', $data);
    }
    
    public function save()
    {
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url('customemailandsmsnotifications/recipient_groups'));
        }
        
        $data = $this->input->post();
        $data['staff_id'] = get_staff_user_id();
        
        // Prepare customer and lead IDs
        if (isset($data['customer_ids']) && is_array($data['customer_ids'])) {
            $data['customer_ids'] = json_encode($data['customer_ids']);
        } else {
            $data['customer_ids'] = json_encode([]);
        }
        
        if (isset($data['lead_ids']) && is_array($data['lead_ids'])) {
            $data['lead_ids'] = json_encode($data['lead_ids']);
        } else {
            $data['lead_ids'] = json_encode([]);
        }
        
        // Calculate recipient count
        $customer_count = count(json_decode($data['customer_ids'], true));
        $lead_count = count(json_decode($data['lead_ids'], true));
        $data['recipient_count'] = $customer_count + $lead_count;
        
        if (empty($data['id'])) {
            // Create new group
            $data['created_at'] = date('Y-m-d H:i:s');
            unset($data['id']);
            
            $id = $this->groups_model->add_recipient_group($data);
            $message = $id ? _l('added_successfully', _l('recipient_group')) : '';
            
            echo json_encode([
                'success' => $id ? true : false,
                'message' => $message,
                'id' => $id
            ]);
        } else {
            // Update existing group
            $data['updated_at'] = date('Y-m-d H:i:s');
            $group_id = $data['id'];
            unset($data['id']);
            
            $success = $this->groups_model->update_recipient_group($group_id, $data);
            $message = $success ? _l('updated_successfully', _l('recipient_group')) : '';
            
            echo json_encode([
                'success' => $success,
                'message' => $message
            ]);
        }
    }
    
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('customemailandsmsnotifications/recipient_groups'));
        }
        
        $response = $this->groups_model->delete_recipient_group($id);
        
        if ($response) {
            set_alert('success', _l('deleted', _l('recipient_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('recipient_group')));
        }
        
        redirect(admin_url('customemailandsmsnotifications/recipient_groups'));
    }
    
    public function get_group_recipients($id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url('customemailandsmsnotifications/recipient_groups'));
        }
        
        $group = $this->groups_model->get_recipient_group($id);
        
        if (!$group) {
            echo json_encode(['success' => false, 'message' => _l('group_not_found')]);
            return;
        }
        
        $recipients = $this->groups_model->get_group_recipient_details($group);
        
        echo json_encode([
            'success' => true,
            'recipients' => $recipients,
            'count' => count($recipients)
        ]);
    }
    
    public function load_to_send_form($id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url('customemailandsmsnotifications/recipient_groups'));
        }
        
        $group = $this->groups_model->get_recipient_group($id);
        
        if (!$group) {
            echo json_encode(['success' => false, 'message' => _l('group_not_found')]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'group' => $group,
            'customer_ids' => json_decode($group->customer_ids, true),
            'lead_ids' => json_decode($group->lead_ids, true),
            'recipient_type' => $group->recipient_type
        ]);
    }
}
