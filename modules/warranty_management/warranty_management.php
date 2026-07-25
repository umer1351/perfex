<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Warranty Management
Description: This module gives warranty administrators full control over the warranty lifecycle and your guarantee program.
Version: 1.0.0
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('WARRANTY_MANAGEMENT_MODULE_NAME', 'warranty_management');
define('WARRANTY_MANAGEMENT_MODULE_UPLOAD_FOLDER', module_dir_path(WARRANTY_MANAGEMENT_MODULE_NAME, 'uploads'));

/*add folder upload link on here*/
define('WARRANTY_MANAGEMENT_PRODUCT_UPLOAD', module_dir_path(WARRANTY_MANAGEMENT_MODULE_NAME, 'uploads/products/'));
define('WARRANTY_MANAGEMENT_PROCESS_UPLOAD', module_dir_path(WARRANTY_MANAGEMENT_MODULE_NAME, 'uploads/warranty_processes/'));
define('WARRANTY_CLAIM_PROCESS_UPLOAD', module_dir_path(WARRANTY_MANAGEMENT_MODULE_NAME, 'uploads/warranty_claim_processes/'));


/*link view on here*/
define('PROCESS_ATTACHMENTS', 'modules/warranty_management/uploads/warranty_processes/');
define('CLAIM_PROCESS_ATTACHMENTS', 'modules/warranty_management/uploads/warranty_claim_processes/');
define('WARRANTY_MANAGEMENT_PRINT_ITEM', 'modules/warranty_management/uploads/print_item/');


hooks()->add_action('admin_init', 'warranty_management_permissions');
hooks()->add_action('app_admin_head', 'warranty_management_add_head_components');
hooks()->add_action('app_admin_footer', 'warranty_management_load_js');
hooks()->add_action('app_search', 'warranty_management_load_search');
hooks()->add_action('admin_init', 'warranty_management_module_init_menu_items');

/*add menu on client portal*/
hooks()->add_action('customers_navigation_end', 'init_warranties_management_portal_menu');
hooks()->add_action('app_customers_portal_head', 'warranty_management_portal_add_head_components');
hooks()->add_action('app_customers_portal_footer', 'warranty_management_portal_add_footer_components');
hooks()->add_action('warranty_management_init',WARRANTY_MANAGEMENT_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', WARRANTY_MANAGEMENT_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', WARRANTY_MANAGEMENT_MODULE_NAME.'_predeactivate');

define('VERSION_WARRANTY_MANAGEMENT', 100);

/**
* Register activation module hook
*/
register_activation_hook(WARRANTY_MANAGEMENT_MODULE_NAME, 'warranty_management_module_activation_hook');

function warranty_management_module_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}


/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(WARRANTY_MANAGEMENT_MODULE_NAME, [WARRANTY_MANAGEMENT_MODULE_NAME]);


$CI = & get_instance();
$CI->load->helper(WARRANTY_MANAGEMENT_MODULE_NAME . '/warranty_management');

