<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Facebook & Instagram Leads Sync Manager
 * 
 * Handles lead processing, duplicate detection, sync history,
 * retry queue, field mapping, and notifications.
 * 
 * @package    FacebookLeadsIntegration
 * @author     Themesic Interactive
 * @version    2.0.0
 */
class Fb_leads_sync_manager
{
    /**
     * @var CI_Controller CodeIgniter instance
     */
    private $CI;
    
    /**
     * @var array Standard Perfex CRM lead fields
     */
    private const STANDARD_FIELDS = [
        'name',
        'address',
        'title',
        'city',
        'email',
        'state',
        'website',
        'country',
        'phonenumber',
        'zip',
        'company',
        'default_language',
        'description'
    ];
    
    /**
     * @var array Facebook field name to Perfex field name mapping
     */
    private const FIELD_ALIASES = [
        'full_name' => 'name',
        'first_name' => 'name',
        'last_name' => 'name',
        'phone_number' => 'phonenumber',
        'phone' => 'phonenumber',
        'street_address' => 'address',
        'city' => 'city',
        'state' => 'state',
        'province' => 'state',
        'zip_code' => 'zip',
        'postal_code' => 'zip',
        'post_code' => 'zip',
        'country' => 'country',
        'email' => 'email',
        'email_address' => 'email',
        'company_name' => 'company',
        'company' => 'company',
        'organization' => 'company',
        'job_title' => 'title',
        'position' => 'title',
        'website' => 'website',
        'url' => 'website'
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('leads_model');
    }

    /**
     * Process incoming lead from Facebook webhook
     * 
     * @param array $lead_data Lead data from Facebook Graph API
     * @param array $meta Meta information (page_id, form_id, etc.)
     * @return array ['success' => bool, 'lead_id' => int|null, 'message' => string, 'action' => string]
     */
    public function process_lead(array $lead_data, array $meta = []): array
    {
        // Extract field data from Facebook format
        $parsed_data = $this->parse_facebook_lead_data($lead_data);
        
        // Check for duplicates
        $duplicate_check = $this->check_duplicate($parsed_data);
        if ($duplicate_check['is_duplicate']) {
            $this->log_sync(
                $lead_data['id'] ?? 'unknown',
                null,
                'skipped',
                'Duplicate lead detected',
                [
                    'duplicate_of' => $duplicate_check['existing_lead_id'],
                    'match_field' => $duplicate_check['match_field']
                ]
            );
            
            return [
                'success' => true,
                'lead_id' => $duplicate_check['existing_lead_id'],
                'message' => 'Duplicate lead - already exists',
                'action' => 'skipped'
            ];
        }
        
        // Apply field mapping
        $mapped_data = $this->apply_field_mapping($parsed_data);
        
        // Add default values (with per-page overrides)
        $mapped_data = $this->apply_defaults($mapped_data, $meta);
        
        // Add meta fields
        $mapped_data['dateadded'] = date('Y-m-d H:i:s');
        $mapped_data['addedfrom'] = 0; // System import
        $mapped_data['is_public'] = 0;
        
        // Handle country lookup
        if (isset($mapped_data['country']) && !empty($mapped_data['country']) && !is_numeric($mapped_data['country'])) {
            $mapped_data['country'] = $this->lookup_country_id($mapped_data['country']);
        }
        if (empty($mapped_data['country'])) {
            $mapped_data['country'] = 0;
        }
        
        // Extract custom fields before inserting main lead data
        $custom_fields_data = [];
        foreach ($parsed_data as $field_name => $value) {
            if (!in_array($field_name, self::STANDARD_FIELDS) && !isset($mapped_data[$field_name])) {
                // Check if it's a custom field
                $custom_field = $this->get_custom_field_by_slug($field_name);
                if ($custom_field) {
                    $custom_fields_data[$custom_field['id']] = $value;
                }
            }
        }
        
        try {
            // Insert lead into database
            $this->CI->db->insert(db_prefix() . 'leads', $mapped_data);
            $lead_id = $this->CI->db->insert_id();
            
            if (!$lead_id) {
                throw new Exception('Failed to insert lead into database');
            }
            
            // Handle custom fields
            if (!empty($custom_fields_data)) {
                $this->save_custom_fields($lead_id, $custom_fields_data);
            }
            
            // Log successful sync
            $this->log_sync(
                $lead_data['id'] ?? 'unknown',
                $lead_id,
                'success',
                'Lead successfully imported',
                [
                    'page_id' => $meta['page_id'] ?? null,
                    'form_id' => $lead_data['form_id'] ?? null,
                    'campaign_id' => $lead_data['campaign_id'] ?? null
                ]
            );
            
            // Send notifications
            $this->send_new_lead_notification($lead_id, $mapped_data);
            
            return [
                'success' => true,
                'lead_id' => $lead_id,
                'message' => 'Lead successfully imported',
                'action' => 'created'
            ];
            
        } catch (Exception $e) {
            // Log failed sync and add to retry queue
            $this->log_sync(
                $lead_data['id'] ?? 'unknown',
                null,
                'failed',
                $e->getMessage(),
                ['raw_data' => $lead_data]
            );
            
            $this->add_to_retry_queue($lead_data, $meta, $e->getMessage());
            
            return [
                'success' => false,
                'lead_id' => null,
                'message' => 'Failed to import lead: ' . $e->getMessage(),
                'action' => 'failed'
            ];
        }
    }

