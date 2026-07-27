<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Facebook & Instagram Leads Integration Controller
 * 
 * Handles webhooks, API endpoints, and module functionality.
 * 
 * @package    FacebookLeadsIntegration
 * @author     Themesic Interactive
 * @version    2.0.0
 */
class Facebookleadsintegration extends AdminController
{
    /**
     * @var Facebook_graph_api
     */
    private $fb_api;

    /**
     * @var Fb_leads_sync_manager
     */
    private $sync_manager;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Check if this is a webhook request - bypass authentication
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_webhook = (strpos($request_uri, 'facebookleadsintegration/webhook') !== false);
        
        if ($is_webhook) {
            // For webhook requests, use CI_Controller instead of AdminController
            // This allows Facebook to access the endpoint without authentication
            CI_Controller::__construct();
            // Load database for webhook operations
            $this->load->database();
        } else {
            parent::__construct();
        }
        
        // Load models (these should always be available in Perfex)
        $this->load->model('leads_model');
        $this->load->model('staff_model');
        
        // Delay library loading - will load on demand
        $this->fb_api = null;
        $this->sync_manager = null;
    }
    
    /**
     * Lazy load the Facebook API library
     */
    private function get_fb_api()
    {
        if ($this->fb_api === null) {
            $this->load->library('facebookleadsintegration/Facebook_graph_api', null, 'facebook_graph_api');
            $this->fb_api = $this->facebook_graph_api;
        }
        return $this->fb_api;
    }
    
    /**
     * Lazy load the sync manager library
     */
    private function get_sync_manager()
    {
        if ($this->sync_manager === null) {
            $this->load->library('facebookleadsintegration/Fb_leads_sync_manager', null, 'fb_leads_sync_manager');
            $this->sync_manager = $this->fb_leads_sync_manager;
        }
        return $this->sync_manager;
    }

    /**
     * Main settings page
     */
    public function index()
    {
        // Debug: If ?diag=1 is passed, show diagnostics
        if ($this->input->get('diag') === '1') {
            echo "<h1>FB Leads Diagnostics</h1><pre>";
            echo "1. Controller loaded OK\n";
            echo "2. PHP Version: " . PHP_VERSION . "\n";
            echo "3. Memory limit: " . ini_get('memory_limit') . "\n";
            try {
                echo "4. Getting leads_model->get_status()... ";
                $statuses = $this->leads_model->get_status();
                echo "OK (" . count($statuses) . " statuses)\n";
            } catch (Exception $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
            }
            try {
                echo "5. Getting sync_manager... ";
                $sm = $this->get_sync_manager();
                echo "OK\n";
            } catch (Exception $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
            } catch (Error $e) {
                echo "FATAL: " . $e->getMessage() . "\n";
            }
            echo "</pre>";
            exit;
        }
        
        // Check if setup wizard should be shown
        if (get_option('fb_leads_setup_completed') !== '1' && !$this->input->get('skip_wizard')) {
            redirect(admin_url('facebookleadsintegration/setup_wizard'));
        }

        try {
            // Debug step by step
            if ($this->input->get('step_debug')) {
                echo "<pre>Step 1: Starting prepare_settings_data...\n";
                flush();
            }
            
            $data = $this->prepare_settings_data();
            
            if ($this->input->get('step_debug')) {
                echo "Step 2: prepare_settings_data complete\n";
                echo "Data keys: " . implode(', ', array_keys($data)) . "\n";
                echo "Step 3: Loading view...\n";
                flush();
            }
            
            $data['title'] = _l('facebookleadsintegration');
            
            $this->load->view('facebookleadsintegration/settings_view', $data);
            
            if ($this->input->get('step_debug')) {
                echo "Step 4: View loaded successfully\n</pre>";
            }
        } catch (Exception $e) {
            log_message('error', 'FB Leads settings page error: ' . $e->getMessage());
            show_error('Error loading Facebook Leads settings: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'FB Leads settings page FATAL: ' . $e->getMessage());
            show_error('Fatal error loading Facebook Leads settings: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        }
    }

    /**
     * Simple wizard test - access at /admin/facebookleadsintegration/wizard_test
     */
    public function wizard_test()
    {
        $data = [
            'app_id' => get_option('fb_leads_app_id') ?: '',
            'webhook_url' => site_url('facebookleadsintegration/webhook'),
            'title' => 'Setup Wizard Test'
        ];
        $this->load->view('facebookleadsintegration/setup_wizard_simple', $data);
    }
    
    /**
     * Debug endpoint - access at /admin/facebookleadsintegration/debug
     */
    public function debug()
    {
        // Immediately output to prevent buffering issues
        @ob_end_flush();
        header('Content-Type: text/html; charset=utf-8');
        
        echo "<h1>FB Leads Debug</h1>";
        echo "<pre>";
        flush();
        
        echo "Step 1: Controller loaded OK\n";
        echo "Step 2: PHP Version: " . PHP_VERSION . "\n";
        flush();
        
        echo "Step 3: Checking models...\n";
        flush();
        
        try {
            $statuses = $this->leads_model->get_status();
            echo "  - leads_model->get_status(): OK (" . (is_array($statuses) ? count($statuses) : 'not array') . " statuses)\n";
        } catch (Exception $e) {
            echo "  - leads_model->get_status(): ERROR - " . $e->getMessage() . "\n";
        } catch (Error $e) {
            echo "  - leads_model->get_status(): FATAL - " . $e->getMessage() . "\n";
        }
        flush();
        
        try {
            $sources = $this->leads_model->get_source();
            echo "  - leads_model->get_source(): OK (" . (is_array($sources) ? count($sources) : 'not array') . " sources)\n";
        } catch (Exception $e) {
            echo "  - leads_model->get_source(): ERROR - " . $e->getMessage() . "\n";
        } catch (Error $e) {
            echo "  - leads_model->get_source(): FATAL - " . $e->getMessage() . "\n";
        }
        flush();
        
        try {
            $staff = $this->staff_model->get('', ['active' => 1]);
            echo "  - staff_model->get(): OK (" . (is_array($staff) ? count($staff) : 'not array') . " staff)\n";
        } catch (Exception $e) {
            echo "  - staff_model->get(): ERROR - " . $e->getMessage() . "\n";
        } catch (Error $e) {
            echo "  - staff_model->get(): FATAL - " . $e->getMessage() . "\n";
        }
        flush();
        
        echo "\nStep 4: Checking options...\n";
        echo "  - fb_leads_app_id: " . (get_option('fb_leads_app_id') ?: '(empty)') . "\n";
        echo "  - fb_leads_webhook_token: " . (get_option('fb_leads_webhook_token') ?: '(empty)') . "\n";
        flush();
        
        echo "\nStep 5: Trying to load sync_manager library...\n";
        flush();
        try {
            $sync_manager = $this->get_sync_manager();
            echo "  - sync_manager: OK\n";
        } catch (Exception $e) {
            echo "  - sync_manager: ERROR - " . $e->getMessage() . "\n";
        } catch (Error $e) {
            echo "  - sync_manager: FATAL - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
        flush();
        
        echo "\nStep 6: All checks completed!\n";
        echo "</pre>";
        exit;
    }
    
    /**
     * Setup wizard
     */
    public function setup_wizard()
    {
        // Debug: Log that we reached this method
        log_message('error', 'FB Leads: setup_wizard() method called');
        
        try {
            log_message('error', 'FB Leads: Getting statuses...');
            $statuses = $this->leads_model->get_status();
            
            log_message('error', 'FB Leads: Getting sources...');
            $sources = $this->leads_model->get_source();
            
            log_message('error', 'FB Leads: Getting staff...');
            $staff = $this->staff_model->get('', ['active' => 1]);
            
            log_message('error', 'FB Leads: Building data array...');
            $data = [
                'statuses' => $statuses ?: [],
                'sources' => $sources ?: [],
                'staff' => $staff ?: [],
                'app_id' => get_option('fb_leads_app_id') ?: '',
                'app_secret' => get_option('fb_leads_app_secret') ?: '',
                'webhook_token' => get_option('fb_leads_webhook_token') ?: 'your_verify_token',
                'access_token' => get_option('fb_leads_access_token') ?: '',
                'webhook_url' => site_url('facebookleadsintegration/webhook'),
                'default_assigned' => get_option('fb_leads_default_assigned') ?: '',
                'default_source' => get_option('fb_leads_default_source') ?: '',
                'default_status' => get_option('fb_leads_default_status') ?: '',
                'duplicate_detection' => get_option('fb_leads_duplicate_detection') ?: '1',
                'notifications_enabled' => get_option('fb_leads_notifications_enabled') ?: '0',
                'pages' => [],
                'stats' => ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0],
                'api_version' => 'v19.0',
                'title' => _l('fb_leads_setup_wizard'),
                'current_step' => (int) ($this->input->get('step') ?: 1)
            ];
            
            log_message('error', 'FB Leads: Loading view...');
            $this->load->view('facebookleadsintegration/setup_wizard_view', $data);
            log_message('error', 'FB Leads: View loaded successfully');
            
        } catch (Exception $e) {
            log_message('error', 'FB Leads ERROR: ' . $e->getMessage());
            echo "Error: " . $e->getMessage();
        } catch (Error $e) {
            log_message('error', 'FB Leads FATAL: ' . $e->getMessage());
            echo "Fatal Error: " . $e->getMessage();
        }
    }

    /**
     * Sync history page
     */
    public function sync_history()
    {
        $page = (int) $this->input->get('page') ?: 1;
        $filters = [
            'status' => $this->input->get('status'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to')
        ];

        $sync_manager = $this->get_sync_manager();
        $data['history'] = $sync_manager->get_sync_history($page, 25, $filters);
        $data['stats'] = $sync_manager->get_sync_stats('month');
        $data['current_page'] = $page;
        $data['filters'] = $filters;
        $data['title'] = _l('fb_leads_sync_history');

        $this->load->view('facebookleadsintegration/sync_history_view', $data);
    }

    /**
     * Field mapping page
     */
    public function field_mapping()
    {
        $data['standard_fields'] = [
            'name' => _l('lead_name'),
            'email' => _l('lead_email'),
            'phonenumber' => _l('lead_phone'),
            'company' => _l('lead_company'),
            'address' => _l('lead_address'),
            'city' => _l('lead_city'),
            'state' => _l('lead_state'),
            'country' => _l('lead_country'),
            'zip' => _l('lead_zip'),
            'website' => _l('lead_website'),
            'title' => _l('lead_position'),
            'description' => _l('lead_description'),
            'default_language' => _l('lead_language')
        ];
        
        $data['custom_fields'] = get_custom_fields('leads');
        $data['current_mappings'] = $this->get_field_mappings();
        $data['title'] = _l('fb_leads_field_mapping');

        $this->load->view('facebookleadsintegration/field_mapping_view', $data);
    }

    /**
     * Prepare common data for settings views
     */
    private function prepare_settings_data()
    {
        // Get statuses with fallback
        $statuses = [];
        if (method_exists($this->leads_model, 'get_status')) {
            $statuses = $this->leads_model->get_status();
        }
        if (!is_array($statuses)) {
            $statuses = [];
        }
        
        // Get sources with fallback
        $sources = [];
        if (method_exists($this->leads_model, 'get_source')) {
            $sources = $this->leads_model->get_source();
        }
        if (!is_array($sources)) {
            $sources = [];
        }
        
        // Get staff with fallback
        $staff = [];
        if (method_exists($this->staff_model, 'get')) {
            $staff = $this->staff_model->get('', ['active' => 1]);
        }
        if (!is_array($staff)) {
            $staff = [];
        }
        
        // Get sync stats with error handling
        $stats = ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0, 'pending_retry' => 0];
        try {
            $sync_manager = $this->get_sync_manager();
            if ($sync_manager && method_exists($sync_manager, 'get_sync_stats')) {
                $stats = array_merge($stats, $sync_manager->get_sync_stats('month'));
            }
        } catch (Exception $e) {
            log_message('error', 'FB Leads: Error getting sync stats: ' . $e->getMessage());
            // Silently fail for stats - use defaults
        } catch (Error $e) {
            log_message('error', 'FB Leads: Fatal error getting sync stats: ' . $e->getMessage());
            // Silently fail for stats - use defaults
        }
        
        return [
            'statuses' => $statuses,
            'sources' => $sources,
            'staff' => $staff,
            'app_id' => get_option('fb_leads_app_id') ?: '',
            'app_secret' => get_option('fb_leads_app_secret') ?: '',
            'webhook_token' => get_option('fb_leads_webhook_token') ?: '',
            'access_token' => get_option('fb_leads_access_token') ?: '',
            'webhook_url' => site_url('facebookleadsintegration/webhook'),
            'default_assigned' => get_option('fb_leads_default_assigned'),
            'default_source' => get_option('fb_leads_default_source'),
            'default_status' => get_option('fb_leads_default_status'),
            'duplicate_detection' => get_option('fb_leads_duplicate_detection'),
            'notifications_enabled' => get_option('fb_leads_notifications_enabled'),
            'pages' => $this->get_subscribed_pages(),
            'stats' => $stats,
            'api_version' => 'v19.0' // Facebook Graph API version
        ];
    }

    /**
     * Get subscribed pages from database
     */
    private function get_subscribed_pages(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'fb_leads_pages')) {
            return [];
        }

        return $this->db->get(db_prefix() . 'fb_leads_pages')->result_array();
    }

    /**
     * Get field mappings from database
     */
    private function get_field_mappings(): array
    {
        if (!$this->db->table_exists(db_prefix() . 'fb_leads_field_mappings')) {
            return [];
        }

        return $this->db->get(db_prefix() . 'fb_leads_field_mappings')->result_array();
    }

    // =========================================================================
    // WEBHOOK HANDLERS
    // =========================================================================

    /**
     * Facebook webhook endpoint
     * 
     * Handles both verification (GET) and lead notifications (POST)
     */
    public function webhook()
    {
        // Log incoming webhook
        $this->log_webhook_request();

        // Handle webhook verification (GET request)
        if ($this->input->method() === 'get') {
            $this->handle_webhook_verification();
            return;
        }

        // Handle lead notification (POST request)
        if ($this->input->method() === 'post') {
            $this->handle_lead_notification();
            return;
        }

        // Method not allowed
        http_response_code(405);
        exit('Method Not Allowed');
    }

    /**
     * Handle webhook verification request from Facebook
     */
    private function handle_webhook_verification(): void
    {
        $mode = $this->input->get('hub_mode');
        $token = $this->input->get('hub_verify_token');
        $challenge = $this->input->get('hub_challenge');

        $stored_token = get_option('fb_leads_webhook_token');

        if ($mode === 'subscribe' && $token === $stored_token) {
            // Mark webhook as verified
            update_option('fb_leads_webhook_verified', '1');
            update_option('fb_leads_webhook_verified_at', date('Y-m-d H:i:s'));
            
            // Log successful verification
            $this->log_activity('webhook', 'Webhook verified successfully', [
                'ip' => $this->input->ip_address()
            ]);

            // Return the challenge
            http_response_code(200);
            echo $challenge;
            exit;
        }

        // Verification failed
        $this->log_activity('error', 'Webhook verification failed', [
            'mode' => $mode,
            'token_match' => $token === $stored_token,
            'ip' => $this->input->ip_address()
        ]);

        http_response_code(403);
        exit('Verification failed');
    }

    /**
     * Handle incoming lead notification from Facebook
     */
    private function handle_lead_notification(): void
    {
        // Get raw payload
        $payload = file_get_contents('php://input');
        
        if (empty($payload)) {
            http_response_code(400);
            exit('Empty payload');
        }

        // Verify signature if app secret is configured
        $app_secret = get_option('fb_leads_app_secret');
        if (!empty($app_secret) && $app_secret !== 'changeme') {
            $signature = $this->input->get_request_header('X-Hub-Signature-256');
            if (!$this->get_fb_api()->verify_webhook_signature($payload, $signature ?? '')) {
                $this->log_activity('error', 'Invalid webhook signature', [
                    'ip' => $this->input->ip_address()
                ]);
                http_response_code(401);
                exit('Invalid signature');
            }
        }

        // Decode payload
        $data = json_decode($payload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_activity('error', 'Invalid JSON payload', [
                'error' => json_last_error_msg()
            ]);
            http_response_code(400);
            exit('Invalid JSON');
        }

        // IMPORTANT: Return 200 immediately to Facebook
        // Process lead asynchronously
        http_response_code(200);
        echo 'OK';
        
        // Flush output to Facebook
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ob_end_flush();
            flush();
        }

        // Now process the lead(s)
        $this->process_webhook_payload($data);
    }

    /**
     * Process webhook payload and extract leads
     */
    private function process_webhook_payload(array $data): void
    {
        // Check if this is a page subscription
        if (!isset($data['object']) || $data['object'] !== 'page') {
            $this->log_activity('debug', 'Non-page webhook received', $data);
            return;
        }

        if (!isset($data['entry']) || !is_array($data['entry'])) {
            $this->log_activity('error', 'Invalid webhook structure', $data);
            return;
        }

        foreach ($data['entry'] as $entry) {
            $page_id = $entry['id'] ?? null;
            
            if (!isset($entry['changes']) || !is_array($entry['changes'])) {
                continue;
            }

            foreach ($entry['changes'] as $change) {
                if (($change['field'] ?? '') !== 'leadgen') {
                    continue;
                }

                $leadgen_id = $change['value']['leadgen_id'] ?? null;
                
                if (!$leadgen_id) {
                    continue;
                }

                // Check for test lead
                if ($leadgen_id === '444444444444') {
                    $this->process_test_lead($page_id);
                    continue;
                }

                // Fetch and process real lead
                $this->fetch_and_process_lead($leadgen_id, $page_id);
            }
        }
    }

    /**
     * Fetch lead data from Facebook and process it
     * 
     * Uses the page's permanent access token when available (preferred),
     * falls back to the global user token.
     */
    private function fetch_and_process_lead(string $leadgen_id, ?string $page_id): void
    {
        $fb_api = $this->get_fb_api();
        $sync_manager = $this->get_sync_manager();
        
        // Try to get the page's permanent access token (never expires)
        $page_token = null;
        if ($page_id && $this->db->table_exists(db_prefix() . 'fb_leads_pages')) {
            $page_row = $this->db->where('page_id', $page_id)->get(db_prefix() . 'fb_leads_pages')->row();
            if ($page_row && !empty($page_row->access_token)) {
                $page_token = $page_row->access_token;
            }
        }
        
        $lead_data = $fb_api->get_lead_data($leadgen_id, $page_token);

        if ($lead_data === false) {
            $this->log_activity('error', 'Failed to fetch lead data', [
                'leadgen_id' => $leadgen_id,
                'error' => $fb_api->get_last_error()
            ]);
            
            // Add to retry queue
            $sync_manager->add_to_retry_queue(
                ['id' => $leadgen_id],
                ['page_id' => $page_id],
                'Failed to fetch lead data: ' . ($fb_api->get_last_error()['message'] ?? 'Unknown error')
            );
            return;
        }

        // Process the lead
        $result = $sync_manager->process_lead($lead_data, ['page_id' => $page_id]);

        // Update page stats
        if ($result['success'] && $page_id) {
            $this->update_page_stats($page_id);
        }
    }

    /**
     * Process a test lead from Facebook
     */
    private function process_test_lead(?string $page_id): void
    {
        $result = $this->get_sync_manager()->process_test_lead();
        
        $this->log_activity('info', 'Test lead processed', [
            'page_id' => $page_id,
            'result' => $result
        ]);
    }

    /**
     * Update page statistics after lead received
     */
    private function update_page_stats(string $page_id): void
    {
        if (!$this->db->table_exists(db_prefix() . 'fb_leads_pages')) {
            return;
        }

        $this->db->where('page_id', $page_id);
        $this->db->set('leads_count', 'leads_count + 1', false);
        $this->db->set('last_lead_at', date('Y-m-d H:i:s'));
        $this->db->update(db_prefix() . 'fb_leads_pages');
    }

    // =========================================================================
    // AJAX API ENDPOINTS
    // =========================================================================

    /**
     * Save settings via AJAX
     */
    public function save_settings()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!has_permission('settings', '', 'edit')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            return;
        }

        $settings = $this->input->post('settings');
        
        if (!is_array($settings)) {
            echo json_encode(['success' => false, 'message' => 'Invalid settings data']);
            return;
        }

        $allowed_settings = [
            'fb_leads_app_id',
            'fb_leads_app_secret',
            'fb_leads_webhook_token',
            'fb_leads_default_assigned',
            'fb_leads_default_source',
            'fb_leads_default_status',
            'fb_leads_duplicate_detection',
            'fb_leads_duplicate_fields',
            'fb_leads_notifications_enabled',
            'fb_leads_notify_staff',
            'fb_leads_max_retries'
        ];

        foreach ($settings as $key => $value) {
            if (in_array($key, $allowed_settings)) {
                $value = $this->security->xss_clean($value);
                update_option($key, $value);
            }
        }

        // Reset webhook verification if app credentials changed
        if (isset($settings['fb_leads_app_id']) || isset($settings['fb_leads_app_secret'])) {
            update_option('fb_leads_webhook_verified', '0');
        }

        $this->log_activity('info', 'Settings updated', [
            'updated_by' => get_staff_user_id()
        ]);

        echo json_encode(['success' => true, 'message' => _l('settings_updated')]);
    }

    /**
     * Test connection to Facebook
     */
    public function test_connection()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        try {
            $fb_api = $this->get_fb_api();
            
            $results = [
                'credentials' => $fb_api->validate_credentials(),
                'access_token' => $fb_api->validate_access_token(),
                'permissions' => $fb_api->check_permissions()
            ];

            echo json_encode([
                'success' => $results['credentials']['valid'],
                'results' => $results
            ]);
        } catch (Exception $e) {
            log_message('error', 'FB Leads test_connection error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'results' => [
                    'credentials' => ['valid' => false, 'message' => $e->getMessage()],
                    'access_token' => ['valid' => false],
                    'permissions' => []
                ]
            ]);
        }
    }

    /**
     * Exchange short-lived token for long-lived token (server-side)
     */
    public function exchange_token()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $short_token = $this->input->post('token');
        
        if (empty($short_token)) {
            echo json_encode(['success' => false, 'message' => 'Token required']);
            return;
        }

        $fb_api = $this->get_fb_api();
        $result = $fb_api->exchange_token($short_token);

        if ($result === false) {
            echo json_encode([
                'success' => false,
                'message' => $fb_api->get_last_error()['message'] ?? 'Token exchange failed'
            ]);
            return;
        }

        // Save the long-lived token
        update_option('fb_leads_access_token', $result['access_token']);
        update_option('fb_leads_token_expires', $result['expires_in'] ? date('Y-m-d H:i:s', time() + $result['expires_in']) : null);

        $this->log_activity('info', 'Access token exchanged and saved', [
            'expires_in' => $result['expires_in']
        ]);

        echo json_encode([
            'success' => true,
            'message' => _l('fb_leads_token_saved'),
            'expires_in' => $result['expires_in']
        ]);
    }

    /**
     * Fetch Facebook pages for the authenticated user
     */
    public function fetch_pages()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $user_token = $this->input->post('user_token');
        
        if (empty($user_token)) {
            // Try using the stored access token
            $user_token = get_option('fb_leads_access_token');
        }

        if (empty($user_token)) {
            echo json_encode(['success' => false, 'message' => 'Access token required']);
            return;
        }

        $fb_api = $this->get_fb_api();
        $pages = $fb_api->get_user_pages($user_token);

        if ($pages === false) {
            echo json_encode([
                'success' => false,
                'message' => $fb_api->get_last_error()['message'] ?? 'Failed to fetch pages'
            ]);
            return;
        }

        // Save pages to database
        $this->save_pages_to_db($pages);

        echo json_encode([
            'success' => true,
            'pages' => $pages,
            'html' => $this->render_pages_table($pages)
        ]);
    }

    /**
     * Save pages to database
     */
    private function save_pages_to_db(array $pages): void
    {
        if (!$this->db->table_exists(db_prefix() . 'fb_leads_pages')) {
            return;
        }

        foreach ($pages as $page) {
            $existing = $this->db->where('page_id', $page['id'])->get(db_prefix() . 'fb_leads_pages')->row();

            if ($existing) {
                $this->db->where('id', $existing->id);
                $this->db->update(db_prefix() . 'fb_leads_pages', [
                    'page_name' => $page['name'],
                    'access_token' => $page['access_token'] ?? null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->insert(db_prefix() . 'fb_leads_pages', [
                    'page_id' => $page['id'],
                    'page_name' => $page['name'],
                    'access_token' => $page['access_token'] ?? null,
                    'is_subscribed' => 0,
                    'leads_count' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    /**
     * Render pages table HTML
     */
    private function render_pages_table(array $pages): string
    {
        if (empty($pages)) {
            return '<div class="alert alert-info">' . _l('fb_leads_no_pages_found') . '</div>';
        }

        // Get subscribed pages with their settings
        $page_settings = [];
        if ($this->db->table_exists(db_prefix() . 'fb_leads_pages')) {
            $result = $this->db->get(db_prefix() . 'fb_leads_pages')->result();
            foreach ($result as $row) {
                $page_settings[$row->page_id] = $row;
            }
        }

        // Get staff for dropdown
        $staff = $this->staff_model->get('', ['active' => 1]);

        $html = '<table class="table table-striped table-bordered" id="pages-table">
            <thead>
                <tr>
                    <th>' . _l('page_name') . '</th>
                    <th>' . _l('fb_leads_status') . '</th>
                    <th>' . _l('fb_leads_page_assigned_to') . '</th>
                    <th class="text-center">' . _l('action') . '</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($pages as $page) {
            $settings = isset($page_settings[$page['id']]) ? $page_settings[$page['id']] : null;
            $is_subscribed = $settings && $settings->is_subscribed;
            $assigned_to = $settings ? ($settings->assigned_to ?? '') : '';
            $status_class = $is_subscribed ? 'success' : 'default';
            $status_text = $is_subscribed ? _l('fb_leads_monitoring') : _l('fb_leads_not_monitoring');
            $btn_class = $is_subscribed ? 'btn-danger' : 'btn-success';
            $btn_text = $is_subscribed ? _l('fb_leads_unsubscribe') : _l('fb_leads_subscribe');
            $action = $is_subscribed ? 'unsubscribe' : 'subscribe';

            // Build staff dropdown
            $staff_options = '<option value="">' . _l('fb_leads_use_default') . '</option>';
            foreach ($staff as $member) {
                $selected = ($assigned_to == $member['staffid']) ? ' selected' : '';
                $staff_options .= '<option value="' . $member['staffid'] . '"' . $selected . '>' 
                    . htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) . '</option>';
            }

            $html .= '<tr data-page-id="' . htmlspecialchars($page['id']) . '">
                <td>
                    <strong>' . htmlspecialchars($page['name']) . '</strong>
                    <br><small class="text-muted">ID: ' . htmlspecialchars($page['id']) . '</small>
                </td>
                <td>
                    <span class="label label-' . $status_class . '">' . $status_text . '</span>
                </td>
                <td>
                    <select class="form-control input-sm page-assigned-select" 
                            data-page-id="' . htmlspecialchars($page['id']) . '" 
                            style="min-width: 140px;">
                        ' . $staff_options . '
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" 
                            class="btn ' . $btn_class . ' btn-sm page-action-btn" 
                            data-page-id="' . htmlspecialchars($page['id']) . '"
                            data-action="' . $action . '">
                        ' . $btn_text . '
                    </button>
                </td>
            </tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Update per-page settings (assigned staff, source, status)
     */
    public function update_page_settings()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $page_id = $this->input->post('page_id');
        if (empty($page_id)) {
            echo json_encode(['success' => false, 'message' => 'Page ID required']);
            return;
        }

        $update_data = ['updated_at' => date('Y-m-d H:i:s')];

        $assigned_to = $this->input->post('assigned_to');
        $update_data['assigned_to'] = !empty($assigned_to) ? (int) $assigned_to : null;

        if ($this->input->post('default_source') !== null) {
            $default_source = $this->input->post('default_source');
            $update_data['default_source'] = !empty($default_source) ? (int) $default_source : null;
        }

        if ($this->input->post('default_status') !== null) {
            $default_status = $this->input->post('default_status');
            $update_data['default_status'] = !empty($default_status) ? (int) $default_status : null;
        }

        $this->db->where('page_id', $page_id);
        $this->db->update(db_prefix() . 'fb_leads_pages', $update_data);

        echo json_encode(['success' => true, 'message' => _l('fb_leads_page_settings_saved')]);
    }

    /**
     * Subscribe page to leadgen webhook
     */
    public function subscribe_page()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $page_id = $this->input->post('page_id');
        
        if (empty($page_id)) {
            echo json_encode(['success' => false, 'message' => 'Page ID required']);
            return;
        }

        // Get page access token from database
        $this->db->where('page_id', $page_id);
        $page = $this->db->get(db_prefix() . 'fb_leads_pages')->row();

        if (!$page || empty($page->access_token)) {
            echo json_encode(['success' => false, 'message' => 'Page not found or missing access token']);
            return;
        }

        // Subscribe via Facebook API
        $fb_api = $this->get_fb_api();
        $success = $fb_api->subscribe_page_to_leadgen($page_id, $page->access_token);

        if (!$success) {
            echo json_encode([
                'success' => false,
                'message' => $fb_api->get_last_error()['message'] ?? 'Failed to subscribe page'
            ]);
            return;
        }

        // Update database
        $this->db->where('page_id', $page_id);
        $this->db->update(db_prefix() . 'fb_leads_pages', [
            'is_subscribed' => 1,
            'subscribed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->log_activity('info', 'Page subscribed to leadgen', [
            'page_id' => $page_id,
            'page_name' => $page->page_name
        ]);

        echo json_encode([
            'success' => true,
            'message' => _l('fb_leads_page_subscribed')
        ]);
    }

    /**
     * Unsubscribe page from leadgen webhook
     */
    public function unsubscribe_page()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $page_id = $this->input->post('page_id');
        
        if (empty($page_id)) {
            echo json_encode(['success' => false, 'message' => 'Page ID required']);
            return;
        }

        // Get page access token from database
        $this->db->where('page_id', $page_id);
        $page = $this->db->get(db_prefix() . 'fb_leads_pages')->row();

        if (!$page || empty($page->access_token)) {
            echo json_encode(['success' => false, 'message' => 'Page not found or missing access token']);
            return;
        }

        // Unsubscribe via Facebook API
        $fb_api = $this->get_fb_api();
        $success = $fb_api->unsubscribe_page_from_leadgen($page_id, $page->access_token);

        if (!$success) {
            echo json_encode([
                'success' => false,
                'message' => $fb_api->get_last_error()['message'] ?? 'Failed to unsubscribe page'
            ]);
            return;
        }

        // Update database
        $this->db->where('page_id', $page_id);
        $this->db->update(db_prefix() . 'fb_leads_pages', [
            'is_subscribed' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->log_activity('info', 'Page unsubscribed from leadgen', [
            'page_id' => $page_id,
            'page_name' => $page->page_name
        ]);

        echo json_encode([
            'success' => true,
            'message' => _l('fb_leads_page_unsubscribed')
        ]);
    }

    /**
     * Process test lead manually
     */
    public function send_test_lead()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $result = $this->get_sync_manager()->process_test_lead();

        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message'],
            'lead_id' => $result['lead_id'],
            'lead_url' => $result['lead_id'] ? admin_url('leads/index/' . $result['lead_id']) : null
        ]);
    }

    /**
     * Save field mapping
     */
    public function save_field_mapping()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $mappings = $this->input->post('mappings');
        
        if (!is_array($mappings)) {
            echo json_encode(['success' => false, 'message' => 'Invalid mappings data']);
            return;
        }

        // Clear existing mappings
        $this->db->truncate(db_prefix() . 'fb_leads_field_mappings');

        // Insert new mappings
        foreach ($mappings as $mapping) {
            if (empty($mapping['facebook_field']) || empty($mapping['perfex_field'])) {
                continue;
            }

            $this->db->insert(db_prefix() . 'fb_leads_field_mappings', [
                'facebook_field' => $this->security->xss_clean($mapping['facebook_field']),
                'perfex_field' => $this->security->xss_clean($mapping['perfex_field']),
                'is_custom_field' => isset($mapping['is_custom_field']) && $mapping['is_custom_field'] ? 1 : 0,
                'custom_field_id' => !empty($mapping['custom_field_id']) ? (int) $mapping['custom_field_id'] : null,
                'form_id' => !empty($mapping['form_id']) ? $this->security->xss_clean($mapping['form_id']) : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Also save as JSON option for quick access
        update_option('fb_leads_field_mappings', json_encode($mappings));

        echo json_encode(['success' => true, 'message' => _l('fb_leads_mappings_saved')]);
    }

    /**
     * Process retry queue
     */
    public function process_retry_queue()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $results = $this->get_sync_manager()->process_retry_queue(10);

        echo json_encode([
            'success' => true,
            'results' => $results,
            'message' => sprintf(
                _l('fb_leads_retry_results'),
                $results['processed'],
                $results['succeeded'],
                $results['failed']
            )
        ]);
    }

    /**
     * Get sync statistics
     */
    public function get_stats()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $period = $this->input->get('period') ?: 'month';
        $stats = $this->get_sync_manager()->get_sync_stats($period);

        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Complete setup wizard
     */
    public function complete_setup()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        update_option('fb_leads_setup_completed', '1');
        update_option('fb_leads_setup_completed_at', date('Y-m-d H:i:s'));

        echo json_encode([
            'success' => true,
            'redirect' => admin_url('facebookleadsintegration')
        ]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Log webhook request for debugging
     */
    private function log_webhook_request(): void
    {
        if (!$this->db->table_exists(db_prefix() . 'fb_leads_logs')) {
            return;
        }

        $this->db->insert(db_prefix() . 'fb_leads_logs', [
            'log_type' => 'webhook',
            'message' => 'Webhook request received',
            'details' => json_encode([
                'method' => $this->input->method(),
                'query_string' => $_SERVER['QUERY_STRING'] ?? '',
                'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 0
            ]),
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Log activity
     */
    private function log_activity(string $type, string $message, array $details = []): void
    {
        if (!$this->db->table_exists(db_prefix() . 'fb_leads_logs')) {
            return;
        }

        $this->db->insert(db_prefix() . 'fb_leads_logs', [
            'log_type' => $type,
            'message' => $message,
            'details' => json_encode($details),
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
