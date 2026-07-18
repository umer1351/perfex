<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'property')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'property` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `address` text DEFAULT NULL,
            `price` decimal(15,2) DEFAULT NULL,
            `description` text DEFAULT NULL,
            `status` varchar(50) DEFAULT "available",
            `created_by` int(11) DEFAULT NULL,
            `active` tinyint(1) DEFAULT 1,
            `is_deleted` tinyint(1) DEFAULT 0,
            `created_at` datetime DEFAULT current_timestamp(),
            `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
}