    /**
     * Parse Facebook lead data format into flat array
     * 
     * @param array $lead_data
     * @return array
     */
    private function parse_facebook_lead_data(array $lead_data): array
    {
        $parsed = [];
        
        if (isset($lead_data['field_data']) && is_array($lead_data['field_data'])) {
            foreach ($lead_data['field_data'] as $field) {
                $name = strtolower(trim($field['name'] ?? ''));
                $value = $field['values'][0] ?? '';
                
                if (!empty($name)) {
                    $parsed[$name] = is_array($value) ? implode(', ', $value) : $value;
                }
            }
        }
        
        // Store Facebook lead ID for reference
        if (isset($lead_data['id'])) {
            $parsed['_facebook_lead_id'] = $lead_data['id'];
        }
        if (isset($lead_data['created_time'])) {
            $parsed['_facebook_created_time'] = $lead_data['created_time'];
        }
        
        return $parsed;
    }

    /**
     * Apply field mapping (standard fields + custom mappings)
     * 
     * @param array $data
     * @return array
     */
    private function apply_field_mapping(array $data): array
    {
        $mapped = [];
        $custom_mappings = $this->get_custom_field_mappings();
        
        foreach ($data as $fb_field => $value) {
            // Skip internal fields
            if (strpos($fb_field, '_') === 0) {
                continue;
            }
            
            // Check custom mappings first
            if (isset($custom_mappings[$fb_field])) {
                $perfex_field = $custom_mappings[$fb_field];
                $mapped[$perfex_field] = $this->merge_field_value($mapped[$perfex_field] ?? '', $value, $perfex_field);
                continue;
            }
            
            // Check standard aliases
            if (isset(self::FIELD_ALIASES[$fb_field])) {
                $perfex_field = self::FIELD_ALIASES[$fb_field];
                $mapped[$perfex_field] = $this->merge_field_value($mapped[$perfex_field] ?? '', $value, $perfex_field);
                continue;
            }
            
            // Check if it's a standard field
            if (in_array($fb_field, self::STANDARD_FIELDS)) {
                $mapped[$fb_field] = $this->merge_field_value($mapped[$fb_field] ?? '', $value, $fb_field);
                continue;
            }
            
            // Store unmatched field for custom field processing
            $mapped[$fb_field] = $value;
        }
        
        return $mapped;
    }

