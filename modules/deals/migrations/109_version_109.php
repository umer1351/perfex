<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_109 extends App_module_migration
{
    /**
     * @throws Exception
     */
    public function up()
    {
        $CI = &get_instance();

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_inbound_mailboxes` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(191) NOT NULL,
          `mailbox_email` varchar(191) NOT NULL,
          `secret_token` varchar(64) NOT NULL,
          `allowed_sources_json` longtext,
          `default_owner_id` int DEFAULT NULL,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `secret_token` (`secret_token`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_email_threads` (
          `id` int NOT NULL AUTO_INCREMENT,
          `deal_id` int NOT NULL,
          `mailbox_id` int DEFAULT NULL,
          `thread_token` varchar(64) NOT NULL,
          `subject` varchar(191) DEFAULT NULL,
          `last_message_at` datetime DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `thread_token` (`thread_token`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_connectors` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(191) NOT NULL,
          `connector_type` varchar(50) NOT NULL DEFAULT 'slack',
          `endpoint_url` varchar(500) NOT NULL,
          `auth_token` varchar(191) DEFAULT NULL,
          `channel_identifier` varchar(191) DEFAULT NULL,
          `trigger_events_json` longtext,
          `template_text` longtext,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `last_run_at` datetime DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_connector_logs` (
          `id` int NOT NULL AUTO_INCREMENT,
          `connector_id` int DEFAULT NULL,
          `deal_id` int DEFAULT NULL,
          `event_type` varchar(50) NOT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'pending',
          `response_code` int DEFAULT NULL,
          `request_payload` longtext,
          `response_body` longtext,
          `error_message` text DEFAULT NULL,
          `processing_ms` int DEFAULT NULL,
          `retry_of_log_id` int DEFAULT NULL,
          `is_test` tinyint(1) NOT NULL DEFAULT '0',
          `attempted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_runtime_qa_logs` (
          `id` int NOT NULL AUTO_INCREMENT,
          `area` varchar(100) NOT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'info',
          `deal_id` int DEFAULT NULL,
          `details_json` longtext,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        if ($CI->db->table_exists('tbl_deals_email')) {
            $columns = [
                'direction' => "ALTER TABLE `tbl_deals_email` ADD `direction` varchar(20) NOT NULL DEFAULT 'outbound' AFTER `subject`;",
                'thread_token' => "ALTER TABLE `tbl_deals_email` ADD `thread_token` varchar(64) DEFAULT NULL AFTER `direction`;",
                'in_reply_to' => "ALTER TABLE `tbl_deals_email` ADD `in_reply_to` varchar(191) DEFAULT NULL AFTER `thread_token`;",
                'message_id' => "ALTER TABLE `tbl_deals_email` ADD `message_id` varchar(191) DEFAULT NULL AFTER `in_reply_to`;",
                'delivery_status' => "ALTER TABLE `tbl_deals_email` ADD `delivery_status` varchar(30) DEFAULT NULL AFTER `message_id`;",
                'bounce_status' => "ALTER TABLE `tbl_deals_email` ADD `bounce_status` varchar(30) DEFAULT NULL AFTER `delivery_status`;",
                'connector_source' => "ALTER TABLE `tbl_deals_email` ADD `connector_source` varchar(50) DEFAULT NULL AFTER `bounce_status`;",
                'raw_headers_json' => "ALTER TABLE `tbl_deals_email` ADD `raw_headers_json` longtext AFTER `connector_source`;",
                'parsed_payload_json' => "ALTER TABLE `tbl_deals_email` ADD `parsed_payload_json` longtext AFTER `raw_headers_json`;",
                'mailbox_id' => "ALTER TABLE `tbl_deals_email` ADD `mailbox_id` int DEFAULT NULL AFTER `parsed_payload_json`;",
                'is_bounce' => "ALTER TABLE `tbl_deals_email` ADD `is_bounce` tinyint(1) NOT NULL DEFAULT '0' AFTER `mailbox_id`;",
            ];

            foreach ($columns as $field => $sql) {
                if (!$CI->db->field_exists($field, 'tbl_deals_email')) {
                    $CI->db->query($sql);
                }
            }
        }

        if ($CI->db->table_exists('tbl_deals_email_preferences')) {
            $columns = [
                'bounce_status' => "ALTER TABLE `tbl_deals_email_preferences` ADD `bounce_status` varchar(30) DEFAULT NULL AFTER `is_unsubscribed`;",
                'last_bounced_at' => "ALTER TABLE `tbl_deals_email_preferences` ADD `last_bounced_at` datetime DEFAULT NULL AFTER `bounce_status`;",
                'bounce_reason' => "ALTER TABLE `tbl_deals_email_preferences` ADD `bounce_reason` text DEFAULT NULL AFTER `last_bounced_at`;",
            ];

            foreach ($columns as $field => $sql) {
                if (!$CI->db->field_exists($field, 'tbl_deals_email_preferences')) {
                    $CI->db->query($sql);
                }
            }
        }
    }
}
