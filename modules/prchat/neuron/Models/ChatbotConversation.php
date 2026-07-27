<?php

namespace PerfexChat\Neuron\Models;

/**
 * ChatbotConversation Model
 * 
 * Represents a conversation session with a visitor.
 */
class ChatbotConversation
{
    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING_ESCALATION = 'pending_escalation';
    const STATUS_HUMAN_HANDLING = 'human_handling';
    const STATUS_CLOSED = 'closed';

    public $id;
    public $chatbot_id;
    public $visitor_id;
    public $session_id;
    public $status;
    public $visitor_info;
    public $is_escalated;
    public $escalated_at;
    public $assigned_staff_id;
    public $created_at;
    public $last_activity_at;
    public $closed_at;
    public $closed_by_staff_id;
    public $needs_followup;
    public $fallback_count;
    public $converted_to_lead_id;
    public $converted_to_client_id;
    public $priority;
    
    // CSAT Rating
    public $csat_score;
    public $csat_comment;
    public $csat_at;

    /** @var string|null JSON array of tag IDs */
    public $tags;

    /**
     * Get visitor info as array (handles both string and array storage).
     */
    public function getVisitorInfo(): array
    {
        if (is_array($this->visitor_info)) {
            return $this->visitor_info;
        }
        return json_decode($this->visitor_info ?? '{}', true) ?: [];
    }

