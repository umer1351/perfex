<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Chatbot Controller
 *
 * Handles all chatbot widget requests:
 * - Widget JavaScript delivery with caching
 * - Configuration endpoint
 * - Message sending/receiving
 * - Conversation history
 * - Lead capture
 */
class Chatbot_Controller extends CI_Controller
{
    private const CACHE_MAX_AGE = 3600; // 1 hour
    private const WIDGET_DELAY_MS = 1000;

    public function __construct()
    {
        parent::__construct();

        require_once APPPATH . '../modules/prchat/neuron/autoload.php';
        $this->load->model('prchat/Chatbot_model', 'chatbot_model');
        $this->loadModuleLanguage(get_option('active_language') ?: 'english');

        // Set CORS headers for all public API requests
        $this->setCorsHeaders();
    }

    private function loadModuleLanguage(string $language): void
    {
        $langFile = APPPATH . '../modules/prchat/language/' . $language . '/chat_lang.php';
        if (file_exists($langFile)) {
            $lang = [];
            include($langFile);
            foreach ($lang as $key => $val) {
                $this->lang->language[$key] = $val;
            }
        }
    }

    private function loadChatbotLanguage(\PerfexChat\Neuron\Models\Chatbot $chatbot): void
    {
        $appearance = $chatbot->appearance ?? [];
        $widgetLang = $appearance['widget_language'] ?? null;
        if ($widgetLang && $widgetLang !== 'english') {
            $this->loadModuleLanguage($widgetLang);
        }
    }

