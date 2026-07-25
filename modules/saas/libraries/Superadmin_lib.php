<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Superadmin_lib
{
    public $ci;
    public $link;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function installTenant($database, $tenant_password)
    {
        $version = $this->ci->app->get_current_db_version();
        $dbFile          = module_dir_path(SUPERADMIN_MODULE, 'sql/database_' . $version . '.sql');
        $host            = get_option('mysql_host');
        $port            = get_option('mysql_port');
        $user            = get_option('mysql_root_username');
        $pass            = $this->ci->encryption->decrypt(get_option('mysql_password'));
        $tenant_password = $this->ci->encryption->decrypt($tenant_password);

        $tenant_database = $database;
        $tenant_user     = $database;
        $tenant_port     = $port;

        switchDatabase('', $user, $pass, $host, $port);

        $this->ci->load->dbforge();
        $this->ci->load->dbutil();

        if ($this->ci->dbutil->database_exists($database)) {
            /* if we drop database then it will drop the existing database and install fresh setup each time when new contact will insert. */
            $this->ci->dbforge->drop_database($database);
        }

        // Create new DB
        $this->ci->dbforge->create_database($database);

        // Create Mysql User
        $this->ci->db->query('CREATE USER ' . $this->ci->db->escape($tenant_user) . '@' . $this->ci->db->escape($host) . ' IDENTIFIED BY ' . $this->ci->db->escape($tenant_password));

        // Give access to newly created user on newly created DB.
        $this->ci->db->query('GRANT ALL PRIVILEGES ON `' . $tenant_database . '`.* TO ' . $this->ci->db->escape($tenant_user) . '@' . $this->ci->db->escape($host) . ' WITH GRANT OPTION');

        $this->link = new mysqli($host, $tenant_user, $tenant_password, $tenant_database, $tenant_port);

        $this->link->begin_transaction();
        try {
            $this->ci->load->library(SUPERADMIN_MODULE . '/SqlScriptParser');
            $parser        = new SqlScriptParser();
            $sqlStatements = $parser->parse($dbFile);
            foreach ($sqlStatements as $statement) {
                $distilled = $parser->removeComments($statement);
                if (!empty($distilled)) {
                    $this->link->query($distilled);
                }
            }
            $this->link->commit();

            $this->ci->load->config('migration');
            $updateToVersion     = $this->ci->config->item('migration_version');

            switchDatabase($tenant_database, $tenant_user, $tenant_password, $host, $tenant_port);

            $this->ci->load->library('migration', [
                'migration_enabled'     => true,
                'migration_type'        => $this->ci->config->item('migration_type'),
                'migration_table'       => $this->ci->config->item('migration_table'),
                'migration_auto_latest' => $this->ci->config->item('migration_auto_latest'),
                'migration_version'     => $updateToVersion,
                'migration_path'        => $this->ci->config->item('migration_path'),
            ]);

            return true;
        } catch (mysqli_sql_exception $exception) {
            $this->link->rollback();
            throw $exception;

            return false;
        }
    }

    public function assignPlanToClientAndInstall($data, $userid)
    {
        $client      = $this->ci->clients_model->get($userid);
        $contact     = $this->ci->clients_model->get_contact($data['contactid']);
        $tenantsName = !empty($data['tenants_name']) ? $data['tenants_name'] : preg_replace('/\s+/', '', $client->company);
        $tenantsName = strtolower(preg_replace('/[^a-z\d]+$/', '', $tenantsName));
        $planDetails = getSaasPlans($data['tenant_plan']);
        $dbName      = TENANT_DB_PREFIX . url_title($tenantsName);
        $password    = $this->ci->encryption->encrypt(randomPassword());

        $clientPlanData = [
            'userid'              => $userid,
            'tenants_name'        => $tenantsName,
            'tenants_db_username' => $dbName,
            'tenants_db_password' => $password,
            'tenants_db'          => $dbName,
            'tenants_admin'       => $data['contactid'],
            'plan_id'             => $data['tenant_plan'],
            'plan_details_json'   => json_encode($planDetails),
            'trial_days'          => empty($planDetails['trial']) ? 0 : get_option('trial_period_days'),
            'trial_start_time'    => date('Y-m-d H:i:s'),
            'is_active'           => 1,
        ];

        $this->ci->load->model(SUPERADMIN_MODULE . '/superadmin_model');

        $is_exist = $this->ci->superadmin_model->getSingleRow('client_plan', [
            'userid'        => $userid,
            'tenants_name'  => $tenantsName,
            'tenants_admin' => $data['contactid'],
        ]);

        if (!empty($is_exist)) {
            return false;
        }

        $this->ci->superadmin_model->insertRow('client_plan', $clientPlanData);
        $log = _l('tenant_register', $userid) . ' ' . _l('contactId', $data['contactid']);

        if ('' == $log && isset($data['contactid'])) {
            $log = get_contact_full_name($data['contactid']);
        }

        $isStaff = null;
        if (!is_client_logged_in() && is_staff_logged_in()) {
            $isStaff = get_staff_user_id();
        }
        saas_activity_log($log, $isStaff);
        try {
            $installed = $this->installTenant($dbName, $clientPlanData['tenants_db_password']);
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        switchDatabase($clientPlanData['tenants_db'], $clientPlanData['tenants_db_username'], $this->ci->encryption->decrypt($password), get_option('mysql_host'), get_option('mysql_port'));

        if ($installed) {
            $installData = [
                'email'       => $contact->email,
                'firstname'   => $contact->firstname,
                'lastname'    => $contact->lastname,
                'password'    => $contact->password,
                'admin'       => 1,
                'active'      => 1,
                'datecreated' => date('Y-m-d H:i:s'),
            ];
            // insert the first contact as tenant admin
            $this->ci->db->insert(db_prefix() . 'staff', $installData);

            // insert row in module table for tenant_management module and make it active by default.
            if (!total_rows(db_prefix() . 'modules', ['module_name' => 'saas', 'installed_version' => '1.0.0', 'active' => 1])) {
                $this->ci->db->insert(db_prefix() . 'modules', ['module_name' => 'saas', 'installed_version' => '1.0.0', 'active' => 1]);

                // force enable tenant management module in branch
                add_option('superadmin_enabled', 1);

                // remove help menu
                add_option('show_help_on_setup_menu', 0);

                $this->ci->app_modules->activate('saas');
            }
        }

        switchDatabase();

        if (empty($clientPlanData['trial_days'])) {
            $this->createPlanInvoice($userid, $planDetails);
        }

        // we are not checking anything here onboard email will only send when branch is created
        send_mail_template('onboarding_email_template', SUPERADMIN_MODULE, $contact->email, $contact);
    }

    public function createPlanInvoice($clientID, $planDetails = null)
    {
        if (empty($planDetails)) {
            $clientPlan  = getClientPlan($clientID);
            $planDetails = getSaasPlans($clientPlan->plan_id); // Get current and updated plan details before invoicing
        }

        $billingShipping = $this->ci->clients_model->get_customer_billing_and_shipping_details($clientID);

        $clientData = $billingShipping[array_key_first($billingShipping)];

        $clientData['currency']  = $this->ci->clients_model->get_customer_default_currency($clientID);
        $clientData['currency']  = (!empty($planDetails['price_' . $clientData['currency']])) ? $clientData['currency'] : get_base_currency()->id;

        $duedate = _d(date('Y-m-d', strtotime('+7 DAY', strtotime(date('Y-m-d')))));
        if (0 != get_option('invoice_due_after')) {
            $duedate = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $invoiceData = [
            'clientid'                 => $clientID,
            'show_shipping_on_invoice' => 'on',
            'number'                   => str_pad(get_option('next_invoice_number'), get_option('number_padding_prefixes'), '0', \STR_PAD_LEFT),
            'date'                     => date('Y-m-d'),
            'duedate'                  => $duedate ?? date('Y-m-d'),
            'allowed_payment_modes'    => unserialize($planDetails['allowed_payment_modes']),
            'recurring'                => ($planDetails['custom_recurring']) ? 'custom' : $planDetails['recurring'],
            'repeat_every_custom'      => ($planDetails['custom_recurring']) ? $planDetails['custom_recurring'] : '',
            'repeat_type_custom'       => ($planDetails['custom_recurring']) ? $planDetails['recurring_type'] : '',
            'discount_type'            => '',
        ];

        $invoiceData = array_merge($clientData, $invoiceData);

        $invoiceItem = [
            'order'            => '1',
            'description'      => $planDetails['plan_name'],
            'long_description' => $planDetails['plan_description'],
            'qty'              => '1',
            'unit'             => '',
            'rate'             => $planDetails['price_' . $clientData['currency']] ?? $planDetails['price'],
            'taxname'          => unserialize($planDetails['taxes']),
        ];

        $invoiceData['newitems'][] = $invoiceItem;

        $total = $subtotal = 0;
        foreach ($invoiceData['newitems'] as $items) {
            $subtotal += $items['rate'] * $items['qty'];
            $total = $subtotal;
            if (!empty($items['taxname'])) {
                foreach ($items['taxname'] as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $total += ($items['rate'] * $items['qty']) / 100 * $tax_array[1];
                }
            }
        }
        $invoiceData['subtotal'] = $subtotal;
        $invoiceData['total']    = $total;

        $this->ci->load->model('invoices_model');
        $id = $this->ci->invoices_model->add($invoiceData);

        if ($id) {
            // Only Add log if trial plan is there
            if (empty($planDetails['trial'])) {
                $log =  _l('trial_plan_over', $id);
                saas_activity_log($log);
            }

            $this->ci->superadmin_model->updateRow('client_plan', ['is_invoiced' => $id], ['userid' => $clientID]);
        }
    }
}
