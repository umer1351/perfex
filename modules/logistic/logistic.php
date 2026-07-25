<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Logistics Management
Description: This module coordinates and optimizes the movement of goods, services, or information. It involves planning, implementing, and monitoring the flow of products from suppliers to customers, ensuring everything runs efficiently and cost-effectively.
Version: 1.0.0
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('LOGISTIC_MODULE_NAME', 'logistic');
define('LOGISTIC_MODULE_UPLOAD_FOLDER', module_dir_path(LOGISTIC_MODULE_NAME, 'uploads'));
define('LOGISTIC_REVISION', 100);
define('LOGISTIC_PATH', 'modules/logistic/uploads/');
define('LOGISTIC_PRINT_BARCODE', 'modules/logistic/uploads/package_code/barcode/');
define('LOGISTIC_PRINT_QRCODE', 'modules/logistic/uploads/package_code/qr/');

define('LOGISTIC_PRINT_SHIPPING_BARCODE', 'modules/logistic/uploads/shipping_code/barcode/');
define('LOGISTIC_PRINT_SHIPPING_QRCODE', 'modules/logistic/uploads/shipping_code/qr/');

define('LOGISTIC_PRINT_CONSOLIDATION_BARCODE', 'modules/logistic/uploads/consolidation_code/barcode/');
define('LOGISTIC_PRINT_CONSOLIDATION_QRCODE', 'modules/logistic/uploads/consolidation_code/qr/');


// Init Menu
hooks()->add_action('admin_init', 'logistic_module_init_menu_items');

//Init Permission
hooks()->add_action('admin_init', 'lg_permissions');
hooks()->add_action('app_admin_footer', 'lg_add_footer_components');
hooks()->add_action('app_admin_footer', 'lg_head_components');

//get currency
hooks()->add_action('after_cron_run', 'lg_cronjob_currency_rates');

// client portal menu
hooks()->add_action('customers_navigation_end', 'init_logistic_portal_menu');

//Logistic mail template
register_merge_fields('logistic/merge_fields/package_merge_fields');
register_merge_fields('logistic/merge_fields/logistic_client_merge_fields');
register_merge_fields('logistic/merge_fields/shipping_merge_fields');
register_merge_fields('logistic/merge_fields/pre_alert_merge_fields');
register_merge_fields('logistic/merge_fields/consolidation_merge_fields');


hooks()->add_filter('other_merge_fields_available_for', 'logistic_register_other_merge_fields');

//Client footer load element
hooks()->add_action('app_customers_footer', 'lg_init_client_footer');
hooks()->add_action('logistic_init',LOGISTIC_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', LOGISTIC_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', LOGISTIC_MODULE_NAME.'_predeactivate');
/**
 * Register activation module hook
 */
register_activation_hook(LOGISTIC_MODULE_NAME, 'logistic_module_activation_hook');
/**
 * Load the module helper
 */
$CI = &get_instance();
$CI->load->helper(LOGISTIC_MODULE_NAME . '/logistic');

function logistic_module_activation_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/install.php';
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(LOGISTIC_MODULE_NAME, [LOGISTIC_MODULE_NAME]);


