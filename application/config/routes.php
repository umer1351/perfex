<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|   example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|   http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|   $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|   $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|   $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|       my-controller/my-method -> my_controller/my_method
*/

$route['default_controller']   = 'clients';
$route['404_override']         = '';
$route['translate_uri_dashes'] = false;

/**
 * Dashboard clean route
 */
$route['admin'] = 'admin/dashboard';

/**
 * Misc controller routes
 */
$route['admin/access_denied'] = 'admin/misc/access_denied';
$route['admin/not_found']     = 'admin/misc/not_found';

/**
 * Staff Routes
 */
$route['admin/profile']           = 'admin/staff/profile';
$route['admin/profile/(:num)']    = 'admin/staff/profile/$1';
$route['admin/tasks/view/(:any)'] = 'admin/tasks/index/$1';

/**
 * Items search rewrite
 */
$route['admin/items/search'] = 'admin/invoice_items/search';

/**
 * In case if client access directly to url without the arguments redirect to clients url
 */
$route['/'] = 'clients';

/**
 * @deprecated
 */
$route['viewinvoice/(:num)/(:any)'] = 'invoice/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['invoice/(:num)/(:any)'] = 'invoice/index/$1/$2';

/**
 * @deprecated
 */
$route['viewestimate/(:num)/(:any)'] = 'estimate/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['estimate/(:num)/(:any)'] = 'estimate/index/$1/$2';
$route['subscription/(:any)']    = 'subscription/index/$1';

/**
 * @deprecated
 */
$route['viewproposal/(:num)/(:any)'] = 'proposal/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['proposal/(:num)/(:any)'] = 'proposal/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['contract/(:num)/(:any)'] = 'contract/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['knowledge-base']                 = 'knowledge_base/index';
$route['knowledge-base/search']          = 'knowledge_base/search';
$route['knowledge-base/article']         = 'knowledge_base/index';
$route['knowledge-base/article/(:any)']  = 'knowledge_base/article/$1';
$route['knowledge-base/category']        = 'knowledge_base/index';
$route['knowledge-base/category/(:any)'] = 'knowledge_base/category/$1';

/**
 * @deprecated 2.2.0
 */
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'add_kb_answer') === false) {
    $route['knowledge-base/(:any)']         = 'knowledge_base/article/$1';
    $route['knowledge_base/(:any)']         = 'knowledge_base/article/$1';
    $route['clients/knowledge_base/(:any)'] = 'knowledge_base/article/$1';
    $route['clients/knowledge-base/(:any)'] = 'knowledge_base/article/$1';
}

/**
 * @deprecated 2.2.0
 * Fallback for auth clients area, changed in version 2.2.0
 */
$route['clients/reset_password']  = 'authentication/reset_password';
$route['clients/forgot_password'] = 'authentication/forgot_password';
$route['clients/logout']          = 'authentication/logout';
$route['clients/register']        = 'authentication/register';
$route['clients/login']           = 'authentication/login';

// Aliases for short routes
$route['reset_password']  = 'authentication/reset_password';
$route['forgot_password'] = 'authentication/forgot_password';
$route['login']           = 'authentication/login';
$route['logout']          = 'authentication/logout';
$route['register']        = 'authentication/register';

/**
 * Terms and conditions and Privacy Policy routes
 */
$route['terms-and-conditions'] = 'terms_and_conditions';
$route['privacy-policy']       = 'privacy_policy';

/**
 * @since 2.3.0
 * Routes for admin/modules URL because Modules.php class is used in application/third_party/MX
 */
$route['admin/modules']               = 'admin/mods';
$route['admin/modules/(:any)']        = 'admin/mods/$1';
$route['admin/modules/(:any)/(:any)'] = 'admin/mods/$1/$2';

// Public single ticket route
$route['forms/tickets/(:any)'] = 'forms/public_ticket/$1';

