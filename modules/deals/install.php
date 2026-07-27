<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();


// check if column exists in table
if (!$CI->db->field_exists('deal_comment_id', db_prefix() . 'files')) {
    $CI->db->query("ALTER TABLE " . db_prefix() . "files ADD `deal_comment_id` INT NULL DEFAULT NULL AFTER `task_comment_id`;");
}

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(191) DEFAULT NULL,
  `deal_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `source_id` int DEFAULT NULL,
  `status` varchar(100) DEFAULT 'open',
  `notes` text,
  `pipeline_id` int DEFAULT NULL,
  `currency` varchar(64) NOT NULL DEFAULT 'USD',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `days_to_close` date DEFAULT NULL,
  `user_id` text,
  `project_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `client_id` text,
  `stage_id` int NOT NULL,
  `default_deal_owner` int NOT NULL,
  `convert_to_project` varchar(100) DEFAULT NULL,
  `lost_reason` text,
  `tax` decimal(18,2) DEFAULT NULL,
  `total_tax` text,
   `dealorder` INT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `deal_id` int NOT NULL,
  `staffid` int NOT NULL,
  `contact_id` int NOT NULL DEFAULT '0',
  `file_id` int NOT NULL DEFAULT '0',
  `dateadded` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `deal_id` (`deal_id`)
) ENGINE=InnoDB;");
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_email` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email_to` text,
  `email_cc` varchar(100) DEFAULT NULL,
  `deals_id` int NOT NULL,
  `subject` varchar(230) DEFAULT NULL,
  `message_body` varchar(512) DEFAULT NULL,
  `uploads` varchar(512) DEFAULT NULL,
  `user_id` int NOT NULL,
  `files` text NOT NULL,
  `uploaded_path` text NOT NULL,
  `file_name` text NOT NULL,
  `size` int NOT NULL,
  `ext` varchar(100) NOT NULL,
  `is_image` int NOT NULL,
  `message_time` datetime NOT NULL,
  `attach_file` text,
  `email_from` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;");
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_items` (
  `items_id` int NOT NULL AUTO_INCREMENT,
  `deals_id` int NOT NULL,
  `tax_rates_id` text,
  `item_tax_rate` decimal(18,2) NOT NULL DEFAULT '0.00',
  `item_tax_name` text,
  `item_tax_total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `quantity` decimal(18,2) DEFAULT '0.00',
  `total_cost` decimal(18,2) DEFAULT '0.00',
  `item_name` varchar(255) DEFAULT 'Item Name',
  `item_desc` longtext,
  `unit_cost` decimal(18,2) DEFAULT '0.00',
  `order` int DEFAULT '0',
  `date_saved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unit` varchar(200) DEFAULT NULL,
  `hsn_code` text,
  `item_id` int DEFAULT '0',
  PRIMARY KEY (`items_id`)
) ENGINE=InnoDB;");
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_mettings` (
  `mettings_id` int NOT NULL AUTO_INCREMENT,
  `leads_id` int DEFAULT NULL,
  `opportunities_id` int DEFAULT NULL,
  `meeting_subject` varchar(200) NOT NULL,
  `attendees` varchar(300) NOT NULL,
  `user_id` int NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `module_field_id` int DEFAULT NULL,
  `start_date` varchar(100) NOT NULL,
  `end_date` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `description` mediumtext NOT NULL,
  PRIMARY KEY (`mettings_id`)
) ENGINE=InnoDB;");

$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_pipelines`;");
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_pipelines` (
  `pipeline_id` int NOT NULL AUTO_INCREMENT,
  `pipeline_name` varchar(100) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`pipeline_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4;");

$CI->db->query("INSERT INTO `tbl_deals_pipelines` (`pipeline_id`, `pipeline_name`, `description`, `order`) VALUES
(1, 'Sales', NULL, 0),
(2, 'Interview', NULL, 0),
(3, 'Store', NULL, 0);");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_source`;");
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_source` (
  `source_id` int NOT NULL AUTO_INCREMENT,
  `source_name` varchar(100) NOT NULL,
  PRIMARY KEY (`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11;");

