<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat Module Installation
 * 
 * Handles fresh installations and creates all necessary tables and options.
 */

$CI = &get_instance();
$CI->load->helper('prchat/prchat_chatbot_v200_schema');

// ===========================================
// Create upload directories
// ===========================================
$upload_dirs = [
  PR_CHAT_MODULE_UPLOAD_FOLDER,
  PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER,
  PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER,
  FCPATH . 'uploads/chatbot_files',
];

foreach ($upload_dirs as $dir) {
  if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
    $fp = fopen($dir . '/index.html', 'w');
    fclose($fp);
  }
}

// ===========================================
// Legacy Chat System Options
// ===========================================
$chat_options = [
  'pusher_chat_enabled' => 1,
  'chat_desktop_messages_notifications' => 1,
  'chat_floating_notifications_enabled' => '1',
  'chat_members_can_create_groups' => 1,
  'chat_client_enabled' => 1,
  'chat_allow_staff_to_create_tickets' => 1,
  'prchat_toggled_chat_disabled' => '1',

  'chat_client_widget_position' => 'right',
  'chat_client_widget_primary_color' => '#4f46e5',
  'chat_client_widget_show_logo' => '1',
  'chat_client_widget_show_staff_names' => '1',
  'chat_client_widget_show_staff_roles' => '1',
  'chat_client_staff_visibility' => 'assigned_and_responded',

  'chat_client_calls_enabled' => '0',
  'chat_staff_calls_enabled' => '1',
  'chat_calls_video_enabled' => '1',

  'chat_staff_can_access_clients' => '1',
];

foreach ($chat_options as $option_name => $default_value) {
  add_option($option_name, $default_value);
}

// ===========================================
// Legacy Chat Tables (Staff-to-Staff, Groups, Clients)
// ===========================================
if (!$CI->db->table_exists(TABLE_CHATMESSAGES)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATMESSAGES . "` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `sender_id` INT(11) NOT NULL,
            `reciever_id` INT(11) NOT NULL,
            `message` LONGTEXT NOT NULL,
            `reactions` TEXT NULL DEFAULT NULL,
            `viewed` INT(11) DEFAULT '0',
            `time_sent` DATETIME NULL DEFAULT NULL,
            `viewed_at` DATETIME NULL DEFAULT NULL,
            `edited_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATSETTINGS)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATSETTINGS . "` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `value` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATGROUPS)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATGROUPS . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `created_by_id` INT(11) NOT NULL,
            `group_name` VARCHAR(255) NOT NULL,
            `related_type` VARCHAR(50) NULL DEFAULT NULL,
            `related_id` INT(11) UNSIGNED NULL DEFAULT NULL,
            `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_related` (`related_type`, `related_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATGROUPMEMBERS)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATGROUPMEMBERS . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `group_id` INT(11) NOT NULL,
            `member_id` INT(11) NOT NULL,
            `group_name` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_unique_member` (`group_id`, `member_id`),
            KEY `group_id` (`group_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATGROUPMESSAGES)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATGROUPMESSAGES . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `group_id` INT(11) NOT NULL,
            `message` LONGTEXT NOT NULL,
            `reactions` TEXT NULL DEFAULT NULL,
            `sender_id` INT(11) NOT NULL,
            `time_sent` DATETIME NULL DEFAULT NULL,
            `edited_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATGROUPSHAREDFILES)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATGROUPSHAREDFILES . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `group_id` INT(11) NOT NULL,
            `sender_id` INT(11) NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATCLIENTMESSAGES)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATCLIENTMESSAGES . "` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `sender_id` VARCHAR(20) NOT NULL,
            `reciever_id` VARCHAR(20) NOT NULL,
            `message` LONGTEXT NOT NULL,
            `reactions` TEXT NULL DEFAULT NULL,
            `viewed` TINYINT(11) NOT NULL DEFAULT '0',
            `time_sent` DATETIME NULL DEFAULT NULL,
            `viewed_at` DATETIME NULL DEFAULT NULL,
            `edited_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

// ===========================================
// New Chatbot System (tables + options + seed data)
// ===========================================
prchat_create_chatbot_tables();
prchat_ensure_options(prchat_chatbot_options());
prchat_seed_default_chatbot();
