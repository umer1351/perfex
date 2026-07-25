<?php

defined('BASEPATH') or exit('No direct script access allowed');

class IntegrationController extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('deals/IntegrationManager', null, 'integrationmanager');
    }

    public function oauth($providerKey = null)
    {
        $provider = $this->integrationmanager->getProvider((string) $providerKey);
        if (!$provider) {
            show_404();
        }

        $state = (string) $this->input->get('state', true);
        $code = (string) $this->input->get('code', true);
        $error = (string) $this->input->get('error', true);
        $errorDescription = (string) $this->input->get('error_description', true);

        $result = $this->integrationmanager->handleAuthorizationCallback(
            $provider->getKey(),
            $state,
            $code,
            $error,
            $errorDescription
        );

        set_alert(!empty($result['success']) ? 'success' : 'warning', $result['message'] ?? 'OAuth callback could not be processed.');
        $redirect = admin_url('deals/integrations');
        if (!empty($result['account_id'])) {
            $redirect = admin_url('deals/integrations?account_id=' . (int) $result['account_id']);
        }

        redirect($redirect);
    }
}
