<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    /**
     * @throws Exception
     */
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->field_exists('probability', 'tbl_deals')) {
            $CI->db->query("ALTER TABLE `tbl_deals`
                ADD `probability` DECIMAL(5,2) NULL DEFAULT NULL AFTER `stage_id`,
                ADD `expected_revenue` DECIMAL(10,2) NULL DEFAULT NULL AFTER `probability`,
                ADD `next_follow_up_at` DATETIME NULL DEFAULT NULL AFTER `expected_revenue`,
                ADD `last_contacted_at` DATETIME NULL DEFAULT NULL AFTER `next_follow_up_at`,
                ADD `last_activity_at` DATETIME NULL DEFAULT NULL AFTER `last_contacted_at`,
                ADD `priority` VARCHAR(20) NOT NULL DEFAULT 'medium' AFTER `last_activity_at`,
                ADD `forecast_category` VARCHAR(20) NOT NULL DEFAULT 'pipeline' AFTER `priority`,
                ADD `health_status` VARCHAR(20) NOT NULL DEFAULT 'on_track' AFTER `forecast_category`,
                ADD `campaign_id` INT NULL DEFAULT NULL AFTER `health_status`;");
        }

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_followups` (
          `id` int NOT NULL AUTO_INCREMENT,
          `deal_id` int NOT NULL,
          `subject` varchar(191) NOT NULL,
          `description` text DEFAULT NULL,
          `follow_up_at` datetime NOT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'pending',
          `type` varchar(30) NOT NULL DEFAULT 'call',
          `owner_id` int DEFAULT NULL,
          `outcome` text DEFAULT NULL,
          `completed_at` datetime DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_campaigns` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(191) NOT NULL,
          `channel` varchar(50) NOT NULL DEFAULT 'email',
          `status` varchar(30) NOT NULL DEFAULT 'draft',
          `audience_size` int NOT NULL DEFAULT '0',
          `sent_count` int NOT NULL DEFAULT '0',
          `open_count` int NOT NULL DEFAULT '0',
          `click_count` int NOT NULL DEFAULT '0',
          `budget` decimal(10,2) NOT NULL DEFAULT '0.00',
          `started_at` datetime DEFAULT NULL,
          `ended_at` datetime DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        $CI->db->query("UPDATE `tbl_deals`
            SET `probability` = CASE
                WHEN `status` = 'won' THEN 100
                WHEN `status` = 'lost' THEN 0
                ELSE COALESCE(`probability`, 0)
            END,
            `expected_revenue` = CASE
                WHEN `expected_revenue` IS NULL THEN ROUND(`deal_value` * (COALESCE(`probability`, 0) / 100), 2)
                ELSE `expected_revenue`
            END,
            `last_activity_at` = COALESCE(`last_activity_at`, `created_at`);");
    }
}
