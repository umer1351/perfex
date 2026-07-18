<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WhatsApp Chat
Description: View WhatsApp conversations and bot replies inside Perfect ERP admin. Integrates with n8n for real-time message sync.
Version: 1.0.3
Requires at least: 2.3.*
Author: Format Design
*/

define('WHATSAPP_CHAT_MODULE_NAME', 'whatsapp_chat');
define('WHATSAPP_CHAT_MODULE_VERSION', '1.0.3');

$CI = &get_instance();

/*
|--------------------------------------------------------------------------
| CSRF Exclusion for External API Endpoints
|--------------------------------------------------------------------------
| This MUST be registered early (before CSRF verification runs).
| Allows n8n and other external services to POST to /whatsapp_chat/api/*
| without requiring a CSRF token. Bearer token auth is used instead.
*/
hooks()->add_filter('csrf_exclude_uris', 'whatsapp_chat_csrf_exclude_uris');

function whatsapp_chat_csrf_exclude_uris($exclude_uris)
{
    // Exclude all whatsapp_chat/api/* routes from CSRF protection
    $exclude_uris[] = 'whatsapp_chat/api/.*';
    $exclude_uris[] = 'whatsapp_chat/api/message';
    $exclude_uris[] = 'whatsapp_chat/api/status';
    return $exclude_uris;
}

/*
|--------------------------------------------------------------------------
| Load the module helper GLOBALLY
|--------------------------------------------------------------------------
| This is the correct Perfex/CI pattern: module_name/helper_name
| Loads: modules/whatsapp_chat/helpers/whatsapp_chat_helper.php
*/
$CI->load->helper(WHATSAPP_CHAT_MODULE_NAME . '/whatsapp_chat');

/*
|--------------------------------------------------------------------------
| Register language files
|--------------------------------------------------------------------------
| Required for _l() translation strings to work
*/
register_language_files(WHATSAPP_CHAT_MODULE_NAME, [WHATSAPP_CHAT_MODULE_NAME]);

/*
|--------------------------------------------------------------------------
| Register activation hook
|--------------------------------------------------------------------------
| Runs install.php when the module is activated from the admin panel.
*/
register_activation_hook(WHATSAPP_CHAT_MODULE_NAME, 'whatsapp_chat_module_activation_hook');

function whatsapp_chat_module_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/*
|--------------------------------------------------------------------------
| Register deactivation hook
|--------------------------------------------------------------------------
*/
register_deactivation_hook(WHATSAPP_CHAT_MODULE_NAME, 'whatsapp_chat_module_deactivation_hook');

function whatsapp_chat_module_deactivation_hook()
{
    // Nothing to clean up on deactivate; tables are preserved.
}

/*
|--------------------------------------------------------------------------
| Register uninstall hook
|--------------------------------------------------------------------------
| Runs uninstall.php when the module is UNINSTALLED (not just deactivated).
*/
register_uninstall_hook(WHATSAPP_CHAT_MODULE_NAME, 'whatsapp_chat_module_uninstall_hook');

function whatsapp_chat_module_uninstall_hook()
{
    require_once(__DIR__ . '/uninstall.php');
}

/*
|--------------------------------------------------------------------------
| Admin init hooks
|--------------------------------------------------------------------------
*/
hooks()->add_action('admin_init', 'whatsapp_chat_module_init_menu_items');
hooks()->add_action('admin_init', 'whatsapp_chat_permissions');

/*
|--------------------------------------------------------------------------
| Add sidebar menu item
|--------------------------------------------------------------------------
*/
function whatsapp_chat_module_init_menu_items()
{
    $CI = &get_instance();

    if (staff_can('view', 'whatsapp_chat')) {
        $CI->app_menu->add_sidebar_menu_item('whatsapp-chat', [
            'name'     => _l('whatsapp_messages'),
            'href'     => admin_url('whatsapp_chat'),
            'icon'     => 'fa fa-whatsapp',
            'position' => 11,
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Register staff permissions
|--------------------------------------------------------------------------
*/
function whatsapp_chat_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
    ];

    register_staff_capabilities('whatsapp_chat', $capabilities, _l('whatsapp_messages'));
}
