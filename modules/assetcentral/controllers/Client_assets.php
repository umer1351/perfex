<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client_assets extends ClientsController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('assetcentral_model');
    }

    public function index()
    {
        show_404();
    }

    public function assets()
    {
        $accessMenu = false;

        if (get_option('assetcentral_show_assets_on_clients_menu') == '1' && has_contact_permission('assets') && is_client_logged_in()) {
            $accessMenu = true;
        }

        if (!$accessMenu) {
            redirect(site_url('knowledge-base'));
        }

        $data = [];

        $data['title'] = _l('assetcentral');
        $data['assets'] = $this->assetcentral_model->get_assigned_assets_for_client(get_client_user_id());

        $this->data($data);
        $this->view('client/manage');
        $this->layout();
    }

    public function view_project($project_id)
    {

        $accessMenu = false;

        if (get_option('projectspot_show_menu_client_side') == '1') {
            $accessMenu = true;
        }
        if (get_option('projectspot_should_client_be_logged_in') == '0' && !is_client_logged_in()) {
            $accessMenu = false;
        }

        if (!$accessMenu) {
            redirect(site_url('knowledge-base'));
        }

        $data = [];

        $data['project_data'] = $this->projectspot_model->getSingleProjectGalleryListWithImages($project_id);

        $data['title'] = _l('projectspot_showcase_gallery') . ' - '. $data['project_data']->project_name;

        $this->data($data);
        $this->view('client/showcase_single');
        $this->layout();
    }
}