    /**
     * Set CORS headers for cross-origin requests.
     * When a chatbot with allowed_domains is provided, restrict origin to matching domain.
     */
    private function setCorsHeaders(?\PerfexChat\Neuron\Models\Chatbot $chatbot = null): void
    {
        $origin = '*';

        // In development mode, allow all origins — skip domain restriction
        if (ENVIRONMENT !== 'development' && $chatbot && !empty($chatbot->allowed_domains)) {
            $origin = 'null';
            $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
            $refererHost = parse_url($requestOrigin, PHP_URL_HOST);

            if ($refererHost) {
                $refererHost = preg_replace('/^www\./', '', strtolower($refererHost));

                if ($this->matchesDomainList($refererHost, $chatbot->allowed_domains)) {
                    $scheme = parse_url($requestOrigin, PHP_URL_SCHEME) ?: 'http';
                    $host = parse_url($requestOrigin, PHP_URL_HOST);
                    $port = parse_url($requestOrigin, PHP_URL_PORT);
                    $origin = $scheme . '://' . $host . ($port ? ':' . $port : '');
                }
            }
        }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

        if ($origin !== '*') {
            header('Vary: Origin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Serve the widget JavaScript.
     *
     * Usage: <script src="https://yoursite.com/prchat/chatbot/widget/WIDGET_KEY"></script>
     */
    public function widget(string $widgetKey = ''): void
    {
        if (empty($widgetKey)) {
            $this->sendError(_l('chatbot_widget_key_required'), 400);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);

        if (!$chatbot) {
            $this->sendError(_l('chatbot_invalid_widget_key'), 404);
            return;
        }

        if (empty(chatbot_resolve_openai_key())) {
            header('Content-Type: application/javascript; charset=utf-8');
            echo '/* Chatbot widget disabled: no API key configured */';
            return;
        }

        // Build JavaScript content
        $jsContent = $this->buildWidgetJavaScript($chatbot);

        // Set headers
        header('Content-Type: application/javascript; charset=utf-8');
        header('X-Robots-Tag: noindex');

        if (ENVIRONMENT === 'production') {
            $etag = md5($jsContent);
            $lastModified = filemtime(__FILE__);

            header('Cache-Control: public, max-age=' . self::CACHE_MAX_AGE);
            header('ETag: ' . $etag);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');

            // Check for 304 Not Modified
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
                http_response_code(304);
                return;
            }
        } else {
            header('Cache-Control: no-cache, no-store, must-revalidate');
        }

        echo $jsContent;
    }

    /**
     * Get chatbot configuration.
     */
    public function config(string $widgetKey = ''): void
    {
        if (empty($widgetKey)) {
            $this->sendJsonError(_l('chatbot_widget_key_required'), 400);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);

        if (!$chatbot) {
            $this->sendJsonError(_l('chatbot_invalid_widget_key'), 404);
            return;
        }

        if (empty(chatbot_resolve_openai_key())) {
            $this->sendJsonError(_l('chatbot_api_key_not_configured'), 503);
            return;
        }

        // Re-send CORS with allowed domains enforcement
        $this->setCorsHeaders($chatbot);

        // Validate domain if configured
        if (!$this->validateDomain($chatbot)) {
            $this->sendJsonError(_l('chatbot_domain_not_allowed'), 403);
            return;
        }

        // Load chatbot-specific language for i18n strings
        $this->loadChatbotLanguage($chatbot);

        $appearance = $chatbot->appearance ?: [];

        $this->sendJson([
            'name' => $chatbot->name,
            'welcome_message' => $appearance['welcome_message'] ?? _l('chatbot_widget_welcome'),
            'input_placeholder' => $appearance['input_placeholder'] ?? _l('chatbot_widget_type_message'),
            'primary_color' => $appearance['primary_color'] ?? '#007bff',
            'header_bg_color' => $appearance['header_bg_color'] ?? '#007bff',
            'position' => $appearance['position'] ?? 'bottom-right',
            'distance_from_bottom' => $appearance['distance_from_bottom'] ?? 20,
            'distance_from_side' => $appearance['distance_from_side'] ?? 20,
            'icon_type' => $appearance['icon_type'] ?? 'chat',
            'custom_icon_url' => $appearance['custom_icon_url'] ?? null,
            'intro_title' => $this->buildDisplayName($chatbot, $appearance),
            'intro_subtitle' => $appearance['intro_subtitle'] ?? _l('chatbot_widget_reply_time'),
            'agent_avatar_url' => $appearance['agent_avatar_url'] ?? null,
            'capture_leads' => (bool) $chatbot->capture_leads,
            'lead_fields' => $chatbot->lead_fields,
            'lead_custom_fields' => $chatbot->lead_custom_fields,
            'lead_capture_success_message' => $chatbot->lead_capture_success_message,
            'max_messages' => $chatbot->max_messages,
            'gdpr_enabled' => (bool) ($appearance['gdpr_enabled'] ?? false),
            'gdpr_consent_text' => $appearance['gdpr_consent_text'] ?? '',
            'gdpr_privacy_url' => !empty($appearance['gdpr_privacy_url']) ? $appearance['gdpr_privacy_url'] : (function_exists('privacy_policy_url') ? privacy_policy_url() : ''),
            'gdpr_consent_purpose_id' => $appearance['gdpr_consent_purpose_id'] ?? null,
            'csat_enabled' => (bool) ($chatbot->csat_enabled ?? true),
            'visitor_file_upload' => (bool) ($appearance['visitor_file_upload'] ?? false),
            'proactive_enabled' => (bool) ($appearance['proactive_enabled'] ?? false),
            'proactive_delay_seconds' => (int) ($appearance['proactive_delay_seconds'] ?? 5),
            // Pusher config for real-time staff messages
            'pusher_key' => get_option('pusher_chat_enabled') == '1' ? get_option('pusher_app_key') : null,
            'pusher_cluster' => get_option('pusher_cluster'),
            // Widget i18n strings
            'i18n' => [
                'connected_with' => str_replace('%s', '{name}', $this->lang->line('chatbot_widget_connected_with') ?: 'Connected with {name}'),
                'support_agent' => _l('chatbot_widget_support_agent'),
                'conversation_ended' => _l('chatbot_widget_conversation_ended'),
                'please_wait' => _l('chatbot_widget_please_wait'),
                'type_message' => _l('chatbot_widget_type_message'),
                'start_new_chat' => _l('chatbot_widget_start_new_chat'),
                'lead_title' => _l('chatbot_widget_lead_title'),
                'lead_subtitle' => _l('chatbot_widget_lead_subtitle'),
                'lead_start' => _l('chatbot_widget_lead_start'),
                'email_invalid' => _l('chatbot_widget_email_invalid'),
                'name_required' => _l('chatbot_widget_name_required'),
                'phone_required' => _l('chatbot_widget_phone_required'),
                'gdpr_required' => _l('chatbot_widget_gdpr_required'),
                'lead_success' => _l('chatbot_widget_lead_success'),
                'error_generic' => _l('chatbot_widget_error_generic'),
                'error_network' => _l('chatbot_widget_error_network'),
                'gdpr_default' => _l('chatbot_widget_gdpr_default'),
                'gdpr_agree' => _l('chatbot_widget_gdpr_agree'),
                'privacy_policy' => _l('chatbot_widget_privacy_policy'),
                'reply_time' => _l('chatbot_widget_reply_time'),
                'conversation_closed' => _l('chatbot_widget_conversation_closed'),
                'message_limit' => _l('chatbot_widget_message_limit'),
                'error_send' => _l('chatbot_widget_error_send'),
                'new_message' => _l('chatbot_widget_new_message'),
                'welcome' => _l('chatbot_widget_welcome'),
                'ai_assistant' => _l('chatbot_widget_ai_assistant'),
                'staff_badge' => _l('chatbot_widget_staff_badge'),
                // CSAT strings
                'chatbot_csat_rate_conversation' => _l('chatbot_csat_rate_conversation'),
                'chatbot_csat_comment_placeholder' => _l('chatbot_csat_comment_placeholder'),
                'chatbot_csat_submit' => _l('chatbot_csat_submit'),
                'chatbot_csat_skip' => _l('chatbot_csat_skip'),
                'chatbot_csat_thank_you' => _l('chatbot_csat_thank_you'),
                'proactive_suggested_title' => _l('chatbot_proactive_suggested_title'),
                // Thinking phrases (custom or defaults)
                'thinking_phrases' => !empty($appearance['thinking_phrases']) && is_array($appearance['thinking_phrases'])
                    ? array_values($appearance['thinking_phrases'])
                    : [
                        _l('chatbot_thinking_1'),
                        _l('chatbot_thinking_2'),
                        _l('chatbot_thinking_3'),
                        _l('chatbot_thinking_4'),
                        _l('chatbot_thinking_5'),
                        _l('chatbot_thinking_6'),
                        _l('chatbot_thinking_7'),
                    ],
            ],
        ]);
    }

    /**
     * Get suggested training Q&As for proactive widget (question + answer for display on click).
     */
    public function suggested_questions(string $widgetKey = ''): void
    {
        if (empty($widgetKey)) {
            $this->sendJsonError(_l('chatbot_widget_key_required'), 400);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);
        if (!$chatbot) {
            $this->sendJsonError(_l('chatbot_invalid_widget_key'), 404);
            return;
        }

        $this->setCorsHeaders($chatbot);
        if (!$this->validateDomain($chatbot)) {
            $this->sendJsonError(_l('chatbot_domain_not_allowed'), 403);
            return;
        }

        $suggestions = $this->chatbot_model->get_training_qa_suggestions($chatbot->id, 5);
        $this->sendJson(['suggestions' => $suggestions]);
    }

    /**
     * Send a message and get AI response.
     */
    public function message(string $widgetKey = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonError(_l('chatbot_post_required'), 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['message']) || empty($input['visitor_id']) || empty($input['session_id'])) {
            $this->sendJsonError(_l('chatbot_missing_required_fields'), 400);
            return;
        }

        // Type validation: message must be a string
        if (!is_string($input['message'])) {
            $this->sendJsonError('Invalid message format. Message must be a string.', 400);
            return;
        }

        // Message length limit: 50KB
        if (strlen($input['message']) > 51200) {
            $this->sendJsonError('Message too large. Maximum size is 50KB.', 413);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);

        if (!$chatbot) {
            $this->sendJsonError(_l('chatbot_invalid_widget_key'), 404);
            return;
        }

        // Re-send CORS with allowed domains enforcement
        $this->setCorsHeaders($chatbot);

        // Validate domain
        if (!$this->validateDomain($chatbot)) {
            $this->sendJsonError(_l('chatbot_domain_not_allowed'), 403);
            return;
        }

        if (!$this->chatbot_model->check_rate_limit($input['visitor_id'])) {
            $this->sendJsonError(_l('chatbot_rate_limit_exceeded'), 429);
            return;
        }

        try {
            // Get or create conversation
            $conversation = $chatbot->getConversation(
                $input['visitor_id'],
                $input['session_id'],
                [
                    'visitor_info' => $this->getVisitorInfo()
                ]
            );

            // Check message limit
            if ($chatbot->hasReachedMessageLimit($conversation)) {
                $this->sendJson([
                    'success' => false,
                    'message_limit_reached' => true,
                    'message' => _l('chatbot_widget_message_limit'),
                    'conversation_id' => $conversation->id,
                ]);
                return;
            }

            // Check if conversation is closed
            if ($conversation->status === \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_CLOSED) {
                $this->sendJson([
                    'success' => false,
                    'message' => _l('chatbot_conversation_has_been_closed'),
                    'conversation_id' => $conversation->id,
                    'closed' => true,
                ]);
                return;
            }

            // If human is handling, just save message and notify staff instantly
            if (!$conversation->shouldAIRespond()) {
                // Save user message
                $conversation->addMessage('user', $input['message']);

                // Notify staff INSTANTLY via Pusher (conversation channel + staff channel for sidebar)
                \PerfexChat\Neuron\Services\PusherService::sendVisitorMessage($conversation->id, $input['message']);
                \PerfexChat\Neuron\Services\PusherService::notifyStaffChannelNewMessage($conversation->id, $input['message'], 'visitor');

                // Bell notification for CRM (when not on live chat page)
                $this->notifyAssignedStaff($conversation, $input['message']);

                $staffName = null;
                $staffImage = null;
                if ($conversation->assigned_staff_id) {
                    $staffName = $this->chatbot_model->get_staff_display_name($conversation->assigned_staff_id);
                    $staffImage = $this->chatbot_model->get_staff_profile_image_url($conversation->assigned_staff_id, 'small');
                }

                $this->sendJson([
                    'success' => true,
                    'message' => null,
                    'conversation_id' => $conversation->id,
                    'human_handling' => true,
                    'staff_name' => $staffName,
                    'staff_image' => $staffImage,
                ]);
                return;
            }

            $this->chatbot_model->extract_and_save_contact_info($conversation, $input['message']);

            // If escalation is active (waiting for staff or pending), save message and notify staff
            if ($conversation->is_escalated || $conversation->status === \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_PENDING_ESCALATION) {
                $conversation->addMessage('user', $input['message']);
                \PerfexChat\Neuron\Services\PusherService::sendVisitorMessage($conversation->id, $input['message']);
                \PerfexChat\Neuron\Services\PusherService::notifyStaffChannelNewMessage($conversation->id, $input['message'], 'visitor');

                // Bell notification for CRM (when not on live chat page)
                $this->notifyAssignedStaff($conversation, $input['message']);

                $this->sendJson([
                    'success' => true,
                    'message' => null,
                    'conversation_id' => $conversation->id,
                    'escalated' => true,
                ]);
                return;
            }

            // If escalation form is already pending (visitor typed keyword again before submitting), skip re-triggering
            $visitorInfo = $conversation->getVisitorInfo();
            if (!empty($visitorInfo['escalation_form_pending'])) {
                $conversation->addMessage('user', $input['message']);

                // If visitor already has contact info, skip form and escalate directly
                if (!empty($visitorInfo['email']) || !empty($visitorInfo['name'])) {
                    $conversation->escalateToHuman();
                    $conversation->markEscalationUsed();
                    $conversation->setVisitorInfoField('escalation_form_pending', false);

                    \PerfexChat\Neuron\Services\PusherService::notifyStaffEscalation(
                        $conversation->id,
                        $chatbot->id,
                        substr($input['message'], 0, 100)
                    );
                    $staffIds = \PerfexChat\Neuron\Services\PusherService::getChatbotStaffIds();
                    \PerfexChat\Neuron\Services\PusherService::notifyStaffBell(
                        $staffIds,
                        $conversation->id,
                        'chatbot_visitor_wants_human'
                    );

                    $this->sendJson([
                        'success'         => true,
                        'message'         => null,
                        'conversation_id' => $conversation->id,
                        'escalated'       => true,
                        'waiting_for_agent' => true,
                    ]);
                    return;
                }

                $this->sendJson([
                    'success'         => true,
                    'message'         => null,
                    'conversation_id' => $conversation->id,
                    'escalation_form' => true,
                ]);
                return;
            }

            // Escalation keywords — one-time mandatory form per conversation.
            if (
                $chatbot->escalation_enabled
                && !$conversation->hasUsedEscalation()
                && $this->chatbot_model->contains_escalation_keywords($input['message'], $chatbot)
            ) {
                $staffIds = \PerfexChat\Neuron\Services\PusherService::getChatbotStaffIds();
                $staffOnline = !empty($staffIds) && \PerfexChat\Neuron\Services\PusherService::isAnyStaffOnline();

                if (!$staffOnline) {
                    $conversation->addMessage('user', $input['message']);
                    $noStaffMsg = _l('chatbot_no_staff_available');
                    $conversation->addMessage('assistant', $noStaffMsg);

                    $this->sendJson([
                        'success'         => true,
                        'message'         => $noStaffMsg,
                        'conversation_id' => $conversation->id,
                    ]);
                    return;
                }

                $conversation->addMessage('user', $input['message']);

                // If visitor already provided contact info (pre-chat lead form), skip the escalation form
                $existingInfo = $conversation->getVisitorInfo();
                if (!empty($existingInfo['email']) || !empty($existingInfo['name'])) {
                    $conversation->escalateToHuman();
                    $conversation->markEscalationUsed();
                    $conversation->setVisitorInfoField('escalation_form_pending', false);

                    \PerfexChat\Neuron\Services\PusherService::notifyStaffEscalation(
                        $conversation->id,
                        $chatbot->id,
                        substr($input['message'], 0, 100)
                    );
                    $staffIds = \PerfexChat\Neuron\Services\PusherService::getChatbotStaffIds();
                    \PerfexChat\Neuron\Services\PusherService::notifyStaffBell(
                        $staffIds,
                        $conversation->id,
                        'chatbot_visitor_wants_human'
                    );

                    $this->sendJson([
                        'success'         => true,
                        'message'         => null,
                        'conversation_id' => $conversation->id,
                        'escalated'       => true,
                        'waiting_for_agent' => true,
                    ]);
                    return;
                }

                $formIntro = _l('chatbot_escalation_form_intro');
                $conversation->addMessage('assistant', $formIntro);

                $conversation->setVisitorInfoField('escalation_form_pending', true);

                $this->sendJson([
                    'success'         => true,
                    'message'         => $formIntro,
                    'conversation_id' => $conversation->id,
                    'escalation_form' => true,
                ]);
                return;
            }

            // Create agent and get response
            $agent = new \PerfexChat\Neuron\Agents\ChatbotAgent($chatbot, $conversation);

            set_time_limit(120);

            // Save user message BEFORE calling AI
            $conversation->addMessage('user', $input['message']);

            $response = $agent->chat(new \NeuronAI\Chat\Messages\UserMessage($input['message']));
            $content = $response->getContent();

            // Reload conversation from DB -- AI tools may have changed status (e.g. escalate_to_human)
            $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::find($conversation->id);

            // Safety net: AI said something like "I'll connect you with support" but
            // didn't actually call the tool. Only offer if escalation hasn't been used yet.
            if (
                $chatbot->escalation_enabled
                && !$conversation->is_escalated
                && !$conversation->hasUsedEscalation()
                && $this->chatbot_model->ai_response_suggests_escalation($content)
            ) {
                $staffIds = \PerfexChat\Neuron\Services\PusherService::getChatbotStaffIds();
                $staffOnline = !empty($staffIds) && \PerfexChat\Neuron\Services\PusherService::isAnyStaffOnline();

                if ($staffOnline) {
                    $formIntro = _l('chatbot_escalation_form_intro');
                    $conversation->addMessage('assistant', $formIntro);
                    $conversation->setVisitorInfoField('escalation_form_pending', true);

                    $this->sendJson([
                        'success'         => true,
                        'message'         => $formIntro,
                        'conversation_id' => $conversation->id,
                        'escalation_form' => true,
                    ]);
                    return;
                }
            }

            // Check for fallback marker (AI couldn't answer from training data)
            $isFallback = str_contains($content, '[FALLBACK]');
            if ($isFallback) {
                $content = trim(str_replace('[FALLBACK]', '', $content));
                $fallbackCount = $conversation->incrementFallbackCount();

                if ($fallbackCount >= 2 && $chatbot->escalation_enabled && !$conversation->hasUsedEscalation()) {
                    $content .= "\n\n" . _l('chatbot_fallback_suggest_escalation');
                }
            } else {
                $conversation->resetFallbackCount();
            }

            \PerfexChat\Neuron\Services\PusherService::trigger(
                \PerfexChat\Neuron\Services\PusherService::getConversationChannel($conversation->id),
                'ai-message',
                [
                    'conversation_id' => $conversation->id,
                    'role' => 'assistant',
                    'content' => $content,
                    'time' => date('g:i A'),
                ]
            );

            $remaining = $chatbot->getRemainingMessages($conversation);

            $responseData = [
                'success' => true,
                'message' => $this->formatMarkdown($content),
                'conversation_id' => $conversation->id,
                'fallback_count' => $conversation->fallback_count ?? 0,
            ];

            if ($conversation->is_escalated) {
                $responseData['escalated'] = true;
            }

            if ($chatbot->max_messages && $remaining <= 0) {
                $responseData['message_limit_reached'] = true;
            }

            $this->sendJson($responseData);
        } catch (\Exception $e) {
            log_message('error', 'Chatbot error: ' . $e->getMessage());

            $this->sendJson([
                'success' => false,
                'message' => _l('chatbot_error_processing'),
                'conversation_id' => $conversation->id ?? null,
            ]);
        }
    }

    /**
     * Get conversation history.
     */
    public function history(string $widgetKey = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonError(_l('chatbot_post_required'), 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = $input['session_id'] ?? null;

        if (empty($sessionId)) {
            $this->sendJsonError(_l('chatbot_session_id_required'), 400);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);

        if (!$chatbot) {
            $this->sendJsonError(_l('chatbot_invalid_widget_key'), 404);
            return;
        }

        // Re-send CORS with allowed domains enforcement
        $this->setCorsHeaders($chatbot);

        // Validate domain
        if (!$this->validateDomain($chatbot)) {
            $this->sendJsonError(_l('chatbot_domain_not_allowed'), 403);
            return;
        }

        $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::findBySession(
            $chatbot->id,
            $sessionId
        );

        if (!$conversation) {
            $this->sendJson(['messages' => [], 'conversation_id' => null]);
            return;
        }

        $messages = $conversation->getMessagesForHistory();

        $staffName = null;
        $staffImage = null;
        if ($conversation->assigned_staff_id) {
            $staffName = $this->chatbot_model->get_staff_display_name($conversation->assigned_staff_id);
            $staffImage = $this->chatbot_model->get_staff_profile_image_url($conversation->assigned_staff_id, 'small');
        }

        $messageLimitReached = $chatbot->hasReachedMessageLimit($conversation);

        $waitingForAgent = $conversation->status === \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_PENDING_ESCALATION && $conversation->is_escalated;

        $visitorInfo = $conversation->getVisitorInfo();
        $escalationFormPending = !empty($visitorInfo['escalation_form_pending']);

        $this->sendJson([
            'messages' => array_map(function ($msg) {
                $metadata = is_array($msg->metadata) ? $msg->metadata : [];
                $isSystem = $msg->role === 'system' || !empty($metadata['is_system']);

                $content = $msg->content;
                // Rebuild system messages from metadata to ensure staff name is present
                if ($isSystem && !empty($metadata['staff_name'])) {
                    $type = $metadata['type'] ?? '';
                    if ($type === 'staff_joined') {
                        $content = _l('chatbot_staff_joined', [$metadata['staff_name']]);
                    } elseif ($type === 'conversation_closed') {
                        $content = _l('chatbot_closed_by_staff', [$metadata['staff_name']]);
                    }
                }
                if (!$isSystem && $msg->role === 'assistant') {
                    $content = $this->formatMarkdown($content);
                }

                $staffImage = null;
                if (!empty($metadata['is_staff']) && !empty($metadata['staff_id'])) {
                    $staffImage = $this->chatbot_model->get_staff_profile_image_url($metadata['staff_id'], 'small');
                }

                return [
                    'id' => $msg->id,
                    'role' => $isSystem ? 'system' : $msg->role,
                    'content' => $content,
                    'is_staff' => !empty($metadata['is_staff']),
                    'staff_name' => $metadata['staff_name'] ?? null,
                    'staff_image' => $staffImage,
                    'created_at' => $msg->created_at,
                ];
            }, $messages),
            'conversation_id' => $conversation->id,
            'status' => $conversation->status ?? 'active',
            'is_escalated' => (bool) $conversation->is_escalated,
            'human_handling' => $conversation->status === \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_HUMAN_HANDLING,
            'closed' => $conversation->status === \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_CLOSED,
            'waiting_for_agent' => $waitingForAgent,
            'escalation_form' => $escalationFormPending,
            'message_limit_reached' => $messageLimitReached,
            'staff_name' => $staffName,
            'staff_image' => $staffImage,
            'csat_eligible' => $conversation->status === \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_CLOSED,
        ]);
    }

    /**
     * Capture lead information.
     */
    public function lead(string $widgetKey = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonError(_l('chatbot_post_required'), 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['email']) || empty($input['session_id'])) {
            $this->sendJsonError(_l('chatbot_email_session_required'), 400);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);
        if (!$chatbot) {
            $this->sendJsonError(_l('chatbot_not_found'), 404);
            return;
        }

        $this->setCorsHeaders($chatbot);

        if (!$this->validateDomain($chatbot)) {
            $this->sendJsonError(_l('chatbot_domain_not_allowed'), 403);
            return;
        }

        $visitorId = $input['visitor_id'] ?? $input['session_id'];
        if (!$this->chatbot_model->check_rate_limit($visitorId)) {
            $this->sendJsonError(_l('chatbot_rate_limit_exceeded'), 429);
            return;
        }

        $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::findBySession(
            $chatbot->id,
            $input['session_id']
        );

        if (!$conversation) {
            $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::create([
                'chatbot_id' => $chatbot->id,
                'session_id' => $input['session_id'],
                'visitor_id' => $input['visitor_id'] ?? null,
            ]);
        }

        if (!$conversation) {
            $this->sendJsonError(_l('chatbot_could_not_save_lead'), 500);
            return;
        }

        try {
            $visitorInfo = $this->getVisitorInfo();
            $email = $input['email'] ?? ($visitorInfo['email'] ?? null);
            $name  = $input['name'] ?? ($visitorInfo['name'] ?? null);
            $phone = $input['phone'] ?? ($visitorInfo['phone'] ?? null);

            // Always update visitor_info on the conversation
            $currentInfo = $conversation->getVisitorInfo();
            $currentInfo = array_merge($currentInfo, $visitorInfo);
            $currentInfo['email'] = $email;
            if ($name) $currentInfo['name'] = $name;
            if ($phone) $currentInfo['phone'] = $phone;

            $contact = $this->chatbot_model->find_contact_by_email($email);
            if ($contact) {
                $currentInfo['contact_id'] = (int) $contact->id;
                if (empty($currentInfo['name'])) {
                    $currentInfo['name'] = trim($contact->firstname . ' ' . $contact->lastname);
                }
                $currentInfo['client_id'] = $contact->userid;
            }

            $conversation->update(['visitor_info' => $currentInfo]);
            $conversation->visitor_info = $currentInfo;

            $crmLeadId = null;

            // Only create CRM lead if capture_leads is enabled
            if ($chatbot->capture_leads) {
                $messages = $conversation->getMessages();
                $description = _l('chatbot_lead_captured_from') . "\n\n";
                $description .= _l('chatbot_lead_conversation_label') . "\n";
                foreach (array_slice($messages, 0, 10) as $msg) {
                    $role = $msg->role === 'user' ? _l('chatbot_lead_role_visitor') : _l('chatbot_lead_role_bot');
                    $description .= "{$role}: {$msg->content}\n";
                }

                $crmLeadId = $conversation->createCrmLead($email, $name, $phone, $description);
            }

            // Record GDPR consent
            if (!empty($input['gdpr_consent']) && function_exists('is_gdpr') && is_gdpr()) {
                $appearance = $chatbot->appearance ?? [];
                $purposeId = $appearance['gdpr_consent_purpose_id'] ?? null;

                if ($purposeId) {
                    $this->load->model('gdpr_model');
                    $this->gdpr_model->add_consent([
                        'purpose_id' => (int) $purposeId,
                        'lead_id' => $crmLeadId ?: 0,
                        'contact_id' => $contact->id ?? 0,
                        'action' => 'opt-in',
                        'description' => 'Chatbot widget consent — ' . $email,
                        'opt_in_purpose_description' => 'Consented via chatbot widget on ' . ($this->input->server('HTTP_REFERER') ?: 'unknown domain'),
                        'ip' => $this->input->ip_address(),
                    ]);
                }
            }

            $this->sendJson([
                'success' => true,
                'lead_id' => $crmLeadId,
                'conversation_id' => $conversation->id,
                'message' => $chatbot->lead_capture_success_message
                    ?: _l('chatbot_lead_success_default'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Lead capture error: ' . $e->getMessage());
            $this->sendJsonError(_l('chatbot_could_not_save_lead'), 500);
        }
    }

    /**
     * Build the widget JavaScript.
     */
    private function buildWidgetJavaScript(\PerfexChat\Neuron\Models\Chatbot $chatbot): string
    {
        $config = [
            'api_url' => site_url('prchat/Chatbot_Controller'),
            'widget_key' => $chatbot->widget_key,
        ];

        $configJson = json_encode($config, JSON_UNESCAPED_SLASHES);
        $widgetKey = $chatbot->widget_key;
        $minPath = APPPATH . '../modules/prchat/widget/assets/js/chatbot-widget.min.js';
        $srcPath = APPPATH . '../modules/prchat/widget/assets/js/chatbot-widget.js';
        $widgetJsPath = file_exists($minPath) ? $minPath : $srcPath;

        if (!file_exists($widgetJsPath)) {
            return "console.error('Chatbot widget not found');";
        }

        $widgetJs = file_get_contents($widgetJsPath);
        $delayMs = self::WIDGET_DELAY_MS;

        return <<<JS
            // Chatbot Widget Configuration
            window.CHATBOT_CONFIG = {$configJson};

            {$widgetJs}

            setTimeout(function(){
                if (!document.querySelector("chatbot-widget")) {
                    if (document.readyState === "loading") {
                        document.addEventListener("DOMContentLoaded", function() {
                            const chatbot = document.createElement("chatbot-widget");
                            chatbot.setAttribute("widget-key", "{$widgetKey}");
                            document.body.appendChild(chatbot);
                        });
                    } else {
                        const chatbot = document.createElement("chatbot-widget");
                        chatbot.setAttribute("widget-key", "{$widgetKey}");
                        document.body.appendChild(chatbot);
                    }
                }
            }, {$delayMs});
            JS;
    }

    /**
     * Validate if request is from allowed domain.
     */
    private function validateDomain(\PerfexChat\Neuron\Models\Chatbot $chatbot): bool
    {
        $allowedDomains = $chatbot->allowed_domains;

        // No domains configured = allow all (no restrictions)
        if (empty($allowedDomains)) {
            return true;
        }

        // Get referer
        $referer = $this->input->server('HTTP_REFERER');

        // Block requests without referer when domains are configured
        // This prevents direct API calls and security testing tools from bypassing domain restrictions
        if (!$referer) {
            $ipAddress = $this->input->ip_address();
            log_activity("Chatbot Security: Blocked direct API request to chatbot #{$chatbot->id} from IP {$ipAddress} - no referer header");
            return false;
        }

        $domain = parse_url($referer, PHP_URL_HOST);
        if (!$domain) {
            $ipAddress = $this->input->ip_address();
            log_activity("Chatbot Security: Blocked request to chatbot #{$chatbot->id} from '{$referer}' (IP: {$ipAddress}) - invalid referer");
            return false;
        }

        // Allow same-origin requests (same domain as this server)
        $serverHost = $this->input->server('HTTP_HOST');
        if ($serverHost && preg_replace('/^www\./', '', $domain) === preg_replace('/^www\./', '', $serverHost)) {
            return true;
        }

        // Remove www prefix for comparison
        $domain = preg_replace('/^www\./', '', strtolower($domain));

        // Check if domain matches allowed list
        $matches = $this->matchesDomainList($domain, $allowedDomains);

        if (!$matches) {
            $ipAddress = $this->input->ip_address();
            log_activity("Chatbot Security: Blocked unauthorized request to chatbot #{$chatbot->id} from domain '{$domain}' (IP: {$ipAddress})");
        }

        return $matches;
    }

    /**
     * Check if a hostname matches any entry in a domain list.
     * Supports exact match, subdomain match, and wildcard (*.example.com).
     */
    private function matchesDomainList(string $hostname, array $domainList): bool
    {
        foreach ($domainList as $allowed) {
            $clean = preg_replace('#^https?://#', '', $allowed);
            $clean = preg_replace('/:\d+$/', '', $clean);
            $clean = preg_replace('/^www\./', '', strtolower(trim($clean, '/')));

            // Wildcard: *.example.com matches any subdomain of example.com
            if (str_starts_with($clean, '*.')) {
                $base = substr($clean, 2); // e.g. "example.com"
                if ($hostname === $base || str_ends_with($hostname, '.' . $base)) {
                    return true;
                }
            } else {
                // Exact or subdomain match
                if ($hostname === $clean || str_ends_with($hostname, '.' . $clean)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Format markdown to HTML.
     * Note: Does NOT escape HTML - frontend will handle escaping via esc() function.
     * This prevents double-escaping issues (e.g. &#039; displaying literally).
     */
    private function formatMarkdown(string $text): string
    {
        $text = strip_tags($text, '<br>');

        // Strip any internal markers that may have slipped through
        $text = str_replace('[FALLBACK]', '', $text);

        // Apply markdown formatting (frontend esc() will handle HTML escaping for security)
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/`(.+?)`/s', '<code>$1</code>', $text);
        // Only allow http/https links
        $text = preg_replace('/\[(.+?)\]\((https?:\/\/.+?)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $text);
        $text = nl2br($text);

        return trim($text);
    }

    /**
     * Upload a file from the chatbot widget or live chat support panel.
     * Accepts POST with: userfile, conversation_id, token
     */
    public function upload()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        $conversationId = $this->input->post('conversation_id');
        $token = $this->input->post('token');
        $staffUpload = $this->input->post('staff_upload') === '1';

        if ($staffUpload) {
            if (!is_staff_logged_in()) {
                $this->sendJsonError('Unauthorized', 401);
                return;
            }
            if (!$conversationId) {
                $this->sendJsonError('Missing conversation_id', 400);
                return;
            }
        } else {
            if (!$conversationId || !$token) {
                $this->sendJsonError('Missing parameters', 400);
                return;
            }
            $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::find($conversationId);
            if (!$conversation || $conversation->session_id !== $token) {
                $this->sendJsonError('Invalid session', 403);
                return;
            }
        }

        $convId = (int) $conversationId;
        $uploadDir = module_dir_path('prchat', 'uploads/chatbot/' . $convId . '/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            file_put_contents(module_dir_path('prchat', 'uploads/chatbot/') . 'index.html', '');
            file_put_contents($uploadDir . 'index.html', '');
        }

        $sender = $staffUpload ? 'staff' : 'visitor';

        $config = [
            'upload_path'   => $uploadDir,
            'allowed_types' => 'gif|jpg|jpeg|png|webp|bmp|pdf|doc|docx|xls|xlsx|txt|csv|zip',
            'max_size'      => 5120,
            'file_name'     => $sender . '_' . time() . '_' . mt_rand(100, 999),
        ];

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('userfile')) {
            $data = $this->upload->data();
            $fileUrl = base_url('modules/prchat/uploads/chatbot/' . $convId . '/' . $data['file_name']);

            $this->sendJson([
                'success'   => true,
                'file_name' => $data['file_name'],
                'file_url'  => $fileUrl,
                'file_type' => $data['file_type'],
                'is_image'  => $data['is_image'],
            ]);
        } else {
            $this->sendJson([
                'success' => false,
                'error'   => strip_tags($this->upload->display_errors()),
            ]);
        }
    }

    /**
     * Send JSON response.
     */
    private function sendJson(array $data): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($data);
    }

    /**
     * Send JSON error response.
     */
    private function sendJsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->sendJson(['success' => false, 'error' => $message]);
    }

    /**
     * Send error response.
     */
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo "// Error: {$message}";
    }

    /**
     * Build visitor info array for anonymous website visitors.
     * Note: Chatbot is only for external websites, not logged-in CRM clients.
     */
    private function getVisitorInfo(): array
    {
        $ip = $this->input->ip_address();
        $location = $this->getIpGeolocation($ip);

        return [
            'ip' => $ip,
            'user_agent' => $this->input->user_agent(),
            'referer' => $this->input->server('HTTP_REFERER'),
            'country' => $location['country'] ?? null,
            'country_code' => $location['country_code'] ?? null,
            'region' => $location['region'] ?? null,
            'city' => $location['city'] ?? null,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'timezone' => $location['timezone'] ?? null,
        ];
    }

    /**
     * Get IP geolocation using ip-api.com (free, no API key required).
     * Rate limit: 45 requests/minute
     * Fails gracefully if API is unavailable.
     */
    private function getIpGeolocation(string $ip): array
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return [];
        }

        $cacheKey = 'chatbot_geo_' . md5($ip);
        $cached = get_option($cacheKey);
        if ($cached) {
            $decoded = json_decode($cached, true);
            if (is_array($decoded) && isset($decoded['_cached_at']) && (time() - $decoded['_cached_at']) < 86400) {
                unset($decoded['_cached_at']);
                return $decoded;
            }
        }

        try {
            $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,region,regionName,city,lat,lon,timezone";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 2,
                    'ignore_errors' => true,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return [];
            }

            $data = json_decode($response, true);

            if (!$data || ($data['status'] ?? '') !== 'success') {
                return [];
            }

            $result = [
                'country' => $data['country'] ?? null,
                'country_code' => $data['countryCode'] ?? null,
                'region' => $data['regionName'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $data['lat'] ?? null,
                'longitude' => $data['lon'] ?? null,
                'timezone' => $data['timezone'] ?? null,
            ];

            $toCache = $result;
            $toCache['_cached_at'] = time();
            add_option($cacheKey, json_encode($toCache));

            return $result;
        } catch (Exception $e) {
            log_message('error', "[Chatbot] Geolocation exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Confirm escalation — called when visitor submits the escalation form.
     * This is where the actual escalation + staff notification happens.
     */
    public function confirm_escalation(string $widgetKey = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonError(_l('chatbot_post_required'), 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['session_id']) || empty($input['conversation_id'])) {
            $this->sendJsonError(_l('chatbot_missing_required_fields'), 400);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);
        if (!$chatbot) {
            $this->sendJsonError(_l('chatbot_invalid_widget_key'), 404);
            return;
        }

        $this->setCorsHeaders($chatbot);

        $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::find((int) $input['conversation_id']);
        if (!$conversation || $conversation->session_id !== $input['session_id']) {
            $this->sendJsonError(_l('chatbot_conversation_not_found'), 404);
            return;
        }

        if ((int)$conversation->chatbot_id !== (int)$chatbot->id) {
            echo json_encode(['success' => false, 'error' => 'Invalid conversation']);
            return;
        }

        if ($conversation->is_escalated) {
            $this->sendJson(['success' => true, 'already_escalated' => true]);
            return;
        }

        $conversation->escalateToHuman();
        $conversation->markEscalationUsed();
        $conversation->setVisitorInfoField('escalation_form_pending', false);

        $staffOnline = \PerfexChat\Neuron\Services\PusherService::isAnyStaffOnline();
        $preview = '';
        $messages = $conversation->getMessages();
        foreach (array_reverse($messages) as $msg) {
            if ($msg->role === 'user') {
                $preview = substr($msg->content, 0, 100);
                break;
            }
        }

        \PerfexChat\Neuron\Services\PusherService::notifyStaffEscalation(
            $conversation->id,
            $chatbot->id,
            $preview
        );

        $staffIds = \PerfexChat\Neuron\Services\PusherService::getChatbotStaffIds();
        \PerfexChat\Neuron\Services\PusherService::notifyStaffBell(
            $staffIds,
            $conversation->id,
            'chatbot_visitor_wants_human'
        );

        if (!$staffOnline) {
            $conversation->markNeedsFollowup();
            $noStaffMsg = _l('chatbot_no_staff_available');
            $conversation->addMessage('assistant', $noStaffMsg);

            $this->sendJson([
                'success' => true,
                'staff_online' => false,
                'message' => $noStaffMsg,
            ]);
            return;
        }

        $this->sendJson([
            'success' => true,
            'staff_online' => $staffOnline,
        ]);
    }

    /**
     * Handle visitor typing indicator.
     */
    public function typing(string $widgetKey = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonError(_l('chatbot_post_required'), 405);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);
        if (!$chatbot) {
            $this->sendJson(['success' => false]);
            return;
        }

        $this->setCorsHeaders($chatbot);

        $input = json_decode(file_get_contents('php://input'), true);
        $conversationId = (int) ($input['conversation_id'] ?? 0);
        $isTyping = !empty($input['typing']);

        if ($conversationId > 0) {
            \PerfexChat\Neuron\Services\PusherService::notifyVisitorTyping($conversationId, $isTyping);
        }

        $this->sendJson(['success' => true]);
    }

    /**
     * Submit CSAT rating for a conversation
     */
    public function submit_rating(string $widgetKey = '')
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->sendJson(['success' => false, 'error' => _l('chatbot_method_not_allowed')]);
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::findByWidgetKey($widgetKey);
        if ($chatbot) {
            $this->setCorsHeaders($chatbot);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $conversationId = (int) ($input['conversation_id'] ?? 0);
        $sessionId = trim($input['session_id'] ?? '');
        $score = (int) ($input['score'] ?? 0);
        $comment = trim($input['comment'] ?? '');

        if (!$conversationId || $score < 1 || $score > 5) {
            http_response_code(400);
            $this->sendJson(['success' => false, 'error' => _l('chatbot_invalid_rating')]);
            return;
        }

        $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::find($conversationId);
        if (!$conversation) {
            http_response_code(404);
            $this->sendJson(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        // Session validation: ensure the requester owns this conversation
        if (empty($sessionId) || $conversation->session_id !== $sessionId) {
            http_response_code(403);
            $this->sendJson(['success' => false, 'error' => _l('chatbot_access_denied')]);
            return;
        }

        if ($chatbot && (int)$conversation->chatbot_id !== (int)$chatbot->id) {
            echo json_encode(['success' => false, 'error' => 'Invalid conversation']);
            return;
        }

        // Check if already rated
        if ($conversation->csat_score !== null) {
            http_response_code(400);
            $this->sendJson(['success' => false, 'error' => _l('chatbot_already_rated')]);
            return;
        }

        $this->chatbot_model->update_csat($conversationId, $score, $comment);

        \PerfexChat\Neuron\Services\PusherService::trigger('chatbot-staff', 'csat-updated', [
            'conversation_id' => $conversationId,
            'csat_score'      => $score,
            'csat_comment'    => $comment ?: null,
            'csat_at'         => date('Y-m-d H:i:s'),
        ]);

        $notifyStaffId = $conversation->assigned_staff_id ?: $conversation->closed_by_staff_id;
        $staffIds = $notifyStaffId
            ? [(int) $notifyStaffId]
            : \PerfexChat\Neuron\Services\PusherService::getChatbotStaffIds();

        if (!empty($staffIds)) {
            \PerfexChat\Neuron\Services\PusherService::notifyStaffBell(
                $staffIds,
                $conversation->id,
                'chatbot_csat_rating_received'
            );
        }

        $this->sendJson(['success' => true, 'message' => _l('chatbot_csat_thank_you')]);
    }

    /**
     * Build the display name for the widget header based on the display name mode setting
     *
     * @param object $chatbot The chatbot object
     * @param array $appearance The appearance configuration array
     * @return string The constructed display name
     */
    private function buildDisplayName($chatbot, $appearance): string
    {
        $displayMode = $appearance['display_name_mode'] ?? 'chatbot_only';

        if ($displayMode === 'company_and_chatbot') {
            // Use company name + chatbot name
            return get_option('companyname') . ' ' . $chatbot->name;
        }

        return $chatbot->name;
    }

    /**
     * Send bell notification to assigned staff when visitor sends a message
     *
     * @param object $conversation The conversation object
     * @param string $message The visitor's message
     * @return void
     */
    private function notifyAssignedStaff($conversation, $message)
    {
        if (!$conversation->assigned_staff_id) {
            return;
        }

        try {
            // Check if this staff member has desktop notifications enabled (per-staff preference)
            $staffMeta = $this->db->where('staff_id', $conversation->assigned_staff_id)
                                   ->where('meta_key', 'chatbot_desktop_notifications_enabled')
                                   ->get(db_prefix() . 'user_meta')
                                   ->row();

            // Default to enabled if not set
            if ($staffMeta && $staffMeta->meta_value === '0') {
                return;
            }

            // Get visitor info for notification
            $visitorInfo = $conversation->getVisitorInfo();
            $visitorName = $visitorInfo['name'] ?? $visitorInfo['email'] ?? _l('visitor');

            // Truncate message for preview
            $truncatedMessage = mb_strlen($message) > 30 ? mb_substr($message, 0, 30) . '...' : $message;

            // Use language key with placeholders to avoid "language line not found" errors
            // The notification system will call _l() with additional_data for sprintf formatting
            $notificationKey = 'chatbot_visitor_message_notification';
            $additionalData = [$visitorName, $truncatedMessage];

            // Send notification to staff member (both CRM bell + desktop notification)
            \PerfexChat\Neuron\Services\PusherService::notifyStaffBell(
                [$conversation->assigned_staff_id],
                $conversation->id,
                $notificationKey,
                $additionalData
            );
        } catch (\Exception $e) {
            log_message('error', 'Chatbot notification error: ' . $e->getMessage());
        }
    }
}
