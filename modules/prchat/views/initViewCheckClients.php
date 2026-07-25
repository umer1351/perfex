<?php

/**
 * Client Portal Chat Widget Initialization
 * 
 * Loads the client-staff chat widget for logged-in clients.
 * Note: AI Chatbot is NOT loaded here - it's only for external websites.
 */

if (get_option('pusher_chat_enabled') == '1' && is_client_logged_in()) {
    // Load Pusher library (latest version)
    echo '<script src="https://js.pusher.com/8.4/pusher.min.js"></script>';

    // Load the client chat widget view (includes all necessary JS)
    $this->load->view('chat_clients_view');
}
