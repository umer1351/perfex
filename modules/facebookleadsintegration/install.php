<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Facebook & Instagram Leads Integration Installation Script
 * 
 * Runs on module activation to set up database tables and default options.
 * 
 * @package    FacebookLeadsIntegration
 * @author     Themesic Interactive
 * @version    2.0.0
 */

$CI = &get_instance();

// ============================================================
// CREATE DATABASE TABLES
// ============================================================

// Sync History Table
if (!$CI->db->table_exists(db_prefix() . 'fb_leads_sync_history')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "fb_leads_sync_history` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
    ");
}

// Logs Table
if (!$CI->db->table_exists(db_prefix() . 'fb_leads_logs')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "fb_leads_logs` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
    ");
}

// Retry Queue Table
if (!$CI->db->table_exists(db_prefix() . 'fb_leads_retry_queue')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "fb_leads_retry_queue` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
    ");
}

// Pages Table
if (!$CI->db->table_exists(db_prefix() . 'fb_leads_pages')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "fb_leads_pages` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
    ");
}

// Field Mappings Table
if (!$CI->db->table_exists(db_prefix() . 'fb_leads_field_mappings')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "fb_leads_field_mappings` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
    ");
}

// Add per-page assignment columns if they don't exist
if ($CI->db->table_exists(db_prefix() . 'fb_leads_pages')) {
    if (!$CI->db->field_exists('assigned_to', db_prefix() . 'fb_leads_pages')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "fb_leads_pages` ADD COLUMN `assigned_to` INT(11) NULL AFTER `leads_count`");
    }
    if (!$CI->db->field_exists('default_source', db_prefix() . 'fb_leads_pages')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "fb_leads_pages` ADD COLUMN `default_source` INT(11) NULL AFTER `assigned_to`");
    }
    if (!$CI->db->field_exists('default_status', db_prefix() . 'fb_leads_pages')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "fb_leads_pages` ADD COLUMN `default_status` INT(11) NULL AFTER `default_source`");
    }
}

// Add facebook_lead_id column to leads table if it doesn't exist
if (!$CI->db->field_exists('facebook_lead_id', db_prefix() . 'leads')) {
    $CI->db->query("
        ALTER TABLE `" . db_prefix() . "leads` 
        ADD COLUMN `facebook_lead_id` VARCHAR(64) NULL AFTER `id`,
        ADD INDEX `facebook_lead_id` (`facebook_lead_id`)
    ");
}

// ============================================================
// MIGRATE OLD OPTIONS TO NEW NAMING CONVENTION
// ============================================================

$option_mappings = [
    'appId' => 'fb_leads_app_id',
    'appSecret' => 'fb_leads_app_secret',
    'verifytoken' => 'fb_leads_webhook_token',
    'longLifeAccessToken' => 'fb_leads_access_token',
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

// ============================================================
// SET DEFAULT OPTIONS
// ============================================================

$default_options = [
    'fb_leads_app_id' => '',
    'fb_leads_app_secret' => '',
    'fb_leads_webhook_token' => bin2hex(random_bytes(16)),
    'fb_leads_access_token' => '',
    'fb_leads_default_assigned' => '',
    'fb_leads_default_source' => '',
    'fb_leads_default_status' => '',
    'fb_leads_duplicate_detection' => '1',
    'fb_leads_duplicate_fields' => json_encode(['email', 'phonenumber']),
    'fb_leads_notifications_enabled' => '0',
    'fb_leads_notify_staff' => '',
    'fb_leads_max_retries' => '5',
    'fb_leads_webhook_verified' => '0',
    'fb_leads_setup_completed' => '0',
    'fb_leads_version' => '2.0.0'
];

foreach ($default_options as $option_name => $default_value) {
    if (!option_exists($option_name) || get_option($option_name) === false || get_option($option_name) === '') {
        // Don't overwrite webhook token if already set
        if ($option_name === 'fb_leads_webhook_token' && get_option($option_name)) {
            continue;
        }
        add_option($option_name, $default_value);
    }
}

// Generate webhook token if not set or still default
$current_token = get_option('fb_leads_webhook_token');
if (empty($current_token) || $current_token === 'token654321') {
    update_option('fb_leads_webhook_token', bin2hex(random_bytes(16)));
}

// ============================================================
// CREATE DEFAULT LEAD SOURCE
// ============================================================

$CI->db->where('name', 'Facebook Lead Ads');
$existing_source = $CI->db->get(db_prefix() . 'leads_sources')->row();

if (!$existing_source) {
    $CI->db->insert(db_prefix() . 'leads_sources', [
        'name' => 'Facebook Lead Ads'
    ]);
    $source_id = $CI->db->insert_id();
    
    // Set as default source if no default is set
    if (empty(get_option('fb_leads_default_source'))) {
        update_option('fb_leads_default_source', $source_id);
    }
}

// ============================================================
// LOG INSTALLATION
// ============================================================

log_activity('Facebook Leads Integration Module Installed/Updated [Version: 2.0.0]');