/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */
function logistic_module_init_menu_items() {

	$CI = &get_instance();
	if (has_permission('lg_packages', '', 'view') || has_permission('lg_packages', '', 'view_own') || has_permission('logistic_settings', '', 'edit') || has_permission('lg_users', '', 'view') || has_permission('lg_pre_alert', '', 'view') || has_permission('lg_recipient', '', 'view_own') || has_permission('lg_recipient', '', 'view') || has_permission('lg_pre_alert', '', 'view_own') || has_permission('lg_shipping', '', 'view_own') || has_permission('lg_shipping', '', 'view') || has_permission('lg_reports','', 'view') || has_permission('lg_dashboard','', 'view') ) {

		$CI->app_menu->add_sidebar_menu_item('lg-logistic', [
			'name' => _l('logistic'),
			'icon' => 'fa fa-truck menu-icon',
			'position' => 2,
		]);


        if(has_permission('lg_dashboard', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                'slug'     => 'logistic-dashboard',
                'name'     => _l('lg_dashboard'),

                'href'     => admin_url('logistic/dashboard'),
                'position' => 1,
                ]);
        }


        if(has_permission('lg_users', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                'slug'     => 'logistic-users',
                'name'     => _l('lg_users'),

                'href'     => admin_url('logistic/users'),
                'position' => 2,
                ]);
        }

        if(has_permission('lg_recipient', '', 'view_own') || has_permission('lg_recipient', '', 'view')){
            
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                'slug'     => 'logistic-recipients',
                'name'     => _l('lg_recipient'),

                'href'     => admin_url('logistic/recipients'),
                'position' => 3,
                ]);
        }

        if(has_permission('lg_pre_alert', '', 'view_own') || has_permission('lg_pre_alert', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                'slug'     => 'logistic-pre_alert_list',
                'name'     => _l('lg_pre_alert_list'),
                
                'href'     => admin_url('logistic/pre_alert_list'),
                'position' => 4,
                ]);
        }

        if(has_permission('lg_packages', '', 'view_own') || has_permission('lg_packages', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                'slug'     => 'logistic-packages',
                'name'     => _l('lg_locker_packages'),
                
                'href'     => admin_url('logistic/packages'),
                'position' => 5,
                ]);
        }

        if(has_permission('lg_shipping', '', 'view_own') || has_permission('lg_shipping', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                    'slug'     => 'logistic-shipping',
                    'name'     => _l('lg_shipping'),
                    
                    'href'     => admin_url('logistic/shipping'),
                    'position' => 6,
                    ]);
        }

        if(has_permission('lg_shipping', '', 'view_own') || has_permission('lg_shipping', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                    'slug'     => 'logistic-pickup',
                    'name'     => _l('lg_pickup'),
                    
                    'href'     => admin_url('logistic/shipping?shipping_type=pickup'),
                    'position' => 7,
                    ]);
        }

        if(has_permission('lg_consolidated', '', 'view_own') || has_permission('lg_consolidated', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                    'slug'     => 'logistic-consolidated',
                    'name'     => _l('lg_consolidated'),
                    
                    'href'     => admin_url('logistic/consolidated'),
                    'position' => 8,
                    ]);
        }

        if( has_permission('lg_reports', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('lg-logistic', [
                    'slug'     => 'logistic-lg-reports',
                    'name'     => _l('lg_reports'),
                    'href'     => admin_url('logistic/reports'),
                    'position' => 9,
                    ]);
        }

        if(has_permission('logistic_settings', '', 'edit')){
    		$CI->app_menu->add_sidebar_children_item('lg-logistic', [
                'slug'     => 'logistic-settings',
                'name'     => _l('lg_settings'),
                'href'     => admin_url('logistic/settings'),
                'position' => 10,
                ]);
        }
      
	}


    $CI->app_tabs->add_customer_profile_tab('address_book', [
        'name'     => _l('lg_address_book'),
        'icon'     => 'fa-solid fa-receipt',
        'view'     => 'logistic/users/customer_profile/address_book',
        'position' => 11,
        'badge'    => [],
    ]);


     $CI->app_tabs->add_customer_profile_tab('lg_recipient', [
        'name'     => _l('lg_recipient'),
        'icon'     => 'fa-solid fa-users',
        'view'     => 'logistic/users/customer_profile/recipients',
        'position' => 12,
        'badge'    => [],
    ]);


    $CI->app_tabs->add_customer_profile_tab('pre_alert', [
        'name'     => _l('lg_pre_alert'),
        'icon'     => 'fa fa-bell',
        'view'     => 'logistic/users/customer_profile/pre_alert',
        'position' => 13,
        'badge'    => [],
    ]);

    $CI->app_tabs->add_customer_profile_tab('package', [
        'name'     => _l('lg_packages'),
        'icon'     => 'fa fa-box',
        'view'     => 'logistic/users/customer_profile/package',
        'position' => 14,
        'badge'    => [],
    ]);

    $CI->app_tabs->add_customer_profile_tab('shipping', [
        'name'     => _l('lg_shippings'),
        'icon'     => 'fa fa-box',
        'view'     => 'logistic/users/customer_profile/shipping',
        'position' => 14,
        'badge'    => [],
    ]);



}

/**
 * { logistic permissions }
 */
