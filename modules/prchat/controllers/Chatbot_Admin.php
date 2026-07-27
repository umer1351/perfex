<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Chatbot Admin Controller
 * 
 * Admin interface for managing chatbots, training data, and viewing conversations.
 */
class Chatbot_Admin extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->app_modules->is_active('prchat')) {
            redirect('admin');
        }

        require_once APPPATH . '../modules/prchat/neuron/autoload.php';
        $this->load->model('Chatbot_model', 'chatbot_model');

        if (!staff_can('chatbot_support', 'prchat') && !staff_can('chatbot_manage', 'prchat')) {
            access_denied('prchat');
        }
    }

    /**
     * Check if staff has chatbot management permissions
     */
    private function requireManagePermission(): void
    {
        if (!staff_can('chatbot_manage', 'prchat')) {
            access_denied('prchat');
        }
    }

    /**
     * Main chatbot settings page (single chatbot mode).
     * Automatically creates or redirects to the default chatbot.
     */
    public function index(): void
    {
        $this->requireManagePermission();

        // Get or create the default chatbot
        $chatbot = $this->getOrCreateDefaultChatbot();

        $data['title'] = _l('chatbot_ai_chatbot');
        $data['chatbot'] = $chatbot;
        $data['leads'] = $chatbot->getLeads();

        // GDPR consent purposes for settings
        $data['consentPurposes'] = [];
        if (function_exists('is_gdpr') && is_gdpr()) {
            $this->load->model('gdpr_model');
            $data['consentPurposes'] = $this->gdpr_model->get_consent_purposes();
        }

        $data['cannedResponses'] = $this->chatbot_model->get_shared_canned_responses();
        $data['chatbot_tags'] = $this->chatbot_model->get_available_tags();

        $this->load->view('chatbots/chatbot_settings', $data);
    }

    /**
     * Get or create the default chatbot.
     */
    private function getOrCreateDefaultChatbot(): \PerfexChat\Neuron\Models\Chatbot
    {
        $chatbots = \PerfexChat\Neuron\Models\Chatbot::all();

        if (!empty($chatbots)) {
            return $chatbots[0];
        }

        // Create default chatbot
        $companyName = get_option('companyname') ?: 'Support';

        return \PerfexChat\Neuron\Models\Chatbot::create([
            'name' => $companyName . ' Assistant',
            'enabled' => 1,
            'ai_provider' => 'openai',
            'ai_model' => get_option('chatbot_default_model') ?: 'gpt-4o-mini',
            'system_prompt' => 'You are a helpful customer support assistant for ' . $companyName . '. Be friendly, professional, and concise.',
            'max_output_tokens' => 500,
            'temperature' => 0.7,
            'appearance' => [
                'primary_color' => '#007bff',
                'position' => 'bottom-right',
                'welcome_message' => 'Hi! How can I help you today?',
                'input_placeholder' => 'Type your message...',
                'intro_subtitle' => 'We typically reply within minutes',
                'display_name_mode' => 'chatbot_only',
            ],
            'context_window' => 10,
            'escalation_enabled' => 0,
            'escalation_message' => "I'll connect you with a team member who can help further.",
            'lead_fields' => [
                'email' => ['enabled' => true, 'required' => true, 'label' => 'Email'],
                'name' => ['enabled' => true, 'required' => false, 'label' => 'Name'],
            ],
            'created_by' => get_staff_user_id(),
        ]);
    }

    /**
     * Find a conversation and verify it belongs to the current chatbot.
     * Returns null if not found or chatbot_id mismatch.
     */
    private function findConversation(int $conversationId): ?\PerfexChat\Neuron\Models\ChatbotConversation
    {
        $conversation = \PerfexChat\Neuron\Models\ChatbotConversation::find($conversationId);
        if (!$conversation) {
            return null;
        }
        $chatbot = $this->getOrCreateDefaultChatbot();
        if ((int) $conversation->chatbot_id !== (int) $chatbot->id) {
            return null;
        }
        return $conversation;
    }

    /**
     * Save chatbot (create or update).
     */
    public function save(): void
    {
        $this->requireManagePermission();

        if ($this->input->post()) {
            $id = $this->input->post('id');

            $data = [
                'name' => $this->input->post('name'),
                'enabled' => $this->input->post('enabled') ? 1 : 0,
                'ai_provider' => 'openai',
                'ai_model' => $this->input->post('ai_model'),
                'system_prompt' => $this->input->post('system_prompt'),
                'max_output_tokens' => (int) $this->input->post('max_output_tokens') ?: 500,
                'temperature' => (float) $this->input->post('temperature') ?: 0.7,
                'max_messages' => $this->input->post('max_messages') ? (int) $this->input->post('max_messages') : null,
                'context_window' => (int) $this->input->post('context_window') ?: 10,
                'auto_close_timeout' => $this->input->post('auto_close_timeout') !== null ? (int) $this->input->post('auto_close_timeout') : 30,
                'capture_leads' => $this->input->post('capture_leads') ? 1 : 0,
                'lead_capture_success_message' => $this->input->post('lead_capture_success_message'),
                'escalation_enabled' => $this->input->post('escalation_enabled') ? 1 : 0,
                'escalation_message' => $this->input->post('escalation_message'),
                'escalation_keywords' => json_encode($this->parseEscalationKeywords()),
                'csat_enabled' => $this->input->post('csat_enabled') ? 1 : 0,
            ];

            // Parse appearance
            $data['appearance'] = [
                'primary_color' => $this->input->post('primary_color') ?: '#007bff',
                'header_bg_color' => $this->input->post('header_bg_color') ?: '#007bff',
                'position' => $this->input->post('position') ?: 'bottom-right',
                'distance_from_bottom' => (int) $this->input->post('distance_from_bottom') ?: 20,
                'distance_from_side' => (int) $this->input->post('distance_from_side') ?: 20,
                'icon_type' => $this->input->post('icon_type') ?: 'chat',
                'welcome_message' => $this->input->post('welcome_message'),
                'input_placeholder' => $this->input->post('input_placeholder'),
                'intro_subtitle' => $this->input->post('intro_subtitle'),
                'agent_avatar_url' => $this->input->post('agent_avatar_url'),
                'thinking_phrases' => $this->sanitizeThinkingPhrases($this->input->post('thinking_phrases')),
                'widget_language' => $this->input->post('widget_language') ?: 'english',
                'display_name_mode' => $this->input->post('display_name_mode') ?: 'chatbot_only',
                'gdpr_enabled' => $this->input->post('gdpr_enabled') ? 1 : 0,
                'gdpr_consent_purpose_id' => $this->input->post('gdpr_consent_purpose_id') ? (int) $this->input->post('gdpr_consent_purpose_id') : null,
                'gdpr_consent_text' => $this->input->post('gdpr_consent_text'),
                'gdpr_privacy_url' => $this->input->post('gdpr_privacy_url'),
                'proactive_enabled' => $this->input->post('proactive_enabled') ? 1 : 0,
                'proactive_delay_seconds' => (int) $this->input->post('proactive_delay_seconds') ?: 5,
                'visitor_file_upload' => $this->input->post('visitor_file_upload') ? 1 : 0,
            ];

            // Parse lead fields
            $data['lead_fields'] = [
                'email' => ['enabled' => true, 'required' => true, 'label' => 'Email'],
                'name' => [
                    'enabled' => (bool) $this->input->post('lead_field_name'),
                    'required' => (bool) $this->input->post('lead_field_name_required'),
                    'label' => 'Name',
                ],
                'phone' => [
                    'enabled' => (bool) $this->input->post('lead_field_phone'),
                    'required' => (bool) $this->input->post('lead_field_phone_required'),
                    'label' => 'Phone',
                ],
            ];

            if ($id) {
                // Update
                $chatbot = \PerfexChat\Neuron\Models\Chatbot::find($id);
                if ($chatbot) {
                    $chatbot->update($data);
                    set_alert('success', _l('chatbot_updated_successfully'));
                }
            } else {
                // Create
                $data['created_by'] = get_staff_user_id();
                \PerfexChat\Neuron\Models\Chatbot::create($data);
                set_alert('success', _l('chatbot_created_successfully'));
            }

            // Check if form was submitted from a specific tab
            $activeTab = $this->input->post('active_tab');
            $redirectUrl = admin_url('prchat/Chatbot_Admin');
            if ($activeTab === 'widget') {
                $redirectUrl .= '#widget';
            }

            redirect($redirectUrl);
        }

        redirect(admin_url('prchat/Chatbot_Admin'));
    }

    /**
     * Save allowed domains only (separate from main save to avoid overwriting other fields).
     * Accepts AJAX with domains_json or standard form with allowed_domains textarea fallback.
     */
    public function save_domains(): void
    {
        $this->requireManagePermission();

        if (!$this->input->post()) {
            redirect(admin_url('prchat/Chatbot_Admin'));
            return;
        }

        $id = (int) $this->input->post('id');
        $chatbot = \PerfexChat\Neuron\Models\Chatbot::find($id);
        $isAjax = $this->input->is_ajax_request();

        if (!$chatbot) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => _l('chatbot_not_found')]);
                return;
            }
            redirect(admin_url('prchat/Chatbot_Admin'));
            return;
        }

        // Parse domains from JSON (SPA) or textarea fallback
        $domainsJson = $this->input->post('domains_json');
        if ($domainsJson !== null) {
            $domains = json_decode($domainsJson, true);
            if (!is_array($domains)) {
                $domains = [];
            }
        } else {
            $raw = $this->input->post('allowed_domains');
            $domains = !empty($raw) ? array_filter(array_map('trim', explode("\n", $raw))) : [];
        }

        // Validate each domain
        $domainPattern = '/^(\*\.)?([a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/';
        $cleaned = [];
        $invalid = [];

        foreach ($domains as $d) {
            $d = trim($d);
            if (empty($d)) continue;

            // Strip protocol, www, trailing slashes, port
            $d = preg_replace('#^https?://#', '', $d);
            $d = preg_replace('/^www\./', '', $d);
            $d = rtrim($d, '/');
            $d = preg_replace('/:\d+$/', '', $d);
            $d = strtolower($d);

            if (!preg_match($domainPattern, $d)) {
                $invalid[] = $d;
                continue;
            }

            if (!in_array($d, $cleaned)) {
                $cleaned[] = $d;
            }
        }

        if (!empty($invalid)) {
            if ($isAjax) {
                echo json_encode([
                    'success' => false,
                    'message' => _l('chatbot_invalid_domains', [implode(', ', $invalid)]),
                ]);
                return;
            }
            set_alert('warning', _l('chatbot_invalid_domains', [implode(', ', $invalid)]));
            redirect(admin_url('prchat/Chatbot_Admin'));
            return;
        }

        $chatbot->update([
            'allowed_domains' => !empty($cleaned) ? $cleaned : null,
        ]);

        if ($isAjax) {
            echo json_encode(['success' => true, 'domains' => $cleaned]);
            return;
        }

        set_alert('success', _l('chatbot_updated_successfully'));
        redirect(admin_url('prchat/Chatbot_Admin'));
    }

    /**
     * Delete chatbot. 
     * maybe in the feature  will add multiple chatbots this is not used for now 
     */
    public function delete(int $id): void
    {
        $this->requireManagePermission();

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::find($id);

        if ($chatbot) {
            $chatbot->delete();
            set_alert('success', _l('chatbot_deleted_successfully'));
        }

        redirect(admin_url('prchat/Chatbot_Admin'));
    }


    /**
     * Add training link.
     */
    public function add_training_link(): void
    {
        $this->requireManagePermission();

        if ($this->input->post()) {
            $chatbotId = (int) $this->input->post('chatbot_id');
            $url = $this->input->post('url');
            $crawlSubpages = $this->input->post('crawl_subpages') ? 1 : 0;
            $crawlDepth = $crawlSubpages ? min(3, max(1, (int) $this->input->post('crawl_depth', true) ?: 1)) : 0;

            \PerfexChat\Neuron\Models\TrainingLink::create([
                'chatbot_id' => $chatbotId,
                'url' => $url,
                'crawl_subpages' => $crawlSubpages,
                'crawl_depth' => $crawlDepth,
            ]);

            // No processing here - everything happens when clicking "Train Data"
            $message = _l('chatbot_training_link_added');
            if ($crawlSubpages) {
                $message .= _l('chatbot_subpages_discovered');
            }

            set_alert('success', $message);
            redirect(admin_url('prchat/Chatbot_Admin#training'));
        }
    }

    /**
     * Delete training link.
     */
    public function delete_training_link(int $id): void
    {
        $this->requireManagePermission();

        $link = \PerfexChat\Neuron\Models\TrainingLink::find($id);
        if ($link) {
            $chatbot = \PerfexChat\Neuron\Models\Chatbot::find($link->chatbot_id);
            if ($chatbot) {
                $vectorStore = new \PerfexChat\Neuron\VectorStore\ChatbotVectorStore($chatbot);
                $subpages = $this->db->where('parent_link_id', $id)
                    ->get(db_prefix() . 'chatbot_training_links')->result();
                foreach ($subpages as $sub) {
                    $vectorStore->deleteByModel(new \PerfexChat\Neuron\Models\TrainingLink($sub));
                }
                $vectorStore->deleteByModel($link);
            }

            $this->db->where('parent_link_id', $id)->delete(db_prefix() . 'chatbot_training_links');
            $link->delete();

            set_alert('success', _l('chatbot_training_link_deleted'));
            redirect(admin_url('prchat/Chatbot_Admin#training'));
        }
        redirect(admin_url('prchat/Chatbot_Admin'));
    }

    /**
     * Delete all training links for a chatbot (POST only).
     */
    public function delete_all_training_links(int $chatbotId): void
    {
        $this->requireManagePermission();

        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::find($chatbotId);
        if ($chatbot) {
            $vectorStore = new \PerfexChat\Neuron\VectorStore\ChatbotVectorStore($chatbot);
            foreach (\PerfexChat\Neuron\Models\TrainingLink::forChatbot($chatbotId) as $link) {
                $vectorStore->deleteByModel($link);
            }
        }

        $this->db->where('chatbot_id', $chatbotId)->delete(db_prefix() . 'chatbot_training_links');

        set_alert('success', _l('chatbot_all_training_links_deleted'));
        redirect(admin_url('prchat/Chatbot_Admin#training'));
    }

    /**
     * Clear all training data for a chatbot (POST only).
     */
    public function clear_all_training(int $chatbotId): void
    {
        $this->requireManagePermission();

        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }
        // Delete all training data from database
        foreach ($this->getTrainingTables() as $table) {
            $this->db->where('chatbot_id', $chatbotId)->delete($table);
        }

        // Delete vector store file - use same path as ChatbotVectorStore
        $vectorFile = module_dir_path('prchat', 'uploads/vectors/') . 'chatbot_' . $chatbotId . '.json';
        if (file_exists($vectorFile)) {
            unlink($vectorFile);
        }

        set_alert('success', _l('chatbot_all_training_cleared'));
        redirect(admin_url('prchat/Chatbot_Admin#training'));
    }

    /**
     * Get the list of training data table names.
     */
    private function getTrainingTables(): array
    {
        return [
            db_prefix() . 'chatbot_training_links',
            db_prefix() . 'chatbot_training_qa',
            db_prefix() . 'chatbot_training_files',
        ];
    }

    /**
     * Get staff display name with fallback for empty names.
     */
    private function getStaffDisplayName(): string
    {
        $name = trim(get_staff_full_name());
        return $name !== '' ? $name : _l('chatbot_support_team_fallback');
    }

    private function parseEscalationKeywords(): array
    {
        $phrasesRaw = $this->input->post('escalation_phrases');
        $coreWordsRaw = $this->input->post('escalation_core_words');

        $phrases = array_values(array_filter(array_map('trim', explode("\n", $phrasesRaw ?? ''))));
        $coreWords = array_values(array_filter(array_map('trim', explode(',', $coreWordsRaw ?? ''))));

        return [
            'phrases' => $phrases,
            'core_words' => $coreWords,
        ];
    }

    /**
     * Remove a single training item's vectors from the store.
     */
    private function deleteItemVectors($model): void
    {
        $chatbot = \PerfexChat\Neuron\Models\Chatbot::find($model->chatbot_id);
        if ($chatbot) {
            $vectorStore = new \PerfexChat\Neuron\VectorStore\ChatbotVectorStore($chatbot);
            $vectorStore->deleteByModel($model);
        }
    }

    /**
     * Upload a training file.
     */
    public function add_training_file(): void
    {
        $this->requireManagePermission();

        if (!empty($_FILES['training_file']['name'])) {
            $chatbotId = (int) $this->input->post('chatbot_id');

            // Validate file type
            $extension = strtolower(pathinfo($_FILES['training_file']['name'], PATHINFO_EXTENSION));
            $allowed = \PerfexChat\Neuron\Models\TrainingFile::supportedExtensions();

            if (!in_array($extension, $allowed)) {
                set_alert('danger', _l('chatbot_file_invalid_type'));
                redirect(admin_url('prchat/Chatbot_Admin#training'));
                return;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = module_dir_path('prchat', 'uploads/training_files/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $newFilename = uniqid('train_') . '.' . $extension;
            $relativePath = 'modules/prchat/uploads/training_files/' . $newFilename;
            $fullPath = FCPATH . $relativePath;

            if (move_uploaded_file($_FILES['training_file']['tmp_name'], $fullPath)) {
                $fileData = base64_encode(file_get_contents($fullPath));

                \PerfexChat\Neuron\Models\TrainingFile::create([
                    'chatbot_id' => $chatbotId,
                    'original_name' => $_FILES['training_file']['name'],
                    'file_path' => $relativePath,
                    'file_type' => $extension,
                    'file_size' => $_FILES['training_file']['size'],
                    'file_data' => $fileData,
                ]);

                set_alert('success', _l('chatbot_file_added'));
            } else {
                set_alert('danger', _l('chatbot_file_upload_failed'));
            }

            redirect(admin_url('prchat/Chatbot_Admin#training'));
        }
    }

    /**
     * Delete a training file.
     */
    public function delete_training_file(int $id): void
    {
        $this->requireManagePermission();

        $file = \PerfexChat\Neuron\Models\TrainingFile::find($id);
        if ($file) {
            $this->deleteItemVectors($file);
            $file->delete();
            set_alert('success', _l('chatbot_file_deleted'));
            redirect(admin_url('prchat/Chatbot_Admin#training'));
        }
        redirect(admin_url('prchat/Chatbot_Admin'));
    }

    /**
     * Add training Q&A.
     */
    public function add_training_qa(): void
    {
        $this->requireManagePermission();

        if ($this->input->post()) {
            $chatbotId = (int) $this->input->post('chatbot_id');

            \PerfexChat\Neuron\Models\TrainingQA::create([
                'chatbot_id' => $chatbotId,
                'question' => $this->input->post('question'),
                'answer' => $this->input->post('answer'),
            ]);

            set_alert('success', _l('chatbot_qa_added'));
            redirect(admin_url('prchat/Chatbot_Admin#training'));
        }
    }

    /**
     * Delete training Q&A.
     */
    public function delete_training_qa(int $id): void
    {
        $this->requireManagePermission();

        $qa = \PerfexChat\Neuron\Models\TrainingQA::find($id);
        if ($qa) {
            $this->deleteItemVectors($qa);
            $qa->delete();
            set_alert('success', _l('chatbot_qa_deleted'));
            redirect(admin_url('prchat/Chatbot_Admin#training'));
        }
        redirect(admin_url('prchat/Chatbot_Admin'));
    }

    /**
     * Retry a single failed training item (resets to pending).
     */
    public function retry_training_item(string $type, int $id): void
    {
        $this->requireManagePermission();

        $modelMap = [
            'link' => \PerfexChat\Neuron\Models\TrainingLink::class,
            'qa'   => \PerfexChat\Neuron\Models\TrainingQA::class,
            'file' => \PerfexChat\Neuron\Models\TrainingFile::class,
        ];

        if (!isset($modelMap[$type])) {
            set_alert('danger', 'Invalid training type');
            redirect(admin_url('prchat/Chatbot_Admin#training'));
            return;
        }

        $item = $modelMap[$type]::find($id);
        if ($item) {
            $this->deleteItemVectors($item);

            $update = ['training_status' => 'pending', 'error_message' => null];
            if ($type === 'link' || $type === 'file') {
                $update['processing_status'] = 'pending';
            }

            $item->update($update);
            set_alert('success', _l('chatbot_item_reset_for_retry'));
        }

        redirect(admin_url('prchat/Chatbot_Admin#training'));
    }

    /**
     * Get training progress for polling.
     */
    public function training_progress(int $id): void
    {
        $this->requireManagePermission();

        header('Content-Type: application/json');
        $file = sys_get_temp_dir() . '/prchat_training_' . $id . '.json';
        if (file_exists($file)) {
            readfile($file);
        } else {
            echo json_encode(['phase' => 'idle']);
        }
    }

    /**
     * Train the chatbot (process all pending training data).
     */
    public function run_training(int $id): void
    {
        $this->requireManagePermission();

        header('Content-Type: application/json');
        ob_start();

        $chatbot = \PerfexChat\Neuron\Models\Chatbot::find($id);

        if (!$chatbot) {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => _l('chatbot_not_found')]);
            return;
        }

        set_time_limit(600);
        session_write_close();

        $progressFile = sys_get_temp_dir() . '/prchat_training_' . $id . '.json';
        $writeProgress = function (array $data) use ($progressFile) {
            file_put_contents($progressFile, json_encode($data));
        };

        try {
            $agent = new \PerfexChat\Neuron\Agents\ChatbotAgent($chatbot);
            /** @var \PerfexChat\Neuron\VectorStore\ChatbotVectorStore $vectorStore */
            $vectorStore = $agent->resolveVectorStore();
            $embeddings = $agent->resolveEmbeddings();

            $writeProgress(['phase' => 'init', 'message' => _l('chatbot_starting_training')]);

            // Vector file missing but DB says items are trained — reset to pending so they get reprocessed
            if (!$vectorStore->hasDocuments()) {
                foreach ($this->getTrainingTables() as $table) {
                    $this->db->where('chatbot_id', $id)
                        ->where('training_status', 'trained')
                        ->update($table, ['training_status' => 'pending', 'error_message' => null]);
                }
            }

            // Check for embedding dimension mismatch and auto-clear if needed (only when model/embedding size actually changed)
            $currentDimensions = (int) $vectorStore->getStoredEmbeddingDimensions();
            if ($currentDimensions > 0) {
                $testEmbedding = $embeddings->embedText('test');
                $newDimensions = (int) count($testEmbedding);

                if ($newDimensions > 0 && $currentDimensions !== $newDimensions) {
                    $vectorStore->clear();

                    foreach ($this->getTrainingTables() as $table) {
                        $this->db->where('chatbot_id', $id)
                            ->update($table, ['training_status' => 'pending', 'error_message' => null]);
                    }
                }
            }

            $processed = 0;
            $processedUrls = 0;

            // Process parent links (may discover subpages)
            $parentLinks = $this->db->where('chatbot_id', $id)
                ->where('processing_status', 'pending')
                ->where('parent_link_id IS NULL', null, false)
                ->get(db_prefix() . 'chatbot_training_links')
                ->result();

            foreach ($parentLinks as $linkData) {
                $link = new \PerfexChat\Neuron\Models\TrainingLink($linkData);
                $link->process();
                $processedUrls++;
                $processed++;
                $writeProgress(['phase' => 'processing', 'message' => _l('chatbot_fetching_urls'), 'current' => $processed, 'detail' => $processedUrls . ' URLs']);
            }

            // Process discovered subpages
            $subpageLinks = $this->db->where('chatbot_id', $id)
                ->where('processing_status', 'pending')
                ->where('parent_link_id IS NOT NULL', null, false)
                ->get(db_prefix() . 'chatbot_training_links')
                ->result();
            $totalSubpages = count($subpageLinks);

            foreach ($subpageLinks as $i => $linkData) {
                $link = new \PerfexChat\Neuron\Models\TrainingLink($linkData);
                $link->process();
                $processedUrls++;
                $processed++;
                $writeProgress(['phase' => 'processing', 'message' => _l('chatbot_fetching_urls'), 'current' => $i + 1, 'total' => $totalSubpages, 'detail' => $processedUrls . ' URLs']);
            }

            // Process files
            $pendingFiles = $this->db->where('chatbot_id', $id)
                ->where('processing_status', 'pending')
                ->get(db_prefix() . 'chatbot_training_files')
                ->result();

            $totalFiles = count($pendingFiles);
            foreach ($pendingFiles as $i => $fileData) {
                $file = new \PerfexChat\Neuron\Models\TrainingFile($fileData);
                $file->process();
                $writeProgress(['phase' => 'processing', 'message' => _l('chatbot_processing_content'), 'current' => $i + 1, 'total' => $totalFiles]);
            }

            // Embedding phase
            $pending = $chatbot->getPendingTrainables();
            $totalPending = count($pending);
            $trained = 0;
            $errors = [];

            $writeProgress(['phase' => 'embedding', 'message' => _l('chatbot_generating_embeddings'), 'current' => 0, 'total' => $totalPending]);

            foreach ($pending as $item) {
                try {
                    $item->markAsTraining();

                    $doc = \PerfexChat\Neuron\Documents\ChatbotDocument::fromTrainingModel($item);
                    $chunks = $this->chatbot_model->split_text_into_chunks($doc->content);
                    $chunkCount = count($chunks);

                    foreach ($chunks as $i => $chunk) {
                        $chunkDoc = clone $doc;
                        $chunkDoc->content = $chunk;

                        if ($chunkCount > 1) {
                            $chunkDoc->addMetadata('chunk_index', $i);
                            $chunkDoc->addMetadata('total_chunks', $chunkCount);
                        }

                        $chunkDoc->embedding = $embeddings->embedText($chunk);
                        $vectorStore->addDocuments([$chunkDoc]);
                    }

                    $item->markAsTrained();
                    $trained++;
                } catch (\Exception $e) {
                    $item->markAsFailed($e->getMessage());
                    $errors[] = get_class($item) . " #{$item->id}: " . $e->getMessage();
                }

                $writeProgress([
                    'phase' => 'embedding',
                    'message' => _l('chatbot_generating_embeddings'),
                    'current' => $trained + count($errors),
                    'total' => $totalPending,
                    'trained' => $trained,
                    'failed' => count($errors),
                ]);
            }

            @unlink($progressFile);

            ob_end_clean();
            echo json_encode([
                'success' => true,
                'trained' => $trained,
                'total_pending' => $totalPending,
                'processed_urls' => $processedUrls,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            @unlink($progressFile);
            ob_end_clean();
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Save API keys (from single chatbot view).
     */
    public function save_api_keys(): void
    {
        $this->requireManagePermission();

        if ($this->input->post()) {
            $newKey = $this->input->post('chatbot_openai_api_key');
            if ($newKey && $newKey !== '••••••••') {
                update_option('chatbot_openai_api_key', $newKey);
            }

            set_alert('success', _l('chatbot_api_keys_saved'));
        }

        redirect(admin_url('prchat/Chatbot_Admin#api-keys'));
    }

    /**
     * Live chat view for staff to respond to escalated conversations.
     * Access and actions (takeover, transfer, priority, export) use the same
     * permission: staff_can('chatbot', 'prchat'). No separate capability is checked.
     */
    public function live_chat(int $conversationId = 0): void
    {
        $chatbot = $this->getOrCreateDefaultChatbot();

        // Get all recent conversations (prioritize escalated)
        $conversations = $this->db
            ->where('chatbot_id', $chatbot->id)
            ->order_by('is_escalated', 'DESC')
            ->order_by('last_activity_at', 'DESC')
            ->limit(50)
            ->get(db_prefix() . 'chatbot_conversations')
            ->result();

        $conversations = array_map(
            fn($row) => new \PerfexChat\Neuron\Models\ChatbotConversation($row),
            $conversations
        );

        $data['title'] = _l('chatbot_live_chat_support');
        $data['chatbot'] = $chatbot;
        $data['conversations'] = $conversations;
        $data['can_delete'] = staff_can('delete', 'prchat');
        $chatbotStaffIds = \PerfexChat\Neuron\Services\PusherService::getChatbotStaffIds();
        $allStaff = $this->staff_model->get('', ['active' => 1]);
        $data['staff_members'] = array_filter($allStaff, function ($m) use ($chatbotStaffIds) {
            return in_array($m['staffid'], $chatbotStaffIds);
        });

        // Load active conversation if specified
        if ($conversationId > 0) {
            $data['active_conversation'] = $this->findConversation($conversationId);
        } elseif (!empty($conversations)) {
            // Auto-select first escalated or most recent
            foreach ($conversations as $conv) {
                if ($conv->is_escalated) {
                    $data['active_conversation'] = $conv;
                    break;
                }
            }
            if (!isset($data['active_conversation'])) {
                $data['active_conversation'] = $conversations[0];
            }
        }

        $this->load->view('chatbots/live_chat', $data);
    }

    /**
     * AJAX search for chatbot-enabled staff (for transfer select).
     * Returns JSON [{id, name, subtext}] compatible with ajaxSelectPicker.
     */
    public function ajaxSearchChatbotStaff(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        header('Content-Type: application/json');

        $q = $this->input->get('q') ?: '';
        $chatbotStaffIds = \PerfexChat\Neuron\Services\PusherService::getChatbotStaffIds();
        $this->load->model('prchat/Prchat_model', 'chat_model');
        echo json_encode($this->chat_model->searchStaffAjax($q, [], $chatbotStaffIds));
    }

    /**
     * Send staff reply to a conversation.
     */
    public function send_staff_reply(): void
    {
        header('Content-Type: application/json');

        if (!$this->input->post()) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_request')]);
            return;
        }

        $conversationId = (int) $this->input->post('conversation_id');
        $message = trim($this->input->post('message') ?? '');

        if (!$conversationId || !$message) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_missing_data')]);
            return;
        }

        $conversation = $this->findConversation($conversationId);

        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        $staffName = get_staff_full_name();

        // Add staff message
        $staffMessage = $conversation->addMessage('assistant', $message, [
            'staff_id' => get_staff_user_id(),
            'staff_name' => $staffName,
            'is_staff' => true,
        ]);

        // Send via Pusher for instant real-time delivery to widget
        \PerfexChat\Neuron\Services\PusherService::sendStaffMessage($conversationId, $message, $staffName, get_staff_user_id());

        echo json_encode([
            'success' => true,
            'message_id' => $staffMessage->id,
            'staff_name' => $staffName,
            'time' => date('M j, g:i a'),
        ]);
    }

    /**
     * Assign conversation to current staff member (takeover).
     */
    public function assign_conversation(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }

        $conversation = $this->findConversation($conversationId);

        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        $staffName = $this->getStaffDisplayName();
        $staffId = get_staff_user_id();

        // Use model method for takeover
        $conversation->staffTakeover($staffId);

        // Add system message so it shows in chat history
        $joinedMsg = _l('chatbot_staff_joined', [$staffName]);
        $conversation->addMessage('system', $joinedMsg, [
            'type' => 'staff_joined',
            'staff_id' => $staffId,
            'staff_name' => $staffName,
        ]);

        // Notify visitor instantly via Pusher
        \PerfexChat\Neuron\Services\PusherService::notifyHumanTakeover($conversationId, $staffName, $staffId);

        // Notify other staff that this escalation was claimed
        \PerfexChat\Neuron\Services\PusherService::notifyStaffEscalationClaimed($conversationId, $staffName);

        echo json_encode([
            'success' => true,
            'staff_name' => $staffName,
            'status' => 'human_handling',
        ]);
    }

    /**
     * Delete a conversation.
     */
    public function delete_conversation(): void
    {
        if (!staff_can('delete', PR_CHAT_MODULE_NAME)) {
            access_denied('prchat');
        }

        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }

        $conversation = $this->findConversation($conversationId);
        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        $this->chatbot_model->delete_conversation($conversationId);
        \PerfexChat\Neuron\Services\PusherService::notifyConversationDeleted($conversationId);

        echo json_encode(['success' => true]);
    }

    /**
     * End a conversation (close it with notification to visitor).
     */
    public function end_conversation(): void
    {

        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }

        $conversation = $this->findConversation($conversationId);

        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        $staffId = get_staff_user_id();
        $staffName = $this->getStaffDisplayName();

        // Update conversation status to closed
        $conversation->update([
            'status' => \PerfexChat\Neuron\Models\ChatbotConversation::STATUS_CLOSED,
            'closed_at' => date('Y-m-d H:i:s'),
            'closed_by_staff_id' => $staffId,
        ]);

        // Notify visitor
        \PerfexChat\Neuron\Services\PusherService::notifyConversationClosed($conversationId, $staffName);

        // Add closing message (use same format as Pusher event for consistency)
        $closedMsg = _l('chatbot_closed_by_staff', [$staffName]);
        $conversation->addMessage('system', $closedMsg, [
            'type' => 'conversation_closed',
            'staff_id' => $staffId,
            'staff_name' => $staffName,
        ]);

        echo json_encode([
            'success' => true,
            'status' => 'closed',
        ]);
    }

    /**
     * Set priority on a conversation.
     */
    public function set_priority(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');
        $priority = $this->input->post('priority');

        $allowed = ['low', 'medium', 'high', ''];
        if (!in_array($priority, $allowed, true)) {
            echo json_encode(['success' => false, 'error' => 'Invalid priority']);
            return;
        }

        $conversation = $this->findConversation($conversationId);
        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        $this->chatbot_model->set_priority($conversationId, $priority === '' ? null : $priority);
        echo json_encode(['success' => true]);
    }

    /**
     * Transfer conversation to another staff member.
     */
    public function transfer_conversation(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');
        $targetStaffId = (int) $this->input->post('staff_id');

        if (!$conversationId || !$targetStaffId) {
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
            return;
        }

        $conversation = $this->findConversation($conversationId);
        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        $currentStaffId = get_staff_user_id();
        if ($conversation->assigned_staff_id != $currentStaffId && !is_admin()) {
            access_denied('prchat');
        }

        $targetStaffName = get_staff_full_name($targetStaffId);
        if (!$targetStaffName || trim($targetStaffName) === '') {
            $targetStaffName = 'Staff #' . $targetStaffId;
        }

        $conversation->staffTakeover($targetStaffId);

        $fromName = $this->getStaffDisplayName();
        $msg = _l('chatbot_transferred_by', [$fromName, $targetStaffName]);
        $conversation->addMessage('system', $msg, [
            'type' => 'staff_transferred',
            'from_staff_id' => get_staff_user_id(),
            'to_staff_id' => $targetStaffId,
        ]);

        \PerfexChat\Neuron\Services\PusherService::notifyHumanTakeover($conversationId, $targetStaffName, $targetStaffId);

        // Notify the staff member who received the conversation (bell + Pusher)
        \PerfexChat\Neuron\Services\PusherService::notifyStaffBell(
            [$targetStaffId],
            $conversationId,
            'chatbot_conversation_transferred_to_you'
        );

        echo json_encode([
            'success' => true,
            'staff_name' => $targetStaffName,
            'message' => _l('chatbot_conversation_transferred'),
        ]);
    }

    /**
     * Export conversation as CSV download or printable HTML page.
     */
    public function export_conversation(): void
    {
        $conversationId = (int) $this->input->get('conversation_id');
        $format = $this->input->get('format') ?: 'csv';

        $conversation = $this->findConversation($conversationId);
        if (!$conversation) {
            show_404();
            return;
        }

        $messages = $this->chatbot_model->get_messages_for_export($conversationId);
        $visitorInfo = $conversation->getVisitorInfo();
        $visitorName = $visitorInfo['name'] ?? ('Visitor #' . $conversation->id);
        $dateStr = date('Y-m-d', strtotime($conversation->created_at));

        if ($format === 'csv') {
            $this->exportConversationCsv($conversationId, $dateStr, $visitorName, $messages);
            return;
        }

        $this->exportConversationHtml($conversationId, $dateStr, $visitorName, $conversation, $messages);
    }

    /**
     * Generate CSV export for a conversation.
     */
    private function exportConversationCsv(int $id, string $dateStr, string $visitorName, array $messages): void
    {
        $filename = "conversation-{$id}-{$dateStr}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Date', 'Time', 'Role', 'Sender', 'Message']);

        foreach ($messages as $msg) {
            $role = $msg->role ?? 'unknown';
            $content = strip_tags($msg->content ?? '');
            $created = $msg->created_at ?? '';
            $metadata = json_decode($msg->metadata ?? '{}', true) ?: [];

            $sender = $role;
            if ($role === 'user') {
                $sender = $visitorName;
            } elseif ($role === 'assistant') {
                $sender = (!empty($metadata['is_staff']) && !empty($metadata['staff_name']))
                    ? $metadata['staff_name']
                    : 'AI Assistant';
            } elseif ($role === 'system') {
                $sender = 'System';
            }

            fputcsv($output, [
                date('Y-m-d', strtotime($created)),
                date('H:i:s', strtotime($created)),
                ucfirst($role),
                $sender,
                $content,
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Generate printable HTML export for a conversation.
     */
    private function exportConversationHtml(int $id, string $dateStr, string $visitorName, $conversation, array $messages): void
    {
        header('Content-Type: text/html; charset=utf-8');

        echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
        echo '<title>Conversation #' . $id . ' - ' . htmlspecialchars($visitorName) . '</title>';
        echo '<style>';
        echo 'body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;max-width:800px;margin:0 auto;padding:20px;color:#1a1a1a;}';
        echo '.header{border-bottom:2px solid #e5e7eb;padding-bottom:16px;margin-bottom:20px;}';
        echo '.header h1{font-size:18px;margin:0 0 8px;}';
        echo '.header .meta{color:#6b7280;font-size:13px;}';
        echo '.msg{padding:10px 0;border-bottom:1px solid #f3f4f6;}';
        echo '.msg .sender{font-weight:600;font-size:13px;}';
        echo '.msg .sender.user{color:#2563eb;}';
        echo '.msg .sender.assistant{color:#059669;}';
        echo '.msg .sender.system{color:#9ca3af;font-style:italic;}';
        echo '.msg .time{color:#9ca3af;font-size:11px;margin-left:8px;}';
        echo '.msg .content{margin-top:4px;font-size:14px;line-height:1.5;white-space:pre-wrap;}';
        echo '@media print{body{padding:0;}.no-print{display:none;}}';
        echo '</style></head><body>';
        echo '<div class="no-print" style="margin-bottom:16px;"><button onclick="window.print()" style="padding:8px 16px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px;">Print / Save as PDF</button></div>';
        echo '<div class="header"><h1>Conversation #' . $id . '</h1>';
        echo '<div class="meta">Visitor: ' . htmlspecialchars($visitorName) . ' &bull; Started: ' . htmlspecialchars($conversation->created_at) . ' &bull; Status: ' . htmlspecialchars(ucfirst(str_replace('_', ' ', $conversation->status))) . '</div></div>';

        foreach ($messages as $msg) {
            $role = $msg->role ?? '';
            $content = $msg->content ?? '';
            $created = $msg->created_at ?? '';
            $metadata = json_decode($msg->metadata ?? '{}', true) ?: [];

            $sender = ucfirst($role);
            if ($role === 'user') {
                $sender = htmlspecialchars($visitorName);
            } elseif ($role === 'assistant') {
                $sender = (!empty($metadata['is_staff']) && !empty($metadata['staff_name']))
                    ? htmlspecialchars($metadata['staff_name'])
                    : 'AI Assistant';
            }

            echo '<div class="msg">';
            echo '<span class="sender ' . htmlspecialchars($role) . '">' . $sender . '</span>';
            echo '<span class="time">' . htmlspecialchars($created) . '</span>';
            echo '<div class="content">' . htmlspecialchars(strip_tags($content)) . '</div>';
            echo '</div>';
        }

        echo '</body></html>';
        exit;
    }

    /**
     * Convert conversation visitor to a CRM Lead.
     */
    public function convert_to_lead(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }

        $conversation = $this->findConversation($conversationId);

        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        // Check if already converted
        if ($conversation->converted_to_lead_id) {
            echo json_encode([
                'success' => false,
                'error' => _l('chatbot_already_converted_lead'),
                'lead_id' => $conversation->converted_to_lead_id,
            ]);
            return;
        }

        // Get visitor info
        $visitorInfo = $conversation->getVisitorInfo();

        $sourceId = $this->chatbot_model->get_or_create_chatbot_lead_source();

        // Prepare lead data
        $leadData = [
            'name' => $visitorInfo['name'] ?? _l('chatbot_chatbot_visitor'),
            'email' => $visitorInfo['email'] ?? '',
            'phonenumber' => $visitorInfo['phone'] ?? '',
            'address' => '',
            'source' => $sourceId,
            'assigned' => get_staff_user_id(),
            'status' => 1,
            'description' => $this->chatbot_model->build_conversation_summary($conversation),
        ];

        $this->load->model('leads_model');
        $leadId = $this->leads_model->add($leadData);

        if ($leadId) {
            $conversation->setConvertedToLead($leadId);

            echo json_encode([
                'success' => true,
                'lead_id' => $leadId,
                'lead_url' => admin_url('leads/index/' . $leadId),
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => _l('chatbot_failed_create_lead')]);
        }
    }

    /**
     * Convert conversation visitor to a CRM Client.
     */
    public function convert_to_client(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }

        $conversation = $this->findConversation($conversationId);

        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        // Check if already converted
        if ($conversation->converted_to_client_id) {
            echo json_encode([
                'success' => false,
                'error' => _l('chatbot_already_converted_client'),
                'client_id' => $conversation->converted_to_client_id,
            ]);
            return;
        }

        // Get visitor info
        $visitorInfo = $conversation->getVisitorInfo();

        // Prepare client data
        $clientData = [
            'company' => $visitorInfo['company'] ?? $visitorInfo['name'] ?? _l('chatbot_chatbot_visitor'),
            'phonenumber' => $visitorInfo['phone'] ?? '',
            'address' => $visitorInfo['address'] ?? '',
        ];

        // Prepare contact data
        $nameParts = explode(' ', trim($visitorInfo['name'] ?? ''));
        $contactData = [
            'firstname' => $nameParts[0] ?: _l('chatbot_visitor_label'),
            'lastname' => count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '',
            'email' => $visitorInfo['email'] ?? '',
            'phonenumber' => $visitorInfo['phone'] ?? '',
            'is_primary' => 1,
        ];

        $this->load->model('clients_model');

        $clientId = $this->clients_model->add($clientData);

        if ($clientId) {
            $contactData['userid'] = $clientId;
            $this->db->insert(db_prefix() . 'contacts', $contactData);

            $conversation->setConvertedToClient($clientId);

            // Link client back to lead and update lead status to "Customer"
            if ($conversation->converted_to_lead_id) {
                $this->db->where('userid', $clientId)
                    ->update(db_prefix() . 'clients', ['leadid' => $conversation->converted_to_lead_id]);

                $customerStatusId = $this->db->select('id')
                    ->where('isdefault', 1)
                    ->get(db_prefix() . 'leads_status')
                    ->row();

                if ($customerStatusId) {
                    $this->db->where('id', $conversation->converted_to_lead_id)
                        ->update(db_prefix() . 'leads', ['status' => $customerStatusId->id]);
                }
            }

            echo json_encode([
                'success' => true,
                'client_id' => $clientId,
                'client_url' => admin_url('clients/client/' . $clientId),
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => _l('chatbot_failed_create_client')]);
        }
    }

    /**
     * Create a follow-up task for the conversation.
     */
    public function create_followup_task(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');
        $taskName = $this->input->post('name');

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }

        $conversation = $this->findConversation($conversationId);

        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        // Get visitor info for task description
        $visitorInfo = $conversation->getVisitorInfo();

        $staffId = get_staff_user_id();

        // Prepare task data
        $taskData = [
            'name' => $taskName ?: _l('chatbot_follow_up_with_visitor'),
            'description' => $this->chatbot_model->build_conversation_summary($conversation),
            'startdate' => date('Y-m-d'),
            'duedate' => date('Y-m-d', strtotime('+1 day')),
            'priority' => 2, // Medium
            'status' => 1, // Not started
            'dateadded' => date('Y-m-d H:i:s'),
            'addedfrom' => $staffId,
        ];

        $this->load->model('tasks_model');

        $taskId = $this->tasks_model->add($taskData);

        if ($taskId) {
            $this->tasks_model->add_task_assignees(['taskid' => $taskId, 'assignee' => $staffId]);

            echo json_encode([
                'success' => true,
                'task_id' => $taskId,
                'task_url' => admin_url('tasks/view/' . $taskId),
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => _l('chatbot_failed_create_task')]);
        }
    }

    // ===========================================
    // SPA API ENDPOINTS
    // ===========================================

    /**
     * Get all conversations for SPA (JSON).
     * Optional: tag_id (int) to filter by tag.
     */
    public function get_conversations(): void
    {
        header('Content-Type: application/json');
        $chatbot = $this->getOrCreateDefaultChatbot();
        $tagId = $this->input->get_post('tag_id');
        $tagId = $tagId !== null && $tagId !== '' ? (int) $tagId : null;
        if ($tagId !== null && $tagId <= 0) {
            $tagId = null;
        }
        $conversations = $this->chatbot_model->get_conversations_list($chatbot->id, get_staff_user_id(), 100, $tagId);
        echo json_encode(['success' => true, 'conversations' => $conversations]);
    }

    /**
     * Get available tags for conversation tagging (JSON).
     */
    public function get_available_tags(): void
    {
        header('Content-Type: application/json');
        $tags = $this->chatbot_model->get_available_tags();
        echo json_encode(['success' => true, 'tags' => $tags]);
    }

    /**
     * Add a tag to a conversation (JSON).
     */
    public function add_conversation_tag(): void
    {
        header('Content-Type: application/json');
        $conversationId = (int) $this->input->post('conversation_id');
        $tagId = (int) $this->input->post('tag_id');
        if (!$conversationId || !$tagId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }
        $conversation = $this->findConversation($conversationId);
        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }
        $conversation->addTag($tagId);
        echo json_encode(['success' => true]);
    }

    /**
     * Remove a tag from a conversation (JSON).
     */
    public function remove_conversation_tag(): void
    {
        header('Content-Type: application/json');
        $conversationId = (int) $this->input->post('conversation_id');
        $tagId = (int) $this->input->post('tag_id');
        if (!$conversationId || !$tagId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }
        $conversation = $this->findConversation($conversationId);
        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }
        $conversation->removeTag($tagId);
        echo json_encode(['success' => true]);
    }

    /**
     * Create tag (JSON). For chatbot settings tag management.
     */
    public function create_tag(): void
    {
        header('Content-Type: application/json');
        $this->requireManagePermission();
        $name = trim($this->input->post('name'));
        $color = trim($this->input->post('color')) ?: '#6c757d';
        if ($name === '') {
            echo json_encode(['success' => false, 'error' => _l('chatbot_tag_name_required')]);
            return;
        }
        $tag = $this->chatbot_model->create_tag($name, $color);
        echo json_encode($tag ? ['success' => true, 'tag' => $tag] : ['success' => false]);
    }

    /**
     * Update tag (JSON).
     */
    public function update_tag(): void
    {
        header('Content-Type: application/json');
        $this->requireManagePermission();
        $id = (int) $this->input->post('id');
        $name = trim($this->input->post('name'));
        $color = trim($this->input->post('color')) ?: '#6c757d';
        if (!$id || $name === '') {
            echo json_encode(['success' => false, 'error' => _l('chatbot_tag_name_required')]);
            return;
        }
        $ok = $this->chatbot_model->update_tag($id, $name, $color);
        echo json_encode(['success' => (bool) $ok]);
    }

    /**
     * Delete tag (JSON).
     */
    public function delete_tag(): void
    {
        header('Content-Type: application/json');
        $this->requireManagePermission();
        $id = (int) $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }
        $ok = $this->chatbot_model->delete_tag($id);
        echo json_encode(['success' => (bool) $ok]);
    }

    /**
     * Get single conversation with messages for SPA (JSON).
     */
    public function get_conversation(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_conversation')]);
            return;
        }

        $conversation = $this->findConversation($conversationId);
        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        $detail = $this->chatbot_model->get_conversation_detail($conversationId, get_staff_user_id());
        if (!$detail) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_conversation_not_found')]);
            return;
        }

        echo json_encode(array_merge(['success' => true], $detail));
    }

    /**
     * Get canned responses for SPA (JSON).
     */
    public function get_canned_responses(): void
    {
        header('Content-Type: application/json');
        $responses = $this->chatbot_model->get_canned_responses(get_staff_user_id());
        echo json_encode(['success' => true, 'responses' => $responses]);
    }

    /**
     * Add a canned response.
     */
    public function add_canned_response(): void
    {
        if ($this->input->post()) {
            $this->chatbot_model->add_canned_response([
                'title' => $this->input->post('title'),
                'shortcut' => $this->input->post('shortcut'),
                'content' => $this->input->post('content'),
            ]);
            set_alert('success', _l('chatbot_canned_response_added'));
            redirect(admin_url('prchat/Chatbot_Admin#canned-responses'));
        }
    }

    /**
     * Delete a canned response.
     */
    public function delete_canned_response(int $id): void
    {
        $this->chatbot_model->delete_canned_response($id);
        set_alert('success', _l('chatbot_canned_response_deleted'));
        redirect(admin_url('prchat/Chatbot_Admin#canned-responses'));
    }

    /**
     * Add a note to a conversation (JSON).
     */
    public function add_conversation_note(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');
        $content = trim($this->input->post('content') ?? '');

        if (!$conversationId || !$content) {
            echo json_encode(['success' => false, 'error' => _l('chatbot_missing_data')]);
            return;
        }

        $note = $this->chatbot_model->add_note($conversationId, get_staff_user_id(), $content);
        echo json_encode(['success' => true, 'note' => $note]);
    }

    /**
     * Analytics page
     */
    public function analytics()
    {
        $this->requireManagePermission();

        $this->load->model('Analytics_model', 'analytics_model');
        $dateRange = $this->input->get('date_range', true) ?: 'all';
        $tagId = $this->input->get('tag_id', true) ?: 'all';
        $data = $this->analytics_model->get_all_analytics_data($dateRange, $tagId);
        $data['title'] = _l('chatbot_analytics_title');
        $data['current_date_range'] = $dateRange;
        $data['current_tag_id'] = $tagId;
        $this->load->view('analytics/index', $data);
    }

    /**
     * Get analytics data (JSON, with optional date and tag filtering).
     */
    public function get_analytics_data(): void
    {
        header('Content-Type: application/json');
        $this->load->model('Analytics_model', 'analytics_model');
        $dateRange = $this->input->get('date_range', true);
        $tagId = $this->input->get('tag_id', true);
        echo json_encode(['success' => true, 'data' => $this->analytics_model->get_analytics_api_data($dateRange, $tagId)]);
    }

    /**
     * Get chart data for different time periods (JSON).
     */
    public function get_chart_data(): void
    {
        $this->requireManagePermission();

        header('Content-Type: application/json');
        $this->load->model('Analytics_model', 'analytics_model');
        $period = $this->input->post('period', true) ?: 30;
        $tagId = $this->input->post('tag_id', true);
        echo json_encode(['success' => true, 'data' => $this->analytics_model->get_chart_data((int) $period, $tagId)]);
    }

    /**
     * Get conversations data for the analytics table (JSON).
     */
    public function get_conversations_data(): void
    {
        $this->requireManagePermission();

        header('Content-Type: application/json');
        $this->load->model('Analytics_model', 'analytics_model');
        $dateRange = $this->input->get('date_range', true);
        $ratingFilter = $this->input->get('rating_filter', true);
        $tagId = $this->input->get('tag_id', true);
        echo json_encode(['success' => true, 'data' => $this->analytics_model->get_conversations_filtered($dateRange, $ratingFilter, $tagId)]);
    }

    /**
     * Sanitize and validate thinking phrases array.
     */
    private function sanitizeThinkingPhrases($phrases): array
    {
        if (!is_array($phrases)) {
            return [];
        }

        $cleaned = [];
        foreach ($phrases as $phrase) {
            $phrase = trim($phrase);
            if (!empty($phrase)) {
                $cleaned[] = $phrase;
            }
        }

        return !empty($cleaned) ? $cleaned : [];
    }

    /**
     * Save desktop notification preference for current staff
     */
    public function save_desktop_notification_preference()
    {
        $enabled = $this->input->post('enabled');
        $staffId = get_staff_user_id();

        update_staff_meta($staffId, 'chatbot_desktop_notifications_enabled', $enabled);

        echo json_encode(['success' => true]);
    }

    /**
     * Notify widget that staff is typing.
     */
    public function staff_typing(): void
    {
        header('Content-Type: application/json');

        $conversationId = (int) $this->input->post('conversation_id');
        $isTyping = $this->input->post('typing') === '1' || $this->input->post('typing') === 'true';

        if ($conversationId > 0) {
            $staffName = get_staff_full_name(get_staff_user_id());
            \PerfexChat\Neuron\Services\PusherService::notifyStaffTyping($conversationId, $isTyping, $staffName);
        }

        echo json_encode(['success' => true]);
    }
}
