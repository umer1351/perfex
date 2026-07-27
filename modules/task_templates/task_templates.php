<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Task Templates
Description: Create tasks directly from templates
Version: 1.0.1
Requires at least: 2.3.*
*/

define('TASK_TEMPLATES_MODULE_VERSION', '1.0.1');
define('TASK_TEMPLATES_MODULE_NAME', 'task_templates');
/* TABLES */
define('TASK_TEMPLATES_TABLE_NAME', db_prefix().'task_templates');
define('TASK_TEMPLATES_QUE_TABLE_NAME', db_prefix().'task_templates_que');
define('TASK_TEMPLATES_ASSIGNEES_TABLE_NAME', db_prefix().'task_templates_assignees');
define('TASK_TEMPLATES_FOLLOWERS_TABLE_NAME', db_prefix().'task_templates_followers');
define('TASK_TEMPLATES_CUSTOM_FIELD_VALUES', db_prefix().'task_templates_custom_fields_values');
/**
 * Tasks attachments
 */
define('TASK_TEMPLATES_ATTACHMENTS_FOLDER', FCPATH . DIRECTORY_SEPARATOR . 'modules/task_templates/uploads/task_templates' . '/');

$CI = &get_instance();
/**
 * Load the module helper
 */
$CI->load->helper(TASK_TEMPLATES_MODULE_NAME . '/task_templates');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(TASK_TEMPLATES_MODULE_NAME, [TASK_TEMPLATES_MODULE_NAME]);

// Adding setup menu item for module
hooks()->add_action('admin_init', 'add_setup_menu_task_templates_link');
// Adding permission for module
hooks()->add_action('staff_permissions', 'task_templates_staff_permissions', 10, 2);

/**
 * Register activation module hook
 */
register_activation_hook(TASK_TEMPLATES_MODULE_NAME, 'task_templates_activation_hook');

function task_templates_activation_hook(){
    require_once(__DIR__ . '/install.php');
}

/**
 * Register deactivation module hook
 */
register_deactivation_hook(TASK_TEMPLATES_MODULE_NAME, 'task_templates_de_activation_hook');

function task_templates_de_activation_hook(){
    require_once(__DIR__ . '/deactivate.php');
}

/**
 * Register uninstall module hook
 */
register_uninstall_hook(TASK_TEMPLATES_MODULE_NAME, 'task_templates_uninstall_hook');

function task_templates_uninstall_hook(){
    require_once(__DIR__ . '/uninstall.php');
}


// ADDING SCRIPT FILES
hooks()->add_action('before_compile_scripts_assets', 'add_task_templates_global_scripts');


hooks()->add_action('task_status_changed', 'tt_task_status_changed');
