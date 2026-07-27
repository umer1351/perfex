<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        $columns = [
            'probability' => "ALTER TABLE `tbl_deals` ADD `probability` DECIMAL(5,2) NULL DEFAULT NULL AFTER `stage_id`;",
            'expected_revenue' => "ALTER TABLE `tbl_deals` ADD `expected_revenue` DECIMAL(10,2) NULL DEFAULT NULL AFTER `probability`;",
            'next_follow_up_at' => "ALTER TABLE `tbl_deals` ADD `next_follow_up_at` DATETIME NULL DEFAULT NULL AFTER `expected_revenue`;",
            'last_contacted_at' => "ALTER TABLE `tbl_deals` ADD `last_contacted_at` DATETIME NULL DEFAULT NULL AFTER `next_follow_up_at`;",
            'last_activity_at' => "ALTER TABLE `tbl_deals` ADD `last_activity_at` DATETIME NULL DEFAULT NULL AFTER `last_contacted_at`;",
            'priority' => "ALTER TABLE `tbl_deals` ADD `priority` VARCHAR(20) NOT NULL DEFAULT 'medium' AFTER `last_activity_at`;",
            'forecast_category' => "ALTER TABLE `tbl_deals` ADD `forecast_category` VARCHAR(20) NOT NULL DEFAULT 'pipeline' AFTER `priority`;",
            'health_status' => "ALTER TABLE `tbl_deals` ADD `health_status` VARCHAR(20) NOT NULL DEFAULT 'on_track' AFTER `forecast_category`;",
            'campaign_id' => "ALTER TABLE `tbl_deals` ADD `campaign_id` INT NULL DEFAULT NULL AFTER `health_status`;",
        ];

        foreach ($columns as $column => $sql) {
            if (!$CI->db->field_exists($column, 'tbl_deals')) {
                $CI->db->query($sql);
            }
        }

        if ($CI->db->field_exists('last_activity_at', 'tbl_deals')) {
            $CI->db->query("UPDATE `tbl_deals`
                SET `last_activity_at` = COALESCE(`last_activity_at`, `created_at`);");
        }
    }
}
