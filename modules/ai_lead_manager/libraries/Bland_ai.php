<?php
defined('BASEPATH') or exit('No direct script access allowed');

use modules\ai_lead_manager\libraries\bland_ai\Calls;
use modules\ai_lead_manager\libraries\bland_ai\Voices;
use modules\ai_lead_manager\libraries\bland_ai\Accounts;
use modules\ai_lead_manager\libraries\bland_ai\Agents;
use modules\ai_lead_manager\libraries\bland_ai\Inbound;
use modules\ai_lead_manager\libraries\bland_ai\Knowledgebase;
use modules\ai_lead_manager\libraries\bland_ai\Prompts;
use modules\ai_lead_manager\libraries\bland_ai\Tools;

class Bland_ai
{
    use Calls, Voices, Accounts, Prompts, Tools, Agents, Inbound, Knowledgebase;

    private $base_url = 'https://api.bland.ai';
    private $auth_token = null;

    public function __construct()
    {
        $this->auth_token = get_option('bland_ai_api_key');
    }

    /**
     * Sets the authentication token used for API requests.
     *
     * @param string $key The authentication token to set.
     */
    public function set_auth_token($key)
    {
        $this->auth_token = $key;
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
            'Authorization: ' . $this->auth_token,
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
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

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
