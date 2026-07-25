<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Facebook & Instagram Leads Integration v2.0.0 Migration
 * 
 * Creates tables for sync history, logs, retry queue, and field mappings.
 * 
 * @package    FacebookLeadsIntegration
 * @author     Themesic Interactive
 * @version    2.0.0
 */
class Migration_Version_200 extends App_module_migration
{
    /**
     * Run the migration
     * 
     * @return void
     */
    public function up()
    {
        $this->create_sync_history_table();
        $this->create_logs_table();
        $this->create_retry_queue_table();
        $this->create_pages_table();
        $this->create_field_mappings_table();
        $this->migrate_old_options();
        $this->add_facebook_lead_id_column();
        $this->add_per_page_assignment_columns();
    }

    /**
     * Revert the migration
     * 
     * @return void
     */
    public function down()
    {
        $prefix = db_prefix();
        
        $this->ci->db->query("DROP TABLE IF EXISTS `{$prefix}fb_leads_sync_history`");
        $this->ci->db->query("DROP TABLE IF EXISTS `{$prefix}fb_leads_logs`");
        $this->ci->db->query("DROP TABLE IF EXISTS `{$prefix}fb_leads_retry_queue`");
        $this->ci->db->query("DROP TABLE IF EXISTS `{$prefix}fb_leads_pages`");
        $this->ci->db->query("DROP TABLE IF EXISTS `{$prefix}fb_leads_field_mappings`");
    }

