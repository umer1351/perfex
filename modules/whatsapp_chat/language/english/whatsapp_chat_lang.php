<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * WhatsApp Chat Module - English Language File
 * Version: 1.0.3
 */

// Module Name & Menu
$lang['whatsapp_messages']        = 'WhatsApp Messages';
$lang['whatsapp_chat']            = 'WhatsApp Chat';
$lang['conversations']            = 'conversations';

// UI Labels
$lang['wac_select_conversation']  = 'Select a conversation from the left panel to view messages.';
$lang['wac_no_conversations']     = 'No conversations yet.';
$lang['wac_no_conversations_hint'] = 'Messages will appear here once WhatsApp messages arrive.';
$lang['wac_no_messages']          = 'No messages yet in this conversation.';
$lang['wac_no_preview']           = 'No message';
$lang['wac_empty_message']        = '(empty message)';
$lang['wac_loading']              = 'Loading...';
$lang['wac_load_error']           = 'Failed to load messages. Please try again.';

// Direction & Status
$lang['wac_sent']                 = 'Sent';
$lang['wac_delivered']            = 'Delivered';
$lang['wac_read']                 = 'Read';
$lang['wac_bot']                  = 'Bot';
$lang['wac_inbound']              = 'Inbound';
$lang['wac_outbound']             = 'Outbound';

// CRM Links
$lang['wac_linked_customer']      = 'Linked to Customer';
$lang['wac_linked_lead']          = 'Linked to Lead';
$lang['wac_view_customer']        = 'View Customer';
$lang['wac_view_lead']            = 'View Lead';
$lang['wac_not_linked']           = 'Not linked to CRM';

// Message Types
$lang['wac_text']                 = 'Text';
$lang['wac_image']                = 'Image';
$lang['wac_audio']                = 'Audio';
$lang['wac_video']                = 'Video';
$lang['wac_document']             = 'Document';
$lang['wac_location']             = 'Location shared';
$lang['wac_sticker']              = 'Sticker';
$lang['wac_contact']              = 'Contact';
$lang['wac_attachment']           = 'Attachment';

// Time
$lang['just_now']                 = 'Just now';
$lang['wac_today']                = 'Today';
$lang['wac_yesterday']            = 'Yesterday';

// Settings Page
$lang['wac_settings']             = 'WhatsApp Chat Settings';
$lang['wac_api_token']            = 'API Token';
$lang['wac_api_token_hint']       = 'Use this token in your n8n HTTP Request headers as: Authorization: Bearer YOUR_TOKEN';
$lang['wac_generate_new_token']   = 'Generate New Token';
$lang['wac_api_endpoint']         = 'API Endpoint';
$lang['wac_api_endpoint_hint']    = 'Send POST requests to this URL from n8n';
$lang['wac_token_copied']         = 'Token copied to clipboard';
$lang['wac_endpoint_copied']      = 'Endpoint copied to clipboard';
$lang['wac_n8n_setup_guide']      = 'n8n Setup Guide';
$lang['wac_inbound_example']      = 'Inbound Message Payload Example';
$lang['wac_outbound_example']     = 'Outbound (Bot) Message Payload Example';

// Permissions
$lang['wac_permission_view']      = 'View WhatsApp Messages';
$lang['wac_permission_create']    = 'Send Messages';

// Status
$lang['wac_status_open']          = 'Open';
$lang['wac_status_closed']        = 'Closed';

// Actions
$lang['wac_refresh']              = 'Refresh';
$lang['wac_mark_read']            = 'Mark as Read';
$lang['wac_close_conversation']   = 'Close Conversation';

// Errors
$lang['wac_error_loading']        = 'Error loading data';
$lang['wac_error_invalid_id']     = 'Invalid conversation ID';
$lang['wac_access_denied']        = 'Access denied';