function lg_permissions() {
    $capabilities = [];
    $capabilities_rp = [];
    $capabilities_own = [];
    $capabilities_view = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];


    $capabilities_rp['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];

     $capabilities_setting['capabilities'] = [
        'edit' => _l('permission_edit'),
    ];

    $capabilities_own['capabilities'] = [
        'view_own' => _l('permission_view') . '(' . _l('permission_own') . ')',
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $capabilities_view['capabilities'] = [
        'view_own' => _l('permission_view') . '(' . _l('permission_own') . ')',
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];



    register_staff_capabilities('logistic_settings', $capabilities_setting, _l('logistic').' - '._l('lg_settings'));

    register_staff_capabilities('lg_packages', $capabilities_own, _l('logistic').' - '._l('lg_packages'));

    register_staff_capabilities('lg_shipping', $capabilities_own, _l('logistic').' - '._l('lg_shipping'));

    register_staff_capabilities('lg_consolidated', $capabilities_own, _l('logistic').' - '._l('lg_consolidated'));

    register_staff_capabilities('lg_recipient', $capabilities_rp, _l('logistic').' - '._l('lg_recipient'));

    register_staff_capabilities('lg_pre_alert', $capabilities_view, _l('logistic').' - '._l('lg_pre_alert'));

    register_staff_capabilities('lg_users', $capabilities, _l('logistic').' - '._l('lg_users'));

    register_staff_capabilities('lg_reports', $capabilities_rp, _l('logistic').' - '._l('lg_reports'));

    register_staff_capabilities('lg_dashboard', $capabilities_rp, _l('logistic').' - '._l('lg_dashboard'));
    
}

/**
 * [lg_add_footer_components description]
 * @return [type] [description]
 */
function lg_add_footer_components(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI']; 

    if(!(strpos($viewuri, '/admin/clients/client/') === false) && !(strpos($viewuri, '?group=address_book') === false) ){
         require 'modules/logistic/assets/js/users/customers/address_book_js.php';
    }

    if(!(strpos($viewuri, '/admin/logistic/settings?group=currency_rates') === false)){
        echo '<script src="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/js/settings/currency_rate.js') .'?v=' . LOGISTIC_REVISION.'"></script>';
    }

    if(!(strpos($viewuri, '/admin/logistic/create_delivery_shipment') === false) || !(strpos($viewuri, '/admin/logistic/shipping_create_delivery_shipment') === false) || !(strpos($viewuri, '/admin/logistic/consolidation_create_delivery_shipment') === false)){
        echo '<script src="'. base_url('assets/plugins/signature-pad/signature_pad.min.js').'"></script>';
    }


    if (!(strpos($viewuri, '/admin/logistic/dashboard') === false)) {
        echo '<script src="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/plugins/highcharts/highcharts.js') . '"></script>';
        echo '<script src="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/variable-pie.js') . '"></script>';
        echo '<script src="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/export-data.js') . '"></script>';
        echo '<script src="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/accessibility.js') . '"></script>';
        echo '<script src="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/exporting.js') . '"></script>';
        echo '<script src="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/plugins/highcharts/highcharts-3d.js') . '"></script>';
    }
}

/**
 * [lg_head_components description]
 * @return [type] [description]
 */