    /**
     * Create sync history table
     */
    private function create_sync_history_table()
    {
        $prefix = db_prefix();
        
        if ($this->ci->db->table_exists("{$prefix}fb_leads_sync_history")) {
            return;
        }

        $this->ci->db->query("
            CREATE TABLE IF NOT EXISTS `{$prefix}fb_leads_sync_history` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `facebook_lead_id` VARCHAR(64) NOT NULL,
                `perfex_lead_id` INT(11) UNSIGNED NULL,
                `status` ENUM('success', 'failed', 'skipped', 'retry') DEFAULT 'success',
                `message` VARCHAR(500) NULL,
                `details` TEXT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `facebook_lead_id` (`facebook_lead_id`),
                KEY `perfex_lead_id` (`perfex_lead_id`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Create logs table
     */
    private function create_logs_table()
    {
        $prefix = db_prefix();
        
        if ($this->ci->db->table_exists("{$prefix}fb_leads_logs")) {
            return;
        }

        $this->ci->db->query("
            CREATE TABLE IF NOT EXISTS `{$prefix}fb_leads_logs` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `log_type` ENUM('info', 'warning', 'error', 'debug', 'webhook') DEFAULT 'info',
                `message` VARCHAR(500) NOT NULL,
                `error_code` INT(11) NULL,
                `details` TEXT NULL,
                `ip_address` VARCHAR(45) NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `log_type` (`log_type`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Create retry queue table
     */
    private function create_retry_queue_table()
    {
        $prefix = db_prefix();
        
        if ($this->ci->db->table_exists("{$prefix}fb_leads_retry_queue")) {
            return;
        }

        $this->ci->db->query("
            CREATE TABLE IF NOT EXISTS `{$prefix}fb_leads_retry_queue` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `facebook_lead_id` VARCHAR(64) NOT NULL,
                `lead_data` TEXT NOT NULL,
                `meta_data` TEXT NULL,
                `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
                `retry_count` INT(11) DEFAULT 0,
                `last_error` VARCHAR(500) NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `facebook_lead_id` (`facebook_lead_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Create pages table for subscribed pages
     */
    private function create_pages_table()
    {
        $prefix = db_prefix();
        
        if ($this->ci->db->table_exists("{$prefix}fb_leads_pages")) {
            return;
        }

        $this->ci->db->query("
            CREATE TABLE IF NOT EXISTS `{$prefix}fb_leads_pages` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `page_id` VARCHAR(64) NOT NULL,
                `page_name` VARCHAR(255) NOT NULL,
                `access_token` TEXT NULL,
                `is_subscribed` TINYINT(1) DEFAULT 0,
                `subscribed_at` DATETIME NULL,
                `last_lead_at` DATETIME NULL,
                `leads_count` INT(11) DEFAULT 0,
                `assigned_to` INT(11) NULL,
                `default_source` INT(11) NULL,
                `default_status` INT(11) NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `page_id` (`page_id`),
                KEY `is_subscribed` (`is_subscribed`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Create field mappings table
     */
    private function create_field_mappings_table()
    {
        $prefix = db_prefix();
        
        if ($this->ci->db->table_exists("{$prefix}fb_leads_field_mappings")) {
            return;
        }

        $this->ci->db->query("
            CREATE TABLE IF NOT EXISTS `{$prefix}fb_leads_field_mappings` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `facebook_field` VARCHAR(100) NOT NULL,
                `perfex_field` VARCHAR(100) NOT NULL,
                `is_custom_field` TINYINT(1) DEFAULT 0,
                `custom_field_id` INT(11) NULL,
                `form_id` VARCHAR(64) NULL COMMENT 'If null, applies to all forms',
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `facebook_field` (`facebook_field`),
                KEY `form_id` (`form_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Migrate old option names to new naming convention
     */
    private function migrate_old_options()
    {
        // Map old option names to new ones
        $option_mappings = [
            'appId' => 'fb_leads_app_id',
            'appSecret' => 'fb_leads_app_secret',
            'verifytoken' => 'fb_leads_webhook_token',
            'longLifeAccessToken' => 'fb_leads_access_token',
            'subscribed_pages' => 'fb_leads_subscribed_pages_legacy',
            'facebook_pages' => 'fb_leads_pages_legacy',
            'facebook_lead_assigned' => 'fb_leads_default_assigned',
            'facebook_lead_source' => 'fb_leads_default_source',
            'facebook_lead_status' => 'fb_leads_default_status'
        ];

        foreach ($option_mappings as $old_name => $new_name) {
            $old_value = get_option($old_name);
            if ($old_value !== false && $old_value !== '' && !get_option($new_name)) {
                update_option($new_name, $old_value);
            }
        }

        // Set default values for new options if not set
        $defaults = [
            'fb_leads_duplicate_detection' => '1',
            'fb_leads_duplicate_fields' => json_encode(['email', 'phonenumber']),
            'fb_leads_notifications_enabled' => '0',
            'fb_leads_max_retries' => '5',
            'fb_leads_webhook_verified' => '0',
            'fb_leads_setup_completed' => '0'
        ];

        foreach ($defaults as $option => $default_value) {
            if (!get_option($option)) {
                add_option($option, $default_value);
            }
        }

        // Generate webhook token if not set
        if (!get_option('fb_leads_webhook_token') || get_option('fb_leads_webhook_token') === 'token654321') {
            update_option('fb_leads_webhook_token', bin2hex(random_bytes(16)));
        }
    }

    /**
     * Add facebook_lead_id column to leads table if it doesn't exist
     */
    private function add_facebook_lead_id_column()
    {
        $prefix = db_prefix();
        
        if (!$this->ci->db->field_exists('facebook_lead_id', "{$prefix}leads")) {
            $this->ci->db->query("ALTER TABLE `{$prefix}leads` ADD `facebook_lead_id` VARCHAR(64) NULL AFTER `id`");
            $this->ci->db->query("ALTER TABLE `{$prefix}leads` ADD INDEX `facebook_lead_id` (`facebook_lead_id`)");
        }
    }

    /**
     * Add per-page assignment columns to pages table (for existing installs upgrading)
     */
    private function add_per_page_assignment_columns()
    {
        $prefix = db_prefix();
        $table = "{$prefix}fb_leads_pages";

        if (!$this->ci->db->table_exists($table)) {
            return;
        }

        if (!$this->ci->db->field_exists('assigned_to', $table)) {
            $this->ci->db->query("ALTER TABLE `{$table}` ADD COLUMN `assigned_to` INT(11) NULL AFTER `leads_count`");
        }
        if (!$this->ci->db->field_exists('default_source', $table)) {
            $this->ci->db->query("ALTER TABLE `{$table}` ADD COLUMN `default_source` INT(11) NULL AFTER `assigned_to`");
        }
        if (!$this->ci->db->field_exists('default_status', $table)) {
            $this->ci->db->query("ALTER TABLE `{$table}` ADD COLUMN `default_status` INT(11) NULL AFTER `default_source`");
        }
    }
}
