<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ingress extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('deals/deals_comms_model', 'deals_comms_model', true);
        $this->load->model('deals/deals_enterprise_model', 'deals_enterprise_model', true);
        $this->load->library('deals/IntegrationManager', null, 'integrationmanager');
    }

    public function inbound($token = null)
    {
        if (empty($token)) {
            show_404();
        }

        $payload = $this->resolve_payload();
        $result = $this->deals_comms_model->ingest_inbound_message($token, $payload, $this->resolve_request_context());

        if (!empty($result['success']) && !empty($result['deal_id'])) {
            $this->deals_enterprise_model->dispatch_event_webhooks('inbound_email_received', $result['deal_id'], [
                'email_id' => $result['email_id'] ?? null,
                'thread_token' => $result['thread_token'] ?? null,
            ]);
            $this->deals_comms_model->dispatch_event_connectors('inbound_email_received', $result['deal_id'], [
                'email_id' => $result['email_id'] ?? null,
                'thread_token' => $result['thread_token'] ?? null,
            ]);
            $this->integrationmanager->emitEvent('email.received', [
                'email_id' => $result['email_id'] ?? null,
                'thread_token' => $result['thread_token'] ?? null,
            ], [
                'deal_id' => $result['deal_id'],
                'source' => 'deals.ingress',
                'direction' => 'inbound',
                'queue_name' => 'platform',
            ]);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(!empty($result['success']) ? 200 : 422)
            ->set_output(json_encode($result));
    }

    public function bounce($token = null)
    {
        if (empty($token)) {
            show_404();
        }

        $payload = $this->resolve_payload();
        $result = $this->deals_comms_model->register_bounce($token, $payload, $this->resolve_request_context());

        if (!empty($result['success']) && !empty($result['deal_id'])) {
            $this->deals_enterprise_model->dispatch_event_webhooks('email_bounced', $result['deal_id'], [
                'email_id' => $result['email_id'] ?? null,
                'bounce_type' => $result['bounce_type'] ?? null,
            ]);
            $this->deals_comms_model->dispatch_event_connectors('email_bounced', $result['deal_id'], [
                'email_id' => $result['email_id'] ?? null,
                'bounce_type' => $result['bounce_type'] ?? null,
            ]);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(!empty($result['success']) ? 200 : 422)
            ->set_output(json_encode($result));
    }

    private function resolve_payload()
    {
        $raw = $this->input->raw_input_stream;
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        $post = $this->input->post(null, false);
        return is_array($post) ? $post : [];
    }

    private function resolve_request_context()
    {
        return [
            'raw' => (string) $this->input->raw_input_stream,
            'headers' => $this->resolve_headers(),
            'method' => $this->input->method(true),
        ];
    }

    private function resolve_headers()
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                return $headers;
            }
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }
}
