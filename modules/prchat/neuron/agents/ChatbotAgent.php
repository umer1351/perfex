<?php

namespace PerfexChat\Neuron\Agents;

use NeuronAI\Providers\AIProviderInterface;
use PerfexChat\Neuron\Providers\SecureOpenAI;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use PerfexChat\Neuron\Embeddings\SecureOpenAIEmbeddings;
use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use PerfexChat\Neuron\History\ChatbotHistory;
use PerfexChat\Neuron\Models\Chatbot;
use PerfexChat\Neuron\Models\ChatbotConversation;
use PerfexChat\Neuron\VectorStore\ChatbotVectorStore;

/**
 * ChatbotAgent
 * 
 * The main AI agent that handles chatbot conversations using RAG.
 */
class ChatbotAgent extends RAG
{
    protected Chatbot $chatbot;
    protected ?ChatbotConversation $conversation;

    /**
     * Create a new ChatbotAgent instance.
     */
    public function __construct(Chatbot $chatbot, ?ChatbotConversation $conversation = null)
    {
        $this->chatbot = $chatbot;
        $this->conversation = $conversation;
    }

    /**
     * Get the AI provider.
     */
    protected function provider(): AIProviderInterface
    {
        $model = $this->chatbot->ai_model ?: 'gpt-4o-mini';
        $temperature = (float) ($this->chatbot->temperature ?: 0.7);
        $maxTokens = (int) ($this->chatbot->max_output_tokens ?: 500);
        $apiKey = \chatbot_resolve_openai_key();

        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured. Set it in Setup → Settings → OpenAI, or in the chatbot API Keys tab.');
        }

        return new SecureOpenAI(
            $apiKey,
            $model,
            [
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]
        );
    }

    /**
     * Get the embeddings provider.
     */
    protected function embeddings(): EmbeddingsProviderInterface
    {
        $apiKey = \chatbot_resolve_openai_key();
        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured. Set it in Setup → Settings → OpenAI, or in the chatbot API Keys tab.');
        }

        return new SecureOpenAIEmbeddings(
            $apiKey,
            get_option('chatbot_openai_embedding_model') ?: 'text-embedding-3-small',
            1536
        );
    }

    /**
     * Get the vector store.
     */
    protected function vectorStore(): VectorStoreInterface
    {
        return new ChatbotVectorStore($this->chatbot);
    }

    /**
     * Get the system instructions.
     */
    public function instructions(): string
    {
        $escalationAvailable = !$this->conversation || !$this->conversation->hasUsedEscalation();
        $visitorName = null;
        if ($this->conversation) {
            $info = $this->conversation->getVisitorInfo();
            $visitorName = $info['name'] ?? null;
        }
        return $this->chatbot->buildSystemPrompt($escalationAvailable, $visitorName)->__toString();
    }

    /**
     * Get the chat history manager.
     */
    protected function chatHistory(): ChatbotHistory
    {
        return new ChatbotHistory(
            $this->chatbot,
            $this->conversation
        );
    }

    /**
     * Get available tools (e.g., escalation tool).
     * Note: Tools are only enabled for providers that properly support function calling.
     */
    public function tools(): array
    {
        if (
            !$this->chatbot->escalation_enabled
            || !$this->conversation
            || !$this->conversation->shouldAIRespond()
            || $this->conversation->hasUsedEscalation()
        ) {
            return [];
        }

        return [
            new ChatbotEscalationTool($this->conversation),
        ];
    }

    /**
     * Get the chatbot instance.
     */
    public function getChatbot(): Chatbot
    {
        return $this->chatbot;
    }

    /**
     * Get the conversation instance.
     */
    public function getConversation(): ?ChatbotConversation
    {
        return $this->conversation;
    }

    /**
     * Resolve and return the vector store (for external use).
     */
    public function resolveVectorStore(): VectorStoreInterface
    {
        return $this->vectorStore();
    }

    /**
     * Resolve and return the embeddings provider (for external use).
     */
    public function resolveEmbeddings(): EmbeddingsProviderInterface
    {
        return $this->embeddings();
    }
}
