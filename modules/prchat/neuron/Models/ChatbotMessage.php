<?php

namespace PerfexChat\Neuron\Models;

/**
 * ChatbotMessage Model
 * 
 * Represents a single message in a conversation.
 */
class ChatbotMessage
{
    public $id;
    public $conversation_id;
    public $role;
    public $content;
    public $type;
    public $metadata;
    public $usage;
    public $include_in_history;
    public $created_at;

    /**
     * Create a new ChatbotMessage instance from database row.
     */
    public function __construct($data = null)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    if (in_array($key, ['metadata', 'usage'])) {
                        $this->$key = is_string($value) ? json_decode($value, true) : $value;
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }
    }

    /**
     * Find a message by ID.
     */
    public static function find(int $id): ?self
    {
        $CI = &get_instance();
        $row = $CI->db->where('id', $id)->get(db_prefix() . 'chatbot_messages')->row();
        return $row ? new self($row) : null;
    }

    /**
     * Create a new message.
     */
    public static function create(array $data): self
    {
        $CI = &get_instance();

        $insertData = [
            'conversation_id' => $data['conversation_id'],
            'role' => $data['role'],
            'content' => $data['content'],
            'type' => $data['type'] ?? 'text',
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'usage' => isset($data['usage']) ? json_encode($data['usage']) : null,
            'include_in_history' => $data['include_in_history'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $CI->db->insert(db_prefix() . 'chatbot_messages', $insertData);
        return self::find($CI->db->insert_id());
    }

    /**
     * Update the message.
     */
    public function update(array $data): bool
    {
        $CI = &get_instance();

        foreach (['metadata', 'usage'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        return $CI->db->where('id', $this->id)
            ->update(db_prefix() . 'chatbot_messages', $data);
    }

    /**
     * Check if this is a user message.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if this is an assistant message.
     */
    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Check if this is a tool-related message.
     */
    public function isToolRelated(): bool
    {
        return in_array($this->role, ['tool', 'tool_result']);
    }

    /**
     * Convert to array for API response.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'content' => $this->content,
            'type' => $this->type,
            'created_at' => $this->created_at,
        ];
    }
}