    /**
     * Create a new ChatbotConversation instance from database row.
     */
    public function __construct($data = null)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    if ($key === 'visitor_info') {
                        $this->$key = is_string($value) ? json_decode($value, true) : $value;
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }
    }

    /**
     * Find a conversation by ID.
     */
    public static function find(int $id): ?self
    {
        $CI = &get_instance();
        $row = $CI->db->where('id', $id)->get(db_prefix() . 'chatbot_conversations')->row();
        return $row ? new self($row) : null;
    }

    /**
     * Find a conversation by session ID.
     */
    public static function findBySession(int $chatbotId, string $sessionId): ?self
    {
        $CI = &get_instance();
        $row = $CI->db->where('chatbot_id', $chatbotId)
            ->where('session_id', $sessionId)
            ->get(db_prefix() . 'chatbot_conversations')
            ->row();
        return $row ? new self($row) : null;
    }

    /**
     * Create a new conversation.
     */
    public static function create(array $data): self
    {
        $CI = &get_instance();

        $insertData = [
            'chatbot_id' => $data['chatbot_id'],
            'visitor_id' => $data['visitor_id'],
            'session_id' => $data['session_id'],
            'status' => self::STATUS_ACTIVE,
            'visitor_info' => isset($data['visitor_info']) ? json_encode($data['visitor_info']) : null,
            'is_escalated' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ];

        $CI->db->insert(db_prefix() . 'chatbot_conversations', $insertData);
        return self::find($CI->db->insert_id());
    }

    /**
     * Check if AI should respond (not human handling or closed).
     */
    public function shouldAIRespond(): bool
    {
        return !in_array($this->status, [self::STATUS_HUMAN_HANDLING, self::STATUS_CLOSED]);
    }

    /**
     * Whether escalation has already been used in this conversation (one-time only).
     */
    public function hasUsedEscalation(): bool
    {
        $info = $this->getVisitorInfo();
        return !empty($info['escalation_used']);
    }

    /**
     * Permanently mark escalation as used for this conversation.
     */
    public function markEscalationUsed(): void
    {
        $this->setVisitorInfoField('escalation_used', true);
    }

    /**
     * Set a single field in the visitor_info JSON.
     */
    public function setVisitorInfoField(string $key, $value): void
    {
        $info = $this->getVisitorInfo();
        $info[$key] = $value;
        $this->update(['visitor_info' => $info]);
    }

    /**
     * Set pending escalation - waiting for contact info from visitor.
     * Does NOT mark as escalated yet (no notification to staff).
     */
    public function setPendingEscalation(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING_ESCALATION,
        ]);
        $this->status = self::STATUS_PENDING_ESCALATION;
    }

    /**
     * Escalate to human - complete the escalation after contact info collected.
     */
    public function escalateToHuman(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING_ESCALATION,
            'is_escalated' => 1,
            'escalated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->status = self::STATUS_PENDING_ESCALATION;
        $this->is_escalated = 1;
    }

    /**
     * Staff takes over the conversation.
     */
    public function staffTakeover(int $staffId): void
    {
        $this->update([
            'status' => self::STATUS_HUMAN_HANDLING,
            'assigned_staff_id' => $staffId,
            'is_escalated' => 1,
        ]);
        $this->status = self::STATUS_HUMAN_HANDLING;
        $this->assigned_staff_id = $staffId;
    }

    /**
     * Close the conversation.
     */
    public function closeConversation(int $staffId): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
            'closed_at' => date('Y-m-d H:i:s'),
            'closed_by_staff_id' => $staffId,
        ]);
        $this->status = self::STATUS_CLOSED;
        $this->closed_at = date('Y-m-d H:i:s');
        $this->closed_by_staff_id = $staffId;
    }

    /**
     * Update the conversation.
     */
    public function update(array $data): bool
    {
        $CI = &get_instance();

        if (isset($data['visitor_info']) && is_array($data['visitor_info'])) {
            $data['visitor_info'] = json_encode($data['visitor_info']);
        }

        return $CI->db->where('id', $this->id)
            ->update(db_prefix() . 'chatbot_conversations', $data);
    }

    /**
     * Touch last activity timestamp.
     */
    public function touch(): void
    {
        $this->update(['last_activity_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Mark conversation as needing follow-up (staff was offline).
     */
    public function markNeedsFollowup(): void
    {
        $this->update(['needs_followup' => 1]);
        $this->needs_followup = 1;
    }

    /**
     * Clear needs follow-up flag.
     */
    public function clearNeedsFollowup(): void
    {
        $this->update(['needs_followup' => 0]);
        $this->needs_followup = 0;
    }

    /**
     * Increment fallback count and return the new value.
     */
    public function incrementFallbackCount(): int
    {
        $CI = &get_instance();
        $CI->db->where('id', $this->id)
            ->set('fallback_count', 'fallback_count + 1', false)
            ->update(db_prefix() . 'chatbot_conversations');

        $this->fallback_count = ($this->fallback_count ?? 0) + 1;
        return $this->fallback_count;
    }

    /**
     * Reset fallback count (after successful answer or escalation).
     */
    public function resetFallbackCount(): void
    {
        $this->update(['fallback_count' => 0]);
        $this->fallback_count = 0;
    }

    /**
     * Set converted to lead ID.
     */
    public function setConvertedToLead(int $leadId): void
    {
        $this->update(['converted_to_lead_id' => $leadId]);
        $this->converted_to_lead_id = $leadId;
    }

    /**
     * Set converted to client ID.
     */
    public function setConvertedToClient(int $clientId): void
    {
        $this->update(['converted_to_client_id' => $clientId]);
        $this->converted_to_client_id = $clientId;
    }

    /**
     * Get the chatbot for this conversation.
     */
    public function getChatbot(): ?Chatbot
    {
        return Chatbot::find($this->chatbot_id);
    }

    /**
     * Get all messages for this conversation.
     */
    public function getMessages(): array
    {
        $CI = &get_instance();
        $rows = $CI->db->where('conversation_id', $this->id)
            ->order_by('created_at', 'ASC')
            ->get(db_prefix() . 'chatbot_messages')
            ->result();

        return array_map(function ($row) {
            // Strip internal markers that may have leaked into stored messages
            if ($row->role === 'assistant' && str_contains($row->content ?? '', '[FALLBACK]')) {
                $row->content = trim(str_replace('[FALLBACK]', '', $row->content));
            }
            return new ChatbotMessage($row);
        }, $rows);
    }

    /**
     * Get messages for widget history display (excluding tool-related, including system).
     */
    public function getMessagesForHistory(): array
    {
        $CI = &get_instance();
        $rows = $CI->db->where('conversation_id', $this->id)
            ->where_not_in('role', ['tool', 'tool_result'])
            ->where('role !=', '')
            ->order_by('created_at', 'ASC')
            ->get(db_prefix() . 'chatbot_messages')
            ->result();

        return array_map(function ($row) {
            // Strip internal markers that may have leaked into stored messages
            if ($row->role === 'assistant' && str_contains($row->content ?? '', '[FALLBACK]')) {
                $row->content = trim(str_replace('[FALLBACK]', '', $row->content));
            }
            return new ChatbotMessage($row);
        }, $rows);
    }

    /**
     * Get user message count (for message limit enforcement).
     */
    public function getMessageCount(): int
    {
        $CI = &get_instance();
        return $CI->db->where('conversation_id', $this->id)
            ->where('role', 'user')
            ->count_all_results(db_prefix() . 'chatbot_messages');
    }

    /**
     * Add a message to the conversation.
     */
    public function addMessage(string $role, string $content, array $metadata = []): ChatbotMessage
    {
        return ChatbotMessage::create([
            'conversation_id' => $this->id,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get tag IDs for this conversation (returns array of int).
     */
    public function getTags(): array
    {
        if (empty($this->tags)) {
            return [];
        }
        $decoded = is_string($this->tags) ? json_decode($this->tags, true) : $this->tags;
        return is_array($decoded) ? array_map('intval', array_filter($decoded, 'is_numeric')) : [];
    }

    /**
     * Add a tag to the conversation.
     */
    public function addTag(int $tagId): bool
    {
        $ids = $this->getTags();
        if (in_array($tagId, $ids, true)) {
            return true;
        }
        $ids[] = $tagId;
        return $this->update(['tags' => json_encode(array_values($ids))]);
    }

    /**
     * Remove a tag from the conversation.
     */
    public function removeTag(int $tagId): bool
    {
        $ids = array_values(array_diff($this->getTags(), [$tagId]));
        return $this->update(['tags' => json_encode($ids)]);
    }

    /**
     * Get the CRM lead linked to this conversation.
     */
    public function getLead(): ?object
    {
        if (empty($this->converted_to_lead_id)) {
            return null;
        }

        $CI = &get_instance();
        return $CI->db->where('id', (int) $this->converted_to_lead_id)
            ->get(db_prefix() . 'leads')
            ->row();
    }

    /**
     * Create a CRM lead directly in Perfex and link it to this conversation.
     *
     * @return int|null The CRM lead ID, or null on failure
     */
    public function createCrmLead(string $email, ?string $name = null, ?string $phone = null, ?string $description = null): ?int
    {
        if ($this->converted_to_lead_id) {
            return (int) $this->converted_to_lead_id;
        }

        $CI = &get_instance();

        if (!isset($CI->chatbot_model)) {
            $CI->load->model('prchat/Chatbot_model', 'chatbot_model');
        }
        $sourceId = $CI->chatbot_model->get_or_create_chatbot_lead_source();

        $assignedStaff = 0;
        if (!empty($this->assigned_staff_id)) {
            $assignedStaff = (int) $this->assigned_staff_id;
        }

        if (!$description) {
            $description = _l('chatbot_auto_captured_lead_desc') . ' #' . $this->id;
        }

        $CI->db->insert(db_prefix() . 'leads', [
            'hash'               => app_generate_hash(),
            'name'               => $name ?: _l('chatbot_chatbot_visitor'),
            'email'              => $email,
            'phonenumber'        => $phone ?: '',
            'address'            => '',
            'source'             => $sourceId,
            'status'             => 2,
            'assigned'           => $assignedStaff,
            'addedfrom'          => $assignedStaff,
            'dateadded'          => date('Y-m-d H:i:s'),
            'last_status_change' => date('Y-m-d H:i:s'),
            'description'        => nl2br($description),
            'is_public'          => 1,
        ]);

        $crmLeadId = $CI->db->insert_id();

        if ($crmLeadId) {
            $this->setConvertedToLead($crmLeadId);
            return $crmLeadId;
        }

        return null;
    }
}
