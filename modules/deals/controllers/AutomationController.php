<?php

defined('BASEPATH') or exit('No direct script access allowed');

class AutomationController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('deals/IntegrationManager', null, 'integrationmanager');
    }

    public function process()
    {
        if (!is_admin() && !staff_can('integrate', 'deals')) {
            access_denied('Integration Automation');
        }

        $results = $this->integrationmanager->processQueue(100);
        set_alert('success', 'Integration automation processed. Completed: ' . (int) ($results['processed'] ?? 0) . ', failed: ' . (int) ($results['failed'] ?? 0) . '.');
        redirect(admin_url('deals/integrations'));
    }
}