    /**
     * Merge field values (for name field combining first_name + last_name, etc.)
     * 
     * @param string $existing
     * @param string $new
     * @param string $field_name
     * @return string
     */
    private function merge_field_value(string $existing, string $new, string $field_name): string
    {
        if (empty($existing)) {
            return $new;
        }
        
        // For name field, concatenate with space
        if ($field_name === 'name') {
            return trim($existing . ' ' . $new);
        }
        
        // For other fields, prefer non-empty new value
        return !empty($new) ? $new : $existing;
    }

    /**
     * Apply default values from settings, with per-page overrides
     * 
     * Priority: Per-page setting > Global default setting
     * 
     * @param array $data
     * @param array $meta Meta information (page_id, form_id, etc.)
     * @return array
     */
    private function apply_defaults(array $data, array $meta = []): array
    {
        // Try to get per-page settings if page_id is available
        $page_settings = null;
        if (!empty($meta['page_id']) && $meta['page_id'] !== 'test') {
            if ($this->CI->db->table_exists(db_prefix() . 'fb_leads_pages')) {
                $page_settings = $this->CI->db->where('page_id', $meta['page_id'])
                    ->get(db_prefix() . 'fb_leads_pages')
                    ->row();
            }
        }
        
        // Apply assigned staff: per-page > global default
        $assigned = null;
        if ($page_settings && !empty($page_settings->assigned_to)) {
            $assigned = (int) $page_settings->assigned_to;
        } else {
            $assigned = get_option('fb_leads_default_assigned');
        }
        if (!empty($assigned)) {
            $data['assigned'] = (int) $assigned;
        }
        
        // Apply source: per-page > global default
        $source = null;
        if ($page_settings && !empty($page_settings->default_source)) {
            $source = (int) $page_settings->default_source;
        } else {
            $source = get_option('fb_leads_default_source');
        }
        if (!empty($source)) {
            $data['source'] = (int) $source;
        }
        
        // Apply status: per-page > global default
        $status = null;
        if ($page_settings && !empty($page_settings->default_status)) {
            $status = (int) $page_settings->default_status;
        } else {
            $status = get_option('fb_leads_default_status');
        }
        if (!empty($status)) {
            $data['status'] = (int) $status;
        }
        
        return $data;
    }

    /**
     * Check if lead is a duplicate
     * 
     * @param array $data
     * @return array ['is_duplicate' => bool, 'existing_lead_id' => int|null, 'match_field' => string|null]
     */
    public function check_duplicate(array $data): array
    {
        if (get_option('fb_leads_duplicate_detection') !== '1') {
            return ['is_duplicate' => false, 'existing_lead_id' => null, 'match_field' => null];
        }
        
        $detection_fields = get_option('fb_leads_duplicate_fields');
        $detection_fields = !empty($detection_fields) ? json_decode($detection_fields, true) : ['email'];
        
        foreach ($detection_fields as $field) {
            $normalized_field = self::FIELD_ALIASES[$field] ?? $field;
            
            if (!empty($data[$normalized_field])) {
                $this->CI->db->where($normalized_field, $data[$normalized_field]);
                $existing = $this->CI->db->get(db_prefix() . 'leads')->row();
                
                if ($existing) {
                    return [
                        'is_duplicate' => true,
                        'existing_lead_id' => $existing->id,
                        'match_field' => $normalized_field
                    ];
                }
            }
        }
        
        // Also check by Facebook lead ID if stored
        if (!empty($data['_facebook_lead_id'])) {
            $this->CI->db->where('facebook_lead_id', $data['_facebook_lead_id']);
            $this->CI->db->or_where('description', 'LIKE', '%' . $data['_facebook_lead_id'] . '%');
            $existing = $this->CI->db->get(db_prefix() . 'leads')->row();
            
            if ($existing) {
                return [
                    'is_duplicate' => true,
                    'existing_lead_id' => $existing->id,
                    'match_field' => 'facebook_lead_id'
                ];
            }
        }
        
        return ['is_duplicate' => false, 'existing_lead_id' => null, 'match_field' => null];
    }

