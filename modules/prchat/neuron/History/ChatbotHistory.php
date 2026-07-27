<?php

namespace PerfexChat\Neuron\History;

use NeuronAI\Chat\History\AbstractChatHistory;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Tools\Tool;
use PerfexChat\Neuron\Models\Chatbot;
use PerfexChat\Neuron\Models\ChatbotConversation;
use PerfexChat\Neuron\Models\ChatbotMessage;

/**
 * ChatbotHistory
 * 
 * Manages conversation history for the chatbot, storing messages in the database.
 */
class ChatbotHistory extends AbstractChatHistory
{
    protected Chatbot $chatbot;
    protected ChatbotConversation $conversation;
    protected int $maxMessages;

    /**
     * Create a new ChatbotHistory instance.
     */
    public function __construct(Chatbot $chatbot, ChatbotConversation $conversation)
    {
        $this->chatbot = $chatbot;
        $this->conversation = $conversation;
        $this->maxMessages = $chatbot->context_window ?: 10;

        // Pass a high token limit to parent - we'll handle trimming by message count ourselves
        parent::__construct(PHP_INT_MAX, new TokenCounter());

        // Load existing messages
        $this->load();
    }

    /**
     * Override trimHistory to use message count instead of token count.
     * This ensures context_window = 10 means exactly 10 messages, not 10 tokens.
     */
    protected function trimHistory(): void
    {
        if (empty($this->history)) {
            return;
        }

        // Keep only the last N messages (context_window setting)
        $count = count($this->history);
        if ($count > $this->maxMessages) {
            $this->history = array_slice($this->history, $count - $this->maxMessages);
        }

        // Ensure valid message sequence (user/assistant alternation)
        $this->ensureValidMessageSequence();
    }

    /**
     * Store messages to the database.
     */
    public function setMessages(array $messages): ChatHistoryInterface
    {
        $currentMessages = array_map(fn(Message $msg) => $msg->jsonSerialize(), $messages);
        $newMessages = array_filter($currentMessages, fn(array $msg) => !array_key_exists('chatbot_message_id', $msg));

        foreach ($newMessages as $index => $messageData) {
            $messageInstance = $messages[$index];
            $dbMessage = $this->storeMessage($messageData);
            $messageInstance->addMetadata('chatbot_message_id', (string) $dbMessage->id);
            $currentMessages[$index]['chatbot_message_id'] = (string) $dbMessage->id;
        }

        // Mark old messages as excluded from history
        $currentIds = array_filter(array_column($currentMessages, 'chatbot_message_id'));
        if (!empty($currentIds)) {
            $this->excludeOldMessages($currentIds);
        }

        // Touch conversation activity
        if (count($newMessages) > 0) {
            $this->conversation->touch();
        }

        return $this;
    }

    /**
     * Store a single message in the database.
     */
    protected function storeMessage(array $attributes): ChatbotMessage
    {
        $metadata = array_diff_key($attributes, array_flip(['role', 'content', 'usage']));
        $type = $metadata['type'] ?? 'text';
        unset($metadata['type']);

        // Handle content - could be string or array
        $content = $attributes['content'] ?? '';
        if (is_array($content)) {
            $content = json_encode($content);
        }

        // Strip internal markers before persisting (e.g. [FALLBACK])
        if ($attributes['role'] === 'assistant') {
            $content = trim(str_replace('[FALLBACK]', '', $content));
        }

        // Skip storing truly empty messages (tool calls without content)
        if (empty($content) || $content === '[]' || $content === 'null') {
            // Return a dummy message object without saving
            return new ChatbotMessage((object)[
                'id' => 0,
                'conversation_id' => $this->conversation->id,
                'role' => $attributes['role'],
                'content' => '',
                'type' => 'skip',
                'metadata' => null,
                'usage' => null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return ChatbotMessage::create([
            'conversation_id' => $this->conversation->id,
            'role' => $attributes['role'],
            'content' => $content,
            'type' => $type,
            'metadata' => array_merge($metadata, ['model' => $this->chatbot->ai_model]),
            'usage' => $attributes['usage'] ?? null,
        ]);
    }

    /**
     * Exclude old messages from history.
     */
    protected function excludeOldMessages(array $keepIds): void
    {
        if (empty($keepIds)) {
            return;
        }

        $CI = &get_instance();
        $CI->db->where('conversation_id', $this->conversation->id)
            ->where_not_in('id', $keepIds)
            ->update(db_prefix() . 'chatbot_messages', ['include_in_history' => 0]);
    }

    /**
     * Clear all messages from history.
     */
    protected function clear(): ChatHistoryInterface
    {
        $CI = &get_instance();
        $CI->db->where('conversation_id', $this->conversation->id)
            ->update(db_prefix() . 'chatbot_messages', ['include_in_history' => 0]);

        return $this;
    }

    /**
     * Deserialize a tool call result message.
     */
    protected function deserializeToolCallResult(array $message): ToolCallResultMessage
    {
        $tools = array_map(function (array $tool) {
            return Tool::make($tool['name'], $tool['description'])
                ->setInputs($tool['inputs'])
                ->setCallId($tool['callId'])
                ->setResult($tool['result']);
        }, $message['tools'] ?? []);

        $item = new ToolCallResultMessage($tools);
        $item->addMetadata('chatbot_message_id', $message['chatbot_message_id'] ?? null);

        return $item;
    }

    /**
     * Load messages from database.
     */
    protected function load(): void
    {
        $CI = &get_instance();
        $rows = $CI->db->where('conversation_id', $this->conversation->id)
            ->where('include_in_history', 1)
            ->where_not_in('role', ['system', 'tool', 'tool_result'])
            ->order_by('created_at', 'ASC')
            ->get(db_prefix() . 'chatbot_messages')
            ->result();

        $messages = [];
        foreach ($rows as $row) {
            // Skip messages with invalid/empty roles (NeuronAI enum only supports user/assistant)
            if (empty($row->role) || !in_array($row->role, ['user', 'assistant'])) {
                continue;
            }

            $metadata = is_string($row->metadata) ? json_decode($row->metadata, true) : ($row->metadata ?: []);
            $usage = is_string($row->usage) ? json_decode($row->usage, true) : $row->usage;

            // Filter metadata - convert booleans to strings (NeuronAI only accepts array|string|null)
            $filteredMetadata = [];
            foreach ($metadata as $key => $value) {
                if (is_bool($value)) {
                    $filteredMetadata[$key] = $value ? 'true' : 'false';
                } elseif (is_scalar($value) || is_array($value) || is_null($value)) {
                    $filteredMetadata[$key] = $value;
                }
                // Skip other types
            }

            $data = [
                'chatbot_message_id' => (string) $row->id,
                'role' => $row->role,
                'content' => $row->content,
                'type' => $row->type,
                ...$filteredMetadata,
            ];

            if ($usage) {
                $data['usage'] = [
                    'input_tokens' => $usage['input_tokens'] ?? 0,
                    'output_tokens' => $usage['output_tokens'] ?? 0,
                ];
            }

            $messages[] = $data;
        }

        $this->history = $this->deserializeMessages($messages);
    }
}

/**
 * Simple token counter for history management.
 */
class TokenCounter implements \NeuronAI\Chat\History\TokenCounterInterface
{
    /**
     * Estimate token count for messages.
     * 
     * @param \NeuronAI\Chat\Messages\Message[] $messages
     */
    public function count(array $messages): int
    {
        $total = 0;
        foreach ($messages as $message) {
            $content = $message->getContent() ?? '';
            // Rough estimate: ~4 characters per token
            $total += (int) ceil(strlen($content) / 4);
        }
        return $total;
    }
}
