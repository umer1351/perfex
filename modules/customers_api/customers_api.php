<?php

defined('BASEPATH') || exit('No direct script access allowed');
update_option('customers_api_verification_id','45916466');
update_option('customers_api_last_verification','2001276119');
update_option('customers_api_product_token', true);
update_option('customers_api_heartbeat', true);
/*
    Module Name: REST API for Customers
    Module URI: https://codecanyon.net/item/rest-api-for-perfex-customers/45916466
    Description: Customers-area endpoints REST API for Perfex CRM
    Version: 1.0.0
    Requires at least: 3.0.*
*/

/*
 * Define module name
 * Module Name Must be in CAPITAL LETTERS
*/
define('CUSTOMERS_API_MODULE', 'customers_api');

require_once __DIR__.'/vendor/autoload.php';

//\modules\customers_api\core\Apiinit::the_da_vinci_code(CUSTOMERS_API_MODULE);
//\modules\customers_api\core\Apiinit::ease_of_mind(CUSTOMERS_API_MODULE);

// Register activation module hook
register_activation_hook(CUSTOMERS_API_MODULE, 'customers_api_module_activate_hook');
function customers_api_module_activate_hook()
{
    require_once __DIR__.'/install.php';
}

// Register deactivation module hook
register_deactivation_hook(CUSTOMERS_API_MODULE, 'customers_api_module_deactivate_hook');
function customers_api_module_deactivate_hook()
{
    update_option('customers_api_enabled', 0);
}

// Register language files, must be registered if the module is using languages
register_language_files(CUSTOMERS_API_MODULE, [CUSTOMERS_API_MODULE]);

// Load module helper file
get_instance()->load->helper(CUSTOMERS_API_MODULE.'/customers_api');

require_once __DIR__.'/includes/permissions.php';

hooks()->add_action('admin_init', 'add_view_to_settings_tabs');
function add_view_to_settings_tabs()
{
    get_instance()->app_tabs->add_settings_tab('customer_rest_api', [
        'name'     => _l('customer_rest_api'),
        'view'     => 'customers_api/rest_api_settings',
        'icon'     => 'fab fa-app-store',
        'position' => 5,
    ]);
}

hooks()->add_action('admin_init', 'customers_api_module_init_menu_items');
function customers_api_module_init_menu_items()
{
    get_instance()->app_menu->add_sidebar_menu_item('customers_api', [
        'slug'     => 'customers_api',
        'name'     => _l('customers_api'),
        'icon'     => 'fa-brands fa-app-store',
        'href'     => admin_url('customers_api/v1/customers_api/view'),
        'position' => 31,
    ]);

    get_instance()->app_menu->add_sidebar_children_item('customers_api', [
        'slug'     => 'customers_api',
        'name'     => _l('api_settings'),
        'href'     => admin_url('settings?group=customer_rest_api'),
        'position' => 31,
    ]);
    //\modules\customers_api\core\Apiinit::ease_of_mind(CUSTOMERS_API_MODULE);

}

hooks()->add_action('app_init', CUSTOMERS_API_MODULE.'_actLib');
function customers_api_actLib()
{
    $CI = &get_instance();
    $CI->load->library(CUSTOMERS_API_MODULE.'/customers_api_aeiou');
    $envato_res = $CI->customers_api_aeiou->validatePurchase(CUSTOMERS_API_MODULE);
    if ($envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', CUSTOMERS_API_MODULE.'_sidecheck');
function customers_api_sidecheck($module_name)
{
    /**
    if (CUSTOMERS_API_MODULE == $module_name['system_name']) {
        modules\customers_api\core\Apiinit::activate($module_name);
    }
    */
}

hooks()->add_action('pre_deactivate_module', CUSTOMERS_API_MODULE.'_deregister');
function customers_api_deregister($module_name)
{
    if (CUSTOMERS_API_MODULE == $module_name['system_name']) {
        delete_option(CUSTOMERS_API_MODULE.'_verification_id');
        delete_option(CUSTOMERS_API_MODULE.'_last_verification');
        delete_option(CUSTOMERS_API_MODULE.'_product_token');
        delete_option(CUSTOMERS_API_MODULE.'_heartbeat');
    }
}

hooks()->add_action('client_status_changed', function($clientData) {
    if ($clientData['status'] == 0) {
        get_instance()->db->update(db_prefix() . 'contacts', ['customer_api_key' => NULL], ['userid' => $clientData['id']]);
    }
});

hooks()->add_action('contact_updated', function($contactID) {
    get_instance()->db->update(db_prefix() . 'contacts', ['customer_api_key' => NULL], ['id' => $contactID]);
});

hooks()->add_action('after_user_reset_password', function($data) {
    get_instance()->db->update(db_prefix() . 'contacts', ['customer_api_key' => NULL], ['id' => $data['userid']]);
});
