<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Customers, Staff & Projects Asset Management
Description: Transform asset management within Perfex CRM using our Advanced Asset Manager module. Seamlessly oversee and track assets across customers, projects, and staff. Streamline asset assignments, monitor statuses, and optimize resource utilization. Elevate organizational efficiency with intuitive asset management capabilities.
Version: 1.0.0
Author: LenzCreative
Author URI: https://codecanyon.net/user/lenzcreativee/portfolio
Requires at least: 1.0.*
*/

define('ASSETCENTRAL_MODULE_NAME', 'assetcentral');
define('ASSETCENTRA_REVISION', 100);

hooks()->add_action('admin_init', 'assetcentral_module_init_menu_items');
hooks()->add_action('admin_init', 'assetcentral_permissions');
hooks()->add_action('clients_init', 'assetcentral_module_clients_init_menu_items');
hooks()->add_action('app_admin_footer', 'assetcentral_load_js');
hooks()->add_action('assetcentral_init', ASSETCENTRAL_MODULE_NAME . '_appint');
hooks()->add_action('pre_activate_module', ASSETCENTRAL_MODULE_NAME . '_preactivate');
hooks()->add_action('pre_deactivate_module', ASSETCENTRAL_MODULE_NAME . '_predeactivate');
hooks()->add_action('pre_uninstall_module', ASSETCENTRAL_MODULE_NAME . '_uninstall');

include(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/libraries/Import_assets.php');

/**
 * Load the module helper
 */
$CI = &get_instance();
$CI->load->helper(ASSETCENTRAL_MODULE_NAME . '/assetcentral');

function assetcentral_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_own' => _l('permission_view_own'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete')
    ];
    register_staff_capabilities('assetcentral', $capabilities, _l('assetcentral'));

//    $capabilities = [];
//
//    $capabilities['capabilities'] = [
//        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
//        'view_own' => _l('permission_view_own'),
//        'create' => _l('permission_create'),
//        'edit' => _l('permission_edit'),
//        'delete' => _l('permission_delete')
//    ];
//    register_staff_capabilities('assetcentral_requests', $capabilities, _l('assetcentral_requests_menu'));

    $capabilities['capabilities'] = [
        'view' => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete')
    ];
    register_staff_capabilities('assetcentral_appreciations', $capabilities, _l('assetcentral_appreciations_menu'));

    $capabilities['capabilities'] = [
        'view' => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete')
    ];
    register_staff_capabilities('assetcentral_depreciation', $capabilities, _l('assetcentral_depreciation_menu'));

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')'
    ];
    register_staff_capabilities('assetcentral_reports', $capabilities, _l('assetcentral_reports_menu'));

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_own' => _l('permission_view_own'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete')
    ];
    register_staff_capabilities('assetcentral_audits', $capabilities, _l('assetcentral_audits'));
}

/**
 * Register activation module hook
 */
register_activation_hook(ASSETCENTRAL_MODULE_NAME, 'assetcentral_module_activation_hook');

