<?php
defined('BASEPATH') or exit('No direct script access allowed');

task_templates_db_migration_down();

function task_templates_db_migration_down(){
    $CI       = & get_instance();

    if ($CI->db->table_exists(db_prefix().'task_templates')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'task_templates`;');
    }
    if ($CI->db->table_exists(db_prefix().'task_templates_assignees')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'task_templates_assignees`;');
    }
    if ($CI->db->table_exists(db_prefix().'task_templates_followers')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'task_templates_followers`;');
    }
    if ($CI->db->table_exists(db_prefix().'task_templates_custom_fields_values')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'task_templates_custom_fields_values`;');
    }

}