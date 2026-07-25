<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_107 extends App_module_migration
{
    /**
     * @throws Exception
     */
    public function up()
    {
        $CI = &get_instance();

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_approvals` (
          `id` int NOT NULL AUTO_INCREMENT,
          `deal_id` int NOT NULL,
          `approval_type` varchar(50) NOT NULL DEFAULT 'custom',
          `title` varchar(191) NOT NULL,
          `requested_by` int DEFAULT NULL,
          `assigned_to` int DEFAULT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'pending',
          `priority` varchar(20) NOT NULL DEFAULT 'medium',
          `notes` text DEFAULT NULL,
          `decision_notes` text DEFAULT NULL,
          `due_at` datetime DEFAULT NULL,
          `requested_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `decided_at` datetime DEFAULT NULL,
          `metadata_json` longtext,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_webhooks` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(191) NOT NULL,
          `endpoint_url` varchar(500) NOT NULL,
          `secret_key` varchar(191) DEFAULT NULL,
          `trigger_events_json` longtext,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `last_run_at` datetime DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_webhook_logs` (
          `id` int NOT NULL AUTO_INCREMENT,
          `webhook_id` int DEFAULT NULL,
          `deal_id` int DEFAULT NULL,
          `event_type` varchar(50) NOT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'pending',
          `response_code` int DEFAULT NULL,
          `request_payload` longtext,
          `response_body` longtext,
          `attempted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");
    }
}
