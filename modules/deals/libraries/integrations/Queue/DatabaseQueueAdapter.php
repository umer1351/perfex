<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../contracts/QueueAdapterInterface.php';

class DatabaseQueueAdapter implements QueueAdapterInterface
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('deals/deals_integration_model', 'deals_integration_model', true);
    }

    public function push(array $eventData)
    {
        return $this->CI->deals_integration_model->create_event($eventData);
    }

    public function reserve($limit = 25)
    {
        return $this->CI->deals_integration_model->reserve_events((int) $limit);
    }

    public function complete($id)
    {
        return $this->CI->deals_integration_model->complete_event($id);
    }

    public function fail($id, $message)
    {
        return $this->CI->deals_integration_model->fail_event($id, $message);
    }
}
