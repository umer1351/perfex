<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

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
    }
}
