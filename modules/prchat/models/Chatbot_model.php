<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Chatbot_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get conversations list for the SPA sidebar with unread counts.
     *
     * @param int|null $tagId Optional tag ID to filter conversations (must have this tag).
     */
    public function get_conversations_list(int $chatbotId, int $staffId, int $limit = 100, ?int $tagId = null): array
    {
        $this->db->where('chatbot_id', $chatbotId);

        if ($tagId !== null && $tagId > 0) {
            $tid = (string) $tagId;
            $this->db->where("(tags LIKE '[{$tid}]' OR tags LIKE '[{$tid},%' OR tags LIKE '%,{$tid}]' OR tags LIKE '%,{$tid},%')", null, false);
        }

        $rows = $this->db
            ->order_by('is_escalated', 'DESC')
            ->order_by('last_activity_at', 'DESC')
            ->limit($limit)
            ->get(db_prefix() . 'chatbot_conversations')
            ->result();

        $allTagIds = $this->get_all_tag_ids_from_conversations($rows);
        $tagsMap = $this->get_tags_by_ids($allTagIds);

        return $this->batch_format_conversations($rows, $staffId, $tagsMap);
    }

    /**
     * Get all tag IDs referenced in conversation rows.
     */
    private function get_all_tag_ids_from_conversations(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            if (empty($row->tags)) {
                continue;
            }
            $decoded = json_decode($row->tags, true);
            if (is_array($decoded)) {
                foreach ($decoded as $id) {
                    if (is_numeric($id)) {
                        $ids[(int) $id] = true;
                    }
                }
            }
        }
        return array_keys($ids);
    }

    /**
     * Get tag rows by IDs (id => {id, name, color}).
     */
    private function get_tags_by_ids(array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }
        $tbl = db_prefix() . 'chatbot_tags';
        $rows = $this->db->where_in('id', $tagIds)->get($tbl)->result();
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r->id] = ['id' => (int) $r->id, 'name' => $r->name, 'color' => $r->color ?? '#6c757d'];
        }
        return $map;
    }

    /**
     * Get all available tags for the chatbot (global list).
     */
    public function get_available_tags(): array
    {
        $tbl = db_prefix() . 'chatbot_tags';
        $rows = $this->db->order_by('name', 'ASC')->get($tbl)->result();
        return array_map(function ($r) {
            return ['id' => (int) $r->id, 'name' => $r->name, 'color' => $r->color ?? '#6c757d'];
        }, $rows);
    }

    /**
     * Create a new tag.
     */
    public function create_tag(string $name, string $color = '#6c757d'): array
    {
        $tbl = db_prefix() . 'chatbot_tags';
        $this->db->insert($tbl, [
            'name' => $name,
            'color' => $color,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db->insert_id();
        return ['id' => (int) $id, 'name' => $name, 'color' => $color];
    }

    /**
     * Update a tag.
     */
    public function update_tag(int $id, string $name, string $color): bool
    {
        $tbl = db_prefix() . 'chatbot_tags';
        return $this->db->where('id', $id)->update($tbl, [
            'name' => $name,
            'color' => $color,
        ]);
    }

    /**
     * Delete a tag.
     */
    public function delete_tag(int $id): bool
    {
        $tbl = db_prefix() . 'chatbot_tags';
        return $this->db->where('id', $id)->delete($tbl);
    }

    /**
     * Get read cursors for a staff member across multiple conversations.
     */
    public function get_read_cursors(int $staffId, array $conversationIds): array
    {
        $tblReads = db_prefix() . 'chatbot_conversation_reads';
        if (empty($conversationIds)) {
            return [];
        }

        $readRows = $this->db->where_in('conversation_id', $conversationIds)
            ->where('staff_id', $staffId)
            ->get($tblReads)
            ->result();

        $cursors = [];
        foreach ($readRows as $r) {
            $cursors[(int) $r->conversation_id] = (int) $r->last_read_message_id;
        }
        return $cursors;
    }

    /**
     * Format a single conversation for the sidebar list.
     *
     * @param array $tagsMap id => {id, name, color} for resolving conversation tags
     */
    private function format_conversation_item(\PerfexChat\Neuron\Models\ChatbotConversation $conv, array $readCursors, array $tagsMap = []): array
    {
        $visitorInfo = $conv->getVisitorInfo();
        $visitorName = $this->resolve_visitor_name($conv, $visitorInfo);

        $lastMsg = $this->db->select('content, role')
            ->where('conversation_id', $conv->id)
            ->where('role !=', 'system')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get(db_prefix() . 'chatbot_messages')
            ->row();

        $contactImage = !empty($visitorInfo['contact_id']) ? contact_profile_image_url($visitorInfo['contact_id']) : null;
        $assignedStaffName = $this->resolve_staff_name($conv->assigned_staff_id);

        $unreadCount = 0;
        if ($conv->status !== 'closed') {
            $cursorId = $readCursors[$conv->id] ?? null;
            $this->db->where('conversation_id', $conv->id)
                ->where('role', 'user');
            if ($cursorId !== null) {
                $this->db->where('id >', $cursorId);
            }
            $unreadCount = $this->db->count_all_results(db_prefix() . 'chatbot_messages');
        }

        $tagIds = $conv->getTags();
        $tags = [];
        foreach ($tagIds as $tid) {
            if (isset($tagsMap[$tid])) {
                $tags[] = $tagsMap[$tid];
            }
        }

        return [
            'id' => $conv->id,
            'visitor_name' => $visitorName,
            'visitor_email' => $visitorInfo['email'] ?? null,
            'contact_image' => $contactImage,
            'status' => $conv->status,
            'is_escalated' => (bool) $conv->is_escalated,
            'assigned_staff_id' => $conv->assigned_staff_id,
            'assigned_staff_name' => $assignedStaffName,
            'last_message' => $lastMsg ? substr($lastMsg->content, 0, 60) : null,
            'time_ago' => time_ago($conv->last_activity_at),
            'updated_at' => $conv->last_activity_at ?? $conv->created_at,
            'created_at' => $conv->created_at,
            'has_unread' => $unreadCount > 0,
            'unread_count' => $unreadCount,
            'priority' => $conv->priority ?? null,
            'csat_score' => $conv->csat_score,
            'csat_comment' => $conv->csat_comment,
            'csat_at' => $conv->csat_at,
            'tags' => $tags,
        ];
    }

    /**
     * Batch-format conversations to avoid N+1 queries in list views.
     * Pre-fetches last messages, unread counts, and staff names in bulk.
     */
    private function batch_format_conversations(array $rows, int $staffId, array $tagsMap): array
    {
        if (empty($rows)) {
            return [];
        }

        $convIds = array_map(fn($r) => (int) $r->id, $rows);
        $idList = implode(',', $convIds);
        $tblMsg = db_prefix() . 'chatbot_messages';
        $tblReads = db_prefix() . 'chatbot_conversation_reads';

        $lastMessages = $this->db->query("
            SELECT m.conversation_id, m.content, m.role
            FROM {$tblMsg} m
            INNER JOIN (
                SELECT conversation_id, MAX(id) AS max_id
                FROM {$tblMsg}
                WHERE conversation_id IN ({$idList}) AND role != 'system'
                GROUP BY conversation_id
            ) latest ON m.id = latest.max_id
        ")->result();

        $lastMsgMap = [];
        foreach ($lastMessages as $m) {
            $lastMsgMap[(int) $m->conversation_id] = $m;
        }

        $unreadRows = $this->db->query("
            SELECT m.conversation_id, COUNT(*) AS cnt
            FROM {$tblMsg} m
            LEFT JOIN {$tblReads} r
                ON r.conversation_id = m.conversation_id AND r.staff_id = ?
            WHERE m.conversation_id IN ({$idList})
              AND m.role = 'user'
              AND m.id > COALESCE(r.last_read_message_id, 0)
            GROUP BY m.conversation_id
        ", [$staffId])->result();

        $unreadMap = [];
        foreach ($unreadRows as $u) {
            $unreadMap[(int) $u->conversation_id] = (int) $u->cnt;
        }

        $staffIds = [];
        foreach ($rows as $r) {
            if (!empty($r->assigned_staff_id)) {
                $staffIds[(int) $r->assigned_staff_id] = true;
            }
        }
        $staffNameMap = [];
        if (!empty($staffIds)) {
            $staffRows = $this->db->select('staffid, firstname, lastname')
                ->where_in('staffid', array_keys($staffIds))
                ->get(db_prefix() . 'staff')
                ->result();
            foreach ($staffRows as $s) {
                $name = trim($s->firstname . ' ' . $s->lastname);
                $staffNameMap[(int) $s->staffid] = $name ?: ('Staff #' . $s->staffid);
            }
        }

        $conversations = [];
        foreach ($rows as $row) {
            $conv = new \PerfexChat\Neuron\Models\ChatbotConversation($row);
            $visitorInfo = $conv->getVisitorInfo();
            $visitorName = $this->resolve_visitor_name($conv, $visitorInfo);
            $contactImage = !empty($visitorInfo['contact_id']) ? contact_profile_image_url($visitorInfo['contact_id']) : null;

            $assignedStaffName = null;
            if ($conv->assigned_staff_id) {
                $assignedStaffName = $staffNameMap[(int) $conv->assigned_staff_id] ?? ('Staff #' . $conv->assigned_staff_id);
            }

            $lastMsg = $lastMsgMap[$conv->id] ?? null;

            $unreadCount = 0;
            if ($conv->status !== 'closed') {
                $unreadCount = $unreadMap[$conv->id] ?? 0;
            }

            $tagIds = $conv->getTags();
            $tags = [];
            foreach ($tagIds as $tid) {
                if (isset($tagsMap[$tid])) {
                    $tags[] = $tagsMap[$tid];
                }
            }

            $conversations[] = [
                'id' => $conv->id,
                'visitor_name' => $visitorName,
                'visitor_email' => $visitorInfo['email'] ?? null,
                'contact_image' => $contactImage,
                'status' => $conv->status,
                'is_escalated' => (bool) $conv->is_escalated,
                'assigned_staff_id' => $conv->assigned_staff_id,
                'assigned_staff_name' => $assignedStaffName,
                'last_message' => $lastMsg ? substr($lastMsg->content, 0, 60) : null,
                'time_ago' => time_ago($conv->last_activity_at),
                'updated_at' => $conv->last_activity_at ?? $conv->created_at,
                'created_at' => $conv->created_at,
                'has_unread' => $unreadCount > 0,
                'unread_count' => $unreadCount,
                'priority' => $conv->priority ?? null,
                'csat_score' => $conv->csat_score,
                'csat_comment' => $conv->csat_comment,
                'csat_at' => $conv->csat_at,
                'tags' => $tags,
            ];
        }

        return $conversations;
    }

    /**
     * Get full conversation detail for the chat panel.
     */
    public function get_conversation_detail(int $conversationId, int $staffId): ?array
    {
        $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::find($conversationId);
        if (!$conversation) {
            return null;
        }

        $visitorInfo = $conversation->getVisitorInfo();
        $visitorName = $this->resolve_visitor_name($conversation, $visitorInfo);
        $contactId = $visitorInfo['contact_id'] ?? null;
        $contactImage = $contactId ? contact_profile_image_url($contactId) : null;
        $assignedStaffName = $this->resolve_staff_name($conversation->assigned_staff_id);

        $messages = $this->format_messages($conversation);
        $notes = $this->get_conversation_notes($conversationId);

        $canReply = ($conversation->status === 'human_handling' && $conversation->assigned_staff_id == $staffId);

        $crmLeadId = $conversation->converted_to_lead_id ?: null;
        if (!$crmLeadId && !empty($visitorInfo['email'])) {
            $existingLead = $this->db->select('id')
                ->where('email', $visitorInfo['email'])
                ->get(db_prefix() . 'leads')
                ->row();
            if ($existingLead) {
                $crmLeadId = $existingLead->id;
            }
        }

        $crmClientId = $conversation->converted_to_client_id ?: null;
        if (!$crmClientId && !empty($visitorInfo['client_id']) && $visitorInfo['client_id'] != '0') {
            $crmClientId = (int) $visitorInfo['client_id'];
        }

        $this->mark_conversation_read($conversationId, $staffId);

        return [
            'conversation' => [
                'id' => $conversation->id,
                'visitor_name' => $visitorName,
                'visitor_email' => $visitorInfo['email'] ?? null,
                'visitor_info' => $visitorInfo,
                'contact_id' => $contactId,
                'contact_image' => $contactImage,
                'status' => $conversation->status,
                'is_escalated' => (bool) $conversation->is_escalated,
                'assigned_staff_id' => $conversation->assigned_staff_id,
                'assigned_staff_name' => $assignedStaffName,
                'time_ago' => time_ago($conversation->created_at),
                'crm_lead_id' => $crmLeadId,
                'crm_client_id' => $crmClientId,
                'priority' => $conversation->priority ?? null,
                'csat_score' => $conversation->csat_score,
                'csat_comment' => $conversation->csat_comment,
                'csat_at' => $conversation->csat_at,
            ],
            'messages' => $messages,
            'notes' => $notes,
            'can_reply' => $canReply,
        ];
    }

    /**
     * Mark a conversation as read for a specific staff member.
     */
    public function mark_conversation_read(int $conversationId, int $staffId): void
    {
        $tblReads = db_prefix() . 'chatbot_conversation_reads';

        $lastMsg = $this->db->select('id')
            ->where('conversation_id', $conversationId)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get(db_prefix() . 'chatbot_messages')
            ->row();

        $lastReadId = $lastMsg ? (int) $lastMsg->id : 0;

        $this->db->query(
            "INSERT INTO `{$tblReads}` (conversation_id, staff_id, last_read_message_id, updated_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE last_read_message_id = VALUES(last_read_message_id), updated_at = NOW()",
            [$conversationId, $staffId, $lastReadId]
        );
    }

    /**
     * Format messages for a conversation.
     */
    private function format_messages(\PerfexChat\Neuron\Models\ChatbotConversation $conversation): array
    {
        $allMessages = $conversation->getMessages();

        $staffIds = [];
        foreach ($allMessages as $msg) {
            $raw = $msg->metadata;
            $meta = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
            if (!empty($meta['staff_id'])) {
                $staffIds[(int) $meta['staff_id']] = true;
            }
        }

        $staffLookup = [];
        if (!empty($staffIds)) {
            $rows = $this->db->select('staffid, firstname, lastname, profile_image')
                ->where_in('staffid', array_keys($staffIds))
                ->get(db_prefix() . 'staff')
                ->result();
            foreach ($rows as $s) {
                $name = trim($s->firstname . ' ' . $s->lastname);
                $staffLookup[(int) $s->staffid] = [
                    'name' => $name ?: _l('chatbot_widget_support_agent'),
                    'profile_image' => $s->profile_image,
                ];
            }
        }

        $messages = [];
        foreach ($allMessages as $msg) {
            $raw = $msg->metadata;
            if (is_string($raw)) {
                $metadata = json_decode($raw, true);
                $metadata = is_array($metadata) ? $metadata : [];
            } elseif (is_array($raw)) {
                $metadata = $raw;
            } else {
                $metadata = [];
            }

            $isStaff = !empty($metadata['is_staff']) || !empty($metadata['staff_name'])
                || ($msg->role === 'assistant' && !empty($metadata['staff_id']));
            $staffName = isset($metadata['staff_name']) ? (string) $metadata['staff_name'] : null;
            if ($isStaff && $staffName === null && !empty($metadata['staff_id'])) {
                $sid = (int) $metadata['staff_id'];
                $staffName = $staffLookup[$sid]['name'] ?? _l('chatbot_widget_support_agent');
            }
            $staffImage = null;
            if ($isStaff && !empty($metadata['staff_id'])) {
                $sid = (int) $metadata['staff_id'];
                $info = $staffLookup[$sid] ?? null;
                $staffImage = base_url('assets/images/user-placeholder.jpg');
                if ($info && !empty($info['profile_image'])) {
                    $path = 'uploads/staff_profile_images/' . $sid . '/small_' . $info['profile_image'];
                    if (file_exists($path)) {
                        $staffImage = base_url($path);
                    }
                }
            }

            $messages[] = [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'is_staff' => (bool) $isStaff,
                'staff_name' => $staffName,
                'staff_image' => $staffImage,
                'created_at' => $msg->created_at,
            ];
        }
        return $messages;
    }

    /**
     * Get notes for a conversation.
     */
    public function get_conversation_notes(int $conversationId): array
    {
        $rows = $this->db->select('n.*, s.firstname, s.lastname')
            ->from(db_prefix() . 'chatbot_conversation_notes n')
            ->join(db_prefix() . 'staff s', 's.staffid = n.staff_id', 'left')
            ->where('n.conversation_id', $conversationId)
            ->order_by('n.created_at', 'ASC')
            ->get()
            ->result();

        return array_map(function ($n) {
            return [
                'id' => $n->id,
                'content' => $n->content,
                'staff_name' => trim($n->firstname . ' ' . $n->lastname),
                'created_at' => $n->created_at,
            ];
        }, $rows);
    }

    /**
     * Add a note to a conversation.
     */
    public function add_note(int $conversationId, int $staffId, string $content): array
    {
        $staffName = $this->get_staff_display_name($staffId);

        $this->db->insert(db_prefix() . 'chatbot_conversation_notes', [
            'conversation_id' => $conversationId,
            'staff_id' => $staffId,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'id' => $this->db->insert_id(),
            'content' => $content,
            'staff_name' => $staffName,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Delete a conversation and its related data.
     */
    public function delete_conversation(int $conversationId): void
    {
        $this->db->trans_start();

        $this->db->where('conversation_id', $conversationId)
            ->delete(db_prefix() . 'chatbot_conversation_reads');

        $this->db->where('conversation_id', $conversationId)
            ->delete(db_prefix() . 'chatbot_conversation_notes');

        $this->db->where('conversation_id', $conversationId)
            ->delete(db_prefix() . 'chatbot_messages');

        $this->db->where('id', $conversationId)
            ->delete(db_prefix() . 'chatbot_conversations');

        $this->db->trans_complete();

        $uploadDir = module_dir_path('prchat', 'uploads/chatbot/' . $conversationId);
        if (is_dir($uploadDir)) {
            $files = glob($uploadDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($uploadDir);
        }
    }

    /**
     * Set priority on a conversation.
     */
    public function set_priority(int $conversationId, ?string $priority): void
    {
        $allowed = [null, 'low', 'medium', 'high'];
        if (!in_array($priority, $allowed, true)) {
            $priority = null;
        }

        $this->db->where('id', $conversationId)
            ->update(db_prefix() . 'chatbot_conversations', [
                'priority' => $priority,
            ]);
    }

    /**
     * Get conversation messages for export.
     */
    public function get_messages_for_export(int $conversationId): array
    {
        return $this->db->where('conversation_id', $conversationId)
            ->order_by('created_at', 'ASC')
            ->get(db_prefix() . 'chatbot_messages')
            ->result();
    }

    /**
     * Get shared canned responses (for settings page).
     */
    public function get_shared_canned_responses(): array
    {
        return $this->db
            ->where('staff_id IS NULL')
            ->order_by('sort_order', 'ASC')
            ->order_by('title', 'ASC')
            ->get(db_prefix() . 'chatbot_canned_responses')
            ->result();
    }

    /**
     * Get canned responses for a staff member (shared + personal).
     */
    public function get_canned_responses(int $staffId): array
    {
        $responses = $this->db
            ->where('staff_id IS NULL OR staff_id = ' . (int) $staffId)
            ->order_by('sort_order', 'ASC')
            ->order_by('title', 'ASC')
            ->get(db_prefix() . 'chatbot_canned_responses')
            ->result();

        return array_map(function ($r) {
            return [
                'id' => $r->id,
                'title' => $r->title,
                'shortcut' => $r->shortcut,
                'content' => $r->content,
            ];
        }, $responses);
    }

    /**
     * Add a shared canned response.
     */
    public function add_canned_response(array $data): int
    {
        $this->db->insert(db_prefix() . 'chatbot_canned_responses', [
            'title' => $data['title'],
            'shortcut' => $data['shortcut'] ?? null,
            'content' => $data['content'],
            'staff_id' => null,
            'sort_order' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insert_id();
    }

    /**
     * Delete a canned response.
     */
    public function delete_canned_response(int $id): void
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'chatbot_canned_responses');
    }

    /**
     * Find or create a "Chatbot" lead source. Returns the source ID.
     */
    public function get_or_create_chatbot_lead_source(): int
    {
        $source = $this->db->select('id')
            ->where('name', 'Chatbot')
            ->get(db_prefix() . 'leads_sources')
            ->row();

        if ($source) {
            return (int) $source->id;
        }

        $this->db->insert(db_prefix() . 'leads_sources', ['name' => 'Chatbot']);
        return (int) $this->db->insert_id();
    }

    /**
     * Find a CRM lead by email.
     */
    public function find_lead_by_email(string $email): ?object
    {
        return $this->db->select('id')
            ->where('email', $email)
            ->get(db_prefix() . 'leads')
            ->row();
    }

    /**
     * Get staff display name for a given staff ID.
     */
    public function resolve_staff_name(?int $staffId): ?string
    {
        if (!$staffId) {
            return null;
        }
        $name = $this->get_staff_display_name($staffId);
        if ($name === null || $name === false || (is_string($name) && trim($name) === '')) {
            return 'Staff #' . $staffId;
        }
        return $name;
    }

    /**
     * Resolve visitor name from visitor_info or CRM lead.
     */
    public function resolve_visitor_name(\PerfexChat\Neuron\Models\ChatbotConversation $conv, array $visitorInfo): ?string
    {
        $name = null;
        if (!empty($visitorInfo['name'])) {
            $name = $visitorInfo['name'];
        }
        if (!$name && !empty($visitorInfo['email'])) {
            $name = $visitorInfo['email'];
        }
        if (!$name && $conv->converted_to_lead_id) {
            $crmLead = $conv->getLead();
            if ($crmLead) {
                $name = $crmLead->name ?: $crmLead->email;
            }
        }
        return $name;
    }

    /**
     * Get staff name by ID for widget/message display (DB only, no staff helper).
     */
    public function get_staff_display_name(int $staffId): string
    {
        $row = $this->db->select('firstname, lastname')
            ->where('staffid', $staffId)
            ->get(db_prefix() . 'staff')
            ->row();

        if ($row) {
            return trim($row->firstname . ' ' . $row->lastname);
        }
        return _l('chatbot_widget_support_agent');
    }

    /**
     * Get staff profile image URL by ID (DB only, no staff helper).
     */
    public function get_staff_profile_image_url(int $staffId, string $type = 'small'): string
    {
        $row = $this->db->select('profile_image')
            ->where('staffid', $staffId)
            ->get(db_prefix() . 'staff')
            ->row();

        $url = base_url('assets/images/user-placeholder.jpg');
        if ($row && !empty($row->profile_image)) {
            $path = 'uploads/staff_profile_images/' . $staffId . '/' . $type . '_' . $row->profile_image;
            if (file_exists($path)) {
                $url = base_url($path);
            }
        }
        return $url;
    }

    /**
     * Check rate limit for a visitor. Returns true if within limit.
     */
    public function check_rate_limit(string $visitorId, int $maxPerMinute = 20): bool
    {
        $oneMinuteAgo = date('Y-m-d H:i:s', strtotime('-60 seconds'));
        $count = $this->db
            ->from(db_prefix() . 'chatbot_messages m')
            ->join(db_prefix() . 'chatbot_conversations c', 'c.id = m.conversation_id')
            ->where('c.visitor_id', $visitorId)
            ->where('m.role', 'user')
            ->where('m.created_at >=', $oneMinuteAgo)
            ->count_all_results();

        return $count < $maxPerMinute;
    }

    /**
     * Update CSAT rating for a conversation.
     */
    public function update_csat(int $conversationId, int $score, ?string $comment): void
    {
        $score = max(1, min(5, $score));
        $comment = $comment ? mb_substr(trim($comment), 0, 2000) : null;

        $this->db->where('id', $conversationId)->update(db_prefix() . 'chatbot_conversations', [
            'csat_score' => $score,
            'csat_comment' => $comment,
            'csat_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Find a contact by email.
     */
    public function find_contact_by_email(string $email): ?object
    {
        return $this->db->select('id, firstname, lastname, userid')
            ->where('email', $email)
            ->get(db_prefix() . 'contacts')
            ->row();
    }

    /**
     * Update visitor_info on a conversation.
     */
    public function update_visitor_info(int $conversationId, array $visitorInfo): void
    {
        $this->db->where('id', $conversationId)
            ->update(db_prefix() . 'chatbot_conversations', [
                'visitor_info' => json_encode($visitorInfo),
            ]);
    }

    /**
     * Extract email/name from a message and save to conversation visitor_info.
     * Auto-creates a CRM lead when capture_leads is enabled.
     */
    public function extract_and_save_contact_info($conversation, string $message): bool
    {
        $email = null;
        $name  = null;

        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $message, $matches)) {
            $email = $matches[0];
        }

        $namePatterns = [
            '/(?:my name is|i\'m|i am|name:?)\s+([a-zA-Z]+(?:\s+[a-zA-Z]+)?)/i',
            '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?),?\s+(?:my email|email)/i',
            '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?),?\s+[a-zA-Z0-9._%+-]+@/i',
        ];

        foreach ($namePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $name = trim($matches[1]);
                break;
            }
        }

        $commonWords = ['no', 'yes', 'ok', 'okay', 'hi', 'hello', 'hey', 'thanks', 'thank', 'sure', 'nope', 'yep', 'yeah'];
        $trimmedMsg = trim($message);
        $lowerMsg   = strtolower($trimmedMsg);

        if (
            !$name && !$email
            && strlen($trimmedMsg) >= 2
            && strlen($trimmedMsg) < 50
            && preg_match('/^[A-Za-z][A-Za-z\s]*$/', $trimmedMsg)
            && !in_array($lowerMsg, $commonWords)
            && $conversation->status === \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_PENDING_ESCALATION
        ) {
            $name = $trimmedMsg;
        }

        if ($email) {
            $visitorInfo = $conversation->getVisitorInfo();
            $visitorInfo['email'] = $email;
            if ($name && empty($visitorInfo['name'])) {
                $visitorInfo['name'] = $name;
            }

            $contact = $this->find_contact_by_email($email);
            if ($contact) {
                $visitorInfo['contact_id'] = (int) $contact->id;
                $visitorInfo['name']       = trim($contact->firstname . ' ' . $contact->lastname);
                $visitorInfo['client_id']  = $contact->userid;
            }

            $conversation->update(['visitor_info' => $visitorInfo]);
            $conversation->visitor_info = $visitorInfo;

            $chatbot = \PerfexChat\Neuron\Models\Chatbot::find($conversation->chatbot_id);
            if ($chatbot && $chatbot->capture_leads && !$conversation->converted_to_lead_id) {
                $leadName = $name ?: ($visitorInfo['name'] ?? _l('chatbot_chatbot_visitor'));
                $conversation->createCrmLead($email, $leadName);
            }

            return true;
        }

        if ($name) {
            $visitorInfo = $conversation->getVisitorInfo();
            if (empty($visitorInfo['name'])) {
                $visitorInfo['name'] = $name;
                $this->update_visitor_info($conversation->id, $visitorInfo);
                $conversation->visitor_info = $visitorInfo;
            }
        }

        return false;
    }

    /**
     * Check if a message contains escalation keywords configured on the chatbot.
     */
    public function contains_escalation_keywords(string $message, \PerfexChat\Neuron\Models\Chatbot $chatbot): bool
    {
        $lowerMessage = strtolower(trim($message));

        foreach ($chatbot->getEscalationPhrases() as $phrase) {
            if (strpos($lowerMessage, strtolower($phrase)) !== false) {
                return true;
            }
        }

        $coreWords   = $chatbot->getEscalationCoreWords();
        $maxDistance  = 2;
        $messageWords = preg_split('/\s+/', $lowerMessage);

        foreach ($messageWords as $word) {
            $word = preg_replace('/[^a-z]/', '', $word);
            if (strlen($word) < 4) {
                continue;
            }
            foreach ($coreWords as $core) {
                if (levenshtein($word, strtolower($core)) <= $maxDistance) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if an AI response text implies escalation without using the tool.
     */
    public function ai_response_suggests_escalation(string $response): bool
    {
        $lower = strtolower($response);

        $patterns = [
            '/i\'ll\s+connect\s+you/',
            '/i\s+will\s+connect\s+you/',
            '/i\'m\s+connect(?:ing)?\s+you/',
            '/i\s+am\s+connect(?:ing)?\s+you/',
            '/i\'ll\s+transfer\s+you/',
            '/i\s+will\s+transfer\s+you/',
            '/i\'m\s+transfer(?:ring)?\s+you/',
            '/i\s+am\s+transfer(?:ring)?\s+you/',
            '/let\s+me\s+connect\s+you/',
            '/let\s+me\s+transfer\s+you/',
            '/let\s+me\s+get\s+(a\s+)?(human|someone|agent)/',
            '/i\'ll\s+get\s+(a\s+)?(human|someone|agent)/',
            '/passing\s+you\s+to\s+(a\s+)?(human|support|agent)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build a text summary of a conversation for CRM leads/tasks.
     */
    public function build_conversation_summary($conversation): string
    {
        $visitorInfo = $conversation->getVisitorInfo();

        $summary  = "== " . _l('chatbot_conversation_summary_title') . " ==\n\n";
        $summary .= _l('chatbot_visitor_label') . ": " . ($visitorInfo['name'] ?? _l('chatbot_unknown')) . "\n";
        $summary .= _l('chatbot_email') . ": " . ($visitorInfo['email'] ?? _l('chatbot_na')) . "\n";
        $summary .= _l('chatbot_started_label') . ": " . date('M j, Y g:i a', strtotime($conversation->created_at)) . "\n\n";

        $messages = $conversation->getMessages();
        $recentMessages = array_slice($messages, -10);

        $summary .= "== " . _l('chatbot_recent_messages_title') . " ==\n\n";
        foreach ($recentMessages as $msg) {
            if ($msg->role === 'user') {
                $summary .= _l('chatbot_visitor_label') . ": " . substr($msg->content, 0, 200) . "\n";
            } elseif ($msg->role === 'assistant') {
                $summary .= _l('chatbot_bot_staff_label') . ": " . substr($msg->content, 0, 200) . "\n";
            }
        }

        return $summary;
    }

    /**
     * Split text into chunks suitable for embedding APIs (max ~8192 tokens).
     * Uses paragraph/sentence boundaries when possible for cleaner splits.
     *
     * @param string $text       The text to split
     * @param int    $maxChars   Max characters per chunk (conservative to handle code/URLs/non-Latin text)
     * @param int    $overlap    Character overlap between chunks for context continuity
     * @return array Array of text chunks
     */
    public function split_text_into_chunks(string $text, int $maxChars = 6000, int $overlap = 300): array
    {
        $text = trim($text);
        if (empty($text) || mb_strlen($text) <= $maxChars) {
            return [$text];
        }

        $chunks = [];
        $offset = 0;
        $length = mb_strlen($text);

        while ($offset < $length) {
            $end = min($offset + $maxChars, $length);

            if ($end < $length) {
                $segment = mb_substr($text, $offset, $maxChars);

                $breakPos = mb_strrpos($segment, "\n\n");
                if ($breakPos === false || $breakPos < $maxChars * 0.5) {
                    $breakPos = mb_strrpos($segment, "\n");
                }
                if ($breakPos === false || $breakPos < $maxChars * 0.5) {
                    $breakPos = mb_strrpos($segment, '. ');
                }
                if ($breakPos !== false && $breakPos >= $maxChars * 0.5) {
                    $end = $offset + $breakPos + 1;
                }
            }

            $chunkLen = $end - $offset;
            $chunk = trim(mb_substr($text, $offset, $chunkLen));
            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }

            if ($end >= $length) {
                break;
            }

            $offset = ($chunkLen <= $overlap) ? $end : $end - $overlap;
        }

        return $chunks;
    }

    /**
     * Auto-close inactive conversations based on chatbot timeout settings
     * 
     * Called by cron job to automatically close conversations that meet all criteria:
     * - Auto-close timeout enabled (> 0 minutes)
     * - Last activity older than timeout duration
     * - Status is NOT 'pending_escalation' (protects waiting visitors)
     * - No staff activity in last 10 minutes (protects active chats)
     * - Status is NOT already 'closed'
     * 
     * @return array Summary of auto-close results ['closed' => int, 'chatbots_checked' => int]
     */
    public function auto_close_inactive_conversations(): array
    {
        // Load Neuron ORM autoloader if not already loaded
        if (!class_exists('\PerfexChat\Neuron\Models\ChatbotConversation')) {
            require_once APPPATH . '../modules/prchat/neuron/autoload.php';
        }

        $CI = &get_instance();
        $closed_count = 0;
        $chatbots_checked = 0;

        $chatbots = $CI->db->select('id, auto_close_timeout')
            ->from(db_prefix() . 'chatbots')
            ->where('enabled', 1)
            ->where('auto_close_timeout >', 0)
            ->get()
            ->result();

        foreach ($chatbots as $chatbot) {
            $chatbots_checked++;
            $timeout_minutes = (int) $chatbot->auto_close_timeout;

            $conversations = $CI->db->query("
                SELECT c.id, c.status, c.chatbot_id, c.created_at, MAX(m.created_at) as last_message_at
                FROM " . db_prefix() . "chatbot_conversations c
                LEFT JOIN " . db_prefix() . "chatbot_messages m ON c.id = m.conversation_id
                WHERE c.chatbot_id = " . (int) $chatbot->id . "
                  AND c.status != 'closed'
                  AND c.status != 'pending_escalation'
                GROUP BY c.id, c.status, c.chatbot_id, c.created_at
                HAVING (last_message_at < DATE_SUB(NOW(), INTERVAL " . $timeout_minutes . " MINUTE))
                    OR (last_message_at IS NULL AND c.created_at < DATE_SUB(NOW(), INTERVAL " . $timeout_minutes . " MINUTE))
            ")->result();

            foreach ($conversations as $conv) {
                $staff_last_activity = $CI->db->select('MAX(created_at) as staff_last_at')
                    ->from(db_prefix() . 'chatbot_messages')
                    ->where('conversation_id', $conv->id)
                    ->like('metadata', '"is_staff":true', 'both')
                    ->get()
                    ->row();

                if ($staff_last_activity && $staff_last_activity->staff_last_at) {
                    $staff_last_time = strtotime($staff_last_activity->staff_last_at);
                    if (time() - $staff_last_time < 600) {
                        continue;
                    }
                }

                $has_staff_messages = $CI->db->where('conversation_id', $conv->id)
                    ->like('metadata', '"is_staff":true', 'both')
                    ->count_all_results(db_prefix() . 'chatbot_messages') > 0;

                $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::find($conv->id);

                if (!$conversation) {
                    continue;
                }

                $conversation->addMessage('system', _l('chatbot_conversation_auto_closed'), [
                    'type' => 'auto_closed'
                ]);

                $conversation->update([
                    'status' => \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_CLOSED,
                    'closed_at' => date('Y-m-d H:i:s')
                ]);

                // Send real-time notification via Pusher
                \PerfexChat\Neuron\Services\PusherService::trigger(
                    'chatbot-conversation-' . $conv->id,
                    'conversation-closed',
                    [
                        'conversation_id' => $conv->id,
                        'reason' => 'auto_closed',
                        'message' => _l('chatbot_conversation_auto_closed'),
                        'time' => date('g:i A'),
                        'csat_eligible' => $has_staff_messages
                    ]
                );

                $closed_count++;
            }
        }

        return [
            'closed' => $closed_count,
            'chatbots_checked' => $chatbots_checked
        ];
    }

    /**
     * Get training Q&A items for proactive widget suggestions (question + answer).
     *
     * @param int $chatbot_id Chatbot ID
     * @param int $limit Max items
     * @return array [ ['question' => string, 'answer' => string], ... ]
     */
    public function get_training_qa_suggestions(int $chatbot_id, int $limit = 5): array
    {
        $tbl = db_prefix() . 'chatbot_training_qa';
        $rows = $this->db->select('question, answer')
            ->from($tbl)
            ->where('chatbot_id', $chatbot_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'question' => $r['question'] ?? '',
                'answer'   => $r['answer'] ?? '',
            ];
        }
        return $out;
    }
}
