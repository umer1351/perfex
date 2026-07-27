<?php
defined('BASEPATH') or exit('No direct script access allowed');

add_option("warranty_management_display_on_portal", 1, 1);
add_option("wm_warranty_receipt_process_prefix", '#RP_', 1);
add_option("wm_warranty_receipt_process_number", 1, 1);
add_option("wm_warranty_claim_prefix", '#WCLAIM_', 1);
add_option("wm_warranty_claim_number", 1, 1);


if (!$CI->db->table_exists(db_prefix() . "wm_timeline_logs")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wm_timeline_logs` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`rel_id` int NULL ,
		`rel_type` varchar(100) NULL ,
		`description` mediumtext NULL,
		`additional_data` text NULL,
		`date` datetime NULL,
		`staffid` int(11) NULL,
		`full_name` varchar(100) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wm_warranty_receipt_process")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wm_warranty_receipt_process` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

		`code` TEXT NULL,
		`name` TEXT NULL,
		`product_group_id` INT(11) NULL,
		`description` TEXT DEFAULT NULL,
		`mark_default` INT(11) NULL DEFAULT '0' COMMENT '0: default',
		`datecreated` DATETIME NULL,
		`staffid` int(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wm_warranty_receipt_process_details")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wm_warranty_receipt_process_details` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`warranty_receipt_id` int(11) NOT NULL ,
		`process_name` TEXT NULL ,
		`order_number` DECIMAL(15,0) DEFAULT '0',
		`person_in_charge` TEXT NULL,
		`estimate_time` DECIMAL(15,2) DEFAULT '0',
		`description` TEXT DEFAULT NULL,
		`step_icon` TEXT DEFAULT NULL,
		`datecreated` DATETIME NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wm_warranty_claim_informations")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wm_warranty_claim_informations` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`claim_code` TEXT NULL,
		`created_id` int(11) NULL,
		`created_type` VARCHAR(20) NULL DEFAULT 'staff',
		`client_id` INT(11) NULL,
		`client_note` TEXT NULL,
		`admin_note` TEXT NULL,
		`description` TEXT NULL,
		`status` TEXT NULL,
		`datecreated` datetime NULL,
		`invoice_id` TEXT NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wm_warranty_claim_information_details")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wm_warranty_claim_information_details` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`warranty_claim` INT(11) NULL,
		`warranty_receipt_process_id` INT(11) NULL,
		`item_id` INT(11) NULL,
		`items_name` TEXT NULL,
		`rel_id` INT(11) NULL,
		`rel_type` VARCHAR(100) NULL,
		`invoice_id` INT(11) NULL,
		`order_id` INT(11) NULL,
		`start_date` DATETIME NULL,
		`expiration_date` DATETIME NULL,
		`datecreated` DATETIME NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wm_warranty_process_details")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wm_warranty_process_details` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`warranty_claim` INT(11) NULL,
		`warranty_claim_detail_id` INT(11) NULL,

		`process_name` TEXT NULL ,
		`order_number` DECIMAL(15,0) DEFAULT '0',
		`person_in_charge` TEXT NULL,
		`estimate_time` DECIMAL(15,2) DEFAULT '0',
		`actual_time` DECIMAL(15,2) DEFAULT '0',
		`description` TEXT DEFAULT NULL,
		`step_icon` TEXT DEFAULT NULL,
		`datecreated` DATETIME NULL,
		`status` TEXT NULL,


		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wm_warranty_process_time_trackings")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wm_warranty_process_time_trackings` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`warranty_process_detail_id` int(11) NOT NULL ,

		`from_date` DATETIME NULL ,
		`to_date` DATETIME NULL ,
		`duration` DECIMAL(15,2) DEFAULT '0',
		`staff_id` INT(11) NULL,
		`description` TEXT DEFAULT NULL,
		`datecreated` DATETIME NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->field_exists("warranty_receipt_process_id" ,db_prefix() . "wm_warranty_claim_information_details")) { 
	$CI->db->query("ALTER TABLE `" . db_prefix() . "wm_warranty_claim_information_details`
		ADD COLUMN `warranty_receipt_process_id` INT(11) NULL
		;");
}

if (!$CI->db->field_exists("warranty_claim_detail_id" ,db_prefix() . "wm_warranty_process_details")) { 
	$CI->db->query("ALTER TABLE `" . db_prefix() . "wm_warranty_process_details`
		ADD COLUMN `warranty_claim_detail_id` INT(11) NULL
		;");
}

if (!$CI->db->field_exists("status" ,db_prefix() . "wm_warranty_process_details")) { 
	$CI->db->query("ALTER TABLE `" . db_prefix() . "wm_warranty_process_details`
		ADD COLUMN `status` TEXT NULL
		;");
}

if (!$CI->db->field_exists("warranty_receipt_process_detail_id" ,db_prefix() . "wm_warranty_process_details")) { 
	$CI->db->query("ALTER TABLE `" . db_prefix() . "wm_warranty_process_details`
		ADD COLUMN `warranty_receipt_process_detail_id` INT(11) NULL
		;");
}

