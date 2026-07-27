<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_237 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Per-recipient send queue. A scheduled message is expanded into one row per
        // recipient so the cron can send a small batch each run instead of all at once.
        // This avoids PHP max_execution_time timeouts on large groups and makes sends
        // resumable without ever double-sending.
        if (!$CI->db->table_exists(db_prefix().'custom_email_sms_queue')) {
            $CI->db->query('CREATE TABLE `'.db_prefix().'custom_email_sms_queue` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `schedule_id` INT NOT NULL,
                `recipient_type` varchar(20) NOT NULL COMMENT "customer, lead",
                `recipient_id` INT NOT NULL,
                `status` varchar(20) NOT NULL DEFAULT "pending" COMMENT "pending, sent, failed",
                `attempts` INT NOT NULL DEFAULT 0,
                `error_message` text DEFAULT NULL,
                `sent_at` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `schedule_id` (`schedule_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
        }

        // Flag so the cron expands each scheduled row into the queue exactly once.
        if (!$CI->db->field_exists('is_queued', db_prefix().'custom_email_sms')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'custom_email_sms`
                ADD COLUMN `is_queued` tinyint(1) NOT NULL DEFAULT 0');
        }

        // How many recipients to process per cron run. Keep modest so a single run
        // stays well under the server execution-time limit. Editable in Settings.
        $exists = $CI->db->where('name', 'customemailandsmsnotifications_cron_batch_size')
                         ->get(db_prefix().'options')
                         ->num_rows();
        if ($exists == 0) {
            add_option('customemailandsmsnotifications_cron_batch_size', 75);
        }
    }
}
