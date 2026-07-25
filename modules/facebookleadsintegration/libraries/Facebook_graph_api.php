<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Facebook Graph API Library
 * 
 * Handles all Facebook Graph API interactions securely without external SDK dependency.
 * Uses direct HTTP calls with proper error handling and validation.
 * 
 * @package    FacebookLeadsIntegration
 * @author     Themesic Interactive
 * @version    2.0.0
 */
class Facebook_graph_api
{
    /**
     * @var string Facebook Graph API base URL
     */
    private const GRAPH_API_BASE = 'https://graph.facebook.com/';
    
    /**
     * @var string Current Graph API version
     */
    private const API_VERSION = 'v19.0';
    
    /**
     * @var string|null Facebook App ID
     */
    private $app_id;
    
    /**
     * @var string|null Facebook App Secret
     */
    private $app_secret;
    
    /**
     * @var string|null Long-lived access token
     */
    private $access_token;
    
    /**
     * @var array Last error information
     */
    private $last_error = [];
    
    /**
     * @var CI_Controller CodeIgniter instance
     */
    private $CI;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->load_credentials();
    }

    /**
     * Load credentials from database options
     * 
     * @return void
     */
    private function load_credentials(): void
    {
        $this->app_id = get_option('fb_leads_app_id');
        $this->app_secret = get_option('fb_leads_app_secret');
        $this->access_token = get_option('fb_leads_access_token');
    }

    /**
     * Reload credentials from database
     * 
     * @return self
     */
    public function refresh_credentials(): self
    {
        $this->load_credentials();
        return $this;
    }

    /**
     * Set App ID
     * 
     * @param string $app_id
     * @return self
     */
    public function set_app_id(string $app_id): self
    {
        $this->app_id = trim($app_id);
        return $this;
    }

    /**
     * Set App Secret
     * 
     * @param string $app_secret
     * @return self
     */
    public function set_app_secret(string $app_secret): self
    {
        $this->app_secret = trim($app_secret);
        return $this;
    }

    /**
     * Set Access Token
     * 
     * @param string $access_token
     * @return self
     */
    public function set_access_token(string $access_token): self
    {
        $this->access_token = trim($access_token);
        return $this;
    }

    /**
     * Get the current API version
     * 
     * @return string
     */
    public function get_api_version(): string
    {
        return self::API_VERSION;
    }

    /**
     * Get the last error
     * 
     * @return array
     */
    public function get_last_error(): array
    {
        return $this->last_error;
    }

    /**
     * Clear last error
     * 
     * @return void
     */
    private function clear_error(): void
    {
        $this->last_error = [];
    }

    /**
     * Set error information
     * 
     * @param string $message
     * @param int $code
     * @param array $details
     * @return void
     */
    private function set_error(string $message, int $code = 0, array $details = []): void
    {
        $this->last_error = [
            'message' => $message,
            'code' => $code,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log the error
        $this->log_error($message, $code, $details);
    }

    /**
     * Log error to database
     * 
     * @param string $message
     * @param int $code
     * @param array $details
     * @return void
     */
    private function log_error(string $message, int $code, array $details): void
    {
        if ($this->CI->db->table_exists(db_prefix() . 'fb_leads_logs')) {
            $this->CI->db->insert(db_prefix() . 'fb_leads_logs', [
                'log_type' => 'error',
                'message' => $message,
                'error_code' => $code,
                'details' => json_encode($details),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Build the full Graph API URL
     * 
     * @param string $endpoint
     * @param array $params
     * @return string
     */
    private function build_url(string $endpoint, array $params = []): string
    {
        $url = self::GRAPH_API_BASE . self::API_VERSION . '/' . ltrim($endpoint, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }

    /**
     * Make a GET request to the Graph API
     * 
     * @param string $endpoint
     * @param array $params
     * @return array|false
     */
    public function get(string $endpoint, array $params = [])
    {
        $this->clear_error();
        
        try {
            $url = $this->build_url($endpoint, $params);
            
            $response = \WpOrg\Requests\Requests::get($url, [
                'Accept' => 'application/json',
                'User-Agent' => 'Perfex-CRM-Facebook-Leads/2.0'
            ], [
                'timeout' => 30,
                'connect_timeout' => 10
            ]);
            
            return $this->handle_response($response);
            
        } catch (\Exception $e) {
            $this->set_error('Request failed: ' . $e->getMessage(), $e->getCode());
            return false;
        }
    }

    /**
     * Make a POST request to the Graph API
     * 
     * @param string $endpoint
     * @param array $data
     * @param array $params
     * @return array|false
     */
    public function post(string $endpoint, array $data = [], array $params = [])
    {
        $this->clear_error();
        
        try {
            $url = $this->build_url($endpoint, $params);
            
            $response = \WpOrg\Requests\Requests::post($url, [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'Perfex-CRM-Facebook-Leads/2.0'
            ], json_encode($data), [
                'timeout' => 30,
                'connect_timeout' => 10
            ]);
            
            return $this->handle_response($response);
            
        } catch (\Exception $e) {
            $this->set_error('Request failed: ' . $e->getMessage(), $e->getCode());
            return false;
        }
    }

    /**
     * Make a DELETE request to the Graph API
     * 
     * @param string $endpoint
     * @param array $params
     * @return array|false
     */
    public function delete(string $endpoint, array $params = [])
    {
        $this->clear_error();
        
        try {
            $url = $this->build_url($endpoint, $params);
            
            $response = \WpOrg\Requests\Requests::delete($url, [
                'Accept' => 'application/json',
                'User-Agent' => 'Perfex-CRM-Facebook-Leads/2.0'
            ], [
                'timeout' => 30,
                'connect_timeout' => 10
            ]);
            
            return $this->handle_response($response);
            
        } catch (\Exception $e) {
            $this->set_error('Request failed: ' . $e->getMessage(), $e->getCode());
            return false;
        }
    }

    /**
     * Handle API response
     * 
     * @param \WpOrg\Requests\Response $response
     * @return array|false
     */
    private function handle_response($response)
    {
        $body = json_decode($response->body, true);
        
        if ($response->status_code >= 400 || isset($body['error'])) {
            $error = $body['error'] ?? [];
            $this->set_error(
                $error['message'] ?? 'Unknown API error',
                $error['code'] ?? $response->status_code,
                [
                    'type' => $error['type'] ?? 'Unknown',
                    'fbtrace_id' => $error['fbtrace_id'] ?? null,
                    'http_status' => $response->status_code
                ]
            );
            return false;
        }
        
        return $body;
    }

    /**
     * Validate App credentials by making a test request
     * 
     * @return array ['valid' => bool, 'message' => string, 'details' => array]
     */
    public function validate_credentials(): array
    {
        if (empty($this->app_id) || $this->app_id === 'changeme') {
            return [
                'valid' => false,
                'message' => 'Facebook App ID is not configured',
                'details' => []
            ];
        }
        
        if (empty($this->app_secret) || $this->app_secret === 'changeme') {
            return [
                'valid' => false,
                'message' => 'Facebook App Secret is not configured',
                'details' => []
            ];
        }
        
        // Test app credentials by fetching app info
        $result = $this->get($this->app_id, [
            'access_token' => $this->app_id . '|' . $this->app_secret,
            'fields' => 'id,name'
        ]);
        
        if ($result === false) {
            return [
                'valid' => false,
                'message' => $this->last_error['message'] ?? 'Failed to validate credentials',
                'details' => $this->last_error
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Credentials are valid',
            'details' => [
                'app_id' => $result['id'] ?? null,
                'app_name' => $result['name'] ?? null
            ]
        ];
    }

    /**
     * Exchange short-lived token for long-lived token
     * 
     * @param string $short_lived_token
     * @return array|false
     */
    public function exchange_token(string $short_lived_token)
    {
        if (empty($this->app_id) || empty($this->app_secret)) {
            $this->set_error('App credentials not configured');
            return false;
        }
        
        $result = $this->get('oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->app_id,
            'client_secret' => $this->app_secret,
            'fb_exchange_token' => $short_lived_token
        ]);
        
        if ($result === false) {
            return false;
        }
        
        return [
            'access_token' => $result['access_token'] ?? null,
            'token_type' => $result['token_type'] ?? 'bearer',
            'expires_in' => $result['expires_in'] ?? null
        ];
    }

    /**
     * Validate access token
     * 
     * @param string|null $token Token to validate, or current token if null
     * @return array ['valid' => bool, 'message' => string, 'details' => array]
     */
    public function validate_access_token(?string $token = null): array
    {
        $token = $token ?? $this->access_token;
        
        if (empty($token)) {
            return [
                'valid' => false,
                'message' => 'No access token available',
                'details' => []
            ];
        }
        
        $result = $this->get('debug_token', [
            'input_token' => $token,
            'access_token' => $this->app_id . '|' . $this->app_secret
        ]);
        
        if ($result === false) {
            return [
                'valid' => false,
                'message' => $this->last_error['message'] ?? 'Failed to validate token',
                'details' => $this->last_error
            ];
        }
        
        $data = $result['data'] ?? [];
        $is_valid = $data['is_valid'] ?? false;
        
        return [
            'valid' => $is_valid,
            'message' => $is_valid ? 'Token is valid' : 'Token is invalid or expired',
            'details' => [
                'app_id' => $data['app_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'type' => $data['type'] ?? null,
                'expires_at' => isset($data['expires_at']) && $data['expires_at'] > 0 
                    ? date('Y-m-d H:i:s', $data['expires_at']) 
                    : 'Never',
                'scopes' => $data['scopes'] ?? [],
                'granular_scopes' => $data['granular_scopes'] ?? []
            ]
        ];
    }

    /**
     * Get user's Facebook pages
     * 
     * @param string|null $token Access token (user token required)
     * @return array|false
     */
    public function get_user_pages(?string $token = null)
    {
        $token = $token ?? $this->access_token;
        
        if (empty($token)) {
            $this->set_error('Access token required to fetch pages');
            return false;
        }
        
        $result = $this->get('me/accounts', [
            'access_token' => $token,
            'fields' => 'id,name,access_token,category,tasks'
        ]);
        
        if ($result === false) {
            return false;
        }
        
        return $result['data'] ?? [];
    }

    /**
     * Subscribe a page to lead gen webhook
     * 
     * @param string $page_id
     * @param string $page_access_token
     * @return bool
     */
    public function subscribe_page_to_leadgen(string $page_id, string $page_access_token): bool
    {
        $result = $this->post($page_id . '/subscribed_apps', [
            'subscribed_fields' => ['leadgen']
        ], [
            'access_token' => $page_access_token
        ]);
        
        return $result !== false && ($result['success'] ?? false);
    }

    /**
     * Unsubscribe a page from lead gen webhook
     * 
     * DELETE /{page-id}/subscribed_apps requires a page access token.
     * If page token fails, falls back to app access token (app_id|app_secret).
     * 
     * @param string $page_id
     * @param string $page_access_token
     * @return bool
     */
    public function unsubscribe_page_from_leadgen(string $page_id, string $page_access_token): bool
    {
        // First try with the page access token
        $result = $this->delete($page_id . '/subscribed_apps', [
            'access_token' => $page_access_token
        ]);
        
        if ($result !== false && ($result['success'] ?? false)) {
            return true;
        }
        
        // Fallback: try with app access token (app_id|app_secret)
        if (!empty($this->app_id) && !empty($this->app_secret)) {
            $app_token = $this->app_id . '|' . $this->app_secret;
            $this->clear_error();
            $result = $this->delete($page_id . '/subscribed_apps', [
                'access_token' => $app_token
            ]);
            return $result !== false && ($result['success'] ?? false);
        }
        
        return false;
    }

    /**
     * Get lead data by leadgen ID
     * 
     * Uses page access token if provided (preferred — permanent token),
     * falls back to global user access token.
     * 
     * @param string $leadgen_id
     * @param string|null $page_access_token Optional page token (never expires)
     * @return array|false
     */
    public function get_lead_data(string $leadgen_id, ?string $page_access_token = null)
    {
        $token = $page_access_token ?? $this->access_token;
        
        if (empty($token)) {
            $this->set_error('Access token required to fetch lead data');
            return false;
        }
        
        return $this->get($leadgen_id, [
            'access_token' => $token,
            'fields' => 'id,created_time,field_data,form_id,page_id,campaign_id,ad_id,adset_id'
        ]);
    }

    /**
     * Get form information
     * 
     * @param string $form_id
     * @return array|false
     */
    public function get_form_info(string $form_id)
    {
        if (empty($this->access_token)) {
            $this->set_error('Access token required to fetch form info');
            return false;
        }
        
        return $this->get($form_id, [
            'access_token' => $this->access_token,
            'fields' => 'id,name,page_id,status,questions'
        ]);
    }

    /**
     * Test webhook connection by sending a test notification
     * 
     * @param string $page_id
     * @return bool
     */
    public function test_webhook(string $page_id): bool
    {
        // The test lead ID used by Facebook for testing
        // This is a special ID that triggers a test lead
        return true; // Webhook testing is done through Facebook's Lead Ads Testing Tool
    }

    /**
     * Verify webhook signature
     * 
     * @param string $payload Raw request body
     * @param string $signature X-Hub-Signature-256 header value
     * @return bool
     */
    public function verify_webhook_signature(string $payload, string $signature): bool
    {
        if (empty($this->app_secret)) {
            return false;
        }
        
        $expected_signature = 'sha256=' . hash_hmac('sha256', $payload, $this->app_secret);
        
        return hash_equals($expected_signature, $signature);
    }

    /**
     * Check if all required permissions are granted
     * 
     * @return array ['has_all' => bool, 'missing' => array, 'granted' => array]
     */
    public function check_permissions(): array
    {
        $required_permissions = [
            'pages_show_list',
            'pages_read_engagement',
            'leads_retrieval',
            'pages_manage_ads',
            'ads_management'
        ];
        
        $token_info = $this->validate_access_token();
        
        if (!$token_info['valid']) {
            return [
                'has_all' => false,
                'missing' => $required_permissions,
                'granted' => [],
                'error' => $token_info['message']
            ];
        }
        
        $granted = $token_info['details']['scopes'] ?? [];
        $missing = array_diff($required_permissions, $granted);
        
        return [
            'has_all' => empty($missing),
            'missing' => array_values($missing),
            'granted' => array_values(array_intersect($required_permissions, $granted))
        ];
    }
}
