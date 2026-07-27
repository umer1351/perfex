<?php

defined('BASEPATH') or exit('No direct script access allowed');

// =========================================================================
// Data Functions — return arrays/strings, never touch the database
// =========================================================================

if (!function_exists('prchat_default_appearance')) {
    /**
     * Default appearance settings for a new chatbot.
     */
    function prchat_default_appearance(): array
    {
        return [
            'primary_color'        => '#007bff',
            'header_bg_color'      => '#007bff',
            'position'             => 'bottom-right',
            'distance_from_bottom' => 20,
            'distance_from_side'   => 20,
            'icon_type'            => 'chat',
            'custom_icon_url'      => null,
            'welcome_message'      => 'Hi! How can we help you today?',
            'input_placeholder'    => 'Type your message...',
            'intro_subtitle'       => 'We typically reply within a few minutes',
            'display_name_mode'    => 'chatbot_only',
            'agent_avatar_url'     => null,
            'header_image_url'     => null,
            'floating_tooltips'    => [],
        ];
    }
}

if (!function_exists('prchat_default_lead_fields')) {
    /**
     * Default lead-capture field configuration.
     */
    function prchat_default_lead_fields(): array
    {
        return [
            'email' => ['enabled' => true, 'required' => true,  'label' => 'Email'],
            'name'  => ['enabled' => true, 'required' => false, 'label' => 'Name'],
            'phone' => ['enabled' => false, 'required' => false, 'label' => 'Phone'],
        ];
    }
}

if (!function_exists('prchat_default_tags')) {
    /**
     * Seven default conversation tags with brand colours.
     */
    function prchat_default_tags(): array
    {
        return [
            ['name' => 'Sales',   'color' => '#0d6efd'],
            ['name' => 'Support', 'color' => '#198754'],
            ['name' => 'Billing', 'color' => '#fd7e14'],
            ['name' => 'Bug',     'color' => '#dc3545'],
            ['name' => 'Feature', 'color' => '#6f42c1'],
            ['name' => 'Urgent',  'color' => '#d63384'],
            ['name' => 'General', 'color' => '#6c757d'],
        ];
    }
}

if (!function_exists('prchat_default_canned_responses')) {
    /**
     * Three starter canned responses shipped with every new install.
     */
    function prchat_default_canned_responses(): array
    {
        return [
            [
                'title'    => 'Greeting',
                'shortcut' => 'greet',
                'content'  => 'Hello {{visitor_name}}! Thank you for reaching out. How can I help you today?',
            ],
            [
                'title'    => 'Bye',
                'shortcut' => 'bye',
                'content'  => 'Thank you for chatting with us today! Have a great day!',
            ],
            [
                'title'    => 'Please Wait',
                'shortcut' => 'wait',
                'content'  => 'Thank you for your patience. I am looking into this for you right now.',
            ],
        ];
    }
}

if (!function_exists('prchat_sample_training_qa')) {
    /**
     * Ten sample Q&A pairs seeded for every new chatbot.
     */
    function prchat_sample_training_qa(): array
    {
        return [
            ['What are your opening hours?',           'We are open Monday to Friday, 9am–5pm. Weekends by appointment.'],
            ['How can I contact support?',             'You can reach us via this chat, by email, or by phone during business hours.'],
            ['Do you offer refunds?',                  'Yes. Please see our refund policy on the website or ask us for the details.'],
            ['Where is my order?',                     'You can track your order using the link in your confirmation email.'],
            ['How do I reset my password?',            'Go to the login page and click "Forgot password" to receive a reset link by email.'],
            ['Can I change or cancel my order?',       'Changes may be possible before shipping. Contact us with your order number.'],
            ['What payment methods do you accept?',    'We accept major credit cards, PayPal, and bank transfer.'],
            ['How do I create an account?',            'Click "Sign up" on our website and follow the steps. You can also ask us to send you a link.'],
            ['Is there a warranty?',                   'Yes. Most products come with a standard warranty. Check the product page for details.'],
            ['How can I unsubscribe from emails?',     'Use the "Unsubscribe" link at the bottom of any marketing email, or contact us.'],
        ];
    }
}

