<?php

namespace PerfexChat\Neuron\Services;

/**
 * PusherService - Centralized Pusher handling for real-time chat.
 */
class PusherService
{
    private static ?\Pusher\Pusher $instance = null;

    /**
     * Get Pusher instance (singleton).
     */
    public static function getInstance(): ?\Pusher\Pusher
    {
        if (get_option('pusher_chat_enabled') != '1') {
            return null;
        }

        if (self::$instance === null) {
            $appKey = get_option('pusher_app_key');
            $appSecret = get_option('pusher_app_secret');
            $appId = get_option('pusher_app_id');
            $cluster = get_option('pusher_cluster');

            if (!$appKey || !$appSecret || !$appId) {
                return null;
            }

            try {
                $guzzleClient = new \GuzzleHttp\Client([
                    'verify' => ENVIRONMENT !== 'development',
                    'timeout' => 30,
                ]);

                self::$instance = new \Pusher\Pusher(
                    $appKey,
                    $appSecret,
                    $appId,
                    ['cluster' => $cluster, 'useTLS' => true],
                    $guzzleClient
                );
            } catch (\Exception $e) {
                log_message('error', 'Pusher init error: ' . $e->getMessage());
                return null;
            }
        }

        return self::$instance;
    }

    /**
     * Send event to a channel.
     */
    public static function trigger(string $channel, string $event, array $data): bool
    {
        $pusher = self::getInstance();
        if (!$pusher) {
            return false;
        }

        try {
            $pusher->trigger($channel, $event, $data);
            return true;
        } catch (\Exception $e) {
            log_message('error', "Pusher trigger error [{$channel}/{$event}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get conversation channel name.
     */
    public static function getConversationChannel(int $conversationId): string
    {
        return 'chatbot-conversation-' . $conversationId;
    }

    /**
     * Get staff notification channel.
     */
    public static function getStaffChannel(): string
    {
        return 'chatbot-staff';
    }

    // ==========================================
    // CONVERSATION EVENTS
    // ==========================================

    /**
     * Notify visitor that human has taken over.
     */
    public static function notifyHumanTakeover(int $conversationId, string $staffName, ?int $staffId = null): bool
    {
        $langMsg = _l('chatbot_staff_joined');
        $message = (strpos($langMsg, '%s') !== false)
            ? sprintf($langMsg, $staffName)
            : $staffName . ' has joined the conversation.';

        return self::trigger(
            self::getConversationChannel($conversationId),
            'human-takeover',
            [
                'conversation_id' => $conversationId,
                'staff_name' => $staffName,
                'staff_image' => $staffId ? staff_profile_image_url($staffId, 'small') : null,
                'message' => $message,
                'time' => date('g:i A'),
            ]
        );
    }

    /**
     * Notify visitor that conversation is closed.
     */
    public static function notifyConversationClosed(int $conversationId, string $staffName): bool
    {
        $langMsg = _l('chatbot_closed_by_staff');
        $message = (strpos($langMsg, '%s') !== false)
            ? sprintf($langMsg, $staffName)
            : 'This conversation has been closed by ' . $staffName . '.';

        $hasStaffMessages = get_instance()->db->where('conversation_id', $conversationId)
            ->like('metadata', '"is_staff":true', 'both')
            ->count_all_results(db_prefix() . 'chatbot_messages') > 0;

        return self::trigger(
            self::getConversationChannel($conversationId),
            'conversation-closed',
            [
                'conversation_id' => $conversationId,
                'staff_name' => $staffName,
                'message' => $message,
                'time' => date('g:i A'),
                'csat_eligible' => $hasStaffMessages,
            ]
        );
    }

    /**
     * Notify visitor that conversation was deleted (so widget can reset).
     */
    public static function notifyConversationDeleted(int $conversationId): bool
    {
        return self::trigger(
            self::getConversationChannel($conversationId),
            'conversation-deleted',
            [
                'conversation_id' => $conversationId,
                'time' => date('g:i A'),
            ]
        );
    }

    /**
     * Send staff message to visitor widget.
     */
    public static function sendStaffMessage(int $conversationId, string $content, string $staffName, ?int $staffId = null): bool
    {
        $payload = [
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $content,
            'staff_name' => $staffName,
            'staff_id' => $staffId,
            'staff_image' => $staffId ? staff_profile_image_url($staffId, 'small') : null,
            'time' => date('g:i A'),
        ];
        return self::trigger(
            self::getConversationChannel($conversationId),
            'staff-message',
            $payload
        );
    }

    /**
     * Notify staff that visitor is typing (or stopped typing).
     */
    public static function notifyVisitorTyping(int $conversationId, bool $isTyping): void
    {
        $pusher = self::getInstance();
        if (!$pusher) return;

        try {
            $pusher->trigger(
                self::getConversationChannel($conversationId),
                'visitor-typing',
                ['typing' => $isTyping]
            );
        } catch (\Exception $e) {
            log_message('error', '[PusherService] Visitor typing error: ' . $e->getMessage());
        }
    }

    /**
     * Notify widget that staff is typing (or stopped typing).
     */
    public static function notifyStaffTyping(int $conversationId, bool $isTyping, string $staffName = ''): void
    {
        $pusher = self::getInstance();
        if (!$pusher) return;

        try {
            $pusher->trigger(
                self::getConversationChannel($conversationId),
                'staff-typing',
                ['typing' => $isTyping, 'staff_name' => $staffName]
            );
        } catch (\Exception $e) {
            log_message('error', '[PusherService] Staff typing error: ' . $e->getMessage());
        }
    }

    /**
     * Send visitor message to CRM staff view.
     */
    public static function sendVisitorMessage(int $conversationId, string $content): bool
    {
        return self::trigger(
            self::getConversationChannel($conversationId),
            'visitor-message',
            [
                'conversation_id' => $conversationId,
                'role' => 'user',
                'content' => $content,
                'time' => date('g:i A'),
            ]
        );
    }

    // ==========================================
    // STAFF NOTIFICATION EVENTS
    // ==========================================

    /**
     * Notify all staff about new escalation request.
     */
    public static function notifyStaffEscalation(int $conversationId, int $chatbotId, string $visitorPreview = ''): bool
    {
        return self::trigger(
            self::getStaffChannel(),
            'new-escalation',
            [
                'conversation_id' => $conversationId,
                'chatbot_id' => $chatbotId,
                'preview' => $visitorPreview,
                'time' => date('g:i A'),
            ]
        );
    }

    /**
     * Notify staff that escalation was claimed.
     */
    public static function notifyStaffEscalationClaimed(int $conversationId, string $staffName): bool
    {
        return self::trigger(
            self::getStaffChannel(),
            'escalation-claimed',
            [
                'conversation_id' => $conversationId,
                'staff_name' => $staffName,
                'time' => date('g:i A'),
            ]
        );
    }

    /**
     * Notify staff channel about any new message (for sidebar updates).
     */
    public static function notifyStaffChannelNewMessage(int $conversationId, string $preview, string $sender = 'visitor'): bool
    {
        return self::trigger(
            self::getStaffChannel(),
            'new-message',
            [
                'conversation_id' => $conversationId,
                'preview' => substr($preview, 0, 100),
                'sender' => $sender,
                'time' => date('g:i A'),
            ]
        );
    }

    /**
     * Send notification to specific staff members.
     * Inserts directly into notifications table and triggers Pusher.
     */
    public static function notifyStaffBell(array $staffIds, int $conversationId, string $message = '', array $additionalData = []): bool
    {
        if (empty($staffIds)) {
            return false;
        }

        $CI = &get_instance();
        $pusher = self::getInstance();

        $link = 'prchat/Chatbot_Admin/live_chat/' . $conversationId;

        // Get visitor info from conversation
        $conversation = $CI->db->where('id', $conversationId)
            ->get(db_prefix() . 'chatbot_conversations')
            ->row();

        $visitorName = 'Visitor';
        $contactId = 0;

        if ($conversation) {
            $raw = $conversation->visitor_info ?? '{}';
            $visitorInfo = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);

            // Use contact_id directly if logged-in client
            if (!empty($visitorInfo['contact_id'])) {
                $contactId = (int) $visitorInfo['contact_id'];
            }

            // Get visitor name
            if (!empty($visitorInfo['name'])) {
                $visitorName = $visitorInfo['name'];
            } elseif ($contactId > 0) {
                // Fetch name from contacts table
                $contact = $CI->db->select('firstname, lastname')
                    ->where('id', $contactId)
                    ->get(db_prefix() . 'contacts')
                    ->row();
                if ($contact) {
                    $visitorName = trim($contact->firstname . ' ' . $contact->lastname);
                }
            }

            // If still no contact_id, try matching by email
            if ($contactId === 0 && !empty($visitorInfo['email'])) {
                $contact = $CI->db->select('id, firstname, lastname')
                    ->where('email', $visitorInfo['email'])
                    ->get(db_prefix() . 'contacts')
                    ->row();
                if ($contact) {
                    $contactId = (int) $contact->id;
                    if ($visitorName === 'Visitor') {
                        $visitorName = trim($contact->firstname . ' ' . $contact->lastname);
                    }
                }
            }

            // If still anonymous, show email or IP
            if ($visitorName === 'Visitor') {
                if (!empty($visitorInfo['email'])) {
                    $visitorName = $visitorInfo['email'];
                } elseif (!empty($visitorInfo['ip'])) {
                    $visitorName = 'Visitor (' . $visitorInfo['ip'] . ')';
                }
            }
        }

        $baseDescription = $message ?: 'chatbot_visitor_needs_help';

        // Serialize additional_data (for sprintf placeholders in _l() function)
        // Note: additional_data should contain ONLY sprintf arguments, nothing else
        $serializedData = !empty($additionalData) ? serialize($additionalData) : '';

        foreach ($staffIds as $staffId) {
            // Insert notification directly into database
            if ($contactId > 0) {
                // Real contact - show name + "Customer" badge + profile image
                $CI->db->insert(db_prefix() . 'notifications', [
                    'isread' => 0,
                    'isread_inline' => 0,
                    'date' => date('Y-m-d H:i:s'),
                    'description' => $baseDescription,
                    'fromuserid' => 0,
                    'fromclientid' => $contactId,
                    'from_fullname' => $visitorName,
                    'touserid' => $staffId,
                    'fromcompany' => null,
                    'link' => $link,
                    'additional_data' => $serializedData,
                ]);
            } else {
                $CI->db->insert(db_prefix() . 'notifications', [
                    'isread' => 0,
                    'isread_inline' => 0,
                    'date' => date('Y-m-d H:i:s'),
                    'description' => $baseDescription,
                    'fromuserid' => 0,
                    'fromclientid' => 0,
                    'from_fullname' => $visitorName,
                    'touserid' => $staffId,
                    'fromcompany' => 1,
                    'link' => $link,
                    'additional_data' => $serializedData,
                ]);
            }
        }

        // Trigger Pusher notification event for all staff
        // Channel format: notifications-channel-{staffid}
        if ($pusher) {
            $channels = [];
            foreach ($staffIds as $staffId) {
                $channels[] = 'notifications-channel-' . $staffId;
            }

            try {
                $pusher->trigger($channels, 'notification', []);
            } catch (\Exception $e) {
                log_message('error', "Pusher notification trigger error: " . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Get staff IDs with chatbot permission (used for escalation notifications).
     * Admins are always included. Non-admin staff need the 'chatbot' capability on 'prchat'.
     */
    public static function getChatbotStaffIds(): array
    {
        $CI = &get_instance();

        // Admins always have all permissions
        $admins = $CI->db->select('staffid')
            ->where('active', 1)
            ->where('admin', 1)
            ->get(db_prefix() . 'staff')
            ->result();

        $ids = array_column($admins, 'staffid');

        // Staff with explicit chatbot capability (only active staff)
        $permitted = $CI->db->select('sp.staff_id')
            ->from(db_prefix() . 'staff_permissions sp')
            ->join(db_prefix() . 'staff s', 's.staffid = sp.staff_id')
            ->where('sp.feature', 'prchat')
            ->where('sp.capability', 'chatbot')
            ->where('s.active', 1)
            ->get()
            ->result();

        foreach ($permitted as $row) {
            if (!in_array($row->staff_id, $ids)) {
                $ids[] = $row->staff_id;
            }
        }

        return $ids;
    }

    /**
     * Check if any chatbot-permitted staff is currently online.
     */
    public static function isAnyStaffOnline(): bool
    {
        return self::getOnlineStaffCount() > 0;
    }

    /**
     * Count chatbot-permitted staff who are currently online.
     * A staff member is online when last_activity is recent AND
     * occurred after their last logout (tracked via options table).
     */
    public static function getOnlineStaffCount(): int
    {
        $CI = &get_instance();
        $staffIds = self::getChatbotStaffIds();

        if (empty($staffIds)) {
            return 0;
        }

        $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));

        $rows = $CI->db->select('staffid, last_activity')
            ->where('active', 1)
            ->where('last_activity IS NOT NULL')
            ->where('last_activity >', $fiveMinutesAgo)
            ->where_in('staffid', $staffIds)
            ->get(db_prefix() . 'staff')
            ->result();

        $count = 0;
        foreach ($rows as $row) {
            $logoutTime = get_option('prchat_logout_' . $row->staffid);
            if ($logoutTime && strtotime($row->last_activity) <= (int) $logoutTime) {
                continue;
            }
            $count++;
        }

        return $count;
    }
}
