<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_105 extends App_module_migration
{
    /**
     * @throws Exception
     */
    public function up()
    {
        $CI = &get_instance();

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_campaign_steps` (
          `id` int NOT NULL AUTO_INCREMENT,
          `campaign_id` int NOT NULL,
          `name` varchar(191) NOT NULL,
          `step_type` varchar(50) NOT NULL DEFAULT 'email',
          `delay_amount` int NOT NULL DEFAULT '0',
          `delay_unit` varchar(20) NOT NULL DEFAULT 'days',
          `send_to` varchar(30) NOT NULL DEFAULT 'primary_contact',
          `email_subject` varchar(191) DEFAULT NULL,
          `email_body` mediumtext,
          `sort_order` int NOT NULL DEFAULT '0',
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_automation_rules` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(191) NOT NULL,
          `trigger_type` varchar(50) NOT NULL,
          `pipeline_id` int DEFAULT NULL,
          `stage_id` int DEFAULT NULL,
          `deal_status` varchar(30) DEFAULT NULL,
          `offset_value` int NOT NULL DEFAULT '0',
          `offset_unit` varchar(20) NOT NULL DEFAULT 'hours',
          `conditions_json` longtext,
          `actions_json` longtext,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `last_run_at` datetime DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_automation_queue` (
          `id` int NOT NULL AUTO_INCREMENT,
          `rule_id` int DEFAULT NULL,
          `campaign_id` int DEFAULT NULL,
          `campaign_step_id` int DEFAULT NULL,
          `deal_id` int NOT NULL,
          `action_type` varchar(50) NOT NULL,
          `execute_at` datetime NOT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'pending',
          `payload` longtext,
          `attempts` int NOT NULL DEFAULT '0',
          `processed_at` datetime DEFAULT NULL,
          `last_error` text DEFAULT NULL,
          `dedupe_key` varchar(191) DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");
    }
}
