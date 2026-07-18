<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Api.php - API controller for the whatsapp_chat module.
 *
 * Receives normalized POST payloads from n8n and stores them in the DB.
 * This controller extends CI_Controller (not AdminController) so it can
 * be called externally without a Perfex session.
 *
 * Endpoint:  POST /whatsapp_chat/api/message
 * Auth:      Authorization: Bearer <token>   (token stored in Perfex options)
 */
class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Wrap in try-catch to catch any initialization errors
        try {
            // Load the helper explicitly since this controller extends CI_Controller
            // (not AdminController), so the module init file doesn't run for API routes
            $this->load->helper('whatsapp_chat/whatsapp_chat');
            $this->load->model('whatsapp_chat/whatsapp_chat_model');
        } catch (Exception $e) {
            $this->_json_response(500, [
                'success' => false,
                'error'   => 'Model load error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * POST /whatsapp_chat/api/message
     */
    public function message()
    {
        try {
            // Only accept POST
            if ($this->input->server('REQUEST_METHOD') !== 'POST') {
                $this->_json_response(405, [
                    'success' => false,
                    'error'   => 'Method Not Allowed. Use POST.'
                ]);
                return;
            }

            // Token validation
            if (!function_exists('wac_api_token_valid')) {
                $this->_json_response(500, [
                    'success' => false,
                    'error'   => 'Helper not loaded. wac_api_token_valid function missing.'
                ]);
                return;
            }

            if (!wac_api_token_valid()) {
                $this->_json_response(401, [
                    'success' => false,
                    'error'   => 'Unauthorized: invalid or missing token'
                ]);
                return;
            }

            // Parse body - support both raw JSON and form POST
            $raw = file_get_contents('php://input');
            if (!empty($raw)) {
                $payload = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_json_response(400, [
                        'success' => false,
                        'error'   => 'Invalid JSON: ' . json_last_error_msg()
                    ]);
                    return;
                }
            } else {
                $payload = $this->input->post(null, true);
            }

            if (empty($payload)) {
                $this->_json_response(400, [
                    'success' => false,
                    'error'   => 'Empty payload'
                ]);
                return;
            }

            // Validate required fields
            $required = ['message_uid', 'phone', 'direction'];
            $missing  = [];
            foreach ($required as $field) {
                if (!isset($payload[$field]) || $payload[$field] === '') {
                    $missing[] = $field;
                }
            }

            if (!empty($missing)) {
                $this->_json_response(422, [
                    'success' => false,
                    'error'   => 'Missing required fields',
                    'missing' => $missing,
                ]);
                return;
            }

            // Validate direction value
            $payload['direction'] = strtolower(trim($payload['direction']));
            if (!in_array($payload['direction'], ['inbound', 'outbound'], true)) {
                $this->_json_response(422, [
                    'success' => false,
                    'error'   => 'direction must be "inbound" or "outbound"'
                ]);
                return;
            }

            // Validate message_type if provided
            $valid_types = ['text', 'image', 'audio', 'video', 'document', 'location', 'sticker', 'contact'];
            if (isset($payload['message_type']) && !in_array($payload['message_type'], $valid_types, true)) {
                $payload['message_type'] = 'text';
            }

            // For text messages, require message_text
            $msg_type = $payload['message_type'] ?? 'text';
            if ($msg_type === 'text' && empty($payload['message_text'])) {
                $this->_json_response(422, [
                    'success' => false,
                    'error'   => 'message_text is required for text messages'
                ]);
                return;
            }

            // Process and store
            $result = $this->whatsapp_chat_model->process_message($payload);

            // Return response based on result
            if (isset($result['duplicate']) && $result['duplicate']) {
                $this->_json_response(200, [
                    'success'         => true,
                    'duplicate'       => true,
                    'message'         => 'Duplicate message_uid, not inserted',
                    'conversation_id' => $result['conversation_id'] ?? null,
                ]);
            } elseif (isset($result['success']) && $result['success']) {
                $this->_json_response(200, [
                    'success'         => true,
                    'duplicate'       => false,
                    'message'         => 'Message stored',
                    'conversation_id' => $result['conversation_id'] ?? null,
                    'message_id'      => $result['message_id'] ?? null,
                ]);
            } else {
                $this->_json_response(500, [
                    'success' => false,
                    'error'   => $result['message'] ?? 'Unknown error in process_message',
                ]);
            }

        } catch (Exception $e) {
            $this->_json_response(500, [
                'success'   => false,
                'error'     => 'Exception: ' . $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);
        } catch (Error $e) {
            $this->_json_response(500, [
                'success'   => false,
                'error'     => 'PHP Error: ' . $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);
        }
    }

    /**
     * GET /whatsapp_chat/api/status
     */
    public function status()
    {
        $version = defined('WHATSAPP_CHAT_MODULE_VERSION') ? WHATSAPP_CHAT_MODULE_VERSION : '1.0.3';
        $this->_json_response(200, [
            'success' => true,
            'module'  => 'whatsapp_chat',
            'version' => $version,
            'status'  => 'ok',
            'helper_loaded' => function_exists('wac_api_token_valid'),
        ]);
    }

    /**
     * GET /whatsapp_chat/api/debug
     *
     * Debug endpoint to check table structure
     */
    public function debug()
    {
        try {
            $tables = [];

            // Check conversations table
            $q1 = $this->db->query("SHOW COLUMNS FROM " . db_prefix() . "whatsapp_conversations");
            $tables['conversations'] = $q1 ? array_column($q1->result_array(), 'Field') : 'error';

            // Check messages table
            $q2 = $this->db->query("SHOW COLUMNS FROM " . db_prefix() . "whatsapp_messages");
            $tables['messages'] = $q2 ? array_column($q2->result_array(), 'Field') : 'error';

            // Check if token exists
            $token = get_option('whatsapp_chat_api_token');

            $this->_json_response(200, [
                'success' => true,
                'tables'  => $tables,
                'token_configured' => !empty($token),
                'helper_loaded' => function_exists('wac_api_token_valid'),
                'php_version' => PHP_VERSION,
            ]);
        } catch (Exception $e) {
            $this->_json_response(500, [
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Helper: output JSON response and exit.
     */
    private function _json_response($status_code, $data)
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status_code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
