<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * uninstall.php - Drops tables and removes options when the module is UNINSTALLED.
 * WARNING: This will delete ALL WhatsApp message data permanently!
 *
 * Note: This runs on UNINSTALL, not on deactivation.
 * Deactivation preserves data; uninstall removes everything.
 */

$CI = &get_instance();

// Drop tables in correct order (messages first due to foreign key)
$tables = [
    db_prefix() . 'whatsapp_messages',
    db_prefix() . 'whatsapp_conversations',
];

foreach ($tables as $table) {
    if ($CI->db->table_exists($table)) {
        $CI->db->query('DROP TABLE IF EXISTS `' . $table . '`');
        log_message('info', '[whatsapp_chat] Dropped table: ' . $table);
    }
}

// Remove module options
$CI->db->where('name', 'whatsapp_chat_api_token');
$CI->db->delete(db_prefix() . 'options');

log_message('info', '[whatsapp_chat] Module uninstalled and all data removed');
