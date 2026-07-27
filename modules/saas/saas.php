<?php

defined('BASEPATH') || exit('No direct script access allowed');

/*
  Module Name: SaaS Module - Multitenancy support for Perfex CRM
  Module URI: https://codecanyon.net/item/saas-module-for-perfex-crm-multitenancy-support/45902802
  Description: Turn your Perfex CRM installation into a multitenant SaaS business/product
  Version: 1.0.1
  Requires at least: 3.0.*
*/

require_once __DIR__.'/vendor/autoload.php';

/*
 * Define module name
 * Module constant name must be in CAPITAL LETTERS
 */
define('SUPERADMIN_MODULE', 'saas');

// DB prefix for tenant's Database
define('TENANT_DB_PREFIX', 'tenant_');

define('SAAS_MODULE_UPLOAD_FOLDER', module_dir_path(SUPERADMIN_MODULE, 'uploads/'));

// Making company field required in settings if admin updated
update_option('company_is_required', 1);

// Get codeigniter instance
get_instance()->config->load('saas/config', false);

register_language_files(SUPERADMIN_MODULE, [SUPERADMIN_MODULE]);

/*
 * Register deactivation module hook
 */
register_deactivation_hook(SUPERADMIN_MODULE, 'saas_module_deactivation_hook');
function saas_module_deactivation_hook()
{
    update_option('superadmin_enabled', 0);

    $my_files_list = [
        VIEWPATH.'themes/perfex/views/my_register.php',
        VIEWPATH.'admin/modules/my_list.php',
    ];

    foreach ($my_files_list as $actual_path) {
        if (file_exists($actual_path)) {
            @unlink($actual_path);
        }
    }

    $backup_files_list = [
        APPPATH.'helpers/clients_helper.php',
        APPPATH.'helpers/files_helper.php',
        APPPATH.'helpers/staff_helper.php',
        APPPATH.'helpers/upload_helper.php',
    ];

    foreach ($backup_files_list as $actual_path) {
        if (file_exists($actual_path) && file_exists($actual_path.'.backup')) {
            @unlink($actual_path);
        }
        if (!file_exists($actual_path)) {
            rename($actual_path.'.backup', $actual_path);
        }
    }
}

register_deactivation_hook(SUPERADMIN_MODULE, 'saas_module_uninstallation_hook');
function saas_module_uninstallation_hook()
{
    $my_files_list = [
        APPPATH.'helpers/my_functions_helper.php',
    ];

    foreach ($my_files_list as $actual_path) {
        if (file_exists($actual_path)) {
            @unlink($actual_path);
        }
    }
}

get_instance()->load->helper([SUPERADMIN_MODULE.'/superadmin', SUPERADMIN_MODULE.'/tenant']);
get_instance()->load->library(SUPERADMIN_MODULE.'/superadmin_lib');
get_instance()->load->model(SUPERADMIN_MODULE.'/custom_model');

$tenant = getSubDomain();
define('IS_TENANT', (bool) $tenant);
if ($tenant) {
    define('TENANT_NAME', $tenant);
}

