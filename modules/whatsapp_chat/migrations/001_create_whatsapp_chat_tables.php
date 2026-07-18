<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * 001_create_whatsapp_chat_tables.php
 *
 * Alternative migration-style install script that can be run manually
 * or referenced for SQL documentation.
 *
 * NOTE: The canonical install path is install.php (run on activation).
 * Use this file if you ever need to re-run in isolation.
 */

$CI = &get_instance();

// ------------------------------------------------------------------
// Table 1: tblwhatsapp_conversations
// ------------------------------------------------------------------
if (!$CI->db->table_exists(db_prefix() . 'whatsapp_conversations')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . 'whatsapp_conversations` (
      `id`               INT(11)      NOT NULL AUTO_INCREMENT,
      `phone`            VARCHAR(30)  NOT NULL,
      `display_name`     VARCHAR(191) NOT NULL DEFAULT \'\',
      `customer_id`      INT(11)      DEFAULT NULL COMMENT \'Linked Perfex customer\',
      `lead_id`          INT(11)      DEFAULT NULL COMMENT \'Linked Perfex lead\',
      `last_message_at`  DATETIME     DEFAULT NULL,
      `last_message_text` TEXT        DEFAULT NULL,
      `last_direction`   ENUM(\'inbound\',\'outbound\') DEFAULT \'inbound\',
      `unread_count`     INT(11)      NOT NULL DEFAULT 0,
      `status`           ENUM(\'open\',\'closed\') NOT NULL DEFAULT \'open\',
      `channel`          VARCHAR(50)  NOT NULL DEFAULT \'whatsapp\',
      `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uq_phone` (`phone`),
      KEY `idx_customer_id` (`customer_id`),
      KEY `idx_lead_id` (`lead_id`),
      KEY `idx_last_message_at` (`last_message_at`),
      KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COMMENT=\'WhatsApp Chat Module: one row per phone thread\';');

    log_message('info', '[whatsapp_chat] Created table: ' . db_prefix() . 'whatsapp_conversations');
}

// ------------------------------------------------------------------
// Table 2: tblwhatsapp_messages
// ------------------------------------------------------------------
if (!$CI->db->table_exists(db_prefix() . 'whatsapp_messages')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . 'whatsapp_messages` (
      `id`               INT(11)        NOT NULL AUTO_INCREMENT,
      `conversation_id`  INT(11)        NOT NULL,
      `message_uid`      VARCHAR(191)   NOT NULL COMMENT \'Provider-unique message ID\',
      `phone`            VARCHAR(30)    NOT NULL,
      `direction`        ENUM(\'inbound\',\'outbound\') NOT NULL DEFAULT \'inbound\',
      `sender_type`      ENUM(\'customer\',\'bot\',\'agent\') NOT NULL DEFAULT \'customer\',
      `sender_name`      VARCHAR(191)   NOT NULL DEFAULT \'\',
      `message_type`     VARCHAR(30)    NOT NULL DEFAULT \'text\',
      `message_text`     TEXT           DEFAULT NULL,
      `media_url`        VARCHAR(500)   DEFAULT NULL,
      `status`           VARCHAR(30)    NOT NULL DEFAULT \'received\',
      `payload_json`     LONGTEXT       DEFAULT NULL COMMENT \'Raw provider payload for debugging\',
      `sent_at`          DATETIME       DEFAULT NULL,
      `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `is_read`          TINYINT(1)     NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uq_message_uid` (`message_uid`),
      KEY `idx_conversation_id` (`conversation_id`),
      KEY `idx_phone` (`phone`),
      KEY `idx_direction` (`direction`),
      KEY `idx_sent_at` (`sent_at`),
      KEY `idx_is_read` (`is_read`),
      CONSTRAINT `fk_wac_messages_conv` FOREIGN KEY (`conversation_id`)
        REFERENCES `' . db_prefix() . 'whatsapp_conversations` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ' COMMENT=\'WhatsApp Chat Module: one row per message\';');

    log_message('info', '[whatsapp_chat] Created table: ' . db_prefix() . 'whatsapp_messages');
}
