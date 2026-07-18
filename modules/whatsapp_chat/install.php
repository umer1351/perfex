<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * install.php - Creates the two custom tables on module activation.
 * Called from whatsapp_chat.php activation hook.
 */

$CI = &get_instance();

/*
|--------------------------------------------------------------------------
| Table 1: tblwhatsapp_conversations
|--------------------------------------------------------------------------
| One row per phone number / chat thread.
| - phone: raw phone as received from WhatsApp
| - phone_normalized: canonical E.164 digits for matching
*/
if (!$CI->db->table_exists(db_prefix() . 'whatsapp_conversations')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . 'whatsapp_conversations` (
      `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `phone`             VARCHAR(50)      NOT NULL COMMENT "Raw phone as received",
      `phone_normalized`  VARCHAR(30)      NOT NULL COMMENT "E.164 digits only, no +",
      `display_name`      VARCHAR(191)     NOT NULL DEFAULT "",
      `customer_id`       INT(11) UNSIGNED DEFAULT NULL COMMENT "Linked Perfex customer",
      `lead_id`           INT(11) UNSIGNED DEFAULT NULL COMMENT "Linked Perfex lead",
      `last_message_at`   DATETIME         DEFAULT NULL,
      `last_message_text` TEXT             DEFAULT NULL,
      `last_direction`    ENUM("inbound","outbound") DEFAULT "inbound",
      `unread_count`      INT(11) UNSIGNED NOT NULL DEFAULT 0,
      `status`            ENUM("open","closed") NOT NULL DEFAULT "open",
      `channel`           VARCHAR(50)      NOT NULL DEFAULT "whatsapp",
      `created_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uq_phone_normalized` (`phone_normalized`),
      KEY `idx_last_message_at` (`last_message_at`),
      KEY `idx_customer_id` (`customer_id`),
      KEY `idx_lead_id` (`lead_id`),
      KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COMMENT="WhatsApp Chat: one row per phone thread";');

    log_message('info', '[whatsapp_chat] Created table: ' . db_prefix() . 'whatsapp_conversations');
}

/*
|--------------------------------------------------------------------------
| Table 2: tblwhatsapp_messages
|--------------------------------------------------------------------------
| One row per individual message.
*/
if (!$CI->db->table_exists(db_prefix() . 'whatsapp_messages')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . 'whatsapp_messages` (
      `id`               INT(11) UNSIGNED   NOT NULL AUTO_INCREMENT,
      `conversation_id`  INT(11) UNSIGNED   NOT NULL,
      `message_uid`      VARCHAR(191)       NOT NULL COMMENT "Provider-unique message ID",
      `phone`            VARCHAR(50)        NOT NULL COMMENT "Raw phone",
      `phone_normalized` VARCHAR(30)        NOT NULL COMMENT "E.164 digits only",
      `direction`        ENUM("inbound","outbound") NOT NULL DEFAULT "inbound",
      `sender_type`      ENUM("customer","bot","agent") NOT NULL DEFAULT "customer",
      `sender_name`      VARCHAR(191)       NOT NULL DEFAULT "",
      `message_type`     VARCHAR(30)        NOT NULL DEFAULT "text",
      `message_text`     TEXT               DEFAULT NULL,
      `media_url`        VARCHAR(500)       DEFAULT NULL,
      `status`           VARCHAR(30)        NOT NULL DEFAULT "received",
      `payload_json`     LONGTEXT           DEFAULT NULL COMMENT "Raw provider payload",
      `sent_at`          DATETIME           DEFAULT NULL,
      `created_at`       DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `is_read`          TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uq_message_uid` (`message_uid`),
      KEY `idx_conversation_id` (`conversation_id`),
      KEY `idx_phone_normalized` (`phone_normalized`),
      KEY `idx_sent_at` (`sent_at`),
      KEY `idx_created_at` (`created_at`),
      KEY `idx_is_read` (`is_read`),
      CONSTRAINT `fk_wac_msg_conv` FOREIGN KEY (`conversation_id`)
        REFERENCES `' . db_prefix() . 'whatsapp_conversations` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COMMENT="WhatsApp Chat: one row per message";');

    log_message('info', '[whatsapp_chat] Created table: ' . db_prefix() . 'whatsapp_messages');
}

/*
|--------------------------------------------------------------------------
| Migration: Add phone_normalized if upgrading from older version
|--------------------------------------------------------------------------
*/
if ($CI->db->table_exists(db_prefix() . 'whatsapp_conversations')) {
    if (!$CI->db->field_exists('phone_normalized', db_prefix() . 'whatsapp_conversations')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'whatsapp_conversations`
            ADD COLUMN `phone_normalized` VARCHAR(30) NOT NULL DEFAULT "" AFTER `phone`,
            ADD UNIQUE KEY `uq_phone_normalized` (`phone_normalized`)');

        // Populate phone_normalized from existing phone data
        $CI->db->query('UPDATE `' . db_prefix() . 'whatsapp_conversations`
            SET `phone_normalized` = REGEXP_REPLACE(`phone`, "[^0-9]", "")
            WHERE `phone_normalized` = ""');

        log_message('info', '[whatsapp_chat] Added phone_normalized column to conversations');
    }
}

if ($CI->db->table_exists(db_prefix() . 'whatsapp_messages')) {
    if (!$CI->db->field_exists('phone_normalized', db_prefix() . 'whatsapp_messages')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'whatsapp_messages`
            ADD COLUMN `phone_normalized` VARCHAR(30) NOT NULL DEFAULT "" AFTER `phone`');

        // Populate phone_normalized from existing phone data
        $CI->db->query('UPDATE `' . db_prefix() . 'whatsapp_messages`
            SET `phone_normalized` = REGEXP_REPLACE(`phone`, "[^0-9]", "")
            WHERE `phone_normalized` = ""');

        // Add index
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'whatsapp_messages`
            ADD KEY `idx_phone_normalized` (`phone_normalized`)');

        log_message('info', '[whatsapp_chat] Added phone_normalized column to messages');
    }
}

/*
|--------------------------------------------------------------------------
| Set default API token if not exists
|--------------------------------------------------------------------------
*/
if (get_option('whatsapp_chat_api_token') === false || get_option('whatsapp_chat_api_token') === '') {
    // Generate a secure random token
    $default_token = bin2hex(random_bytes(32));
    add_option('whatsapp_chat_api_token', $default_token);
    log_message('info', '[whatsapp_chat] Generated default API token');
}