/**
 * @since  2.3.0
 * Route for clients set password URL, because it's using the same controller for staff to
 * If user addded block /admin by .htaccess this won't work, so we need to rewrite the URL
 * In future if there is implementation for clients set password, this route should be removed
 */
$route['authentication/set_password/(:num)/(:num)/(:any)'] = 'admin/authentication/set_password/$1/$2/$3';

// For backward compatilibilty
$route['survey/(:num)/(:any)'] = 'surveys/participate/index/$1/$2';

// --- portal/ mirrors of admin/ special routes ---
$route['portal'] = 'admin/dashboard';
$route['portal/access_denied'] = 'admin/misc/access_denied';
$route['portal/not_found']     = 'admin/misc/not_found';
$route['portal/profile']           = 'admin/staff/profile';
$route['portal/profile/(:num)']    = 'admin/staff/profile/$1';
$route['portal/tasks/view/(:any)'] = 'admin/tasks/index/$1';
$route['portal/items/search'] = 'admin/invoice_items/search';
$route['portal/modules']               = 'admin/mods';
$route['portal/modules/(:any)']        = 'admin/mods/$1';
$route['portal/modules/(:any)/(:any)'] = 'admin/mods/$1/$2';

// --- special module-specific route overrides (must come before generic mirrors) ---
$route['portal/wiki/(:any)'] = 'Wiki/index/$1';
$route['portal/customers_api/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)'] = '$1/$2/$2/$3/$4/$5/$6';
$route['portal/customers_api/(.*)/(.*)/(.*)/(.*)']           = '$1/$2/$2/$3/$4';
$route['portal/customers_api/(:any)/(:any)']                 = '$1/$2/$2';