if (!function_exists('prchat_chatbot_options')) {
    /**
     * Global chatbot options with their default values.
     */
    function prchat_chatbot_options(): array
    {
        return [
            'chatbot_openai_api_key'           => '',
            'chatbot_openai_embedding_model'   => 'text-embedding-3-small',
            'chatbot_default_provider'         => 'openai',
            'chatbot_default_model'            => 'gpt-4o-mini',
        ];
    }
}

// =========================================================================
// Action Functions — perform idempotent DB operations
// =========================================================================

if (!function_exists('prchat_create_chatbot_tables')) {
    /**
     * Create all 11 chatbot-related tables if they don't already exist.
     */
    function prchat_create_chatbot_tables(): void
    {
        $CI      = &get_instance();
        $prefix  = db_prefix();
        $charset = $CI->db->char_set;

        // 1. chatbots
        if (!$CI->db->table_exists($prefix . 'chatbots')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbots` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `widget_key` varchar(64) NOT NULL,
                    `enabled` tinyint(1) NOT NULL DEFAULT 1,
                    `ai_provider` varchar(20) NOT NULL DEFAULT 'openai',
                    `ai_model` varchar(100) DEFAULT 'gpt-4o-mini',
                    `system_prompt` text DEFAULT NULL,
                    `max_output_tokens` int(11) DEFAULT 500,
                    `temperature` decimal(3,2) DEFAULT 0.70,
                    `appearance` longtext DEFAULT NULL,
                    `allowed_domains` longtext DEFAULT NULL,
                    `max_messages` int(11) DEFAULT NULL COMMENT 'Max messages per conversation (null = unlimited)',
                    `context_window` int(11) DEFAULT 10 COMMENT 'Number of messages to include in context',
                    `auto_close_timeout` int(11) DEFAULT 30 COMMENT 'Auto-close conversations after X minutes of inactivity (0 = never)',
                    `capture_leads` tinyint(1) DEFAULT 0,
                    `lead_fields` longtext DEFAULT NULL,
                    `lead_custom_fields` longtext DEFAULT NULL,
                    `lead_capture_success_message` text DEFAULT NULL,
                    `escalation_enabled` tinyint(1) DEFAULT 1,
                    `escalation_message` text DEFAULT NULL,
                    `escalation_keywords` longtext DEFAULT NULL,
                    `csat_enabled` tinyint(1) DEFAULT 0,
                    `created_by` int(11) DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime DEFAULT NULL, 
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `widget_key` (`widget_key`),
                    KEY `enabled` (`enabled`),
                    KEY `created_by` (`created_by`)
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 2. chatbot_conversations
        if (!$CI->db->table_exists($prefix . 'chatbot_conversations')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_conversations` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `chatbot_id` int(11) unsigned NOT NULL,
                    `visitor_id` varchar(64) NOT NULL,
                    `session_id` varchar(64) NOT NULL,
                    `status` enum('active','pending_escalation','human_handling','closed') DEFAULT 'active',
                    `visitor_info` longtext DEFAULT NULL,
                    `is_escalated` tinyint(1) DEFAULT 0,
                    `escalated_at` datetime DEFAULT NULL,
                    `assigned_staff_id` int(11) DEFAULT NULL,
                    `closed_at` datetime DEFAULT NULL,
                    `closed_by_staff_id` int(11) DEFAULT NULL,
                    `priority` enum('low','medium','high') DEFAULT NULL,
                    `csat_score` tinyint(1) DEFAULT NULL COMMENT 'Rating 1-5 stars',
                    `csat_comment` text DEFAULT NULL,
                    `csat_at` datetime DEFAULT NULL,
                    `needs_followup` tinyint(1) DEFAULT 0,
                    `fallback_count` int(11) DEFAULT 0,
                    `converted_to_lead_id` int(11) DEFAULT NULL,
                    `converted_to_client_id` int(11) DEFAULT NULL,
                    `tags` text DEFAULT NULL COMMENT 'JSON array of tag IDs for conversation',
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `last_activity_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `chatbot_session` (`chatbot_id`, `session_id`),
                    KEY `chatbot_id` (`chatbot_id`),
                    KEY `visitor_id` (`visitor_id`),
                    KEY `session_id` (`session_id`),
                    KEY `status` (`status`),
                    KEY `is_escalated` (`is_escalated`),
                    KEY `last_activity_at` (`last_activity_at`),
                    FOREIGN KEY (`chatbot_id`) REFERENCES `{$prefix}chatbots` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 3. chatbot_tags
        if (!$CI->db->table_exists($prefix . 'chatbot_tags')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_tags` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(100) NOT NULL,
                    `color` varchar(20) NOT NULL DEFAULT '#6c757d',
                    `created_by` int(11) DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `created_by` (`created_by`)
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 4. chatbot_messages
        if (!$CI->db->table_exists($prefix . 'chatbot_messages')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_messages` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `conversation_id` int(11) unsigned NOT NULL,
                    `role` enum('user','assistant','tool','tool_result','system') NOT NULL,
                    `content` longtext NOT NULL,
                    `type` varchar(50) DEFAULT 'text',
                    `metadata` longtext DEFAULT NULL,
                    `usage` longtext DEFAULT NULL COMMENT 'Token usage stats',
                    `include_in_history` tinyint(1) DEFAULT 1,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `conversation_id` (`conversation_id`),
                    KEY `role` (`role`),
                    KEY `include_in_history` (`include_in_history`),
                    KEY `created_at` (`created_at`),
                    FOREIGN KEY (`conversation_id`) REFERENCES `{$prefix}chatbot_conversations` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 5. chatbot_conversation_reads
        if (!$CI->db->table_exists($prefix . 'chatbot_conversation_reads')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_conversation_reads` (
                    `conversation_id` int(11) unsigned NOT NULL,
                    `staff_id` int(11) NOT NULL,
                    `last_read_message_id` int(11) unsigned NOT NULL DEFAULT 0,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`conversation_id`, `staff_id`),
                    KEY `staff_id` (`staff_id`),
                    FOREIGN KEY (`conversation_id`) REFERENCES `{$prefix}chatbot_conversations` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 6. chatbot_training_links
        if (!$CI->db->table_exists($prefix . 'chatbot_training_links')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_training_links` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `chatbot_id` int(11) unsigned NOT NULL,
                    `url` varchar(2048) NOT NULL,
                    `title` varchar(255) DEFAULT NULL,
                    `extracted_content` longtext DEFAULT NULL,
                    `processing_status` enum('pending','processing','completed','failed') DEFAULT 'pending',
                    `processed_at` datetime DEFAULT NULL,
                    `training_status` enum('pending','training','trained','failed') DEFAULT 'pending',
                    `trained_at` datetime DEFAULT NULL,
                    `error_message` text DEFAULT NULL,
                    `crawl_subpages` tinyint(1) DEFAULT 0 COMMENT 'Whether to crawl and index subpages',
                    `crawl_depth` int(11) DEFAULT 1 COMMENT 'How many levels deep to crawl subpages',
                    `parent_link_id` int(11) unsigned DEFAULT NULL COMMENT 'Parent link ID if crawled from another link',
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `chatbot_id` (`chatbot_id`),
                    KEY `processing_status` (`processing_status`),
                    KEY `training_status` (`training_status`),
                    KEY `parent_link_id` (`parent_link_id`),
                    FOREIGN KEY (`chatbot_id`) REFERENCES `{$prefix}chatbots` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 7. chatbot_training_qa
        if (!$CI->db->table_exists($prefix . 'chatbot_training_qa')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_training_qa` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `chatbot_id` int(11) unsigned NOT NULL,
                    `question` text NOT NULL,
                    `answer` text NOT NULL,
                    `training_status` enum('pending','training','trained','failed') DEFAULT 'pending',
                    `trained_at` datetime DEFAULT NULL,
                    `error_message` text DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `chatbot_id` (`chatbot_id`),
                    KEY `training_status` (`training_status`),
                    FOREIGN KEY (`chatbot_id`) REFERENCES `{$prefix}chatbots` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 8. chatbot_training_files
        if (!$CI->db->table_exists($prefix . 'chatbot_training_files')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_training_files` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `chatbot_id` int(11) unsigned NOT NULL,
                    `original_name` varchar(255) NOT NULL,
                    `file_path` varchar(500) NOT NULL,
                    `file_type` varchar(50) DEFAULT NULL,
                    `file_size` int(11) DEFAULT NULL,
                    `file_data` longtext DEFAULT NULL,
                    `extracted_content` longtext DEFAULT NULL,
                    `processing_status` enum('pending','processing','completed','failed') DEFAULT 'pending',
                    `processed_at` datetime DEFAULT NULL,
                    `training_status` enum('pending','training','trained','failed') DEFAULT 'pending',
                    `trained_at` datetime DEFAULT NULL,
                    `error_message` text DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `chatbot_id` (`chatbot_id`),
                    KEY `processing_status` (`processing_status`),
                    KEY `training_status` (`training_status`),
                    FOREIGN KEY (`chatbot_id`) REFERENCES `{$prefix}chatbots` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 9. chatbot_canned_responses
        if (!$CI->db->table_exists($prefix . 'chatbot_canned_responses')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_canned_responses` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `chatbot_id` int(11) unsigned DEFAULT NULL COMMENT 'NULL = global for all chatbots',
                    `staff_id` int(11) DEFAULT NULL COMMENT 'NULL = shared, otherwise personal',
                    `title` varchar(100) NOT NULL,
                    `shortcut` varchar(50) DEFAULT NULL COMMENT 'e.g. /greet, /hours',
                    `content` text NOT NULL,
                    `category` varchar(50) DEFAULT 'general',
                    `sort_order` int(11) DEFAULT 0,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `chatbot_id` (`chatbot_id`),
                    KEY `staff_id` (`staff_id`),
                    KEY `shortcut` (`shortcut`),
                    KEY `category` (`category`)
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 10. chatbot_conversation_notes
        if (!$CI->db->table_exists($prefix . 'chatbot_conversation_notes')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chatbot_conversation_notes` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `conversation_id` int(11) unsigned NOT NULL,
                    `staff_id` int(11) NOT NULL,
                    `content` text NOT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `conversation_id` (`conversation_id`),
                    KEY `staff_id` (`staff_id`),
                    FOREIGN KEY (`conversation_id`) REFERENCES `{$prefix}chatbot_conversations` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }

        // 11. chat_client_notes
        if (!$CI->db->table_exists($prefix . 'chat_client_notes')) {
            $CI->db->query("
                CREATE TABLE `{$prefix}chat_client_notes` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `contact_id` int(11) unsigned NOT NULL,
                    `note_content` text NOT NULL,
                    `created_by` int(11) NOT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `contact_id` (`contact_id`),
                    KEY `created_by` (`created_by`)
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
            ");
        }
    }
}

if (!function_exists('prchat_seed_default_chatbot')) {
    /**
     * Idempotent seeding: default chatbot, tags, canned responses, sample Q&A.
     * Safe to call from both install.php and migrations.
     */
    function prchat_seed_default_chatbot(): void
    {
        $CI     = &get_instance();
        $prefix = db_prefix();
        $now    = date('Y-m-d H:i:s');

        // --- Default chatbot (only when table is empty) ---
        if ($CI->db->count_all($prefix . 'chatbots') == 0) {
            $company_name = get_option('companyname') ?: 'Support';

            $CI->db->insert($prefix . 'chatbots', [
                'name'                         => 'Support Assistant',
                'widget_key'                   => bin2hex(random_bytes(16)),
                'enabled'                      => 1,
                'ai_provider'                  => 'openai',
                'ai_model'                     => 'gpt-4o-mini',
                'system_prompt'                => 'You are a helpful customer support assistant for ' . $company_name . '. Be friendly, professional, and concise. If you don\'t know something, say so honestly.',
                'max_output_tokens'            => 500,
                'temperature'                  => 0.70,
                'appearance'                   => json_encode(prchat_default_appearance()),
                'allowed_domains'              => null,
                'max_messages'                 => null,
                'context_window'               => 10,
                'auto_close_timeout'           => 30,
                'capture_leads'                => 0,
                'lead_fields'                  => json_encode(prchat_default_lead_fields()),
                'lead_custom_fields'           => null,
                'lead_capture_success_message' => 'Thank you! We\'ll be in touch soon.',
                'escalation_enabled'           => 0,
                'escalation_message'           => 'I\'ll connect you with a team member who can help further.',
                'csat_enabled'                 => 0,
                'created_by'                   => null,
                'created_at'                   => $now,
                'updated_at'                   => null,
            ]);
        }

        // --- Default tags (only when table is empty) ---
        if ($CI->db->count_all($prefix . 'chatbot_tags') == 0) {
            foreach (prchat_default_tags() as $tag) {
                $CI->db->insert($prefix . 'chatbot_tags', [
                    'name'       => $tag['name'],
                    'color'      => $tag['color'],
                    'created_by' => null,
                    'created_at' => $now,
                ]);
            }
        }

        // --- Default canned responses (only when table is empty) ---
        if ($CI->db->count_all($prefix . 'chatbot_canned_responses') == 0) {
            $first_bot = $CI->db->select('id')->order_by('id', 'asc')
                ->limit(1)->get($prefix . 'chatbots')->row();
            $bot_id = $first_bot ? (int) $first_bot->id : 1;

            foreach (prchat_default_canned_responses() as $resp) {
                $CI->db->insert($prefix . 'chatbot_canned_responses', [
                    'chatbot_id' => $bot_id,
                    'staff_id'   => null,
                    'title'      => $resp['title'],
                    'shortcut'   => $resp['shortcut'],
                    'content'    => $resp['content'],
                    'sort_order' => 0,
                    'created_at' => $now,
                ]);
            }
        }

        // --- Sample Q&A for every chatbot that has none ---
        $tbl_qa   = $prefix . 'chatbot_training_qa';
        $tbl_bots = $prefix . 'chatbots';

        if ($CI->db->table_exists($tbl_qa) && $CI->db->table_exists($tbl_bots)) {
            $chatbots = $CI->db->select('id')->get($tbl_bots)->result_array();
            $samples  = prchat_sample_training_qa();

            foreach ($chatbots as $bot) {
                $cid   = (int) $bot['id'];
                $count = $CI->db->where('chatbot_id', $cid)->count_all_results($tbl_qa);
                if ($count > 0) {
                    continue;
                }
                foreach ($samples as $qa) {
                    $CI->db->insert($tbl_qa, [
                        'chatbot_id'      => $cid,
                        'question'        => $qa[0],
                        'answer'          => $qa[1],
                        'training_status' => 'pending',
                        'created_at'      => $now,
                    ]);
                }
            }
        }
    }
}

if (!function_exists('prchat_ensure_options')) {
    /**
     * Register options only if they don't already exist.
     * Uses add_option() which is already idempotent in Perfex CRM.
     */
    function prchat_ensure_options(array $options): void
    {
        foreach ($options as $name => $value) {
            add_option($name, $value);
        }
    }
}
