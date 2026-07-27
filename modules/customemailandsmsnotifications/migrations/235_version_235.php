<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_235 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        
        // Create message history table
        if (!$CI->db->table_exists(db_prefix().'custom_message_history')) {
            $CI->db->query('CREATE TABLE `'.db_prefix().'custom_message_history` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `staff_id` INT NOT NULL,
                `message_type` varchar(20) NOT NULL COMMENT "email, sms, whatsapp",
                `recipient_type` varchar(20) NOT NULL COMMENT "customer, lead",
                `recipient_id` INT NOT NULL,
                `recipient_name` varchar(255) NOT NULL,
                `recipient_contact` varchar(255) NOT NULL,
                `subject` varchar(500) DEFAULT NULL,
                `message_content` text NOT NULL,
                `template_id` INT DEFAULT NULL,
                `status` varchar(50) NOT NULL DEFAULT "pending" COMMENT "pending, sent, failed, scheduled",
                `error_message` text DEFAULT NULL,
                `scheduled_at` datetime DEFAULT NULL,
                `sent_at` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `has_attachment` tinyint(1) DEFAULT 0,
                `attachment_path` varchar(500) DEFAULT NULL,
                `gateway` varchar(50) DEFAULT NULL COMMENT "twilio, clickatell, msg91, smtp, whatsapp",
                `cost_estimate` decimal(10,4) DEFAULT NULL,
                `sms_segments` int DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `staff_id` (`staff_id`),
                KEY `status` (`status`),
                KEY `message_type` (`message_type`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
        }
        
        // Create recipient groups table
        if (!$CI->db->table_exists(db_prefix().'custom_recipient_groups')) {
            $CI->db->query('CREATE TABLE `'.db_prefix().'custom_recipient_groups` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `staff_id` INT NOT NULL,
                `group_name` varchar(255) NOT NULL,
                `description` text DEFAULT NULL,
                `recipient_type` varchar(20) NOT NULL COMMENT "customers, leads, mixed",
                `customer_ids` text DEFAULT NULL COMMENT "JSON array of customer IDs",
                `lead_ids` text DEFAULT NULL COMMENT "JSON array of lead IDs",
                `filter_criteria` text DEFAULT NULL COMMENT "JSON object with filter rules",
                `recipient_count` INT DEFAULT 0,
                `is_dynamic` tinyint(1) DEFAULT 0 COMMENT "0=static, 1=dynamic based on filters",
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
        }
        
        // Create template categories table
        if (!$CI->db->table_exists(db_prefix().'custom_template_categories')) {
            $CI->db->query('CREATE TABLE `'.db_prefix().'custom_template_categories` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text DEFAULT NULL,
                `color` varchar(20) DEFAULT NULL,
                `icon` varchar(50) DEFAULT NULL,
                `sort_order` INT DEFAULT 0,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
        }
        
        // Add category_id to custom_templates table
        if (!$CI->db->field_exists('category_id', db_prefix().'custom_templates')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'custom_templates` 
                ADD COLUMN `category_id` INT DEFAULT NULL AFTER `staff_id`,
                ADD COLUMN `is_shared` tinyint(1) DEFAULT 0 COMMENT "0=private, 1=shared with all staff",
                ADD COLUMN `template_type` varchar(20) DEFAULT "both" COMMENT "email, sms, whatsapp, both",
                ADD COLUMN `created_at` datetime DEFAULT NULL,
                ADD COLUMN `updated_at` datetime DEFAULT NULL
            ');
        }
        
        // Add delivery tracking fields to custom_email_sms table
        if (!$CI->db->field_exists('message_history_id', db_prefix().'custom_email_sms')) {
            $CI->db->query('ALTER TABLE `'.db_prefix().'custom_email_sms` 
                ADD COLUMN `message_history_id` INT DEFAULT NULL,
                ADD COLUMN `error_details` text DEFAULT NULL,
                ADD COLUMN `retry_count` INT DEFAULT 0,
                ADD COLUMN `last_retry_at` datetime DEFAULT NULL,
                ADD COLUMN `delivered_at` datetime DEFAULT NULL
            ');
        }
        
        // Insert default template categories
        $default_categories = [
            ['name' => 'General', 'description' => 'General purpose templates', 'color' => '#3498db', 'icon' => 'fa-folder', 'sort_order' => 1],
            ['name' => 'Marketing', 'description' => 'Marketing and promotional messages', 'color' => '#e74c3c', 'icon' => 'fa-bullhorn', 'sort_order' => 2],
            ['name' => 'Invoices', 'description' => 'Invoice related notifications', 'color' => '#2ecc71', 'icon' => 'fa-file-invoice', 'sort_order' => 3],
            ['name' => 'Projects', 'description' => 'Project updates and notifications', 'color' => '#f39c12', 'icon' => 'fa-project-diagram', 'sort_order' => 4],
            ['name' => 'Support', 'description' => 'Customer support messages', 'color' => '#9b59b6', 'icon' => 'fa-life-ring', 'sort_order' => 5],
            ['name' => 'Reminders', 'description' => 'Payment and deadline reminders', 'color' => '#1abc9c', 'icon' => 'fa-bell', 'sort_order' => 6]
        ];
        
        foreach ($default_categories as $category) {
            $category['created_at'] = date('Y-m-d H:i:s');
            
            // Check if category already exists
            $exists = $CI->db->where('name', $category['name'])
                             ->get(db_prefix().'custom_template_categories')
                             ->num_rows();
            
            if ($exists == 0) {
                $CI->db->insert(db_prefix().'custom_template_categories', $category);
            }
        }
        
        // Add WhatsApp settings options
        add_option('sms_whatsapp_active', 0);
        add_option('sms_whatsapp_account_sid', '');
        add_option('sms_whatsapp_auth_token', '');
        add_option('sms_whatsapp_phone_number', '');
    }
}