if (!IS_TENANT) {
    require_once __DIR__.'/includes/landloard_scripts/scripts.php';
    require_once __DIR__.'/includes/landloard_scripts/sidemenu.php';
    require_once __DIR__.'/includes/landloard_scripts/email_templates.php';
    require_once __DIR__.'/includes/landloard_scripts/client_scripts.php';

    // Include install.php only when activating saas module from superadmin side
    if (APP_DB_NAME == get_instance()->db->database) {
        register_activation_hook(SUPERADMIN_MODULE, 'superadmin_module_activation_hook');
        function superadmin_module_activation_hook()
        {
            require_once __DIR__.'/install.php';
        }
    }

    // Inject widget on dashboard for superadmin module
    hooks()->add_filter('get_dashboard_widgets', 'tenants_add_dashboard_widget');
    function tenants_add_dashboard_widget($widgets)
    {
        $new_widgets[] = [
            'path'      => SUPERADMIN_MODULE.'/widgets/superadmin-widget',
            'container' => 'top-12',
        ];

        return array_merge($new_widgets, $widgets);
    }

    // Add SaaS Pricing Menu In Customer Side
    hooks()->add_action('customers_navigation_start', 'add_saas_menu');
    function add_saas_menu()
    {
        if (!is_client_logged_in()) {
            if ('1' == get_option('tenants_landing')) {
                echo '<li class="customers-nav-item-contracts">
                <a href="'.site_url(SUPERADMIN_MODULE.'/pricing').'">'._l('pricing_menu').'</a>
                </li>';
            }
            echo '<li class="customers-nav-item-contracts">
            <a href="'.site_url(SUPERADMIN_MODULE.'/tenants/find').'">'._l('find_my_tenant').'</a>
            </li>';
        }
    }

    // Cron job logic
    hooks()->add_action('after_cron_run', function ($manually) {
        /* Create Plan invoice after trial ends */
        $clientPlans = getClientPlan();

        foreach ($clientPlans as $client) {
            if (!$client->is_invoiced) {
                $date = \Carbon\Carbon::create($client->trial_start_time);
                $date->addDays($client->trial_days);
                $diff = getRemainingDays($date);
                if ($diff >= 0) {
                    get_instance()->superadmin_lib->createPlanInvoice($client->userid);
                }
            }

            /* Before tenant plan expiration send tenant plan expiration reminder mail */
            /* Start */
            if (0 != $client->trial_days) {
                $plan_created_date = \Carbon\Carbon::create($client->trial_start_time);
                $plan_created_date->addDays($client->trial_days);
                $today          = \Carbon\Carbon::now();
                $remaining_days = $plan_created_date->diffInDays($today);

                if ($remaining_days < $client->trial_days) {
                    $contact = get_instance()->db->get_where(db_prefix().'contacts', ['userid' => $client->userid, 'is_primary' => '1'])->row();

                    send_mail_template('tenant_expiration_email', SUPERADMIN_MODULE, $client->userid, $contact->email);
                }
            }
            /* Over */
        }

        /* RUN TENANT'S CRON */
        get_instance()->load->library(SUPERADMIN_MODULE.'/Tenants_CronManagement_lib');
        get_instance()->tenants_cronmanagement_lib->init_tenants_cron();

        /* DELETE */
        $inactive_tenants_limit_in_days = get_option('inactive_tenants_limit');
        $inactive_tenants               =  get_instance()->db->get_where(db_prefix().'client_plan', ['is_active' => '0'])->result_array();
        if (!empty($inactive_tenants)) {
            foreach ($inactive_tenants as $tenant_data) {
                $inactive_date = \Carbon\Carbon::createFromTimestamp($tenant_data['inactive_date']);
                $difference    = $inactive_date->diffInDays(\Carbon\Carbon::now());

                // check if invative teanants delete limit days are crossed
                if ($inactive_tenants_limit_in_days <= $difference) {
                    /* Delete database and user */
                    $host = get_option('mysql_host');
                    $port = get_option('mysql_port');
                    $user = get_option('mysql_root_username');
                    $pass = get_instance()->encryption->decrypt(get_option('mysql_password'));

                    switchDatabase('', $user, $pass, $host, $port);

                    get_instance()->db->query('DROP DATABASE '.$tenant_data['tenants_db'].';');
                    get_instance()->db->query('DROP USER '.$tenant_data['tenants_db_username'].'@'.$host.';');

                    switchDatabase();

                    get_instance()->load->model('saas/superadmin_model');
                    get_instance()->superadmin_model->deleteRow('client_plan', ['id' => $tenant_data['id']]);

                    if (get_instance()->db->affected_rows() > 0) {
                        $log = _l('tenant_delete', $tenant_data['id']).' '._l('tenant_name', $tenant_data['tenants_name']);
                        saas_activity_log($log);
                    }

                    /* remove dir */
                    remove_tenant_directory(FCPATH.'uploads', $tenant_data['tenants_name']);
                }
            }
        }
    });

    hooks()->add_action('invoice_overdue_reminder_sent', function ($data) {
        get_instance()->load->model('invoices_model');
        get_instance()->load->model('saas/superadmin_model');
        $invoice = get_instance()->invoices_model->get($data['invoice_id']);

        $invoice_data['cycles']              = 0;
        $invoice_data['total_cycles']        = 0;
        $invoice_data['last_recurring_date'] = null;
        $first_invoice_id = $data['invoice_id'];
        if(!empty($invoice->is_recurring_from)){
            $first_invoice_id = $invoice->is_recurring_from;
        }
        get_instance()->superadmin_model->updateRow('invoices', $invoice_data, ['id' => $first_invoice_id]);

        get_instance()->superadmin_model->updateRow('client_plan', ['is_active'=>0], ['userid' => $invoice->clientid]);
    });

    hooks()->add_action('before_start_render_dashboard_content', 'display_server_settings_error');
    function display_server_settings_error()
    {
        if (!check_server_settings() && !empty(get_option('mysql_verification_message'))) {
            get_instance()->load->view(SUPERADMIN_MODULE.'/settings/server_settings_alert');
        }
    }

    hooks()->add_filter('before_settings_updated', 'before_setting_update');
    function before_setting_update($data)
    {
        $posted_data = $data['settings'];

        if (isset($posted_data['mysql_host'])) {
            $host         = trim($posted_data['mysql_host']);
            $port         = (int) trim($posted_data['mysql_port']);
            $user         = trim($posted_data['mysql_root_username']);
            $pass         = trim($posted_data['mysql_password']);
            $encrypt_pass = get_instance()->encryption->encrypt($pass);

            update_option('mysql_host', $host);
            update_option('mysql_port', $port);
            update_option('mysql_root_username', $user);
            update_option('mysql_password', $encrypt_pass);

            $keys_to_remove = [
                'mysql_host',
                'mysql_port',
                'mysql_root_username',
                'mysql_password',
            ];
            $data['settings'] = array_diff_key($data['settings'], array_flip($keys_to_remove));

            $test_name = 'test_'.time();

            try {
                $link = @new mysqli($host, $user, $pass, '', $port);

                if ($link->connect_errno) {
                    throw new Exception($link->connect_error);
                }

                $link->query("CREATE USER $test_name@$host IDENTIFIED BY 'testuser';");
                $link->query("CREATE DATABASE $test_name;");
                $link->query("DROP USER $test_name@$host;");
                $link->query("DROP DATABASE $test_name;");

                update_option('mysql_verification_message', '');
            } catch (Exception $e) {
                update_option('mysql_verification_message', $e->getMessage());
            }
            saas_activity_log(_l('mysql_server_settings_log'));
        }

        if (isset($posted_data['email_verification_require_after_tenant_register']) && '1' == $posted_data['email_verification_require_after_tenant_register']) {
            update_option('customers_register_require_confirmation', 0);
        }

        return $data;
    }

    function is_contains($str, array $arr)
    {
        foreach ($arr as $a) {
            if (false !== stripos($str, $a)) {
                return true;
            }
        }

        return false;
    }
}

