<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Call_logs extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(AI_LEAD_MANAGER_MODULE_NAME . '/Call_logs_model', 'call_logs');
    }

    /**
     * Display the call logs view.
     *
     * @return void
     */
    public function index()
    {
        if (!has_permission('alm_call_logs', '', 'view')) {
            access_denied('alm_call_logs');
        }

        $data['title']                 = _l('alm_call_logs');
        $this->load->view('call_logs', $data);
    }

    /**
     * List of call logs. This function is used to populate the table of call logs.
     *
     * @return void
     */
    public function table()
    {
        if (!has_permission('alm_call_logs', '', 'view')) {
            access_denied('alm_call_logs');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(AI_LEAD_MANAGER_MODULE_NAME, 'tables/call_logs'));
        }
    }

    /**
     * Call logs relations. This function is used to populate the table of call logs of a lead/customer.
     *
     * @param int $rel_id   The ID of the lead/customer.
     * @param string $rel_type The type of the relation. Is it a lead or a customer.
     *
     * @return void
     */
    public function call_log_relations($rel_id, $rel_type)
    {
        $this->app->get_table_data(module_views_path(AI_LEAD_MANAGER_MODULE_NAME, 'tables/call_logs_relations'), [
            'rel_id'   => $rel_id,
            'rel_type' => $rel_type,
        ]);
    }

    /**
     * Get call log data.
     *
     * @param int $callid The ID of the call log.
     * @param bool $return Whether to return the data or not.
     *
     * @return void|string
     */
    public function get_call_data($callid, $return = false)
    {

        $call_log = $this->call_logs->get($callid);

        if (!$call_log) {
            header('HTTP/1.0 404 Not Found');
            echo 'Call not found';
            die();
        }
        $data['call_log']       = $call_log;
        $data['id']         = $call_log->id;

        if ($return == false) {
            $this->load->view(AI_LEAD_MANAGER_MODULE_NAME . '/modals/call_view_modal', $data);
        } else {
            return $this->load->view(AI_LEAD_MANAGER_MODULE_NAME . '/modals/call_view_modal', $data, true);
        }
    }
}
