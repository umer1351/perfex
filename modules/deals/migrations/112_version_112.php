<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_integrations` (
          `id` int NOT NULL AUTO_INCREMENT,
          `provider_key` varchar(64) NOT NULL,
          `name` varchar(191) NOT NULL,
          `driver_class` varchar(191) NOT NULL,
          `status` varchar(30) NOT NULL DEFAULT 'active',
          `supports_oauth` tinyint(1) NOT NULL DEFAULT '0',
          `supports_webhooks` tinyint(1) NOT NULL DEFAULT '0',
          `supports_sync` tinyint(1) NOT NULL DEFAULT '0',
          `config_json` longtext,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `provider_key` (`provider_key`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_integration_accounts` (
          `id` int NOT NULL AUTO_INCREMENT,
          `integration_id` int DEFAULT NULL,
          `provider_key` varchar(64) NOT NULL,
          `staff_id` int DEFAULT NULL,
          `account_label` varchar(191) NOT NULL,
          `external_account_id` varchar(191) DEFAULT NULL,
          `connection_status` varchar(30) NOT NULL DEFAULT 'disconnected',
          `scopes_json` longtext,
          `access_token_encrypted` longtext,
          `refresh_token_encrypted` longtext,
          `token_expires_at` datetime DEFAULT NULL,
          `webhook_secret` varchar(64) DEFAULT NULL,
          `oauth_state` varchar(64) DEFAULT NULL,
          `account_meta_json` longtext,
          `is_active` tinyint(1) NOT NULL DEFAULT '0',
          `last_synced_at` datetime DEFAULT NULL,
          `last_webhook_at` datetime DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `provider_key` (`provider_key`),
          KEY `connection_status` (`connection_status`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_integration_events` (
          `id` int NOT NULL AUTO_INCREMENT,
          `event_uuid` varchar(64) NOT NULL,
          `event_name` varchar(100) NOT NULL,
          `integration_id` int DEFAULT NULL,
          `account_id` int DEFAULT NULL,
          `deal_id` int DEFAULT NULL,
          `source` varchar(80) NOT NULL DEFAULT 'deals',
          `direction` varchar(20) NOT NULL DEFAULT 'internal',
          `payload_json` longtext,
          `status` varchar(20) NOT NULL DEFAULT 'queued',
          `attempts` int NOT NULL DEFAULT '0',
          `available_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `processed_at` datetime DEFAULT NULL,
          `error_message` text DEFAULT NULL,
          `queue_name` varchar(50) NOT NULL DEFAULT 'default',
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `event_uuid` (`event_uuid`),
          KEY `status` (`status`),
          KEY `queue_name` (`queue_name`),
          KEY `event_name` (`event_name`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_integration_logs` (
          `id` int NOT NULL AUTO_INCREMENT,
          `integration_id` int DEFAULT NULL,
          `account_id` int DEFAULT NULL,
          `event_id` int DEFAULT NULL,
          `level` varchar(20) NOT NULL DEFAULT 'info',
          `action` varchar(100) NOT NULL DEFAULT 'system',
          `message` text NOT NULL,
          `context_json` longtext,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `level` (`level`),
          KEY `action` (`action`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_sync_states` (
          `id` int NOT NULL AUTO_INCREMENT,
          `provider_key` varchar(64) NOT NULL,
          `account_id` int NOT NULL,
          `resource_type` varchar(80) NOT NULL,
          `cursor_token` varchar(191) DEFAULT NULL,
          `external_channel_id` varchar(191) DEFAULT NULL,
          `external_resource_id` varchar(191) DEFAULT NULL,
          `sync_status` varchar(30) NOT NULL DEFAULT 'idle',
          `last_synced_at` datetime DEFAULT NULL,
          `next_sync_at` datetime DEFAULT NULL,
          `last_webhook_at` datetime DEFAULT NULL,
          `state_json` longtext,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `account_resource` (`account_id`,`resource_type`),
          KEY `provider_key` (`provider_key`),
          KEY `sync_status` (`sync_status`)
        ) ENGINE=InnoDB;");
    }
}
