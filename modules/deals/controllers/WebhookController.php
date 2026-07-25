<?php

defined('BASEPATH') or exit('No direct script access allowed');

class WebhookController extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('deals/IntegrationManager', null, 'integrationmanager');
    }

    public function provider($providerKey = null, $accountId = null)
    {
        if (empty($providerKey) || empty($accountId)) {
            show_404();
        }

        $result = $this->integrationmanager->handleWebhook(
            (string) $providerKey,
            (int) $accountId,
            $this->resolvePayload(),
            $this->resolveRequestContext()
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(!empty($result['success']) ? 200 : 422)
            ->set_output(json_encode($result));
    }

    protected function resolvePayload()
    {
        $raw = (string) $this->input->raw_input_stream;
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        $post = $this->input->post(null, false);
        return is_array($post) ? $post : [];
    }

    protected function resolveRequestContext()
    {
        return [
            'raw' => (string) $this->input->raw_input_stream,
            'headers' => $this->resolveHeaders(),
            'method' => $this->input->method(true),
        ];
    }

    protected function resolveHeaders()
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
