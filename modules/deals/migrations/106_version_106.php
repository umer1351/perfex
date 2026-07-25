<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_106 extends App_module_migration
{
    /**
     * @throws Exception
     */
    public function up()
    {
        $CI = &get_instance();

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_campaign_messages` (
          `id` int NOT NULL AUTO_INCREMENT,
          `message_token` varchar(64) NOT NULL,
          `campaign_id` int DEFAULT NULL,
          `campaign_step_id` int DEFAULT NULL,
          `deal_id` int NOT NULL,
          `recipient_email` varchar(191) NOT NULL,
          `subject` varchar(191) DEFAULT NULL,
          `status` varchar(20) NOT NULL DEFAULT 'sent',
          `open_count` int NOT NULL DEFAULT '0',
          `click_count` int NOT NULL DEFAULT '0',
          `delivered_at` datetime DEFAULT NULL,
          `first_opened_at` datetime DEFAULT NULL,
          `last_opened_at` datetime DEFAULT NULL,
          `first_clicked_at` datetime DEFAULT NULL,
          `last_clicked_at` datetime DEFAULT NULL,
          `metadata_json` longtext,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `message_token` (`message_token`)
        ) ENGINE=InnoDB;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_email_preferences` (
          `id` int NOT NULL AUTO_INCREMENT,
          `email` varchar(191) NOT NULL,
          `token` varchar(64) NOT NULL,
          `is_unsubscribed` tinyint(1) NOT NULL DEFAULT '0',
          `unsubscribed_at` datetime DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`),
          UNIQUE KEY `token` (`token`)
        ) ENGINE=InnoDB;");
    }
}
