<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Facebook & Instagram Leads Integration
Module URI: https://codecanyon.net/item/facebook-leads-integration-sync-module-for-perfex-crm/25623248
Description: Sync leads between Facebook & Instagram Lead Ads and Perfex CRM in real-time. Supports multiple pages, custom fields, and automatic lead notifications.
Version: 2.0.0
Requires at least: 2.3.*
Author: Themesic Interactive
Author URI: https://codecanyon.net/user/themesic/portfolio
*/

if (!defined('FB_LEADS_MODULE_NAME')) {
    define('FB_LEADS_MODULE_NAME', 'facebookleadsintegration');
}
if (!defined('FB_LEADS_MODULE_PATH')) {
    define('FB_LEADS_MODULE_PATH', __DIR__ . '/');
}
if (!defined('FB_LEADS_MODULE_VERSION')) {
    define('FB_LEADS_MODULE_VERSION', '2.0.0');
}

// Load vendor autoloader
require_once __DIR__ . '/vendor/autoload.php';

// License verification (keep existing system)
modules\facebookleadsintegration\core\Apiinit::the_da_vinci_code(FB_LEADS_MODULE_NAME);
modules\facebookleadsintegration\core\Apiinit::ease_of_mind(FB_LEADS_MODULE_NAME);

/**
 * Register module hooks
 */
hooks()->add_action('admin_init', 'fb_leads_init_menu_items');
hooks()->add_action('admin_init', 'fb_leads_add_settings_tab');
hooks()->add_action('app_admin_head', 'fb_leads_add_admin_head');
hooks()->add_action('app_admin_footer', 'fb_leads_add_admin_footer');

/**
 * Register CSRF exclusions for webhook endpoint
 * Using the proper hook method instead of modifying config.php
 */
hooks()->add_filter('csrf_exclude_uris', 'fb_leads_csrf_exclude');

function fb_leads_csrf_exclude($exclude_uris)
{
    $exclude_uris[] = 'facebookleadsintegration/webhook';
    $exclude_uris[] = 'facebookleadsintegration/webhook.*';
    return $exclude_uris;
}

/**
 * Add settings tab in Perfex CRM Settings
 * Updated for Perfex CRM 3.2.0+ compatibility
 */
function fb_leads_add_settings_tab()
{
    $CI = &get_instance();
    
    // Use the new API for Perfex CRM 3.2.0+
    // Signature: add_settings_section($slug, $options)
    if (method_exists($CI->app, 'add_settings_section')) {
        $CI->app->add_settings_section(FB_LEADS_MODULE_NAME, [
            'name'     => _l('facebookleadsintegration'),
            'view'     => FB_LEADS_MODULE_NAME . '/settings_tab_view',
            'position' => 101,
            'icon'     => 'fab fa-facebook',
            'children' => []
        ]);
    } elseif (isset($CI->app_tabs) && method_exists($CI->app_tabs, 'add_settings_tab')) {
        // Fallback for older versions
        $CI->app_tabs->add_settings_tab(FB_LEADS_MODULE_NAME, [
            'name'     => _l('facebookleadsintegration'),
            'view'     => FB_LEADS_MODULE_NAME . '/settings_tab_view',
            'position' => 101,
        ]);
    }
}

/**
 * Initialize menu items
 */