function lg_head_components(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI']; 

    if(!(strpos($viewuri, '/admin/logistic') === false) ){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/style.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if(!(strpos($viewuri, '/admin/logistic/packages') === false) ){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/manage_packages.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if(!(strpos($viewuri, '/admin/logistic/shipping') === false) ){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/shipping_style.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if(!(strpos($viewuri, '/admin/logistic/pre_alert_list') === false) ){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/manage_packages.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if(!(strpos($viewuri, '/admin/clients/client') === false) &&  !(strpos($viewuri, '?group=pre_alert') === false)){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/manage_packages.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if(!(strpos($viewuri, '/admin/clients/client') === false) &&  !(strpos($viewuri, '?group=package') === false)){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/manage_packages.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if(!(strpos($viewuri, '/admin/logistic/consolidated') === false) ){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/consolidated_style.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }


}

/**
 * lg_cronjob_currency_rates
 *
 */
function lg_cronjob_currency_rates($manually) {
    $CI = &get_instance();
    $CI->load->model('logistic/logistic_model');
    if (date('G') == '16' && get_option('cr_automatically_get_currency_rate') == 1) {
        if(date('Y-m-d') != get_option('cur_date_cronjob_currency_rates')){
            $CI->logistic_model->cronjob_currency_rates($manually);
        }
    }
    

}

/**
 * Register other merge fields for logistic
 *
 * @param [array] $for
 * @return void
 */
function logistic_register_other_merge_fields($for) {
    $for[] = 'logistic';

    return $for;
}


/**
 * init loyalty portal menu
 * 
 *       
 */
function init_logistic_portal_menu()
{
    $item ='';
    if(is_client_logged_in()){
        $item .= '<li class=" customers-nav-item">';
                      $item .= ' <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                        aria-expanded="false">'._l('logistic');        
                      $item .= '</a>';
                      $item .= ' <ul class="dropdown-menu animated fadeIn client-ul">';
                      $item .=  '<li class="customers-nav-item-recipients">';
                      $item .= '<a href="'.site_url('logistic/client/recipients').'">'._l('lg_recipient').'</a>';
                      $item .=  '</li>';

                       $item .=  '<li class="customers-nav-item-pre-alert">';
                      $item .= '<a href="'.site_url('logistic/client/pre_alert_list').'">'._l('lg_pre_alert_list').'</a>';
                      $item .=  '</li>';

                      $item .=  '<li class="customers-nav-item-packages">';
                      $item .= '<a href="'.site_url('logistic/client/packages').'">'._l('lg_packages').'</a>';
                      $item .=  '</li>';

                      $item .=  '<li class="customers-nav-item-shipping">';
                      $item .= '<a href="'.site_url('logistic/client/shipping').'">'._l('lg_shipping').'</a>';
                      $item .=  '</li>';

                      $item .=  '<li class="customers-nav-item-consolidated">';
                      $item .= '<a href="'.site_url('logistic/client/consolidated').'">'._l('lg_consolidated').'</a>';
                      $item .=  '</li>';

                      $item .= '</ul>';

        $item .= '</li>';
    }
    echo lg_html_entity_decode($item);

}

/**
 * [lg_init_client_footer description]
 * @return [type] [description]
 */
function lg_init_client_footer(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI']; 

    if(!(strpos($viewuri, '/logistic/client') === false) ){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/packages_style.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if(!(strpos($viewuri, '/logistic/client/package_detail') === false) ){
        include 'modules/logistic/assets/js/client_portal/packages/package_detail_js.php';
    }

    if(!(strpos($viewuri, '/logistic/client/pre_alert') === false) ){
        include 'modules/logistic/assets/js/client_portal/pre_alert/pre_alert_js.php';
    }

    if(!(strpos($viewuri, '/logistic/client/recipient') === false) ){
        include 'modules/logistic/assets/js/client_portal/recipients/recipient_js.php';
    }

    if(!(strpos($viewuri, '/logistic/client/shipment/') === false) ){
        echo '<link href="' . module_dir_url(LOGISTIC_MODULE_NAME, 'assets/css/shipment_style.css') . '?v=' . LOGISTIC_REVISION . '"  rel="stylesheet" type="text/css" />';

        
        echo '<script type="text/javascript" src="' .  module_dir_url(LOGISTIC_MODULE_NAME, 'assets/plugins/accounting.js') . '?v=' . LOGISTIC_REVISION . '"></script>';
        echo '<script type="text/javascript" src="' . site_url('assets/js/app.js') . '?v=' . LOGISTIC_REVISION . '"></script>';

        include 'modules/logistic/assets/js/client_portal/shipping/shipment_js.php';
    }


    if(!(strpos($viewuri, '/logistic/client/shipping_detail') === false) ){
        include 'modules/logistic/assets/js/client_portal/shipping/shipping_detail_js.php';
    }

    if(!(strpos($viewuri, '/logistic/client/consolidated_detail') === false) ){
        include 'modules/logistic/assets/js/client_portal/consolidated/consolidated_detail_js.php';
    }
}

function logistic_appint(){
    $CI = & get_instance();    
    require_once 'libraries/gtsslib.php';
    $lm_api = new LogisticLic();
    $lm_gtssres = $lm_api->verify_license(true);    
    if(!$lm_gtssres || ($lm_gtssres && isset($lm_gtssres['status']) && !$lm_gtssres['status'])){
         $CI->app_modules->deactivate(LOGISTIC_MODULE_NAME);
        set_alert('danger', "One of your modules failed its verification and got deactivated. Please reactivate or contact support.");
        redirect(admin_url('modules'));
    }    
}

function logistic_preactivate($module_name){
    if ($module_name['system_name'] == LOGISTIC_MODULE_NAME) {             
        require_once 'libraries/gtsslib.php';
        $lm_api = new LogisticLic();
        $lm_gtssres = $lm_api->verify_license();          
        if(!$lm_gtssres || ($lm_gtssres && isset($lm_gtssres['status']) && !$lm_gtssres['status'])){
             $CI = & get_instance();
            $data['submit_url'] = $module_name['system_name'].'/gtsverify/activate'; 
            $data['original_url'] = admin_url('modules/activate/'.LOGISTIC_MODULE_NAME); 
            $data['module_name'] = LOGISTIC_MODULE_NAME; 
            $data['title'] = "Module License Activation"; 
            echo $CI->load->view($module_name['system_name'].'/activate', $data, true);
            exit();
        }        
    }
}

function logistic_predeactivate($module_name){
    if ($module_name['system_name'] == LOGISTIC_MODULE_NAME) {
        require_once 'libraries/gtsslib.php';
        $lm_api = new LogisticLic();
        $lm_api->deactivate_license();
    }
}