/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */
function warranty_management_module_init_menu_items()
{   
	$CI = &get_instance();

	/*add menu on here*/

	if(has_permission('warranty_management','','view') ){

		$CI->app_menu->add_sidebar_menu_item('warranty_management', [
			'name'     => _l('warranties_management_name'),
			'icon'     => 'fa fa-shield', 
			'position' => 5,
		]);
	}

	if(has_permission('warranty_management','','view')){
		$CI->app_menu->add_sidebar_children_item('warranty_management', [
			'slug'     => 'warranty_management_information',
			'name'     => _l('wm_warranty_informations'),
			'icon'     => 'fa fa-dashboard',
			'href'     => admin_url('warranty_management/warranty_information'),
			'position' => 1,
		]);
	}
	if(has_permission('warranty_management','','view')){
		$CI->app_menu->add_sidebar_children_item('warranty_management', [
			'slug'     => 'warranty_claim_information',
			'name'     => _l('wm_warranty_claim_information'),
			'icon'     => 'fa fa-shield',
			'href'     => admin_url('warranty_management/warranty_claim_information'),
			'position' => 1,
		]);
	}


	if(false){
		if(has_permission('warranty_management','','view')){
			$CI->app_menu->add_sidebar_children_item('warranty_management', [
				'slug'     => 'warranty_management_terms_conditions',
				'name'     => _l('wm_terms_conditions'),
				'icon'     => 'fa fa-columns',
				'href'     => admin_url('warranty_management/terms_conditions'),
				'position' => 1,
			]);
		}
	}



	if(has_permission('warranty_management','','view')){
		$CI->app_menu->add_sidebar_children_item('warranty_management', [
			'slug'     => 'warranty_management_setting',
			'name'     => _l('mrp_settings'),
			'icon'     => 'fa fa-cog menu-icon',
			'href'     => admin_url('warranty_management/setting?group=general'),
			'position' => 10,
		]);
	}


}

	/**
	 * warranty_management load js
	 */
	function warranty_management_load_js(){    
		$CI = &get_instance();    
		$viewuri = $_SERVER['REQUEST_URI'];
		
		/*change this code*/

		if(!(strpos($viewuri, '/admin/warranty_management') === false)){
			echo '<script src="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/js/tinymce_init.js') .'?v=' . VERSION_WARRANTY_MANAGEMENT.'"></script>';
		}
		if(!(strpos($viewuri,'admin/warranty_management/process_manage') === false)){
			echo '<script src="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/plugins/jquery-nestable/jquery.nestable.js') . '"></script>';
			echo '<script src="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/plugins/font-awesome-icon-picker/js/fontawesome-iconpicker.js') . '"></script>';
		}

		if(!(strpos($viewuri,'admin/warranty_management/warranty_claim_detail') === false)){
			echo '<script src="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/plugins/handsontable/chosen.jquery.js') . '"></script>';
			echo '<script src="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/plugins/handsontable/handsontable-chosen-editor.js') . '"></script>';
		}

	}

	/**
	 * warranty_management add head components
	 */
	function warranty_management_add_head_components(){    
		$CI = &get_instance();
		$viewuri = $_SERVER['REQUEST_URI'];

		/*change this code*/
		if(!(strpos($viewuri,'admin/warranty_management') === false)){
			echo '<link href="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/css/styles.css') . '?v=' . VERSION_WARRANTY_MANAGEMENT. '"  rel="stylesheet" type="text/css" />';
		}
		if(!(strpos($viewuri,'admin/warranty_management') === false)){
			echo '<link href="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/plugins/font-awesome-icon-picker/css/fontawesome-iconpicker.min.css') . '?v=' . VERSION_WARRANTY_MANAGEMENT. '"  rel="stylesheet" type="text/css" />';
		}
		if(!(strpos($viewuri,'admin/warranty_management/warranty_claim_detail') === false)){
			echo '<link href="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/css/warranty_claims/warranty_claim_detail.css') . '?v=' . VERSION_WARRANTY_MANAGEMENT. '"  rel="stylesheet" type="text/css" />';
		}

		if(!(strpos($viewuri,'admin/warranty_management/warranty_claim_detail') === false)){
			echo '<link href="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.css') . '"  rel="stylesheet" type="text/css" />';
			echo '<link href="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/plugins/handsontable/chosen.css') . '"  rel="stylesheet" type="text/css" />';
			echo '<script src="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.js') . '"></script>';
		}

	}

	/**
	 * warranty_management permissions
	 */
	function warranty_management_permissions()
	{

		$capabilities = [];

		$capabilities['capabilities'] = [
			'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
			'create' => _l('permission_create'),
			'edit'   => _l('permission_edit'),
			'delete' => _l('permission_delete'),
		];

		
		register_staff_capabilities('warranty_management', $capabilities, _l('warranty_management_name'));

	}

	/**
	 * init service management portal menu
	 * @return [type] 
	 */
	function init_warranties_management_portal_menu()
	{
		$item ='';
		if(is_client_logged_in()){
			if(get_option('warranty_management_display_on_portal') == 1){
				$item .= '<li class="customers-nav-item">';
				$item .= '<a href="'.site_url('warranty_management/warranty_management_client/warranty_informations').'">'._l("warranties_management_name").'';        
				$item .= '</a>';
				$item .= '</li>';
			}
		}
		echo new_html_entity_decode($item);

	}

	/**
	 * warranty management portal add head components
	 * @return [type] 
	 */
	function warranty_management_portal_add_head_components() {
		$CI = &get_instance();
		$viewuri = $_SERVER['REQUEST_URI'];

		if(!(strpos($viewuri,'warranty_management/warranty_management_client') === false)){
			echo '<link href="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/css/styles.css') . '?v=' . VERSION_WARRANTY_MANAGEMENT. '"  rel="stylesheet" type="text/css" />';
			echo '<link href="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/css/client_portals/styles.css') . '?v=' . VERSION_WARRANTY_MANAGEMENT. '"  rel="stylesheet" type="text/css" />';
		}

		if(!(strpos($viewuri,'warranty_management/warranty_management_client/warranty_claim_detail') === false)){
			echo '<link href="' . module_dir_url(WARRANTY_MANAGEMENT_MODULE_NAME, 'assets/css/warranty_claims/warranty_claim_detail.css') . '?v=' . VERSION_WARRANTY_MANAGEMENT. '"  rel="stylesheet" type="text/css" />';
		}
		

	}

	/**
	 * warranty management portal add footer components
	 * @return [type] 
	 */
	function warranty_management_portal_add_footer_components() {
		$CI = &get_instance();
		$viewuri = $_SERVER['REQUEST_URI'];

	}
	function warranty_management_appint(){
    $CI = & get_instance();    
    require_once 'libraries/gtsslib.php';
    $wm_api = new WarrantyManagementLic();
    $wm_gtssres = $wm_api->verify_license(true);    
    if(!$wm_gtssres || ($wm_gtssres && isset($wm_gtssres['status']) && !$wm_gtssres['status'])){
         $CI->app_modules->deactivate(WARRANTY_MANAGEMENT_MODULE_NAME);
        set_alert('danger', "One of your modules failed its verification and got deactivated. Please reactivate or contact support.");
        redirect(admin_url('modules'));
    }    
}

function warranty_management_preactivate($module_name){
    if ($module_name['system_name'] == WARRANTY_MANAGEMENT_MODULE_NAME) {             
        require_once 'libraries/gtsslib.php';
        $wm_api = new WarrantyManagementLic();
        $wm_gtssres = $wm_api->verify_license();          
        if(!$wm_gtssres || ($wm_gtssres && isset($wm_gtssres['status']) && !$wm_gtssres['status'])){
             $CI = & get_instance();
            $data['submit_url'] = $module_name['system_name'].'/gtsverify/activate'; 
            $data['original_url'] = admin_url('modules/activate/'.WARRANTY_MANAGEMENT_MODULE_NAME); 
            $data['module_name'] = WARRANTY_MANAGEMENT_MODULE_NAME; 
            $data['title'] = "Module License Activation"; 
            echo $CI->load->view($module_name['system_name'].'/activate', $data, true);
            exit();
        }        
    }
}

function warranty_management_predeactivate($module_name){
    if ($module_name['system_name'] == WARRANTY_MANAGEMENT_MODULE_NAME) {
        require_once 'libraries/gtsslib.php';
        $wm_api = new WarrantyManagementLic();
        $wm_api->deactivate_license();
    }
}