if (IS_TENANT) {
    get_instance()->load->helper(SUPERADMIN_MODULE.'/tenant');
    require_once __DIR__.'/includes/tenants_scripts/limitations.php';
}

// Inject upload folder location for SaaS module
hooks()->add_filter('get_upload_path_by_type', 'product_upload_folder', 10, 2);
function product_upload_folder($path, $type)
{
    if ('saas_plan' == $type) {
        return SAAS_MODULE_UPLOAD_FOLDER;
    }

    return $path;
}

// Module upload restriction for tenants
hooks()->add_action('pre_upload_module', 'module_upload_restriction');
function module_upload_restriction($files)
{
    unset($files);
    set_alert('danger', _l('module_upload_restriction'));
    redirect(admin_url(), 'refresh');
}

hooks()->add_action('app_init', function () {
    // URL for mail: "/clients/open_ticket?ref=email"
    if ('clients/open_ticket' == get_instance()->uri->uri_string() && 'email' == get_instance()->input->get('ref')) {
        if (is_client_logged_in()) {
            set_alert('danger', 'create ticket');
        } else {
            set_alert('danger', 'Login and create ticket');
        }
    }

    foreach ($_FILES as $key => $file) {
        if (!is_array($file['name'])) {
            $extension = strtolower(pathinfo($file['name'], \PATHINFO_EXTENSION));
            if (in_array('.'.$extension, ['.php', '.php3', '.php5', '.sh', '.exe', '.bat'])) {
                $_FILES[$key]['tmp_name'] = '';
                set_alert('danger', _l('validation_extension_not_allowed'));
            }
        }
        if (is_array($file['name'])) {
            for ($i=0; $i < count($file['name']); ++$i) {
                $extension = strtolower(pathinfo($file['name'][$i], \PATHINFO_EXTENSION));
                if (in_array('.'.$extension, ['.php', '.php3', '.php5', '.sh', '.exe', '.bat'])) {
                    $_FILES[$key]['tmp_name'][$i] = '';
                    set_alert('danger', _l('validation_extension_not_allowed'));
                }
            }
        }
    }
});