function fb_leads_init_menu_items()
{
    $CI = &get_instance();

    // Add to quick actions
    $CI->app->add_quick_actions_link([
        'name'       => _l('facebookleadsintegration'),
        'permission' => 'settings',
        'url'        => admin_url('facebookleadsintegration'),
        'position'   => 69,
        'icon'       => 'fab fa-facebook'
    ]);

    // Add sidebar menu item
    if (has_permission('settings', '', 'view') || is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('fb-leads-integration', [
            'name'     => _l('facebookleadsintegration'),
            'icon'     => 'fab fa-facebook',
            'position' => 50,
            'collapse' => true,
            'href'     => '#',
        ]);

        $CI->app_menu->add_sidebar_children_item('fb-leads-integration', [
            'slug'     => 'fb-leads-settings',
            'name'     => _l('fb_leads_settings'),
            'icon'     => 'fa fa-cog',
            'href'     => admin_url('facebookleadsintegration'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('fb-leads-integration', [
            'slug'     => 'fb-leads-sync-history',
            'name'     => _l('fb_leads_sync_history'),
            'icon'     => 'fa fa-history',
            'href'     => admin_url('facebookleadsintegration/sync_history'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('fb-leads-integration', [
            'slug'     => 'fb-leads-field-mapping',
            'name'     => _l('fb_leads_field_mapping'),
            'icon'     => 'fa fa-exchange',
            'href'     => admin_url('facebookleadsintegration/field_mapping'),
            'position' => 3,
        ]);
    }
}

/**
 * Add CSS to admin head
 */
function fb_leads_add_admin_head()
{
    $CI = &get_instance();
    
    // Only load on our module pages
    if (strpos($CI->uri->uri_string(), 'facebookleadsintegration') === false) {
        return;
    }
    
    echo '<style>
        .fb-leads-stat-box {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .fb-leads-stat-box .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .fb-leads-stat-box .stat-label {
            font-size: 14px;
            color: #666;
        }
        .fb-leads-stat-box.success { background: #e8f5e9; color: #2e7d32; }
        .fb-leads-stat-box.danger { background: #ffebee; color: #c62828; }
        .fb-leads-stat-box.warning { background: #fff8e1; color: #f57f17; }
        .fb-leads-stat-box.info { background: #e3f2fd; color: #1565c0; }
        
        .fb-leads-status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .fb-leads-status-indicator.connected { background: #4caf50; }
        .fb-leads-status-indicator.disconnected { background: #f44336; }
        .fb-leads-status-indicator.warning { background: #ff9800; }
        
        .fb-leads-help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .fb-leads-step {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .fb-leads-step.active {
            border-color: #3b5998;
            background: #f0f4f9;
        }
        .fb-leads-step.completed {
            border-color: #4caf50;
            background: #e8f5e9;
        }
        .fb-leads-step .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            background: #ddd;
            color: #fff;
            margin-right: 10px;
        }
        .fb-leads-step.active .step-number { background: #3b5998; }
        .fb-leads-step.completed .step-number { background: #4caf50; }
        
        .fb-leads-connection-test {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .fb-leads-connection-test.success { background: #e8f5e9; border: 1px solid #4caf50; }
        .fb-leads-connection-test.error { background: #ffebee; border: 1px solid #f44336; }
        
        .fb-leads-mapping-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            gap: 10px;
        }
        .fb-leads-mapping-row input.form-control {
            flex: 1;
            min-width: 0;
        }
        .fb-leads-mapping-row .arrow {
            flex-shrink: 0;
            color: #666;
        }
        .fb-leads-mapping-row select.form-control {
            flex: 1;
            min-width: 0;
            height: 34px;
            padding: 6px 12px;
            appearance: menulist;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
        }
        .fb-leads-mapping-row .btn {
            flex-shrink: 0;
        }
        
        #pages-table .label {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>';
}

/**
 * Add JS to admin footer
 */
function fb_leads_add_admin_footer()
{
    $CI = &get_instance();
    
    // Only load on our module pages
    if (strpos($CI->uri->uri_string(), 'facebookleadsintegration') === false) {
        return;
    }
    
    $app_id = get_option('fb_leads_app_id');
    $api_version = 'v19.0';
    
    echo '<script>
        // Facebook SDK initialization
        window.fbAsyncInit = function() {
            FB.init({
                appId: "' . htmlspecialchars($app_id) . '",
                cookie: true,
                xfbml: true,
                version: "' . $api_version . '"
            });
        };

        // Load Facebook SDK
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); 
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, "script", "facebook-jssdk"));

        // Wait for jQuery to be available before defining FBLeads
        (function initFBLeads() {
            if (typeof jQuery === "undefined") {
                setTimeout(initFBLeads, 50);
                return;
            }

            // Global FB Leads module functions
            window.FBLeads = {
            // Test connection to Facebook
            testConnection: function(callback) {
                $.ajax({
                    url: admin_url + "facebookleadsintegration/test_connection",
                    type: "POST",
                    dataType: "json",
                    data: { ' . $CI->security->get_csrf_token_name() . ': "' . $CI->security->get_csrf_hash() . '" },
                    success: callback,
                    error: function(xhr) {
                        callback({ success: false, message: "Connection test failed" });
                    }
                });
            },

            // Fetch Facebook pages after login
            fetchPages: function(userToken, callback) {
                $.ajax({
                    url: admin_url + "facebookleadsintegration/fetch_pages",
                    type: "POST",
                    dataType: "json",
                    data: { 
                        user_token: userToken,
                        ' . $CI->security->get_csrf_token_name() . ': "' . $CI->security->get_csrf_hash() . '"
                    },
                    success: callback,
                    error: function(xhr) {
                        callback({ success: false, message: "Failed to fetch pages" });
                    }
                });
            },

            // Exchange short-lived token for long-lived token
            exchangeToken: function(shortToken, callback) {
                $.ajax({
                    url: admin_url + "facebookleadsintegration/exchange_token",
                    type: "POST",
                    dataType: "json",
                    data: { 
                        token: shortToken,
                        ' . $CI->security->get_csrf_token_name() . ': "' . $CI->security->get_csrf_hash() . '"
                    },
                    success: callback,
                    error: function(xhr) {
                        callback({ success: false, message: "Token exchange failed" });
                    }
                });
            },

            // Subscribe/unsubscribe page
            pageAction: function(pageId, action, callback) {
                $.ajax({
                    url: admin_url + "facebookleadsintegration/" + action + "_page",
                    type: "POST",
                    dataType: "json",
                    data: { 
                        page_id: pageId,
                        ' . $CI->security->get_csrf_token_name() . ': "' . $CI->security->get_csrf_hash() . '"
                    },
                    success: callback,
                    error: function(xhr) {
                        callback({ success: false, message: "Action failed" });
                    }
                });
            },

            // Send test lead
            sendTestLead: function(callback) {
                $.ajax({
                    url: admin_url + "facebookleadsintegration/send_test_lead",
                    type: "POST",
                    dataType: "json",
                    data: { ' . $CI->security->get_csrf_token_name() . ': "' . $CI->security->get_csrf_hash() . '" },
                    success: callback,
                    error: function(xhr) {
                        callback({ success: false, message: "Failed to create test lead" });
                    }
                });
            },

            // Save settings
            saveSettings: function(settings, callback) {
                $.ajax({
                    url: admin_url + "facebookleadsintegration/save_settings",
                    type: "POST",
                    dataType: "json",
                    data: { 
                        settings: settings,
                        ' . $CI->security->get_csrf_token_name() . ': "' . $CI->security->get_csrf_hash() . '"
                    },
                    success: callback,
                    error: function(xhr) {
                        callback({ success: false, message: "Failed to save settings" });
                    }
                });
            },

            // Facebook Login
            loginWithFacebook: function(callback) {
                var requiredPermissions = [
                    "pages_show_list",
                    "pages_read_engagement",
                    "leads_retrieval",
                    "pages_manage_ads",
                    "ads_management"
                ];

                FB.login(function(response) {
                    if (response.status === "connected") {
                        var accessToken = response.authResponse.accessToken;
                        
                        // Exchange for long-lived token
                        FBLeads.exchangeToken(accessToken, function(tokenResult) {
                            if (tokenResult.success) {
                                // Fetch pages with the new token
                                FBLeads.fetchPages(accessToken, callback);
                            } else {
                                callback(tokenResult);
                            }
                        });
                    } else {
                        callback({ 
                            success: false, 
                            message: "Facebook login failed or was cancelled" 
                        });
                    }
                }, {
                    scope: requiredPermissions.join(","),
                    return_scopes: true
                });
            }
        };
        })(); // End of initFBLeads IIFE
    </script>';
}

/**
 * Register activation hook
 */
register_activation_hook(FB_LEADS_MODULE_NAME, 'fb_leads_activation_hook');

function fb_leads_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register deactivation hook
 */
register_deactivation_hook(FB_LEADS_MODULE_NAME, 'fb_leads_deactivation_hook');

function fb_leads_deactivation_hook()
{
    // Clean up if needed
}

/**
 * Register language files
 */
register_language_files(FB_LEADS_MODULE_NAME, [FB_LEADS_MODULE_NAME]);

/**
 * License validation hooks (keeping existing license system)
 */
hooks()->add_action('app_init', FB_LEADS_MODULE_NAME . '_actLib');

function facebookleadsintegration_actLib()
{
    $CI = &get_instance();
    $CI->load->library(FB_LEADS_MODULE_NAME . '/Facebookleadsintegration_aeiou');
    $envato_res = $CI->facebookleadsintegration_aeiou->validatePurchase(FB_LEADS_MODULE_NAME);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', FB_LEADS_MODULE_NAME . '_sidecheck');

function facebookleadsintegration_sidecheck($module_name)
{
    if (FB_LEADS_MODULE_NAME == $module_name['system_name']) {
        modules\facebookleadsintegration\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', FB_LEADS_MODULE_NAME . '_deregister');

function facebookleadsintegration_deregister($module_name)
{
    if (FB_LEADS_MODULE_NAME == $module_name['system_name']) {
        delete_option(FB_LEADS_MODULE_NAME . '_verification_id');
        delete_option(FB_LEADS_MODULE_NAME . '_last_verification');
        delete_option(FB_LEADS_MODULE_NAME . '_product_token');
        delete_option(FB_LEADS_MODULE_NAME . '_heartbeat');
    }
}

/**
 * Cron job for retry queue processing
 */
hooks()->add_action('after_cron_run', 'fb_leads_process_retry_queue');

function fb_leads_process_retry_queue()
{
    $CI = &get_instance();
    $CI->load->library(FB_LEADS_MODULE_NAME . '/Fb_leads_sync_manager');
    $CI->fb_leads_sync_manager->process_retry_queue(10);
}

/**
 * Add leads activity log for Facebook imported leads
 */
hooks()->add_action('lead_created', 'fb_leads_log_lead_creation');

function fb_leads_log_lead_creation($lead_id)
{
    $CI = &get_instance();
    
    // Check if this lead was created by our module
    $CI->db->select('facebook_lead_id');
    $CI->db->where('id', $lead_id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();
    
    if ($lead && !empty($lead->facebook_lead_id)) {
        // Log activity
        log_activity('Facebook/Instagram Lead Imported [ID: ' . $lead_id . ', FB ID: ' . $lead->facebook_lead_id . ']');
    }
}
