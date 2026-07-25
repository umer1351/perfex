<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 200 - Version 2.0 - Complete Chatbot System
 * 
 * Creates the full chatbot architecture:
 * - Chatbots (main configuration per chatbot instance)
 * - Conversations (visitor sessions with session tracking)
 * - Messages (conversation history with AI tracking)
 * - Leads (captured visitor information)
 * - Training Data (texts, links, files, Q&A for RAG)
 * - Notification Staff (staff members to notify on escalation)
 */
class Migration_Version_200 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $CI->load->helper('prchat/prchat_chatbot_v200_schema');

        // ===========================================
        // Legacy Chat Table Updates
        // ===========================================

        // Add related_type, related_id, created_at to chatgroups
        if ($CI->db->table_exists(TABLE_CHATGROUPS)) {
            if (!$CI->db->field_exists('related_type', TABLE_CHATGROUPS)) {
                $CI->db->query("ALTER TABLE `" . TABLE_CHATGROUPS . "` ADD `related_type` VARCHAR(50) NULL DEFAULT NULL");
            }
            if (!$CI->db->field_exists('related_id', TABLE_CHATGROUPS)) {
                $CI->db->query("ALTER TABLE `" . TABLE_CHATGROUPS . "` ADD `related_id` INT(11) UNSIGNED NULL DEFAULT NULL");
            }
            if (!$CI->db->field_exists('created_at', TABLE_CHATGROUPS)) {
                $CI->db->query("ALTER TABLE `" . TABLE_CHATGROUPS . "` ADD `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP");
            }

            $indexes = $CI->db->query("SHOW INDEX FROM `" . TABLE_CHATGROUPS . "` WHERE Key_name = 'idx_related'")->result();
            if (empty($indexes)) {
                $CI->db->query("ALTER TABLE `" . TABLE_CHATGROUPS . "` ADD KEY `idx_related` (`related_type`, `related_id`)");
            }
        }

        // Add unique index on chatgroupmembers (group_id, member_id)
        if ($CI->db->table_exists(TABLE_CHATGROUPMEMBERS)) {
            $indexes = $CI->db->query("SHOW INDEX FROM `" . TABLE_CHATGROUPMEMBERS . "` WHERE Key_name = 'idx_unique_member'")->result();
            if (empty($indexes)) {
                $CI->db->query("DELETE t1 FROM `" . TABLE_CHATGROUPMEMBERS . "` t1
                    INNER JOIN `" . TABLE_CHATGROUPMEMBERS . "` t2
                    WHERE t1.id > t2.id AND t1.group_id = t2.group_id AND t1.member_id = t2.member_id");
                $CI->db->query("ALTER TABLE `" . TABLE_CHATGROUPMEMBERS . "` ADD UNIQUE KEY `idx_unique_member` (`group_id`, `member_id`)");
            }
            $indexes = $CI->db->query("SHOW INDEX FROM `" . TABLE_CHATGROUPMEMBERS . "` WHERE Key_name = 'group_id'")->result();
            if (empty($indexes)) {
                $CI->db->query("ALTER TABLE `" . TABLE_CHATGROUPMEMBERS . "` ADD KEY `group_id` (`group_id`)");
            }
        }

        // Add reactions column (emoji reactions) to legacy chat message tables.
        // Stored as TEXT (JSON string) to avoid JSON-type differences across MySQL variants.
        $legacyMessageTables = [
            TABLE_CHATMESSAGES => 'staff-staff',
            TABLE_CHATCLIENTMESSAGES => 'staff-client',
            TABLE_CHATGROUPMESSAGES => 'group',
        ];
        foreach ($legacyMessageTables as $table => $label) {
            if ($CI->db->table_exists($table) && !$CI->db->field_exists('reactions', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `reactions` TEXT NULL DEFAULT NULL");
            }
            if ($CI->db->table_exists($table) && !$CI->db->field_exists('edited_at', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `edited_at` DATETIME NULL DEFAULT NULL");
            }
        }

        // ===========================================
        // Chatbot Tables (shared helper)
        // ===========================================
        prchat_create_chatbot_tables();

        // Ensure csat/tags columns exist for users who had the table from an earlier migration
        $conv_tbl = db_prefix() . 'chatbot_conversations';
        if ($CI->db->table_exists($conv_tbl)) {
            $add_cols = [
                'csat_score'             => "ADD `csat_score` tinyint(1) DEFAULT NULL COMMENT 'Rating 1-5 stars'",
                'csat_comment'           => "ADD `csat_comment` text DEFAULT NULL",
                'csat_at'                => "ADD `csat_at` datetime DEFAULT NULL",
                'needs_followup'         => "ADD `needs_followup` tinyint(1) DEFAULT 0",
                'fallback_count'         => "ADD `fallback_count` int(11) DEFAULT 0",
                'converted_to_lead_id'   => "ADD `converted_to_lead_id` int(11) DEFAULT NULL",
                'converted_to_client_id' => "ADD `converted_to_client_id` int(11) DEFAULT NULL",
                'tags'                   => "ADD `tags` text DEFAULT NULL COMMENT 'JSON array of tag IDs for conversation'",
            ];
            foreach ($add_cols as $col => $sql) {
                if (!$CI->db->field_exists($col, $conv_tbl)) {
                    $CI->db->query("ALTER TABLE `{$conv_tbl}` {$sql}");
                }
            }
        }

        // Ensure csat_enabled exists on chatbots table
        $bots_tbl = db_prefix() . 'chatbots';
        if ($CI->db->table_exists($bots_tbl) && !$CI->db->field_exists('csat_enabled', $bots_tbl)) {
            $CI->db->query("ALTER TABLE `{$bots_tbl}` ADD `csat_enabled` tinyint(1) DEFAULT 0");
        }

        // Drop notification_staff table (replaced by staff_permissions chatbot capability)
        if ($CI->db->table_exists(db_prefix() . 'chatbot_notification_staff')) {
            $CI->db->query("DROP TABLE `" . db_prefix() . "chatbot_notification_staff`");
        }

        // Add file_data column to training_files for surviving module reinstalls
        $files_tbl = db_prefix() . 'chatbot_training_files';
        if ($CI->db->table_exists($files_tbl) && !$CI->db->field_exists('file_data', $files_tbl)) {
            $CI->db->query("ALTER TABLE `{$files_tbl}` ADD `file_data` LONGTEXT NULL DEFAULT NULL AFTER `file_size`");
        }

        // ===========================================
        // Options (chatbot-specific + widget/call for existing users)
        // ===========================================
        prchat_ensure_options(prchat_chatbot_options());

        prchat_ensure_options([
            'chat_client_widget_position'          => 'right',
            'chat_client_widget_primary_color'      => '#4f46e5',
            'chat_client_widget_show_logo'          => '1',
            'chat_client_widget_show_staff_names'   => '1',
            'chat_client_widget_show_staff_roles'   => '1',
            'chat_client_staff_visibility'          => 'assigned_and_responded',
            'chat_client_calls_enabled'             => '0',
            'chat_staff_calls_enabled'              => '1',
            'chat_calls_video_enabled'              => '1',
            'prchat_toggled_chat_disabled'           => '1',
            'chat_staff_can_access_clients'         => '1',
            'chat_floating_notifications_enabled'   => '1',
        ]);

        // ===========================================
        // Seed Data (shared helper — idempotent)
        // ===========================================
        prchat_seed_default_chatbot();
    }
}