$CI->db->query("INSERT INTO `tbl_deals_source` (`source_id`, `source_name`) VALUES
(1, 'Facebook'),
(2, 'Google Organic'),
(3, 'Web'),
(4, 'Twitter'),
(6, 'Youtube'),
(7, 'Mailchimp'),
(8, 'Previous Client'),
(9, 'Email List'),
(10, 'Google Ads');");

$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_stages`;");
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deals_stages` (
  `stage_id` int NOT NULL AUTO_INCREMENT,
  `stage_name` varchar(512) DEFAULT NULL,
  `pipeline_id` int NOT NULL,
  `stage_order` int NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stage_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8;");
$CI->db->query("INSERT INTO `tbl_deals_stages` (`stage_id`, `stage_name`, `pipeline_id`, `stage_order`, `date`) VALUES
(1, 'Qualified To Buy', 1, 1, '2023-08-23 09:22:38'),
(2, 'Contact Made', 1, 2, '2023-08-23 09:41:24'),
(3, 'Presentation Scheduled', 1, 3, '2023-08-23 09:41:47'),
(4, 'Proposal Made', 1, 4, '2023-08-23 09:41:55'),
(5, 'Appointment Scheduled', 1, 5, '2023-08-23 09:42:13');");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deal_activity_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `deal_id` int NOT NULL,
  `description` mediumtext NOT NULL,
  `additional_data` text,
  `date` datetime NOT NULL,
  `staffid` int NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `custom_activity` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_deal_calls` (
  `calls_id` int NOT NULL AUTO_INCREMENT,
  `leads_id` int DEFAULT NULL,
  `opportunities_id` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `module_field_id` int DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `call_summary` varchar(200) NOT NULL,
  `call_type` varchar(50) DEFAULT NULL,
  `outcome` varchar(50) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`calls_id`)
) ENGINE=InnoDB;");

if (!$CI->db->field_exists('rel_type', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `rel_type` VARCHAR(30) NULL DEFAULT NULL AFTER `client_id`, ADD `rel_id` INT(11) NULL DEFAULT NULL AFTER `rel_type`;");
}

if (!$CI->db->field_exists('probability', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `probability` DECIMAL(5,2) NULL DEFAULT NULL AFTER `stage_id`;");
}

if (!$CI->db->field_exists('expected_revenue', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `expected_revenue` DECIMAL(10,2) NULL DEFAULT NULL AFTER `probability`;");
}

if (!$CI->db->field_exists('next_follow_up_at', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `next_follow_up_at` DATETIME NULL DEFAULT NULL AFTER `expected_revenue`;");
}

if (!$CI->db->field_exists('last_contacted_at', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `last_contacted_at` DATETIME NULL DEFAULT NULL AFTER `next_follow_up_at`;");
}

if (!$CI->db->field_exists('last_activity_at', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `last_activity_at` DATETIME NULL DEFAULT NULL AFTER `last_contacted_at`;");
}

if (!$CI->db->field_exists('priority', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `priority` VARCHAR(20) NOT NULL DEFAULT 'medium' AFTER `last_activity_at`;");
}

if (!$CI->db->field_exists('forecast_category', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `forecast_category` VARCHAR(20) NOT NULL DEFAULT 'pipeline' AFTER `priority`;");
}

if (!$CI->db->field_exists('health_status', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `health_status` VARCHAR(20) NOT NULL DEFAULT 'on_track' AFTER `forecast_category`;");
}

if (!$CI->db->field_exists('campaign_id', 'tbl_deals')) {
    $CI->db->query("ALTER TABLE `tbl_deals` ADD `campaign_id` INT NULL DEFAULT NULL AFTER `health_status`;");
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
    `last_activity_at` = COALESCE(`last_activity_at`, `created_at`)
;");

if ($CI->db->table_exists('tbl_deals_inbound_mailboxes')) {
    $mailboxColumns = [
        'provider_type' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `provider_type` varchar(50) NOT NULL DEFAULT 'custom' AFTER `mailbox_email`;",
        'verification_mode' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `verification_mode` varchar(50) NOT NULL DEFAULT 'token_only' AFTER `secret_token`;",
        'verification_header' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `verification_header` varchar(100) DEFAULT 'X-Deals-Signature' AFTER `verification_mode`;",
        'verification_secret' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `verification_secret` varchar(191) DEFAULT NULL AFTER `verification_header`;",
        'routing_mode' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `routing_mode` varchar(50) NOT NULL DEFAULT 'thread_or_message_id' AFTER `allowed_sources_json`;",
        'allowed_sender_domains_json' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `allowed_sender_domains_json` longtext AFTER `routing_mode`;",
        'allow_reply_processing' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `allow_reply_processing` tinyint(1) NOT NULL DEFAULT '1' AFTER `allowed_sender_domains_json`;",
        'allow_bounce_processing' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `allow_bounce_processing` tinyint(1) NOT NULL DEFAULT '1' AFTER `allow_reply_processing`;",
        'last_received_at' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `last_received_at` datetime DEFAULT NULL AFTER `allow_bounce_processing`;",
        'last_bounced_at' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `last_bounced_at` datetime DEFAULT NULL AFTER `last_received_at`;",
        'last_error_at' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `last_error_at` datetime DEFAULT NULL AFTER `last_bounced_at`;",
        'last_error_message' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `last_error_message` text DEFAULT NULL AFTER `last_error_at`;",
        'notes' => "ALTER TABLE `tbl_deals_inbound_mailboxes` ADD `notes` longtext AFTER `last_error_message`;",
    ];

    foreach ($mailboxColumns as $field => $sql) {
        if (!$CI->db->field_exists($field, 'tbl_deals_inbound_mailboxes')) {
            $CI->db->query($sql);
        }
    }
}

if ($CI->db->table_exists('tbl_deals_connectors')) {
    $connectorColumns = [
        'delivery_method' => "ALTER TABLE `tbl_deals_connectors` ADD `delivery_method` varchar(10) NOT NULL DEFAULT 'POST' AFTER `endpoint_url`;",
        'auth_type' => "ALTER TABLE `tbl_deals_connectors` ADD `auth_type` varchar(20) NOT NULL DEFAULT 'bearer' AFTER `delivery_method`;",
        'auth_header_name' => "ALTER TABLE `tbl_deals_connectors` ADD `auth_header_name` varchar(100) DEFAULT 'Authorization' AFTER `auth_type`;",
        'auth_username' => "ALTER TABLE `tbl_deals_connectors` ADD `auth_username` varchar(191) DEFAULT NULL AFTER `auth_header_name`;",
        'custom_headers_json' => "ALTER TABLE `tbl_deals_connectors` ADD `custom_headers_json` longtext AFTER `auth_username`;",
        'payload_format' => "ALTER TABLE `tbl_deals_connectors` ADD `payload_format` varchar(30) NOT NULL DEFAULT 'json' AFTER `custom_headers_json`;",
        'timeout_seconds' => "ALTER TABLE `tbl_deals_connectors` ADD `timeout_seconds` int NOT NULL DEFAULT '10' AFTER `payload_format`;",
        'retry_limit' => "ALTER TABLE `tbl_deals_connectors` ADD `retry_limit` int NOT NULL DEFAULT '0' AFTER `timeout_seconds`;",
        'retry_backoff_ms' => "ALTER TABLE `tbl_deals_connectors` ADD `retry_backoff_ms` int NOT NULL DEFAULT '250' AFTER `retry_limit`;",
        'signature_header_name' => "ALTER TABLE `tbl_deals_connectors` ADD `signature_header_name` varchar(100) DEFAULT 'X-Deals-Signature' AFTER `retry_backoff_ms`;",
        'signature_secret' => "ALTER TABLE `tbl_deals_connectors` ADD `signature_secret` varchar(191) DEFAULT NULL AFTER `signature_header_name`;",
        'last_status' => "ALTER TABLE `tbl_deals_connectors` ADD `last_status` varchar(20) DEFAULT NULL AFTER `last_run_at`;",
        'last_success_at' => "ALTER TABLE `tbl_deals_connectors` ADD `last_success_at` datetime DEFAULT NULL AFTER `last_status`;",
        'consecutive_failures' => "ALTER TABLE `tbl_deals_connectors` ADD `consecutive_failures` int NOT NULL DEFAULT '0' AFTER `last_success_at`;",
        'notes' => "ALTER TABLE `tbl_deals_connectors` ADD `notes` longtext AFTER `consecutive_failures`;",
    ];

    foreach ($connectorColumns as $field => $sql) {
        if (!$CI->db->field_exists($field, 'tbl_deals_connectors')) {
            $CI->db->query($sql);
        }
    }
}

if ($CI->db->table_exists('tbl_deals_connector_logs')) {
    $logColumns = [
        'request_headers_json' => "ALTER TABLE `tbl_deals_connector_logs` ADD `request_headers_json` longtext AFTER `request_payload`;",
        'attempt_count' => "ALTER TABLE `tbl_deals_connector_logs` ADD `attempt_count` int NOT NULL DEFAULT '1' AFTER `processing_ms`;",
    ];

    foreach ($logColumns as $field => $sql) {
        if (!$CI->db->field_exists($field, 'tbl_deals_connector_logs')) {
            $CI->db->query($sql);
        }
    }
}

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_integrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `provider_key` varchar(64) NOT NULL,
  `name` varchar(191) NOT NULL,
  `driver_class` varchar(191) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `supports_oauth` tinyint(1) NOT NULL DEFAULT '0',
  `supports_webhooks` tinyint(1) NOT NULL DEFAULT '0',
  `supports_sync` tinyint(1) NOT NULL DEFAULT '0',
  `config_json` longtext,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_key` (`provider_key`)
) ENGINE=InnoDB;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_integration_accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integration_id` int DEFAULT NULL,
  `provider_key` varchar(64) NOT NULL,
  `staff_id` int DEFAULT NULL,
  `account_label` varchar(191) NOT NULL,
  `external_account_id` varchar(191) DEFAULT NULL,
  `connection_status` varchar(30) NOT NULL DEFAULT 'disconnected',
  `scopes_json` longtext,
  `access_token_encrypted` longtext,
  `refresh_token_encrypted` longtext,
  `token_expires_at` datetime DEFAULT NULL,
  `webhook_secret` varchar(64) DEFAULT NULL,
  `oauth_state` varchar(64) DEFAULT NULL,
  `account_meta_json` longtext,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `last_synced_at` datetime DEFAULT NULL,
  `last_webhook_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `provider_key` (`provider_key`),
  KEY `connection_status` (`connection_status`)
) ENGINE=InnoDB;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_integration_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_uuid` varchar(64) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `integration_id` int DEFAULT NULL,
  `account_id` int DEFAULT NULL,
  `deal_id` int DEFAULT NULL,
  `source` varchar(80) NOT NULL DEFAULT 'deals',
  `direction` varchar(20) NOT NULL DEFAULT 'internal',
  `payload_json` longtext,
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `attempts` int NOT NULL DEFAULT '0',
  `available_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `queue_name` varchar(50) NOT NULL DEFAULT 'default',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_uuid` (`event_uuid`),
  KEY `status` (`status`),
  KEY `queue_name` (`queue_name`),
  KEY `event_name` (`event_name`)
) ENGINE=InnoDB;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_integration_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integration_id` int DEFAULT NULL,
  `account_id` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `level` varchar(20) NOT NULL DEFAULT 'info',
  `action` varchar(100) NOT NULL DEFAULT 'system',
  `message` text NOT NULL,
  `context_json` longtext,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `action` (`action`)
) ENGINE=InnoDB;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_sync_states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `provider_key` varchar(64) NOT NULL,
  `account_id` int NOT NULL,
  `resource_type` varchar(80) NOT NULL,
  `cursor_token` varchar(191) DEFAULT NULL,
  `external_channel_id` varchar(191) DEFAULT NULL,
  `external_resource_id` varchar(191) DEFAULT NULL,
  `sync_status` varchar(30) NOT NULL DEFAULT 'idle',
  `last_synced_at` datetime DEFAULT NULL,
  `next_sync_at` datetime DEFAULT NULL,
  `last_webhook_at` datetime DEFAULT NULL,
  `state_json` longtext,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_resource` (`account_id`,`resource_type`),
  KEY `provider_key` (`provider_key`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB;");
$deal_send_email = [
    'type' => 'deal',
    'slug' => 'deal_send_email',
    'name' => 'Deal Send Email',
    'subject' => '{subject}',
    'message' => '{message}'
];
create_email_template($deal_send_email['subject'], $deal_send_email['message'], $deal_send_email['type'], $deal_send_email['name'], $deal_send_email['slug']);
