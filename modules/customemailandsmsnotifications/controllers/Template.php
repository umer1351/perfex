<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Template extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customemailandsmsnotifications_model','custom_model');
        if (!has_permission('customemailandsmsnotifications', '', 'create')) {
             access_denied(_l('sms_title'));
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE, 'tables/custom_templates'));
        }
        
        // Get template categories
        $data['categories'] = $this->custom_model->get_template_categories();
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/add_edit_templates', $data);
    }

    public function save($id=''){
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $data['staff_id']=$this->session->userdata('staff_user_id');
            
            if ('' == $data['id']) {
                $data['created_at'] = date('Y-m-d H:i:s');
                $id      = $this->custom_model->add($data);
                $message = $id ? _l('added_successfully', _l('template')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id'      => $id,
                    'name'    => $data['template_name'],
                ]);
            } else {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $success = $this->custom_model->update($data['id'], $data);
                $message = '';
                if (true == $success) {
                    $message = _l('updated_successfully', _l('template'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function delete($id=''){

        if (!$id) {
            redirect(admin_url('customemailandsmsnotifications/template'));
        }
        $response = $this->custom_model->delete($id);
        if (true == $response) {
            set_alert('success', _l('deleted', _l('template')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('template')));
        }
        redirect(admin_url('customemailandsmsnotifications/template'));
    }

    public function get_item_by_id($id)
    {
        if ($this->input->is_ajax_request()) {
            $item                     = $this->custom_model->get($id);
            $item->template_content   = nl2br($item->template_content);

            echo json_encode($item);
        }
    }

    public function get_template_data(){
    	if ($this->input->is_ajax_request()) {
	        $post = $this->input->post();
	        $where = ['id'=>$post['template_id']];
	        $template_content = $this->custom_model->get('id',$where);
	        echo json_encode($template_content);
    	}
    }
    
    // Template Categories Management
    public function categories()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE, 'tables/template_categories'));
        }
        
        $data['categories'] = $this->custom_model->get_template_categories();
        $this->load->view(CUSTOMEMAILANDSMSNOTIFICATIONS_MODULE.'/template_categories', $data);
    }
    
    public function save_category()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            
            if (empty($data['id'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
                unset($data['id']);
                
                $id = $this->custom_model->add_template_category($data);
                $message = $id ? _l('added_successfully', _l('category')) : '';
                
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id' => $id
                ]);
            } else {
                $category_id = $data['id'];
                unset($data['id']);
                
                $success = $this->custom_model->update_template_category($category_id, $data);
                $message = $success ? _l('updated_successfully', _l('category')) : '';
                
                echo json_encode([
                    'success' => $success,
                    'message' => $message
                ]);
            }
        }
    }
    
    public function delete_category($id)
    {
        if (!$id) {
            redirect(admin_url('customemailandsmsnotifications/template/categories'));
        }
        
        $response = $this->custom_model->delete_template_category($id);
        
        if ($response) {
            set_alert('success', _l('deleted', _l('category')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('category')));
        }
        
        redirect(admin_url('customemailandsmsnotifications/template/categories'));
    }
    
    public function get_category($id)
    {
        if ($this->input->is_ajax_request()) {
            $category = $this->custom_model->get_template_category($id);
            echo json_encode($category);
        }
    }
    
    public function get_templates_by_category($category_id)
    {
        if ($this->input->is_ajax_request()) {
            $templates = $this->custom_model->get_templates_by_category($category_id);
            echo json_encode($templates);
        }
    }
}
?>