    /**
     * Look up country ID by name
     * 
     * @param string $country_name
     * @return int
     */
    private function lookup_country_id(string $country_name): int
    {
        $this->CI->db->select('country_id');
        $this->CI->db->where('LOWER(short_name)', strtolower(trim($country_name)));
        $this->CI->db->or_where('LOWER(long_name)', strtolower(trim($country_name)));
        $this->CI->db->or_where('LOWER(iso2)', strtolower(trim($country_name)));
        $this->CI->db->or_where('LOWER(iso3)', strtolower(trim($country_name)));
        $result = $this->CI->db->get(db_prefix() . 'countries')->row();
        
        return $result ? (int) $result->country_id : 0;
    }

    /**
     * Get custom field by slug
     * 
     * @param string $slug
     * @return array|null
     */
    private function get_custom_field_by_slug(string $slug): ?array
    {
        $this->CI->db->where('fieldto', 'leads');
        $this->CI->db->where('slug', $slug);
        $field = $this->CI->db->get(db_prefix() . 'customfields')->row_array();
        
        return $field ?: null;
    }

    /**
     * Save custom fields for a lead
     * 
     * @param int $lead_id
     * @param array $custom_fields_data ['field_id' => 'value']
     * @return void
     */
    private function save_custom_fields(int $lead_id, array $custom_fields_data): void
    {
        foreach ($custom_fields_data as $field_id => $value) {
            $this->CI->db->insert(db_prefix() . 'customfieldsvalues', [
                'relid' => $lead_id,
                'fieldid' => $field_id,
                'fieldto' => 'leads',
                'value' => $value
            ]);
        }
    }

    /**
     * Get custom field mappings from settings
     * 
     * @return array
     */
    private function get_custom_field_mappings(): array
    {
        $mappings = get_option('fb_leads_field_mappings');
        return !empty($mappings) ? json_decode($mappings, true) : [];
    }