// --- bulk portal/ mirrors for all standalone HMVC modules ---
$route['portal/accounting'] = 'accounting';
$route['portal/accounting/(.+)'] = 'accounting/$1';
$route['portal/admin_light_theme'] = 'admin_light_theme';
$route['portal/admin_light_theme/(.+)'] = 'admin_light_theme/$1';
$route['portal/advanced_task_status_manager'] = 'advanced_task_status_manager';
$route['portal/advanced_task_status_manager/(.+)'] = 'advanced_task_status_manager/$1';
$route['portal/affiliate_management'] = 'affiliate_management';
$route['portal/affiliate_management/(.+)'] = 'affiliate_management/$1';
$route['portal/ai_lead_manager'] = 'ai_lead_manager';
$route['portal/ai_lead_manager/(.+)'] = 'ai_lead_manager/$1';
$route['portal/aiwriter'] = 'aiwriter';
$route['portal/aiwriter/(.+)'] = 'aiwriter/$1';
$route['portal/amolood_Log-Tracker-Module-Perfect-crm'] = 'amolood_Log-Tracker-Module-Perfect-crm';
$route['portal/amolood_Log-Tracker-Module-Perfect-crm/(.+)'] = 'amolood_Log-Tracker-Module-Perfect-crm/$1';
$route['portal/appointly'] = 'appointly';
$route['portal/appointly/(.+)'] = 'appointly/$1';
$route['portal/aryadwiputra_perfect-crm-starter-module-template'] = 'aryadwiputra_perfect-crm-starter-module-template';
$route['portal/aryadwiputra_perfect-crm-starter-module-template/(.+)'] = 'aryadwiputra_perfect-crm-starter-module-template/$1';
$route['portal/assetcentral'] = 'assetcentral';
$route['portal/assetcentral/(.+)'] = 'assetcentral/$1';
$route['portal/assets'] = 'assets';
$route['portal/assets/(.+)'] = 'assets/$1';
$route['portal/automation_manager'] = 'automation_manager';
$route['portal/automation_manager/(.+)'] = 'automation_manager/$1';
$route['portal/backup'] = 'backup';
$route['portal/backup/(.+)'] = 'backup/$1';
$route['portal/cashfree'] = 'cashfree';
$route['portal/cashfree/(.+)'] = 'cashfree/$1';
$route['portal/chip'] = 'chip';
$route['portal/chip/(.+)'] = 'chip/$1';
$route['portal/commission'] = 'commission';
$route['portal/commission/(.+)'] = 'commission/$1';
$route['portal/custom_email_and_sms_notifications'] = 'custom_email_and_sms_notifications';
$route['portal/custom_email_and_sms_notifications/(.+)'] = 'custom_email_and_sms_notifications/$1';
$route['portal/customemailandsmsnotifications'] = 'customemailandsmsnotifications';
$route['portal/customemailandsmsnotifications/(.+)'] = 'customemailandsmsnotifications/$1';
$route['portal/customer_merge'] = 'customer_merge';
$route['portal/customer_merge/(.+)'] = 'customer_merge/$1';
$route['portal/customers_api'] = 'customers_api';
$route['portal/customers_api/(.+)'] = 'customers_api/$1';
$route['portal/custom_links'] = 'custom_links';
$route['portal/custom_links/(.+)'] = 'custom_links/$1';
$route['portal/deals'] = 'deals';
$route['portal/deals/(.+)'] = 'deals/$1';
$route['portal/einvoice'] = 'einvoice';
$route['portal/einvoice/(.+)'] = 'einvoice/$1';
$route['portal/elite_custom_js_css'] = 'elite_custom_js_css';
$route['portal/elite_custom_js_css/(.+)'] = 'elite_custom_js_css/$1';
$route['portal/exports'] = 'exports';
$route['portal/exports/(.+)'] = 'exports/$1';
$route['portal/extended_email'] = 'extended_email';
$route['portal/extended_email/(.+)'] = 'extended_email/$1';
$route['portal/facebook_leads_integration'] = 'facebook_leads_integration';
$route['portal/facebook_leads_integration/(.+)'] = 'facebook_leads_integration/$1';
$route['portal/facebookleadsintegration'] = 'facebookleadsintegration';
$route['portal/facebookleadsintegration/(.+)'] = 'facebookleadsintegration/$1';
$route['portal/feedback'] = 'feedback';
$route['portal/feedback/(.+)'] = 'feedback/$1';
$route['portal/file_sharing'] = 'file_sharing';
// Explicit overrides for the admin File_sharing controller's own methods.
// The module ships its own config/routes.php with a catch-all
// ($route['file_sharing/(:any)'] = 'file_sharing_public/index/$1';) meant for
// public share links. Without these, any admin method not already known to
// that internal routing table (manage, setting, sharing, reports, etc.) falls
// through to the public controller instead, which shows a generic
// "file or folder does not exist" message instead of the real admin page.
$route['portal/file_sharing/manage'] = 'file_sharing/manage';
$route['portal/file_sharing/file_sharing_media_connector'] = 'file_sharing/file_sharing_media_connector';
$route['portal/file_sharing/getDirectories/(.+)'] = 'file_sharing/getDirectories/$1';
$route['portal/file_sharing/setting'] = 'file_sharing/setting';
$route['portal/file_sharing/change_staff_permissions/(.+)'] = 'file_sharing/change_staff_permissions/$1';
$route['portal/file_sharing/new_folder'] = 'file_sharing/new_folder';
$route['portal/file_sharing/add_new_share'] = 'file_sharing/add_new_share';
$route['portal/file_sharing/add_new_config'] = 'file_sharing/add_new_config';
$route['portal/file_sharing/delete_config/(.+)'] = 'file_sharing/delete_config/$1';
$route['portal/file_sharing/update_field/(.+)'] = 'file_sharing/update_field/$1';
$route['portal/file_sharing/update_sharing_permission/(.+)'] = 'file_sharing/update_sharing_permission/$1';
$route['portal/file_sharing/update_setting'] = 'file_sharing/update_setting';
$route['portal/file_sharing/download_management'] = 'file_sharing/download_management';
$route['portal/file_sharing/sharing'] = 'file_sharing/sharing';
$route['portal/file_sharing/download_management_table'] = 'file_sharing/download_management_table';
$route['portal/file_sharing/sharing_table'] = 'file_sharing/sharing_table';
$route['portal/file_sharing/sharing_detail_table'] = 'file_sharing/sharing_detail_table';
$route['portal/file_sharing/reports'] = 'file_sharing/reports';
$route['portal/file_sharing/edit_sharing/(.+)'] = 'file_sharing/edit_sharing/$1';
$route['portal/file_sharing/delete_sharing/(.+)'] = 'file_sharing/delete_sharing/$1';
$route['portal/file_sharing/sharing_chart'] = 'file_sharing/sharing_chart';
$route['portal/file_sharing/download_chart'] = 'file_sharing/download_chart';
$route['portal/file_sharing/send_mail_to_public'] = 'file_sharing/send_mail_to_public';
$route['portal/file_sharing/(.+)'] = 'file_sharing/$1';
$route['portal/flexform'] = 'flexform';
$route['portal/flexform/(.+)'] = 'flexform/$1';
$route['portal/gladepay'] = 'gladepay';
$route['portal/gladepay/(.+)'] = 'gladepay/$1';
$route['portal/gladepay-NGN-payment-gateway-perfex-crm-master'] = 'gladepay-NGN-payment-gateway-perfex-crm-master';
$route['portal/gladepay-NGN-payment-gateway-perfex-crm-master/(.+)'] = 'gladepay-NGN-payment-gateway-perfex-crm-master/$1';
$route['portal/goals'] = 'goals';
$route['portal/goals/(.+)'] = 'goals/$1';
$route['portal/gocardless_gateway'] = 'gocardless_gateway';
$route['portal/gocardless_gateway/(.+)'] = 'gocardless_gateway/$1';
$route['portal/Granulr_perfect_calls'] = 'Granulr_perfect_calls';
$route['portal/Granulr_perfect_calls/(.+)'] = 'Granulr_perfect_calls/$1';
$route['portal/hrm'] = 'hrm';
$route['portal/hrm/(.+)'] = 'hrm/$1';
$route['portal/ideal'] = 'ideal';
$route['portal/ideal/(.+)'] = 'ideal/$1';
$route['portal/inject_javascript'] = 'inject_javascript';
$route['portal/inject_javascript/(.+)'] = 'inject_javascript/$1';
$route['portal/kabantickets'] = 'kabantickets';
$route['portal/kabantickets/(.+)'] = 'kabantickets/$1';
$route['portal/logistic'] = 'logistic';
$route['portal/logistic/(.+)'] = 'logistic/$1';
$route['portal/logtracker'] = 'logtracker';
$route['portal/logtracker/(.+)'] = 'logtracker/$1';
$route['portal/loyalty'] = 'loyalty';
$route['portal/loyalty/(.+)'] = 'loyalty/$1';
$route['portal/mailbox'] = 'mailbox';
$route['portal/mailbox/(.+)'] = 'mailbox/$1';
$route['portal/mailflow'] = 'mailflow';
$route['portal/mailflow/(.+)'] = 'mailflow/$1';
$route['portal/manufacturing'] = 'manufacturing';
$route['portal/manufacturing/(.+)'] = 'manufacturing/$1';
$route['portal/mention'] = 'mention';
$route['portal/mention/(.+)'] = 'mention/$1';
$route['portal/menu_setup'] = 'menu_setup';
$route['portal/menu_setup/(.+)'] = 'menu_setup/$1';
$route['portal/mercadopago_gateway'] = 'mercadopago_gateway';
$route['portal/mercadopago_gateway/(.+)'] = 'mercadopago_gateway/$1';
$route['portal/multi_page_wtl'] = 'multi_page_wtl';
$route['portal/multi_page_wtl/(.+)'] = 'multi_page_wtl/$1';
$route['portal/mypos_gateway'] = 'mypos_gateway';
$route['portal/mypos_gateway/(.+)'] = 'mypos_gateway/$1';
$route['portal/okr'] = 'okr';
$route['portal/okr/(.+)'] = 'okr/$1';
$route['portal/omni_sales'] = 'omni_sales';
$route['portal/omni_sales/(.+)'] = 'omni_sales/$1';
$route['portal/openai'] = 'openai';
$route['portal/openai/(.+)'] = 'openai/$1';
$route['portal/paystack'] = 'paystack';
$route['portal/paystack/(.+)'] = 'paystack/$1';
$route['portal/perfex_calls'] = 'perfex_calls';
$route['portal/perfex_calls/(.+)'] = 'perfex_calls/$1';
$route['portal/perfex_dark_theme'] = 'perfex_dark_theme';
$route['portal/perfex_dark_theme/(.+)'] = 'perfex_dark_theme/$1';
$route['portal/perfex_dashboard'] = 'perfex_dashboard';
$route['portal/perfex_dashboard/(.+)'] = 'perfex_dashboard/$1';
$route['portal/perfex_email_builder'] = 'perfex_email_builder';
$route['portal/perfex_email_builder/(.+)'] = 'perfex_email_builder/$1';
$route['portal/perfex_saas'] = 'perfex_saas';
// The background "deploy status" poll (perfexSaasAdminDeployService in
// admin.js) calls admin_url('perfex_saas/companies/deploy/...') without an
// 'admin/' segment, which 404s because Companies.php lives in the module's
// controllers/admin/ subdirectory. Route it there explicitly so the poll
// stops erroring and leaving a stuck loading indicator on every page.
$route['portal/perfex_saas/companies/deploy'] = 'perfex_saas/admin/companies/deploy';
$route['portal/perfex_saas/companies/deploy/(.*)'] = 'perfex_saas/admin/companies/deploy/$1';
$route['portal/perfex_saas/(.+)'] = 'perfex_saas/$1';
$route['portal/prchat'] = 'prchat';
$route['portal/prchat/(.+)'] = 'prchat/$1';
$route['portal/products'] = 'products';
$route['portal/products/(.+)'] = 'products/$1';
$route['portal/project_roadmap'] = 'project_roadmap';
$route['portal/project_roadmap/(.+)'] = 'project_roadmap/$1';
$route['portal/property'] = 'property';
$route['portal/property/(.+)'] = 'property/$1';
$route['portal/purchase'] = 'purchase';
$route['portal/purchase/(.+)'] = 'purchase/$1';
$route['portal/quick_customer'] = 'quick_customer';
$route['portal/quick_customer/(.+)'] = 'quick_customer/$1';
$route['portal/razorpay'] = 'razorpay';
$route['portal/razorpay/(.+)'] = 'razorpay/$1';
$route['portal/reminder'] = 'reminder';
$route['portal/reminder/(.+)'] = 'reminder/$1';
$route['portal/resource_workload'] = 'resource_workload';
$route['portal/resource_workload/(.+)'] = 'resource_workload/$1';
$route['portal/saas'] = 'saas';
$route['portal/saas/(.+)'] = 'saas/$1';
$route['portal/services'] = 'services';
$route['portal/services/(.+)'] = 'services/$1';
$route['portal/shopier'] = 'shopier';
$route['portal/shopier/(.+)'] = 'shopier/$1';
$route['portal/si_export_customer'] = 'si_export_customer';
$route['portal/si_export_customer/(.+)'] = 'si_export_customer/$1';
$route['portal/si_lead_filters'] = 'si_lead_filters';
$route['portal/si_lead_filters/(.+)'] = 'si_lead_filters/$1';
$route['portal/si_lead_followup'] = 'si_lead_followup';
$route['portal/si_lead_followup/(.+)'] = 'si_lead_followup/$1';
$route['portal/si_sms'] = 'si_sms';
$route['portal/si_sms/(.+)'] = 'si_sms/$1';
$route['portal/si_task_filters'] = 'si_task_filters';
$route['portal/si_task_filters/(.+)'] = 'si_task_filters/$1';
$route['portal/si_timesheet'] = 'si_timesheet';
$route['portal/si_timesheet/(.+)'] = 'si_timesheet/$1';
$route['portal/si_todo'] = 'si_todo';
$route['portal/si_todo/(.+)'] = 'si_todo/$1';
$route['portal/skrill_gateway'] = 'skrill_gateway';
$route['portal/skrill_gateway/(.+)'] = 'skrill_gateway/$1';
$route['portal/smsapi'] = 'smsapi';
$route['portal/smsapi/(.+)'] = 'smsapi/$1';
$route['portal/supplier'] = 'supplier';
$route['portal/supplier/(.+)'] = 'supplier/$1';
$route['portal/supportboard'] = 'supportboard';
$route['portal/supportboard/(.+)'] = 'supportboard/$1';
$route['portal/support_contact'] = 'support_contact';
$route['portal/support_contact/(.+)'] = 'support_contact/$1';
$route['portal/surveys'] = 'surveys';
$route['portal/surveys/(.+)'] = 'surveys/$1';
$route['portal/task_bookmarks'] = 'task_bookmarks';
$route['portal/task_bookmarks/(.+)'] = 'task_bookmarks/$1';
$route['portal/task_templates'] = 'task_templates';
$route['portal/task_templates/(.+)'] = 'task_templates/$1';
$route['portal/team_password'] = 'team_password';
$route['portal/team_password/(.+)'] = 'team_password/$1';
$route['portal/telegram_chat'] = 'telegram_chat';
$route['portal/telegram_chat/(.+)'] = 'telegram_chat/$1';
$route['portal/theme_style'] = 'theme_style';
$route['portal/theme_style/(.+)'] = 'theme_style/$1';
$route['portal/translations'] = 'translations';
$route['portal/translations/(.+)'] = 'translations/$1';
$route['portal/ultimate_dark_theme'] = 'ultimate_dark_theme';
$route['portal/ultimate_dark_theme/(.+)'] = 'ultimate_dark_theme/$1';
$route['portal/ultimate_purple_theme'] = 'ultimate_purple_theme';
$route['portal/ultimate_purple_theme/(.+)'] = 'ultimate_purple_theme/$1';
$route['portal/warranty_management'] = 'warranty_management';
$route['portal/warranty_management/(.+)'] = 'warranty_management/$1';
$route['portal/webhooks'] = 'webhooks';
$route['portal/webhooks/(.+)'] = 'webhooks/$1';
$route['portal/whatsapp_api'] = 'whatsapp_api';
$route['portal/whatsapp_api/(.+)'] = 'whatsapp_api/$1';
$route['portal/whatsapp_chat'] = 'whatsapp_chat';
$route['portal/whatsapp_chat/(.+)'] = 'whatsapp_chat/$1';
$route['portal/whiteboard'] = 'whiteboard';
$route['portal/whiteboard/(.+)'] = 'whiteboard/$1';
$route['portal/wiki'] = 'wiki';
$route['portal/wiki/(.+)'] = 'wiki/$1';
$route['portal/woocommerce'] = 'woocommerce';
$route['portal/woocommerce/(.+)'] = 'woocommerce/$1';
$route['portal/Yuri-Lima_Uatiz-Perfex-CRM-BroadCast'] = 'Yuri-Lima_Uatiz-Perfex-CRM-BroadCast';
$route['portal/Yuri-Lima_Uatiz-Perfex-CRM-BroadCast/(.+)'] = 'Yuri-Lima_Uatiz-Perfex-CRM-BroadCast/$1';
$route['portal/zoom_meetings'] = 'zoom_meetings';
$route['portal/zoom_meetings/(.+)'] = 'zoom_meetings/$1';
$route['portal/(.+)'] = 'admin/$1';

if (file_exists(APPPATH . 'config/my_routes.php')) {
    include_once(APPPATH . 'config/my_routes.php');
}
