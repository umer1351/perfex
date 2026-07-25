<?php

namespace PerfexChat\Neuron\Agents;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use PerfexChat\Neuron\Models\ChatbotConversation;
use PerfexChat\Neuron\Services\PusherService;

/**
 * ChatbotEscalationTool
 * 
 * A tool that allows the AI to escalate the conversation to a human agent.
 * Only triggered when visitor explicitly requests human help or provides contact info.
 */
class ChatbotEscalationTool extends Tool
{
    protected ChatbotConversation $conversation;

    /**
     * Create a new instance of the ChatbotEscalationTool.
     */
    public function __construct(ChatbotConversation $conversation)
    {
        $this->conversation = $conversation;

        parent::__construct(
            'escalate_to_human',
            'Escalate the conversation to a human support agent. Only use when the visitor explicitly requests to speak with a human.'
        );
    }

    /**
     * Return the list of the properties.
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'reason',
                type: PropertyType::STRING,
                description: 'Brief reason for escalation (e.g., "visitor requested human agent")',
                required: true
            ),
        ];
    }

    /**
     * Execute the escalation.
     */
    public function __invoke(string $reason): string
    {
        $this->conversation->escalateToHuman();
        $this->conversation->markEscalationUsed();

        // Get last visitor message as preview
        $messages = $this->conversation->getMessages();
        $lastUserMessage = '';
        foreach (array_reverse($messages) as $msg) {
            if ($msg->role === 'user') {
                $lastUserMessage = substr($msg->content, 0, 100);
                break;
            }
        }

        // Notify staff using Perfex CRM's built-in notification system
        $this->notifyStaffViaPerfex($lastUserMessage);

        // Also send via custom Pusher for live_chat view updates
        PusherService::notifyStaffEscalation(
            $this->conversation->id,
            $this->conversation->chatbot_id,
            $lastUserMessage
        );

        return 'I\'m connecting you with a support agent now. Please wait a moment while they join the conversation.';
    }

    /**
     * Notify staff via bell notification (DB + Pusher).
     * Uses notifyStaffBell which handles visitor name resolution and contact matching.
     */
    private function notifyStaffViaPerfex(string $preview): void
    {
        $staffIds = PusherService::getChatbotStaffIds();

        if (empty($staffIds)) {
            return;
        }

        PusherService::notifyStaffBell(
            $staffIds,
            $this->conversation->id,
            'chatbot_visitor_wants_human'
        );
    }
}