    /**
     * Log sync activity
     * 
     * @param string $facebook_lead_id
     * @param int|null $perfex_lead_id
     * @param string $status success|failed|skipped|retry
     * @param string $message
     * @param array $details
     * @return int|false Insert ID or false on failure
     */
    public function log_sync(
        string $facebook_lead_id,
        ?int $perfex_lead_id,
        string $status,
        string $message,
        array $details = []
    ) {
        if (!$this->CI->db->table_exists(db_prefix() . 'fb_leads_sync_history')) {
            return false;
        }
        
        $this->CI->db->insert(db_prefix() . 'fb_leads_sync_history', [
            'facebook_lead_id' => $facebook_lead_id,
            'perfex_lead_id' => $perfex_lead_id,
            'status' => $status,
            'message' => $message,
            'details' => json_encode($details),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->CI->db->insert_id();
    }

    /**
     * Add failed lead to retry queue
     * 
     * @param array $lead_data
     * @param array $meta
     * @param string $error_message
     * @return int|false
     */
    public function add_to_retry_queue(array $lead_data, array $meta, string $error_message)
    {
        if (!$this->CI->db->table_exists(db_prefix() . 'fb_leads_retry_queue')) {
            return false;
        }
        
        // Check if already in queue
        $this->CI->db->where('facebook_lead_id', $lead_data['id'] ?? '');
        $this->CI->db->where('status', 'pending');
        $existing = $this->CI->db->get(db_prefix() . 'fb_leads_retry_queue')->row();
        
        if ($existing) {
            // Update retry count
            $this->CI->db->where('id', $existing->id);
            $this->CI->db->update(db_prefix() . 'fb_leads_retry_queue', [
                'retry_count' => $existing->retry_count + 1,
                'last_error' => $error_message,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return $existing->id;
        }
        
        $this->CI->db->insert(db_prefix() . 'fb_leads_retry_queue', [
            'facebook_lead_id' => $lead_data['id'] ?? '',
            'lead_data' => json_encode($lead_data),
            'meta_data' => json_encode($meta),
            'status' => 'pending',
            'retry_count' => 0,
            'last_error' => $error_message,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->CI->db->insert_id();
    }

    /**
     * Process retry queue
     * 
     * @param int $limit Maximum items to process
     * @return array ['processed' => int, 'succeeded' => int, 'failed' => int]
     */
    public function process_retry_queue(int $limit = 10): array
    {
        $results = ['processed' => 0, 'succeeded' => 0, 'failed' => 0];
        
        if (!$this->CI->db->table_exists(db_prefix() . 'fb_leads_retry_queue')) {
            return $results;
        }
        
        $max_retries = (int) get_option('fb_leads_max_retries') ?: 5;
        
        $this->CI->db->where('status', 'pending');
        $this->CI->db->where('retry_count <', $max_retries);
        $this->CI->db->order_by('created_at', 'ASC');
        $this->CI->db->limit($limit);
        $queue_items = $this->CI->db->get(db_prefix() . 'fb_leads_retry_queue')->result();
        
        foreach ($queue_items as $item) {
            $results['processed']++;
            
            $lead_data = json_decode($item->lead_data, true);
            $meta_data = json_decode($item->meta_data, true);
            
            $process_result = $this->process_lead($lead_data, $meta_data);
            
            if ($process_result['success']) {
                // Mark as completed
                $this->CI->db->where('id', $item->id);
                $this->CI->db->update(db_prefix() . 'fb_leads_retry_queue', [
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $results['succeeded']++;
            } else {
                // Increment retry count
                $new_count = $item->retry_count + 1;
                $new_status = $new_count >= $max_retries ? 'failed' : 'pending';
                
                $this->CI->db->where('id', $item->id);
                $this->CI->db->update(db_prefix() . 'fb_leads_retry_queue', [
                    'status' => $new_status,
                    'retry_count' => $new_count,
                    'last_error' => $process_result['message'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $results['failed']++;
            }
        }
        
        return $results;
    }

    /**
     * Send notification for new lead
     * 
     * @param int $lead_id
     * @param array $lead_data
     * @return void
     */
    public function send_new_lead_notification(int $lead_id, array $lead_data): void
    {
        if (get_option('fb_leads_notifications_enabled') !== '1') {
            return;
        }
        
        $notify_staff = get_option('fb_leads_notify_staff');
        if (empty($notify_staff)) {
            // Notify assigned staff member
            $notify_staff = $lead_data['assigned'] ?? get_option('fb_leads_default_assigned');
        }
        
        if (empty($notify_staff)) {
            return;
        }
        
        // Load email library
        $this->CI->load->library('email');
        $this->CI->load->model('staff_model');
        
        $staff_ids = is_array($notify_staff) ? $notify_staff : explode(',', $notify_staff);
        
        foreach ($staff_ids as $staff_id) {
            $staff = $this->CI->staff_model->get((int) $staff_id);
            if (!$staff || empty($staff->email)) {
                continue;
            }
            
            $lead_name = $lead_data['name'] ?? 'Unknown';
            $lead_email = $lead_data['email'] ?? 'N/A';
            $lead_phone = $lead_data['phonenumber'] ?? 'N/A';
            $lead_company = $lead_data['company'] ?? 'N/A';
            
            $subject = _l('fb_leads_notification_subject', $lead_name);
            $message = _l('fb_leads_notification_body', [
                $lead_name,
                $lead_email,
                $lead_phone,
                $lead_company,
                admin_url('leads/index/' . $lead_id)
            ]);
            
            // Send via Perfex mail system
            send_mail_template('fb_leads_new_lead', $staff->email, $staff_id, [
                'lead_id' => $lead_id,
                'lead_name' => $lead_name,
                'lead_email' => $lead_email,
                'lead_phone' => $lead_phone,
                'lead_company' => $lead_company,
                'lead_url' => admin_url('leads/index/' . $lead_id)
            ]);
        }
    }

    /**
     * Get sync statistics
     * 
     * @param string $period day|week|month|all
     * @return array
     */
    public function get_sync_stats(string $period = 'all'): array
    {
        $stats = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'pending_retry' => 0,
            'last_sync' => null
        ];
        
        if (!$this->CI->db->table_exists(db_prefix() . 'fb_leads_sync_history')) {
            return $stats;
        }
        
        // Date filter
        $date_filter = null;
        switch ($period) {
            case 'day':
                $date_filter = date('Y-m-d 00:00:00');
                break;
            case 'week':
                $date_filter = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
                $date_filter = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
        }
        
        // Get counts by status
        $this->CI->db->select('status, COUNT(*) as count');
        if ($date_filter) {
            $this->CI->db->where('created_at >=', $date_filter);
        }
        $this->CI->db->group_by('status');
        $results = $this->CI->db->get(db_prefix() . 'fb_leads_sync_history')->result();
        
        foreach ($results as $row) {
            $stats['total'] += $row->count;
            $stats[$row->status] = (int) $row->count;
        }
        
        // Get pending retry count
        if ($this->CI->db->table_exists(db_prefix() . 'fb_leads_retry_queue')) {
            $this->CI->db->where('status', 'pending');
            $stats['pending_retry'] = $this->CI->db->count_all_results(db_prefix() . 'fb_leads_retry_queue');
        }
        
        // Get last sync time
        $this->CI->db->select('created_at');
        $this->CI->db->order_by('created_at', 'DESC');
        $this->CI->db->limit(1);
        $last = $this->CI->db->get(db_prefix() . 'fb_leads_sync_history')->row();
        $stats['last_sync'] = $last ? $last->created_at : null;
        
        return $stats;
    }

    /**
     * Get sync history with pagination
     * 
     * @param int $page
     * @param int $per_page
     * @param array $filters
     * @return array ['items' => array, 'total' => int, 'pages' => int]
     */
    public function get_sync_history(int $page = 1, int $per_page = 20, array $filters = []): array
    {
        $table = db_prefix() . 'fb_leads_sync_history';
        
        if (!$this->CI->db->table_exists($table)) {
            return ['items' => [], 'total' => 0, 'pages' => 0];
        }
        
        // Build filter conditions
        $where = [];
        if (!empty($filters['status'])) {
            $where['status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where['created_at >='] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where['created_at <='] = $filters['date_to'];
        }
        
        // Get total count with filters
        if (!empty($where)) {
            $this->CI->db->where($where);
        }
        $total = $this->CI->db->count_all_results($table);
        
        // Get paginated results (re-apply filters since count_all_results resets query)
        if (!empty($where)) {
            $this->CI->db->where($where);
        }
        $offset = ($page - 1) * $per_page;
        $this->CI->db->order_by('created_at', 'DESC');
        $this->CI->db->limit($per_page, $offset);
        $items = $this->CI->db->get($table)->result_array();
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $per_page)
        ];
    }

    /**
     * Clear old sync history
     * 
     * @param int $days_to_keep
     * @return int Number of deleted records
     */
    public function clear_old_history(int $days_to_keep = 90): int
    {
        if (!$this->CI->db->table_exists(db_prefix() . 'fb_leads_sync_history')) {
            return 0;
        }
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days_to_keep} days"));
        
        $this->CI->db->where('created_at <', $cutoff_date);
        $this->CI->db->delete(db_prefix() . 'fb_leads_sync_history');
        
        return $this->CI->db->affected_rows();
    }

    /**
     * Process a test lead (for testing purposes)
     * 
     * @return array
     */
    public function process_test_lead(): array
    {
        $test_lead_data = [
            'id' => '444444444444',
            'created_time' => date('c'),
            'field_data' => [
                ['name' => 'full_name', 'values' => ['Test Lead from Facebook']],
                ['name' => 'email', 'values' => ['test@facebook-leads.local']],
                ['name' => 'phone_number', 'values' => ['+1234567890']],
                ['name' => 'company_name', 'values' => ['Test Company']],
                ['name' => 'city', 'values' => ['Test City']],
                ['name' => 'country', 'values' => ['United States']]
            ]
        ];
        
        return $this->process_lead($test_lead_data, [
            'source' => 'manual_test',
            'page_id' => 'test'
        ]);
    }
}
