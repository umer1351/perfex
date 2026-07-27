<?php

namespace PerfexChat\Neuron\Models;

use NeuronAI\SystemPrompt;

/**
 * Chatbot Model
 * 
 * Main chatbot configuration and instance.
 */
class Chatbot
{
    public $id;
    public $name;
    public $widget_key;
    public $enabled;
    public $ai_provider;
    public $ai_model;
    public $system_prompt;
    public $max_output_tokens;
    public $temperature;
    public $appearance;
    public $allowed_domains;
    public $max_messages;
    public $context_window;
    public $capture_leads;
    public $lead_fields;
    public $lead_custom_fields;
    public $lead_capture_success_message;
    public $escalation_enabled;
    public $escalation_message;
    public $escalation_keywords;
    public $csat_enabled;
    public $auto_close_timeout;
    public $created_by;
    public $created_at;
    public $updated_at;

    /**
     * Create a new Chatbot instance from database row.
     */
    public function __construct($data = null)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    // Decode JSON fields
                    if (in_array($key, ['appearance', 'allowed_domains', 'lead_fields', 'lead_custom_fields', 'escalation_keywords'])) {
                        $this->$key = is_string($value) ? json_decode($value, true) : $value;
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }
    }

    /**
     * Find a chatbot by ID.
     */
    public static function find(int $id): ?self
    {
        $CI = &get_instance();
        $row = $CI->db->where('id', $id)->get(db_prefix() . 'chatbots')->row();
        return $row ? new self($row) : null;
    }

    /**
     * Find a chatbot by widget key.
     */
    public static function findByWidgetKey(string $widgetKey): ?self
    {
        $CI = &get_instance();
        $row = $CI->db->where('widget_key', $widgetKey)
            ->where('enabled', 1)
            ->get(db_prefix() . 'chatbots')
            ->row();
        return $row ? new self($row) : null;
    }

    /**
     * Get all chatbots.
     */
    public static function all(): array
    {
        $CI = &get_instance();
        $rows = $CI->db->order_by('created_at', 'DESC')
            ->get(db_prefix() . 'chatbots')
            ->result();

        return array_map(fn($row) => new self($row), $rows);
    }

    /**
     * Get all enabled chatbots.
     */
    public static function enabled(): array
    {
        $CI = &get_instance();
        $rows = $CI->db->where('enabled', 1)
            ->order_by('name', 'ASC')
            ->get(db_prefix() . 'chatbots')
            ->result();

        return array_map(fn($row) => new self($row), $rows);
    }

    /**
     * Create a new chatbot.
     */
    public static function create(array $data): self
    {
        $CI = &get_instance();

        // Generate widget key if not provided
        if (empty($data['widget_key'])) {
            $data['widget_key'] = bin2hex(random_bytes(16));
        }

        // Encode JSON fields
        foreach (['appearance', 'allowed_domains', 'lead_fields', 'lead_custom_fields', 'escalation_keywords'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['created_at'] = date('Y-m-d H:i:s');

        $CI->db->insert(db_prefix() . 'chatbots', $data);
        return self::find($CI->db->insert_id());
    }

    /**
     * Update the chatbot.
     */
    public function update(array $data): bool
    {
        $CI = &get_instance();

        // Encode JSON fields
        foreach (['appearance', 'allowed_domains', 'lead_fields', 'lead_custom_fields', 'escalation_keywords'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $CI->db->where('id', $this->id)
            ->update(db_prefix() . 'chatbots', $data);
    }

    /**
     * Delete the chatbot, maybe in the feature  will add multiple chatbots this is not used for now 
     */
    public function delete(): bool
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        $CI->db->trans_start();

        $convIds = $CI->db->select('id')->where('chatbot_id', $this->id)->get($prefix . 'chatbot_conversations')->result_array();
        $convIds = array_column($convIds, 'id');

        if (!empty($convIds)) {
            $CI->db->where_in('conversation_id', $convIds)->delete($prefix . 'chatbot_conversation_reads');
            $CI->db->where_in('conversation_id', $convIds)->delete($prefix . 'chatbot_conversation_notes');
            $CI->db->where_in('conversation_id', $convIds)->delete($prefix . 'chatbot_messages');
        }

        $CI->db->where('chatbot_id', $this->id)->delete($prefix . 'chatbot_conversations');
        $CI->db->where('chatbot_id', $this->id)->delete($prefix . 'chatbot_training_links');
        $CI->db->where('chatbot_id', $this->id)->delete($prefix . 'chatbot_training_qa');
        $CI->db->where('chatbot_id', $this->id)->delete($prefix . 'chatbot_training_files');
        $CI->db->where('chatbot_id', $this->id)->delete($prefix . 'chatbot_canned_responses');
        $result = $CI->db->where('id', $this->id)->delete($prefix . 'chatbots');

        $CI->db->trans_complete();

        return $CI->db->trans_status() && $result;
    }

    /**
     * Get or create a conversation for a visitor session.
     */
    public function getConversation(string $visitorId, string $sessionId, array $data = []): ChatbotConversation
    {
        $CI = &get_instance();

        $existing = $CI->db->where('chatbot_id', $this->id)
            ->where('session_id', $sessionId)
            ->get(db_prefix() . 'chatbot_conversations')
            ->row();

        if ($existing) {
            $updateData = ['last_activity_at' => date('Y-m-d H:i:s')];

            // Merge visitor info if new data has contact_id or other important info
            if (!empty($data['visitor_info'])) {
                $existingInfo = json_decode($existing->visitor_info ?? '{}', true) ?: [];
                $newInfo = $data['visitor_info'];

                // If new info has contact_id or name, update it
                if (!empty($newInfo['contact_id']) || !empty($newInfo['name']) || !empty($newInfo['email'])) {
                    $mergedInfo = array_merge($existingInfo, $newInfo);
                    $updateData['visitor_info'] = json_encode($mergedInfo);
                }
            }

            $CI->db->where('id', $existing->id)
                ->update(db_prefix() . 'chatbot_conversations', $updateData);

            // Refresh the row with updated data
            $existing = $CI->db->where('id', $existing->id)
                ->get(db_prefix() . 'chatbot_conversations')
                ->row();

            return new ChatbotConversation($existing);
        }

        // Create new conversation
        return ChatbotConversation::create([
            'chatbot_id' => $this->id,
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'visitor_info' => $data['visitor_info'] ?? null,
        ]);
    }

    /**
     * Check if conversation has reached message limit.
     */
    public function hasReachedMessageLimit(ChatbotConversation $conversation): bool
    {
        if (!$this->max_messages) {
            return false;
        }

        return $conversation->getMessageCount() >= $this->max_messages;
    }

    /**
     * Get remaining messages for a conversation.
     */
    public function getRemainingMessages(ChatbotConversation $conversation): int
    {
        if (!$this->max_messages) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_messages - $conversation->getMessageCount());
    }

    /**
     * Get configured escalation trigger phrases (or defaults).
     */
    public function getEscalationPhrases(): array
    {
        $kw = $this->escalation_keywords;
        if (!empty($kw['phrases']) && is_array($kw['phrases'])) {
            return array_filter(array_map('trim', $kw['phrases']));
        }

        return [
            'talk to human', 'talk to a human', 'speak to human', 'speak to a human',
            'speak with human', 'human agent', 'human help', 'human please',
            'need human', 'want human', 'real person', 'talk to person',
            'speak to person', 'connect me with support', 'connect to support',
            'live agent', 'live support', 'customer service',
            'transfer to agent', 'transfer me', 'real support',
            'actual person', 'actual human',
        ];
    }

    public function getEscalationCoreWords(): array
    {
        $kw = $this->escalation_keywords;
        if (!empty($kw['core_words']) && is_array($kw['core_words'])) {
            return array_filter(array_map('trim', $kw['core_words']));
        }

        return ['human', 'person', 'agent', 'support'];
    }

    public function getEmbedCode(): string
    {
        $url = site_url('prchat/Chatbot_Controller/widget/' . $this->widget_key);
        return '<script type="text/javascript">(function(){d=document;s=d.createElement("script");s.src="' . $url . '";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>';
    }

    /**
     * Build the system prompt sent to the AI.
     * Order: (1) Base role + bot name + language, (2) Company context from CRM (buildCompanyContext),
     * (3) Additional instructions from the System Prompt textarea (this->system_prompt).
     * So when a client fills the "System Prompt" in settings, that text is appended as "Additional Instructions"
     * and is used together with the auto-injected company name, address, phone, website, etc.
     */
    private static array $languageNames = [
        'english' => 'English',
        'dutch' => 'Dutch',
        'french' => 'French',
        'german' => 'German',
        'italian' => 'Italian',
        'spanish' => 'Spanish',
        'portuguese_br' => 'Portuguese (Brazilian)',
        'turkish' => 'Turkish',
        'ukrainian' => 'Ukrainian',
    ];

    public function buildSystemPrompt(bool $escalationAvailable = true, ?string $visitorName = null): SystemPrompt
    {
        $appearance = is_array($this->appearance) ? $this->appearance : (is_string($this->appearance) ? json_decode($this->appearance, true) : []);
        $widgetLang = $appearance['widget_language'] ?? 'english';
        $languageName = self::$languageNames[$widgetLang] ?? 'English';

        $background = [
            'You are a friendly and helpful customer support assistant.',
            "Your name is {$this->name}.",
            'Be warm, professional, and conversational.',
            'When someone greets you, respond with a friendly greeting and ask how you can help.',
        ];

        if ($visitorName) {
            $background[] = "The visitor's name is \"{$visitorName}\". Use their name naturally in your responses to make the conversation personal (e.g. greetings, confirmations), but do not overuse it in every sentence.";
        }

        $companyContext = $this->buildCompanyContext();
        if ($companyContext) {
            $background[] = "Company Information: {$companyContext}";
        }

        if ($this->system_prompt) {
            $background[] = "Additional Instructions: {$this->system_prompt}";
        }

        $steps = [
            'Understand what the visitor needs help with.',
            'Search your knowledge base (<EXTRA-CONTEXT>) for relevant information.',
            'Respond naturally, using ONLY facts from <EXTRA-CONTEXT> or your background.',
        ];

        $canEscalate = $this->escalation_enabled && $escalationAvailable;

        $output = [
            "LANGUAGE: Respond in {$languageName} by default. If the visitor explicitly asks for a different language, switch immediately.",
            'NEVER mention internal tools, function names, JSON, or technical implementation details.',
            'You are a SPECIALIZED assistant. Only answer using information from <EXTRA-CONTEXT> or your background. Do NOT use general knowledge or invent features/services.',
            'If <EXTRA-CONTEXT> contains a matching FAQ or answer, use it as provided.',
            'For greetings, respond warmly and ask how you can help — do NOT list specific topics unless they appear in <EXTRA-CONTEXT>.',
            'When <EXTRA-CONTEXT> has no relevant answer, politely say you don\'t have information on that topic and suggest the visitor rephrase or ask something else. NEVER proactively offer or suggest connecting with a support agent. Add [FALLBACK] at the end.',
            'FALLBACK MARKER: Add [FALLBACK] at the end of any response where you could not answer from <EXTRA-CONTEXT>. This marker is stripped before display.',
            'If referencing a source link, use format: "Source: [Title](URL)".',
        ];

        $toolsUsage = [];
        if ($canEscalate) {
            $toolsUsage = [
                'Only use escalate_to_human when the visitor EXPLICITLY requests human help (e.g., "talk to a human", "need support", "real person").',
                'Do NOT offer to escalate for simple questions — always try to help first.',
                'When escalating, respond briefly (e.g., "I\'ll connect you with a support agent right away.") and IMMEDIATELY call escalate_to_human.',
                'NEVER ask for name, email, phone, or any contact info — the system shows a form automatically after escalation.',
                'Keep the escalation response SHORT — do not explain next steps.',
            ];
        }

        return new SystemPrompt(
            background: $background,
            steps: $steps,
            output: $output,
            toolsUsage: $toolsUsage,
        );
    }

    /**
     * Build company context from Perfex CRM settings.
     * Pulls company name, description, address, phone, etc.
     */
    protected function buildCompanyContext(): string
    {
        $parts = [];

        // Company name from CRM settings
        $companyName = get_option('companyname');
        if ($companyName) {
            $parts[] = "Company name: {$companyName}";
        }

        // AI-specific company description (from widget settings)
        $aiDescription = get_option('widget_ai_company_description');
        if ($aiDescription) {
            $parts[] = "About: {$aiDescription}";
        }

        // Company address details
        $address = get_option('invoice_company_address');
        $city = get_option('invoice_company_city');
        $state = get_option('company_state');
        $zip = get_option('invoice_company_postal_code');
        $country = get_option('invoice_company_country_code');

        $location = array_filter([$address, $city, $state, $zip, $country]);
        if (!empty($location)) {
            $parts[] = 'Location: ' . implode(', ', $location);
        }

        // Phone
        $phone = get_option('invoice_company_phonenumber');
        if ($phone) {
            $parts[] = "Phone: {$phone}";
        }

        // Main domain
        $domain = get_option('main_domain');
        if ($domain) {
            $parts[] = "Website: {$domain}";
        }

        return implode('. ', $parts);
    }

    /**
     * Get training links for this chatbot.
     */
    public function getTrainingLinks(): array
    {
        return TrainingLink::forChatbot($this->id);
    }

    /**
     * Get training Q&As for this chatbot.
     */
    public function getTrainingQAs(): array
    {
        return TrainingQA::forChatbot($this->id);
    }

    /**
     * Get training files for this chatbot.
     */
    public function getTrainingFiles(): array
    {
        return TrainingFile::forChatbot($this->id);
    }

    /**
     * Get all pending trainable items.
     */
    public function getPendingTrainables(): array
    {
        return array_merge(
            TrainingLink::pendingForChatbot($this->id),
            TrainingQA::pendingForChatbot($this->id),
            TrainingFile::pendingForChatbot($this->id)
        );
    }

    /**
     * Get conversations for this chatbot.
     */
    public function getConversations(int $limit = 50): array
    {
        $CI = &get_instance();
        $rows = $CI->db->where('chatbot_id', $this->id)
            ->order_by('last_activity_at', 'DESC')
            ->limit($limit)
            ->get(db_prefix() . 'chatbot_conversations')
            ->result();

        return array_map(fn($row) => new ChatbotConversation($row), $rows);
    }

    /**
     * Get CRM leads captured by this chatbot (via conversations).
     */
    public function getLeads(): array
    {
        $CI = &get_instance();
        return $CI->db->select('l.*, c.id as conversation_id, c.session_id, c.visitor_info, c.converted_to_client_id, c.created_at as conversation_started_at, ls.name as status_name, ls.color as status_color, CONCAT(s.firstname, " ", s.lastname) as assigned_staff_name, COALESCE(cl1.userid, cl2.userid) as client_id, COALESCE(cl1.company, cl2.company) as client_company, src.name as source')
            ->from(db_prefix() . 'leads l')
            ->join(db_prefix() . 'chatbot_conversations c', 'c.converted_to_lead_id = l.id')
            ->join(db_prefix() . 'leads_status ls', 'ls.id = l.status', 'left')
            ->join(db_prefix() . 'staff s', 's.staffid = l.assigned', 'left')
            ->join(db_prefix() . 'clients cl1', 'cl1.leadid = l.id', 'left')
            ->join(db_prefix() . 'clients cl2', 'cl2.userid = c.converted_to_client_id', 'left')
            ->join(db_prefix() . 'leads_sources src', 'src.id = l.source', 'left')
            ->where('c.chatbot_id', $this->id)
            ->where('c.converted_to_lead_id IS NOT NULL')
            ->order_by('l.dateadded', 'DESC')
            ->get()
            ->result();
    }
}
