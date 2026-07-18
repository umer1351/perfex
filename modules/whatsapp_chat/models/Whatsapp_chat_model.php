<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Whatsapp_chat_model.php
 *
 * All database interactions for the whatsapp_chat module.
 */
class Whatsapp_chat_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        // Helper is already loaded globally in whatsapp_chat.php
    }

    // =========================================================================
    // CONVERSATIONS
    // =========================================================================

    /**
     * Get all conversations ordered by latest message.
     *
     * @param  int $limit  0 = no limit (return all)
     * @param  int $offset
     * @return array
     */
    public function get_conversations($limit = 0, $offset = 0)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'whatsapp_conversations');
        $this->db->order_by('last_message_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result_array();
    }

    /**
     * Get total unread count across all conversations.
     *
     * @return int
     */
    public function get_total_unread_count()
    {
        $this->db->select_sum('unread_count');
        $result = $this->db->get(db_prefix() . 'whatsapp_conversations')->row_array();
        return (int) ($result['unread_count'] ?? 0);
    }

    /**
     * Get a single conversation by ID.
     *
     * @param  int $id
     * @return array|null
     */
    public function get_conversation($id)
    {
        $this->db->where('id', (int) $id);
        return $this->db->get(db_prefix() . 'whatsapp_conversations')->row_array();
    }

    /**
     * Find conversation by normalized phone.
     *
     * @param  string $normalized_phone
     * @return array|null
     */
    public function get_conversation_by_phone($normalized_phone)
    {
        $this->db->where('phone_normalized', $normalized_phone);
        return $this->db->get(db_prefix() . 'whatsapp_conversations')->row_array();
    }

    /**
     * Create a new conversation row.
     *
     * @param  array $data
     * @return int   Inserted ID
     */
    public function create_conversation($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'whatsapp_conversations', $data);
        return $this->db->insert_id();
    }

    /**
     * Update conversation summary (last message preview, unread, timestamps).
     *
     * @param  int   $conversation_id
     * @param  array $data
     * @return bool
     */
    public function update_conversation($conversation_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $conversation_id);
        return $this->db->update(db_prefix() . 'whatsapp_conversations', $data);
    }

    /**
     * Reset unread count to zero for a conversation (when admin opens it).
     * ONLY marks INBOUND messages as read - outbound messages stay as-is.
     *
     * @param  int $conversation_id
     * @return void
     */
    public function mark_conversation_read($conversation_id)
    {
        $conversation_id = (int) $conversation_id;

        // Reset unread count on the conversation
        $this->db->where('id', $conversation_id);
        $this->db->update(db_prefix() . 'whatsapp_conversations', ['unread_count' => 0]);

        // Mark only INBOUND unread messages as read
        // Outbound messages don't count toward "unread"
        $this->db->where('conversation_id', $conversation_id);
        $this->db->where('direction', 'inbound');
        $this->db->where('is_read', 0);
        $this->db->update(db_prefix() . 'whatsapp_messages', ['is_read' => 1]);
    }

    // =========================================================================
    // MESSAGES
    // =========================================================================

    /**
     * Check if a message_uid already exists to prevent duplicates.
     *
     * @param  string $message_uid
     * @return array|false  Returns existing message data if found, false otherwise
     */
    public function get_message_by_uid($message_uid)
    {
        $this->db->where('message_uid', $message_uid);
        $row = $this->db->get(db_prefix() . 'whatsapp_messages')->row_array();
        return $row ?: false;
    }

    /**
     * Check if a message_uid already exists to prevent duplicates.
     *
     * @param  string $message_uid
     * @return bool
     */
    public function message_uid_exists($message_uid)
    {
        return $this->get_message_by_uid($message_uid) !== false;
    }

    /**
     * Insert a new message row.
     *
     * @param  array $data
     * @return int|false   Inserted ID or false on duplicate
     */
    public function insert_message($data)
    {
        // Duplicate guard
        if ($this->message_uid_exists($data['message_uid'])) {
            return false;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'whatsapp_messages', $data);
        return $this->db->insert_id();
    }

    /**
     * Get all messages for a conversation ordered by sent_at ASC.
     *
     * @param  int $conversation_id
     * @return array
     */
    public function get_messages($conversation_id)
    {
        $this->db->where('conversation_id', (int) $conversation_id);
        $this->db->order_by('sent_at', 'ASC');
        $this->db->order_by('id', 'ASC'); // Secondary sort for same-second messages
        return $this->db->get(db_prefix() . 'whatsapp_messages')->result_array();
    }

    // =========================================================================
    // MAIN ENTRY POINT - called by API controller
    // =========================================================================

    /**
     * Process an incoming message payload from n8n.
     *
     * This is the core upsert logic:
     *   1. Normalize phone
     *   2. Find or create conversation
     *   3. Match CRM contact if not already linked
     *   4. Prevent duplicate messages
     *   5. Insert message
     *   6. Update conversation summary
     *
     * @param  array $payload  Normalized payload from API controller
     * @return array  [
     *   'success'         => bool,
     *   'duplicate'       => bool,
     *   'message'         => string,
     *   'conversation_id' => int,
     *   'message_id'      => int|null
     * ]
     */
    public function process_message($payload)
    {
        $raw_phone        = $payload['phone'];
        $normalized_phone = wac_normalize_phone($raw_phone);
        $direction        = $payload['direction'] ?? 'inbound';
        $is_inbound       = ($direction === 'inbound');

        // --- Check for duplicate message first ---
        $existing_msg = $this->get_message_by_uid($payload['message_uid']);
        if ($existing_msg) {
            return [
                'success'         => true,
                'duplicate'       => true,
                'message'         => 'Duplicate message_uid',
                'conversation_id' => (int) $existing_msg['conversation_id'],
                'message_id'      => (int) $existing_msg['id'],
            ];
        }

        // --- Find or create conversation ---
        $conversation = $this->get_conversation_by_phone($normalized_phone);
        $crm          = ['customer_id' => null, 'lead_id' => null];

        if (!$conversation) {
            // Try CRM matching for new conversation
            $crm = wac_match_crm_contact($normalized_phone);

            $conv_id = $this->create_conversation([
                'phone'             => $raw_phone,
                'phone_normalized'  => $normalized_phone,
                'display_name'      => $payload['display_name'] ?? $normalized_phone,
                'customer_id'       => $crm['customer_id'],
                'lead_id'           => $crm['lead_id'],
                'channel'           => $payload['channel'] ?? 'whatsapp',
                'last_message_at'   => $payload['sent_at'] ?? date('Y-m-d H:i:s'),
                'last_message_text' => mb_substr($payload['message_text'] ?? '', 0, 200),
                'last_direction'    => $direction,
                'unread_count'      => $is_inbound ? 1 : 0,
                'status'            => 'open',
            ]);
        } else {
            $conv_id = (int) $conversation['id'];

            // Re-try CRM link if not yet set
            if (empty($conversation['customer_id']) && empty($conversation['lead_id'])) {
                $crm = wac_match_crm_contact($normalized_phone);
            } else {
                $crm = [
                    'customer_id' => $conversation['customer_id'],
                    'lead_id'     => $conversation['lead_id'],
                ];
            }
        }

        // --- Insert message ---
        $message_data = [
            'conversation_id'  => $conv_id,
            'message_uid'      => $payload['message_uid'],
            'phone'            => $raw_phone,
            'phone_normalized' => $normalized_phone,
            'direction'        => $direction,
            'sender_type'      => $payload['sender_type'] ?? ($is_inbound ? 'customer' : 'bot'),
            'sender_name'      => $payload['display_name'] ?? '',
            'message_type'     => $payload['message_type'] ?? 'text',
            'message_text'     => $payload['message_text'] ?? '',
            'media_url'        => $payload['media_url'] ?? null,
            'status'           => 'received',
            'payload_json'     => isset($payload['payload']) ? json_encode($payload['payload'], JSON_UNESCAPED_UNICODE) : null,
            'sent_at'          => $payload['sent_at'] ?? date('Y-m-d H:i:s'),
            'is_read'          => 0,
        ];

        $message_id = $this->insert_message($message_data);

        // This should not happen if we checked for duplicates above, but safety first
        if ($message_id === false) {
            return [
                'success'         => true,
                'duplicate'       => true,
                'message'         => 'Duplicate message_uid (race condition)',
                'conversation_id' => $conv_id,
                'message_id'      => null,
            ];
        }

        // --- Update conversation summary ---
        $message_preview = $payload['message_text'] ?? '';
        if (empty($message_preview) && !empty($payload['message_type']) && $payload['message_type'] !== 'text') {
            // For media messages without text, show the type
            $message_preview = '[' . ucfirst($payload['message_type']) . ']';
        }

        $update_data = [
            'last_message_at'   => $payload['sent_at'] ?? date('Y-m-d H:i:s'),
            'last_message_text' => mb_substr($message_preview, 0, 200),
            'last_direction'    => $direction,
        ];

        // Only update CRM links if we found new ones
        if (!empty($crm['customer_id'])) {
            $update_data['customer_id'] = $crm['customer_id'];
        }
        if (!empty($crm['lead_id'])) {
            $update_data['lead_id'] = $crm['lead_id'];
        }

        // Re-open conversation if it was closed
        $update_data['status'] = 'open';

        if ($is_inbound) {
            // Increment unread count for inbound messages
            $this->db->set('unread_count', 'unread_count + 1', false);
            $this->db->where('id', $conv_id);
            $this->db->update(db_prefix() . 'whatsapp_conversations', $update_data);
        } else {
            // Outbound messages don't increment unread
            $this->update_conversation($conv_id, $update_data);
        }

        return [
            'success'         => true,
            'duplicate'       => false,
            'message'         => 'Message stored',
            'conversation_id' => $conv_id,
            'message_id'      => $message_id,
        ];
    }
}
