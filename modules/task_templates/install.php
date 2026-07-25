<?php
defined('BASEPATH') or exit('No direct script access allowed');

task_templates_db_up();

function task_templates_db_up(){
    $CI       = & get_instance();

    if (!$CI->db->table_exists(TASK_TEMPLATES_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' . TASK_TEMPLATES_TABLE_NAME.  "` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` mediumtext DEFAULT NULL,
  `description` text DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `dateadded` datetime NOT NULL,
  `added_by` int(11) NOT NULL,
  `checklist_items` varchar(255) NULL,
  `start_type` varchar(255) NULL,
  `startdate` int(11) NULL,
  `startdate_type` varchar(30) DEFAULT NULL,
  `duration_value` int(11) NULL,
  `duration_type` varchar(30) DEFAULT NULL,
  `after_task_id` int(11) NULL,
  `repeat_every` varchar(55) DEFAULT NULL,
  `repeat_every_custom` varchar(55) DEFAULT NULL,
  `repeat_type_custom` varchar(55) DEFAULT NULL,
  `cycles` int(11) NOT NULL DEFAULT 0,
  `rel_id` int(11) DEFAULT NULL,
  `rel_type` varchar(55) DEFAULT NULL,
  `milestone` int(11) NULL DEFAULT 0,
  `kanban_order` int(11) NULL DEFAULT 1,
  `milestone_order` int(11) NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `billable` tinyint(1) NOT NULL DEFAULT 0,
  `hourly_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(TASK_TEMPLATES_QUE_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' . TASK_TEMPLATES_QUE_TABLE_NAME.  "` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_template_id` int(11) NULL,
  `real_task_id` int(11) NULL,
  `next_task_template_id` int(11) NULL,
  `dateadded` datetime NOT NULL,
  `added_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(TASK_TEMPLATES_ASSIGNEES_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' . TASK_TEMPLATES_ASSIGNEES_TABLE_NAME.  "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staffid` int(11) NOT NULL,
  `taskid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(TASK_TEMPLATES_FOLLOWERS_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' . TASK_TEMPLATES_FOLLOWERS_TABLE_NAME.  "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staffid` int(11) NOT NULL,
  `taskid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(TASK_TEMPLATES_CUSTOM_FIELD_VALUES)) {
        $CI->db->query('CREATE TABLE `' .TASK_TEMPLATES_CUSTOM_FIELD_VALUES. '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `relid` int(11) NOT NULL,
  `fieldid` int(11) NOT NULL,
  `fieldto` varchar(15) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
}