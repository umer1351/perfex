<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Saas_tenants extends ClientsController
{
    public function __construct()
    {
        parent::__construct();

        $this->app_modules->is_inactive('saas') ? access_denied() : '';

        if (!is_client_logged_in()) {
            redirect(site_url('authentication'));
        }
    }

    public function index()
    {
        $this->view('saas_tenants');
        $this->layout();
    }
}
