<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Property
Description: Module created for Property
Version: 2.3.0
Author: Websicon Infoworld Pvt Ltd
Author URI: https://websicon.in/
Requires at least: 2.3.*
*/



$config['module_name']  = 'property';   // must match folder name
$config['name']         = 'Property';
$config['description']  = 'Module to manage properties';
$config['author']       = 'Your Name';
$config['version']      = '1.0.0';
$config['requires']     = ['perfex_version' => '3.0.0'];


// Define module permissions
$config['permissions'] = [
    'view'   => _l('permission_view_property'),
    'create' => _l('permission_create_property'),
    'edit'   => _l('permission_edit_property'),
    'delete' => _l('permission_delete_property')
];



define('PROPERTY_MODULE_NAME', 'property');
// Register module
register_activation_hook(PROPERTY_MODULE_NAME, 'property_module_activation_hook');

function property_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');


    $alreadyInstalled = get_option('property_active');

    if ($alreadyInstalled != "" && $alreadyInstalled != NULL) {
        update_option('property_active', 1);
    } else {
        add_option('property_active', 1);
    }
}


register_deactivation_hook(PROPERTY_MODULE_NAME, 'property_deactivation_hook');

function property_deactivation_hook()
{
    update_option('property_active', 0, 1);
}

register_language_files(PROPERTY_MODULE_NAME, [PROPERTY_MODULE_NAME]);

// Register permissions
hooks()->add_action('admin_init', 'property_module_permissions');

function property_module_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view_property'),
        'create' => _l('permission_create_property'),
        'edit'   => _l('permission_edit_property'),
        'delete' => _l('permission_delete_property'),
    ];

    register_staff_capabilities('property', $capabilities, _l('property_list'));
}


hooks()->add_action('admin_init', 'property_init_menu_items');

function property_init_menu_items()
{
    $CI = &get_instance();

    /**
     * If the logged in user is administrator, add custom menu in Setup
     */

    if (has_permission('permission_view_property', '', 'view') ) {



        $CI->app_menu->add_sidebar_menu_item('property', [
            'slug' => 'property',
            'name' => _l('property'),
            'icon' => 'fa fa-cog',
            'href' => '',
            'position' => 34,
        ]);


        $CI->app_menu->add_sidebar_children_item('property', [
            'slug' => 'property',
            'name' => _l('property'),
            'href' => admin_url('property/'),
            'icon' => '',
            'position' => 34,
        ]);
 
    }
}
