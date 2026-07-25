<?php

use modules\ai_lead_manager\libraries\vapi_ai\Assistants;
use modules\ai_lead_manager\libraries\vapi_ai\Calls;
use modules\ai_lead_manager\libraries\vapi_ai\Files;
use modules\ai_lead_manager\libraries\vapi_ai\Knowledgebase;
use modules\ai_lead_manager\libraries\vapi_ai\Phone_numbers;
use modules\ai_lead_manager\libraries\vapi_ai\Tools;
use modules\ai_lead_manager\libraries\vapi_ai\Voices;

defined('BASEPATH') or exit('No direct script access allowed');


class Vapi_ai
{
    use Calls, Tools, Assistants, Phone_numbers, Voices, Knowledgebase, Files;

    private $base_url = 'https://api.vapi.ai';
    private $auth_token = null;

    public function __construct()
    {
        $this->auth_token = get_option('vapi_ai_api_key');
    }

    /**
     * Sends an HTTP request to the given URL using the specified method and data.
     *
     * @param string $method The HTTP method to use for the request (e.g., 'GET', 'POST').
     * @param string $url The URL to send the request to.
     * @param array $data The data to send with the request, applicable for methods like 'POST'.
     * @return array The API response decoded from JSON, or an error message if the request fails.
     */
    protected function send_request($method, $url, $data = [], $headers = [])
    {
        $ch = curl_init();
        $default_headers = [
            'Authorization: Bearer ' . $this->auth_token,
        ];

        $is_multipart = false;
        foreach ($data as $value) {
            if ($value instanceof CURLFile) {
                $is_multipart = true;
                break;
            }
        }

        if (!$is_multipart) {
            $default_headers[] = 'Content-Type: application/json';
        }

        $headers = array_merge($default_headers, $headers);

        curl_setopt($ch, CURLOPT_URL, $this->base_url . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Set HTTP method and payload
        $method = strtoupper($method);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $is_multipart ? $data : json_encode($data));
        } elseif (in_array($method, ['PATCH', 'PUT', 'DELETE'], true)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ["error" => $error_msg];
        }

        curl_close($ch);

        // Check for successful HTTP status codes (200-299)
        if ($http_status < 200 || $http_status >= 300) {
            return [
                "error" => "API call failed with status $http_status",
                "response" => json_decode($response, true)
            ];
        }

        return json_decode($response, true);
    }
}
