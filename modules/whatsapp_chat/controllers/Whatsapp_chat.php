<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Whatsapp_chat.php - Admin controller
 *
 * Handles the chat UI page and AJAX sub-requests for:
 *   - conversation list (polling)
 *   - message list (polling)
 *   - mark conversation as read
 */
class Whatsapp_chat extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsapp_chat/whatsapp_chat_model');
        // Helper is already loaded globally in whatsapp_chat.php
    }

    /**
     * Main page - shows the two-panel chat interface.
     * URL: /admin/whatsapp_chat
     */
    public function index()
    {
        if (!staff_can('view', 'whatsapp_chat')) {
            access_denied('whatsapp_chat');
        }

        // Enqueue module assets
        $this->app_css->add(
            'wac-chat-css',
            module_dir_url(WHATSAPP_CHAT_MODULE_NAME, 'assets/css/chat.css')
        );
        $this->app_scripts->add(
            'wac-chat-js',
            module_dir_url(WHATSAPP_CHAT_MODULE_NAME, 'assets/js/chat.js')
        );

        $data['title']         = _l('whatsapp_messages');
        $data['conversations'] = $this->whatsapp_chat_model->get_conversations();

        // Load view from module directory
        $this->load->view('whatsapp_chat/manage', $data);
    }

    /**
     * AJAX: Return updated conversation list (partial HTML).
     * URL: /admin/whatsapp_chat/get_conversations
     */
    public function get_conversations()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!staff_can('view', 'whatsapp_chat')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        $conversations = $this->whatsapp_chat_model->get_conversations();
        $this->load->view('whatsapp_chat/partials/conversations', ['conversations' => $conversations]);
    }

    /**
     * AJAX: Return messages for a specific conversation (partial HTML).
     * URL: /admin/whatsapp_chat/get_messages/{conversation_id}
     */
    public function get_messages($conversation_id = 0)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!staff_can('view', 'whatsapp_chat')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        $conversation_id = (int) $conversation_id;
        if ($conversation_id <= 0) {
            show_404();
        }

        $conversation = $this->whatsapp_chat_model->get_conversation($conversation_id);
        if (!$conversation) {
            show_404();
        }

        // Auto-mark inbound messages as read when admin opens
        $this->whatsapp_chat_model->mark_conversation_read($conversation_id);

        $messages = $this->whatsapp_chat_model->get_messages($conversation_id);

        $this->load->view('whatsapp_chat/partials/messages', [
            'conversation' => $conversation,
            'messages'     => $messages,
        ]);
    }

    /**
     * AJAX: Mark a conversation as read and return updated unread count.
     * URL: /admin/whatsapp_chat/mark_read/{conversation_id}
     */
    public function mark_read($conversation_id = 0)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $conversation_id = (int) $conversation_id;
        if ($conversation_id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid conversation ID']);
            return;
        }

        $this->whatsapp_chat_model->mark_conversation_read($conversation_id);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    /**
     * Settings page (optional, for admins only)
     * URL: /admin/whatsapp_chat/settings
     */
    public function settings()
    {
        if (!is_admin()) {
            access_denied('whatsapp_chat');
        }

        if ($this->input->post()) {
            $token = $this->input->post('new_token', true);
            if (!empty($token)) {
                update_option('whatsapp_chat_api_token', trim($token));
                set_alert('success', _l('updated_successfully'));
            }
            redirect(admin_url('whatsapp_chat/settings'));
        }

        $data['title']     = _l('whatsapp_messages') . ' - Settings';
        $data['api_token'] = get_option('whatsapp_chat_api_token');

        $this->load->view('whatsapp_chat/settings', $data);
    }
}
