<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Property extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('property_model');

        if (!has_permission('property', '', 'view')) {
            access_denied('property');
        }
    }

    public function index()
    {
        $data['properties'] = $this->property_model->get();
        $data['title'] = _l('property_list');
        $this->load->view('property/list', $data);
    }

    public function add()
    {
        if (!has_permission('property', '', 'create')) {
            access_denied('property');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $this->property_model->add($data);
            set_alert('success', _l('property_added_success'));
            redirect(admin_url('property'));
        }

        $data['title'] = _l('add_property');
        $this->load->view('property/add', $data);
    }

    public function edit($id)
    {
        if (!has_permission('property', '', 'edit')) {
            access_denied('property');
        }

        $property = $this->property_model->get($id);
        if (!$property) {
            show_404();
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $this->property_model->update($id, $data);
            set_alert('success', _l('property_updated_success'));
            redirect(admin_url('property'));
        }

        $data['property'] = $property;
        $data['title'] = _l('edit_property');
        $this->load->view('property/edit', $data);
    }

    public function delete($id)
    {
        if (!has_permission('property', '', 'delete')) {
            access_denied('property');
        }

        $this->property_model->delete($id);
        set_alert('success', _l('property_deleted_success'));
        redirect(admin_url('property'));
    }
}