function assetcentral_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(ASSETCENTRAL_MODULE_NAME, [ASSETCENTRAL_MODULE_NAME]);

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
function assetcentral_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('assetcentral', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('assetcentral', [
            'slug' => 'assetcentral',
            'name' => _l('assetcentral_menu'),
            'position' => 6,
            'icon' => 'fas fa-cube'
        ]);
    }

    if (has_permission('assetcentral', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('assetcentral', [
            'slug' => 'assetcentral-manage-assets',
            'name' => _l('assetcentral'),
            'position' => 6,
            'icon' => 'fas fa-cube',
            'href' => admin_url('assetcentral/manage_assets')
        ]);
    }

    if (has_permission('assetcentral', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('assetcentral', [
            'slug' => 'assetcentral-manage-my-assets',
            'name' => _l('assetcentral_my_assets_menu'),
            'position' => 6,
            'icon' => 'fas fa-briefcase',
            'href' => admin_url('assetcentral/manage_my_assets')
        ]);
    }

//    if (has_permission('assetcentral_requests', '', 'view') || has_permission('assetcentral_requests', '', 'view_own')) {
//        $CI->app_menu->add_sidebar_children_item('assetcentral', [
//            'slug' => 'assetcentral-requests',
//            'name' => _l('assetcentral_requests_menu'),
//            'position' => 6,
//            'icon' => 'fas fa-bell',
//            'href' => admin_url('assetcentral/manage_requests')
//        ]);
//    }

    if (has_permission('assetcentral_audits', '', 'view') || has_permission('assetcentral_audits', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('assetcentral', [
            'slug' => 'assetcentral-audits',
            'name' => _l('assetcentral_audit_menu'),
            'position' => 6,
            'icon' => 'fas fa-search',
            'href' => admin_url('assetcentral/manage_audits')
        ]);
    }

    if (has_permission('assetcentral_appreciations', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('assetcentral', [
            'slug' => 'assetcentral-manage-appreciation',
            'name' => _l('assetcentral_appreciations_menu'),
            'position' => 6,
            'icon' => 'fas fa-long-arrow-alt-up',
            'href' => admin_url('assetcentral/manage_appreciation')
        ]);
    }

    if (has_permission('assetcentral_depreciation', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('assetcentral', [
            'slug' => 'assetcentral-manage-depreciation',
            'name' => _l('assetcentral_depreciation_menu'),
            'position' => 6,
            'icon' => 'fas fa-long-arrow-alt-down',
            'href' => admin_url('assetcentral/manage_depreciation')
        ]);
    }

    if (has_permission('assetcentral_reports', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('assetcentral', [
            'slug' => 'assetcentral-reports',
            'name' => _l('assetcentral_reports_menu'),
            'position' => 6,
            'icon' => 'fas fa-chart-line',
            'href' => admin_url('assetcentral/reports')
        ]);
    }

    $CI->app_menu->add_sidebar_children_item('assetcentral', [
        'slug' => 'assetcentral-qr-scanner',
        'name' => _l('assetcentral_qr_scanner'),
        'position' => 6,
        'icon' => 'fas fa-qrcode',
        'href' => admin_url('assetcentral/qr_scanner')
    ]);

    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('assetcentral', [
            'slug' => 'assetcentral-settings',
            'name' => _l('assetcentral_settings_menu'),
            'position' => 6,
            'icon' => 'fas fa-cog',
            'href' => admin_url('assetcentral/settings')
        ]);
    }
}

function assetcentral_module_clients_init_menu_items()
{

    $CI = &get_instance();

    $accessMenu = false;

    if (get_option('assetcentral_show_assets_on_clients_menu') == '1' && has_contact_permission('assets') && is_client_logged_in()) {
        $accessMenu = true;
    }

    if ($accessMenu) {
        $CI->app_menu->add_theme_item('assetcentral', [
            'name' => _l('assetcentral'),
            'href' => site_url('assetcentral/client_assets/assets'),
            'position' => 10,
        ]);
    }

}

hooks()->add_filter('get_contact_permissions', 'assetcentral_add_contact_permission');
function assetcentral_add_contact_permission($permissions)
{
    $new_permission = [
        'id' => 4321,
        'name' => _l('assetcentral'),
        'short_name' => 'assets',
    ];

    $permissions[] = $new_permission;
    return $permissions;
}

hooks()->add_action('after_custom_fields_select_options', 'assetcentral_new_custom_field_type');
function assetcentral_new_custom_field_type($custom_field)
{
    ?>
    <option value="assetcentral_as" <?php if (isset($custom_field) && $custom_field->fieldto == 'assetcentral_assets') {
        echo 'selected';
    } ?>><?php echo _l('assetcentral_asset_custom_fields'); ?></option>
    <?php
}

function assetcentral_load_js()
{

    $viewuri = $_SERVER['REQUEST_URI'];

    if (!(strpos($viewuri, 'assetcentral/settings?group=asset_categories') === false) || !(strpos($viewuri, 'assetcentral/settings') === false)) {
        echo '<script src="' . module_dir_url(ASSETCENTRAL_MODULE_NAME, 'assets/js/settings/asset_category.js') . '?v=' . ASSETCENTRA_REVISION . '"></script>';
    }

    if (!(strpos($viewuri, 'assetcentral/settings?group=asset_locations') === false)) {
        echo '<script src="' . module_dir_url(ASSETCENTRAL_MODULE_NAME, 'assets/js/settings/asset_locations.js') . '?v=' . ASSETCENTRA_REVISION . '"></script>';
    }

    if (!(strpos($viewuri, 'assetcentral/settings?group=asset_request_types') === false)) {
        echo '<script src="' . module_dir_url(ASSETCENTRAL_MODULE_NAME, 'assets/js/settings/asset_request_types.js') . '?v=' . ASSETCENTRA_REVISION . '"></script>';
    }

    if (!(strpos($viewuri, 'admin/projects/view') === false)) {
        echo '<script>
                document.querySelector("a[data-group=\'project_assets\']").addEventListener("click", function(event) {
                    event.preventDefault();
                    
                    var currentUrl = window.location.href;
                    var regex = /projects\/view\/(\d+)/;
                    var match = currentUrl.match(regex);
                    
                    if (match) {
                        var projectId = match[1];
                        var url = admin_url + "assetcentral/manage_project_assets/" + projectId;
                    window.location.href = url;
                    } else {
                        console.error("Project ID not found in the URL");
                    }
                });
            </script>';

    }

}

hooks()->add_action('admin_init', 'add_assets_tab_in_projects');
function add_assets_tab_in_projects()
{
    $CI = &get_instance();

    $CI->app_tabs->add_project_tab('project_assets', [
        'name' => _l('assetcentral_project_assets'),
        'icon' => 'fas fa-cube',
        'view' => '',
        'position' => 60,
    ]);
}

function assetcentral_appint()
{
    $CI = &get_instance();
    // Comentamos a lógica de verificação da licença
    // require_once 'libraries/leclib.php';
    // $module_api = new AssetcentralLic();
    // $module_leclib = $module_api->verify_license(true);
    // if (!$module_leclib || ($module_leclib && isset($module_leclib['status']) && !$module_leclib['status'])) {
    //     $CI->app_modules->deactivate(ASSETCENTRAL_MODULE_NAME);
    //     set_alert('danger', "One of your modules failed its verification and got deactivated. Please reactivate or contact support.");
    //     redirect(admin_url('modules'));
    // }
}

function assetcentral_preactivate($module_name)
{
    if ($module_name['system_name'] == ASSETCENTRAL_MODULE_NAME) {
        // Comentamos a lógica de verificação da licença
        // require_once 'libraries/leclib.php';
        // $module_api = new AssetcentralLic();
        // $module_leclib = $module_api->verify_license();
        // if (!$module_leclib || ($module_leclib && isset($module_leclib['status']) && !$module_leclib['status'])) {
        //     $CI = &get_instance();
        //     $data['submit_url'] = $module_name['system_name'] . '/lecverify/activate';
        //     $data['original_url'] = admin_url('modules/activate/' . ASSETCENTRAL_MODULE_NAME);
        //     $data['module_name'] = ASSETCENTRAL_MODULE_NAME;
        //     $data['title'] = "Module License Activation";
        //     echo $CI->load->view($module_name['system_name'] . '/activate', $data, true);
        //     exit();
        // }
    }
}

function assetcentral_predeactivate($module_name)
{
    if ($module_name['system_name'] == ASSETCENTRAL_MODULE_NAME) {
        // Comentamos a lógica de desativação da licença
        // require_once 'libraries/leclib.php';
        // $assetcentral_api = new AssetcentralLic();
        // $assetcentral_api->deactivate_license();
    }
}

function assetcentral_uninstall($module_name)
{
    if ($module_name['system_name'] == ASSETCENTRAL_MODULE_NAME) {
        // Comentamos a lógica de desativação da licença
        // require_once 'libraries/leclib.php';
        // $assetcentral_api = new AssetcentralLic();
        // $assetcentral_api->deactivate_license();
    }
}