if (!IS_TENANT) {
    hooks()->add_action('app_init', SUPERADMIN_MODULE.'_actLib');
    function saas_actLib()
    {
        $CI = &get_instance();
        $CI->load->library(SUPERADMIN_MODULE.'/Saas_aeiou');
        $envato_res = $CI->saas_aeiou->validatePurchase(SUPERADMIN_MODULE);
        if (!$envato_res) {
            set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
        }
    }

    hooks()->add_action('pre_activate_module', SUPERADMIN_MODULE.'_sidecheck');
    function saas_sidecheck($module_name)
    {
        if (SUPERADMIN_MODULE == $module_name['system_name']) {
            modules\saas\core\Apiinit::activate($module_name);
        }
    }

    hooks()->add_action('pre_deactivate_module', SUPERADMIN_MODULE.'_deregister');
    function saas_deregister($module_name)
    {
        if (SUPERADMIN_MODULE == $module_name['system_name']) {
            delete_option(SUPERADMIN_MODULE.'_verification_id');
            delete_option(SUPERADMIN_MODULE.'_last_verification');
            delete_option(SUPERADMIN_MODULE.'_product_token');
            delete_option(SUPERADMIN_MODULE.'_heartbeat');
        }
    }
    \modules\saas\core\Apiinit::ease_of_mind(SUPERADMIN_MODULE);
}

$upload_hooks = [
    'before_upload_estimate_request_attachment',
    'before_upload_newsfeed_attachment',
    'before_upload_project_attachment',
    'before_upload_contract_attachment',
    'before_upload_client_attachment',
    'before_upload_expense_attachment',
    'before_upload_ticket_attachment',
    'before_upload_company_logo_attachment',
    'before_upload_signature_image_attachment',
    'before_upload_favicon_attachment',
    'before_upload_staff_profile_image',
    'before_upload_contact_profile_image',
    'before_upload_project_discussion_comment_attachment',
];

foreach ($upload_hooks as $hook) {
    hooks()->add_action($hook, function ($data) {
        $disallowed_extensions = ['.php', '.php3', '.php5', '.sh', '.exe', '.bat'];
        foreach ($_FILES as $key => $file) {
            if (!is_array($file['name'])) {
                $extension = strtolower(pathinfo($file['name'], \PATHINFO_EXTENSION));
                if (in_array('.'.$extension, $disallowed_extensions)) {
                    $_FILES[$key]['tmp_name'] = '';
                    set_alert('danger', _l('validation_extension_not_allowed'));
                }
            }
            if (is_array($file['name'])) {
                for ($i=0; $i < count($file['name']); ++$i) {
                    $extension = strtolower(pathinfo($file['name'][$i], \PATHINFO_EXTENSION));
                    if (in_array('.'.$extension, $disallowed_extensions)) {
                        $_FILES[$key]['tmp_name'][$i] = '';
                        set_alert('danger', _l('validation_extension_not_allowed'));
                    }
                }
            }
        }
    });
}

hooks()->add_filter('before_settings_updated', 'check_file_settings');
function check_file_settings($data)
{
    get_instance()->load->helper(SUPERADMIN_MODULE.'/superadmin');

    if (isset($posted_data['ticket_attachments_file_extensions'])) {
        $value                                                  = trim($posted_data['ticket_attachments_file_extensions'] ?? '');
        $data['settings']['ticket_attachments_file_extensions'] = sanitize_file_extensions($value);
    }

    if (isset($posted_data['allowed_files'])) {
        $value                             = trim($posted_data['allowed_files'] ?? '');
        $data['settings']['allowed_files'] = sanitize_file_extensions($value);
    }

    return $data;
}

hooks()->add_action('pre_deactivate_module', 'checkIfUserIsTenant');
function checkIfUserIsTenant()
{
    if (IS_TENANT) {
        access_denied();
    }
}

hooks()->add_filter("module_saas_action_links", function($action_links) {
    if (!IS_TENANT) {
        $help_link_url = 'http://perfexsaas.themesic.com';
        array_unshift($action_links, '<a href="' . $help_link_url . '" class="text-primary bol" target="_blank">' . _l('help') . '</a>');

        $settings_link_url = admin_url('settings?group=saas');
        array_unshift($action_links, '<a href="' . $settings_link_url . '" class="text-danger bol">' . _l('settings') . '</a>');
    }

    return $action_links;
});
