<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_108 extends App_module_migration
{
    /**
     * @throws Exception
     */
    public function up()
    {
        $CI = &get_instance();

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_approval_policies` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(191) NOT NULL,
          `trigger_event` varchar(50) NOT NULL DEFAULT 'status_won',
          `approval_type` varchar(50) NOT NULL DEFAULT 'custom',
          `title_template` varchar(191) NOT NULL,
          `assigned_to` int DEFAULT NULL,
          `priority` varchar(20) NOT NULL DEFAULT 'high',
          `pipeline_id` int DEFAULT NULL,
          `stage_id` int DEFAULT NULL,
          `min_deal_value` decimal(10,2) DEFAULT NULL,
          `health_status` varchar(20) DEFAULT NULL,
          `due_in_hours` int NOT NULL DEFAULT '24',
          `auto_create` tinyint(1) NOT NULL DEFAULT '1',
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;");

        if ($CI->db->table_exists('tbl_deals_approvals')) {
            if (!$CI->db->field_exists('policy_id', 'tbl_deals_approvals')) {
                $CI->db->query("ALTER TABLE `tbl_deals_approvals` ADD `policy_id` int DEFAULT NULL AFTER `deal_id`;");
            }

            if (!$CI->db->field_exists('decided_by', 'tbl_deals_approvals')) {
                $CI->db->query("ALTER TABLE `tbl_deals_approvals` ADD `decided_by` int DEFAULT NULL AFTER `decided_at`;");
            }
        }

        if ($CI->db->table_exists('tbl_deals_webhook_logs')) {
            if (!$CI->db->field_exists('error_message', 'tbl_deals_webhook_logs')) {
                $CI->db->query("ALTER TABLE `tbl_deals_webhook_logs` ADD `error_message` text DEFAULT NULL AFTER `response_body`;");
            }

            if (!$CI->db->field_exists('is_test', 'tbl_deals_webhook_logs')) {
                $CI->db->query("ALTER TABLE `tbl_deals_webhook_logs` ADD `is_test` tinyint(1) NOT NULL DEFAULT '0' AFTER `error_message`;");
            }

            if (!$CI->db->field_exists('retry_of_log_id', 'tbl_deals_webhook_logs')) {
                $CI->db->query("ALTER TABLE `tbl_deals_webhook_logs` ADD `retry_of_log_id` int DEFAULT NULL AFTER `is_test`;");
            }

            if (!$CI->db->field_exists('processing_ms', 'tbl_deals_webhook_logs')) {
                $CI->db->query("ALTER TABLE `tbl_deals_webhook_logs` ADD `processing_ms` int DEFAULT NULL AFTER `retry_of_log_id`;");
            }
        }
    }
}
