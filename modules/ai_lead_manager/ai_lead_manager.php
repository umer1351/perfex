<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: LeadPilot AI
Description: Automate lead generation and follow-ups with Vapi.ai and Bland.ai integration.
Author: My Perfex CRM
Author URI: https://myperfexcrm.com
Version: 1.3.3
Requires at least: 2.3.*
*/

define('AI_LEAD_MANAGER_MODULE_NAME', 'ai_lead_manager');

/**
 * Load the module helper
 */
get_instance()->load->helper(AI_LEAD_MANAGER_MODULE_NAME . '/ai_lead_manager');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(AI_LEAD_MANAGER_MODULE_NAME, [AI_LEAD_MANAGER_MODULE_NAME]);

hooks()->add_filter('module_ai_lead_manager_action_links', 'ai_lead_manager_action_links');
hooks()->add_action('admin_init', 'ai_lead_manager_module_init_menu_items');
hooks()->add_action('mobile_app_init', 'ai_lead_manager_module_init_menu_items');
hooks()->add_action('admin_init', 'ai_lead_manager_app_admin_assets_added');
hooks()->add_filter('before_settings_updated', "ai_lead_manager_before_setting_updated");
hooks()->add_action('lead_created', "ai_lead_manager_lead_created");
hooks()->add_action('lead_status_changed', "ai_lead_manager_lead_status_changed");
hooks()->add_action('after_lead_lead_tabs', 'ai_lead_manager_after_lead_lead_tabs');
hooks()->add_action('after_lead_tabs_content', 'ai_lead_manager_after_lead_tabs_content');
hooks()->add_action('app_admin_footer', 'ai_lead_manager_app_admin_footer');

function ai_lead_manager_action_links(array $actions): array
{
    $actions[] = '<a href="' . admin_url('settings?group=ai_lead_manager') . '">' . _l('settings') . '</a>';
    return $actions;
}
/**
 * Registers permissions for LeadPilot AI.
 *
 * This function defines the capabilities related to call logs, including
 * view, create, edit, and delete permissions. It then registers these
 * capabilities using the 'register_staff_capabilities' function, if it
 * exists.
 */
function ai_lead_manager_call_logs_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities('alm_call_logs', $capabilities, _l('alm_call_logs'));
    }
}

/**
 * Initializes the menu items for LeadPilot AI.
 *
 * This function registers two menu items. The first menu item is added to the
 * sidebar menu, and it is visible only if the user has the 'view' permission for
 * call logs. This menu item links to the 'call_logs' page in the 'ai_lead_manager'
 * module.
 *
 * The second menu item is added to the settings page, and it is visible to all
 * users. This menu item links to the 'ai_lead_manager' page in the 'ai_lead_manager'
 * module.
 */
function ai_lead_manager_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('alm_call_logs', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('ai-call-logs', [
            'slug'     => 'ai-call-logs',
            'name'     => _l('alm_call_logs'),
            'position' => 5,
            'icon'     => 'fa fa-phone',
            'href'     => admin_url('ai_lead_manager/call_logs')
        ]);

        if (function_exists('add_mobile_app_menu_item')) {
            add_mobile_app_menu_item('ai_call_logs', [
                'title'      => 'AI Call Logs',
                'routerLink' => 'modules/ai_lead_manager/call-logs',
                'icon'       => 'lead',
                'position'   => 6,
            ]);
        }
    }

    $CI->app_tabs->add_settings_tab('ai_lead_manager', [
        'icon' => 'fa-solid fa-phone',
        'name' => 'LeadPilot AI',
        'position' => 100,
        'view' => 'ai_lead_manager/ai_lead_manager',
    ]);
}

/**
 * Adds the necessary JavaScript and CSS files for the LeadPilot AI
 * module to the page.
 *
 * This function is triggered by the 'app_admin_assets_added' hook. It adds the
 * 'main.js' and 'main.css' files from the module's 'assets' directory to the page.
 *
 * @return void
 */
function ai_lead_manager_app_admin_assets_added()
{
    $CI = &get_instance();
    $CI->app_scripts->add(AI_LEAD_MANAGER_MODULE_NAME . '-main-js', base_url('modules/' . AI_LEAD_MANAGER_MODULE_NAME . '/assets/main.js'));
    $CI->app_css->add(AI_LEAD_MANAGER_MODULE_NAME . '-main-css', base_url('modules/' . AI_LEAD_MANAGER_MODULE_NAME . '/assets/main.css'));
}

/**
 * Modifies the settings data before saving it based on the selected voice assistant.
 *
 * This function is triggered by the 'before_settings_updated' hook. It checks the
 * selected voice assistant and calls the corresponding function to modify the
 * settings data before saving it. If the selected voice assistant is 'vapi_ai', it
 * calls the 'before_setting_update_vapi_ai' function. If the selected voice
 * assistant is 'bland_ai', it calls the 'before_setting_update_bland_ai'
 * function.
 *
 * @param array $data An associative array containing the settings data to be
 *                    saved.
 * @return array The modified settings data.
 */
function ai_lead_manager_before_setting_updated($data)
{
    if (isset($data['settings']['alm_voice_assistant'])) {
        $data['settings']['vapi_ai_tools_inbound'] = json_encode(($data['settings']['vapi_ai_tools_inbound'] ?? []));
        $data['settings']['vapi_ai_tools_outbound'] = json_encode(($data['settings']['vapi_ai_tools_outbound'] ?? []));

        $data['settings']['vapi_ai_knowledgebase_inbound'] = json_encode(($data['settings']['vapi_ai_knowledgebase_inbound'] ?? []));
        $data['settings']['vapi_ai_knowledgebase_outbound'] = json_encode(($data['settings']['vapi_ai_knowledgebase_outbound'] ?? []));

        $data['settings']['bland_ai_knowledgebase_inbound'] = json_encode(($data['settings']['bland_ai_knowledgebase_inbound'] ?? []));
        $data['settings']['bland_ai_knowledgebase_outbound'] = json_encode(($data['settings']['bland_ai_knowledgebase_outbound'] ?? []));

        $alm_voice_assistant = $data['settings']['alm_voice_assistant'];

        if ($alm_voice_assistant == 'vapi_ai') {
            return before_setting_update_vapi_ai($data);
        } else if ($alm_voice_assistant == 'bland_ai') {
            return before_setting_update_bland_ai($data);
        }
    }

    return $data;
}

/**
 * Modifies the settings data before saving it based on the selected voice assistant.
 *
 * This function is triggered by the 'before_settings_updated' hook when the
 * selected voice assistant is 'bland_ai'. It checks if the Bland AI API key has
 * changed and, if so, updates the API key and creates a new encrypted key. It
 * also updates the list of inbound numbers and sets the call settings.
 *
 * @param array $data An associative array containing the settings data to be
 *                    saved.
 * @return array The modified settings data.
 */
function before_setting_update_bland_ai($data)
{
    $CI = &get_instance();

    $bland_ai_api_key = get_option('bland_ai_api_key');
    $bland_ai_encrypted_key = get_option('bland_ai_encrypted_key');

    $twilio_account_number = get_option('twilio_account_number');

    if (
        !empty($data["settings"]["twilio_account_sid"]) &&
        !empty($data["settings"]["twilio_auth_token"]) &&
        !empty($data["settings"]["bland_ai_api_key"])
    ) {
        if (empty($bland_ai_api_key) || $data["settings"]["bland_ai_api_key"] != $bland_ai_api_key) {
            update_option('bland_ai_api_key', $data["settings"]["bland_ai_api_key"]);

            $bland_ai_encrypted_key = '';
            $twilio_account_number = '';
        }

        $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/Bland_ai');
        $bland_ai = new Bland_ai();
        $bland_ai->set_auth_token($data["settings"]["bland_ai_api_key"]);

        if (empty($bland_ai_encrypted_key)) {
            $create_encrypted_key = $bland_ai->create_encrypted_key($data["settings"]["twilio_account_sid"], $data["settings"]["twilio_auth_token"]);

            if ($create_encrypted_key['status'] == 'success') {
                update_option('bland_ai_encrypted_key', $create_encrypted_key['encrypted_key']);

                $bland_ai->set_encrypted_key($create_encrypted_key['encrypted_key']);
            } else {
                set_alert('warning', $create_encrypted_key['message']);
            }
        }

        if (empty($twilio_account_number) && !empty($data["settings"]["twilio_account_number"])) {
            $response = $bland_ai->upload_inbound_numbers([
                $data["settings"]["twilio_account_number"]
            ]);
        }

        if (!empty($data["settings"]["bland_ai_agent_voice"]) && $data["settings"]["bland_ai_company_name"] != "" && $data["settings"]["bland_ai_company_speciality"] != "") {
            $first_sentence = $data["settings"]["alm_first_sentence"];
            $first_sentence = str_replace('{company_name}', get_option('invoice_company_name'), $first_sentence);
            $first_sentence = str_replace('{address}', get_option('invoice_company_address'), $first_sentence);
            $first_sentence = str_replace('{city}', get_option('invoice_company_city'), $first_sentence);
            $first_sentence = str_replace('{state}', get_option('invoice_company_state'), $first_sentence);
            $first_sentence = str_replace('{zip}', get_option('invoice_company_zip'), $first_sentence);
            $first_sentence = str_replace('{country}', get_option('invoice_company_country'), $first_sentence);

            $call_settings = [
                "model" => "enhanced",
                'max_duration' => !empty($data["settings"]["bland_ai_max_duration"]) ? $data["settings"]["bland_ai_max_duration"] : 12,
                "first_sentence" => $first_sentence,
                "voice" => $data["settings"]["bland_ai_agent_voice"],
                'temperature' => !empty($data["settings"]["bland_ai_temperature"]) ? $data["settings"]["bland_ai_temperature"] : 0.5,
                "summary_prompt" => "Provide a complete summary of the call.",
                "wait_for_greeting" => false,
                "webhook" => admin_url('ai_lead_manager/webhooks/bland_ai'),
                'webhook_events' => ['call', 'webhook'],
                'record' => true,
                "prompt" => $data["settings"]["alm_system_prompt"],
                "analysis_schema" => [
                    "Name" => "string",
                    "Email" => "string",
                    "Phone" => "string",
                    "Address" => "string",
                    "City" => "string",
                    "State" => "string",
                    "Country" => "string",
                    "Zip Code" => "string",
                    "Description" => "string"
                ],
                'tools' => json_decode($data['settings']['bland_ai_knowledgebase_inbound']),
                "encrypted_key" => $bland_ai->get_encrypted_key()
            ];

            $response = $bland_ai->update_inbound_details($data["settings"]["twilio_account_number"], $call_settings);
        }
    }
    return $data;
}

function before_setting_update_vapi_ai($data)
{
    $CI = &get_instance();

    $vapi_ai_api_key = get_option('vapi_ai_api_key');
    $vapi_ai_assistant_id = get_option('vapi_ai_assistant_id');

    if (
        !empty($data["settings"]["twilio_account_sid"]) &&
        !empty($data["settings"]["twilio_auth_token"]) &&
        !empty($data["settings"]["vapi_ai_api_key"])
    ) {
        if (empty($vapi_ai_api_key) || $data["settings"]["vapi_ai_api_key"] != $vapi_ai_api_key) {
            update_option('vapi_ai_api_key', $data["settings"]["vapi_ai_api_key"]);
        }

        $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/Vapi_ai');
        $vapi_ai = new Vapi_ai();

        if (!empty($data["settings"]["vapi_ai_agent_voice"]) && $data["settings"]["alm_first_sentence"] != "" && $data["settings"]["alm_system_prompt"] != "") {
            $first_sentence = $data["settings"]["alm_first_sentence"];
            $first_sentence = str_replace('{company_name}', get_option('invoice_company_name'), $first_sentence);
            $first_sentence = str_replace('{address}', get_option('invoice_company_address'), $first_sentence);
            $first_sentence = str_replace('{city}', get_option('invoice_company_city'), $first_sentence);
            $first_sentence = str_replace('{state}', get_option('invoice_company_state'), $first_sentence);
            $first_sentence = str_replace('{zip}', get_option('invoice_company_zip'), $first_sentence);
            $first_sentence = str_replace('{country}', get_option('invoice_company_country'), $first_sentence);


            $assistant_settings = [
                "name" => 'AI Lead Manager',
                "firstMessage" => $first_sentence,
                "transcriber" => [
                    "provider" => "deepgram",
                    "model" => "nova-2",
                    "language" => "en-US",
                ],
                "backchannelingEnabled" => $data['settings']['back_channeling_enabled'] == 1 ? true : false,
                "voice" => [
                    "fillerInjectionEnabled" => $data['settings']['filler_injection_enabled'] == 1 ? true : false,
                    "provider" => $data['settings']['vapi_ai_voice_provider'],
                    "voiceId" => ($data['settings']['vapi_ai_is_custom_voice_id'] == 1 ? $data['settings']['vapi_ai_agent_voice_id'] : $data['settings']['vapi_ai_agent_voice'])
                ],
                "model" => [
                    "provider" => "openai",
                    "model" => "gpt-4o",
                    "maxTokens" => (int) $data['settings']['vapi_ai_max_tokens'],
                    "temperature" =>  (int) $data['settings']['vapi_ai_temperature'],
                    "emotionRecognitionEnabled" => $data['settings']['vapi_ai_detect_emotions'] == 1 ? true : false,
                    "messages" => [
                        ["role" => "system", "content" => $data["settings"]["alm_system_prompt"] . '\n\n DateTime Now: ' . date('Y-m-d H:i:s')]
                    ]
                ],
                'dialKeypadFunctionEnabled' => $data['settings']['dial_keypad_function_enabled'] == 1 ? true : false,
                'endCallFunctionEnabled' => $data['settings']['end_call_function_enabled'] == 1 ? true : false,
                "analysisPlan" => [
                    'successEvaluationPrompt' => "Evaluate the call's success based on the customer's satisfaction, the completeness of the information provided, and whether any appointments were scheduled.",
                    'structuredDataSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'customer_name' => [
                                'type' => 'string',
                                'description' => 'The name of the customer'
                            ],
                            'customer_email' => [
                                'type' => 'string',
                                'description' => 'The email address of the customer'
                            ],
                            'customer_phonenumber' => [
                                'type' => 'string',
                                'description' => 'The phone number of the customer'
                            ],
                            'customer_address' => [
                                'type' => 'string',
                                'description' => 'The address of the customer'
                            ],
                            'customer_city' => [
                                'type' => 'string',
                                'description' => 'The city of the customer'
                            ],
                            'customer_state' => [
                                'type' => 'string',
                                'description' => 'The state of the customer'
                            ],
                            'customer_country' => [
                                'type' => 'string',
                                'description' => 'The country of the customer'
                            ],
                            'customer_zip' => [
                                'type' => 'integer',
                                'description' => 'The zip code of the customer'
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'A description of the customer'
                            ]
                        ],
                        'required' => ['customer_name', 'customer_email', 'customer_phonenumber', 'description']
                    ]
                ],
                "endCallPhrases" => ["Have a great day", "Goodbye", "Thank you for your time"],
                'serverUrl' => admin_url('ai_lead_manager/webhooks/vapi_ai_inbound'),
                // 'serverUrl' => "https://webhook.site/70da4ff5-b5b3-4f24-b428-1fe8c3576478",
                'serverMessages' => ['end-of-call-report'],
                'maxDurationSeconds' => (int) (get_option('vapi_ai_max_duration') != 0 ? get_option('vapi_ai_max_duration') : 300),
                'startSpeakingPlan' => [
                    'smartEndpointingEnabled' => true
                ],
                'stopSpeakingPlan' => [
                    'numWords' => 2,
                    'voiceSeconds' => 0.2,
                    'backoffSeconds' => 1
                ]
            ];

            if (!empty($data['settings']['vapi_ai_tools_inbound'])) {
                $assistant_settings['model']['toolIds'] = json_decode($data['settings']['vapi_ai_tools_inbound']);
            }

            if (!empty($data['settings']['vapi_ai_knowledgebase_inbound'])) {
                $assistant_settings['model']['knowledgeBase'] = [
                    'topK' => 2,
                    'fileIds' => json_decode($data['settings']['vapi_ai_knowledgebase_inbound']),
                    'provider' => 'canonical'
                ];
            }


            if (empty($vapi_ai_assistant_id)) {
                $response = $vapi_ai->create_assistant($assistant_settings);
                if (isset($response['id'])) {
                    update_option('vapi_ai_assistant_id', $response['id']);
                }
            } else {
                $response = $vapi_ai->get_assistant_by_id($vapi_ai_assistant_id);
                if (isset($response['id'])) {
                    $response = $vapi_ai->update_assistant($vapi_ai_assistant_id, $assistant_settings);
                } else {
                    $response = $vapi_ai->create_assistant($assistant_settings);
                    if (isset($response['id'])) {
                        update_option('vapi_ai_assistant_id', $response['id']);
                    }
                }
            }

            // print_r($response);
            // die();
            if (isset($response['id'])) {
                $assistant_id = $response['id'];
                if (!empty($data["settings"]["twilio_account_number"])) {
                    $response = $vapi_ai->create_phone_number([
                        'provider' => 'twilio',
                        'number' => $data["settings"]["twilio_account_number"],
                        'twilioAccountSid' => $data["settings"]["twilio_account_sid"],
                        'twilioAuthToken' => $data["settings"]["twilio_auth_token"],
                        'assistantId' => $assistant_id,
                        'name' => 'LeadPilot AI'
                    ]);

                    if (isset($response['id'])) {
                        update_option('vapi_ai_phone_number_id', $response['id']);
                    } else {
                        $phone_numbers = $vapi_ai->get_phone_numbers();
                        foreach ($phone_numbers as $number) {
                            if ($number['provider'] == 'twilio' && $number['number'] == $data["settings"]["twilio_account_number"]) {
                                update_option('vapi_ai_phone_number_id', $number['id']);
                                $response = $vapi_ai->update_phone_number($number['id'], ['assistantId' => $assistant_id]);
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    return $data;
}

/**
 * Handles the event when a lead is created.
 *
 * This function is triggered when a lead is created and it checks the voice
 * assistant setting. If the setting is 'bland_ai', it makes a call to the lead
 * using the Bland AI service.
 *
 * @param int $id The ID of the lead that was created.
 *
 * @return void
 */
function ai_lead_manager_lead_created($id)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $lead = $CI->leads_model->get($id);

    $alm_voice_assistant = get_option('alm_voice_assistant');

    if ($alm_voice_assistant == 'vapi_ai') {
        alm_vapi_ai_make_call($lead);
    } else if ($alm_voice_assistant == 'bland_ai') {
        alm_bland_ai_make_call($lead);
    }
}

/**
 * Handles the event when a lead's status is changed.
 *
 * This function is triggered when a lead's status is updated. It checks the
 * voice assistant setting and, if set to 'bland_ai', prepares and sends a
 * request to the Bland AI service to make a call to the lead. The function
 * retrieves and formats lead data and custom fields to be sent as part of the
 * request. It also updates the first sentence with company-related details and
 * configures various call settings.
 *
 * @param array $data An associative array containing information about the lead,
 *                    including the lead ID.
 */
function ai_lead_manager_lead_status_changed($data)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $lead = $CI->leads_model->get($data['lead_id']);

    $alm_voice_assistant = get_option('alm_voice_assistant');

    if ($alm_voice_assistant == 'vapi_ai') {
        alm_vapi_ai_make_call($lead);
    } else if ($alm_voice_assistant == 'bland_ai') {
        alm_bland_ai_make_call($lead);
    }
}

/**
 * Adds a new tab to the lead view containing call logs related to the lead.
 *
 * This function is triggered by the 'after_lead_lead_tabs' hook and adds a new
 * tab to the lead view, which contains a data table with call logs related to
 * the lead. The data table is initialized using the 'initDataTable' function and
 * retrieves data from the 'call_log_relations' function in the 'ai_lead_manager'
 * controller.
 *
 * @param object $lead A lead object containing information about the lead.
 */
function ai_lead_manager_after_lead_lead_tabs($lead)
{
    if ($lead) {
        echo '<li role="presentation">
            <a 
                href="#tab_ai_call_logs_leads" 
                onclick="initDataTable(\'.table-alm_call_logs-lead\', admin_url + \'ai_lead_manager/call_logs/call_log_relations/\' + ' . $lead->id . ' + \'/lead\',\'undefined\', \'undefined\',\'undefined\',[6,\'desc\']);" 
                aria-controls="tab_ai_call_logs_leads" 
                role="tab" 
                data-toggle="tab"
            >' . _l('alm_call_logs') . '
            </a>
        </li>';
    }
}


/**
 * Adds content to the lead view tab containing call logs related to the lead.
 *
 * This function is triggered by the 'after_lead_tabs_content' hook and adds
 * content to the lead view tab, which contains a data table with call logs
 * related to the lead. The data table is initialized using the 'initDataTable'
 * function and retrieves data from the 'call_log_relations' function in the
 * 'call_logs' controller.
 *
 * @param object $lead A lead object containing information about the lead.
 */
function ai_lead_manager_after_lead_tabs_content($lead)
{
    if ($lead) {
        $CI = &get_instance();
        $CI->load->view(AI_LEAD_MANAGER_MODULE_NAME . '/lead/call_logs_tab_content');
    }
}

/**
 * Register activation module hook
 */
register_activation_hook(AI_LEAD_MANAGER_MODULE_NAME, 'ai_lead_manager_module_activation_hook');
function ai_lead_manager_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}


/**
 * Adds an empty div to the admin footer with the id "alm_call_log_div".
 * This div is used by the 'init_alm_call_log_modal' function to display a
 * modal with call log details when a link is clicked.
 */
function ai_lead_manager_app_admin_footer()
{
    echo '<div id="alm_call_log_div"></div>';

    echo '<div class="modal fade" id="vapi_ai_kwnowledgebase_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    ' . form_open(admin_url('ai_lead_manager/add_knowledgebase/vapi'), ['method' => 'post', 'enctype' => 'multipart/form-data']) . '
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title" id="myModalLabel">Add knowledgebase</h4>
                    </div>
                    <div class="modal-body">
                        ' . render_input('file', 'file', '', 'file') . '
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                    ' . form_close() . '
                </div>
            </div>
        </div>
    ';
    echo '<div class="modal fade" id="bland_ai_kwnowledgebase_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    ' . form_open(admin_url('ai_lead_manager/add_knowledgebase/bland'), ['method' => 'post', 'enctype' => 'multipart/form-data']) . '
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title" id="myModalLabel">Add knowledgebase</h4>
                    </div>
                    <div class="modal-body">
                        ' . render_input('name', 'name') . '
                        ' . render_textarea('description', 'description') . '
                        ' . render_input('file', 'file', '', 'file') . '
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                    ' . form_close() . '
                </div>
            </div>
        </div>
    ';
}

hooks()->add_filter('leads_table_columns', 'ai_lead_manager_leads_table_columns');
function ai_lead_manager_leads_table_columns($columns)
{
    array_splice($columns, 9, 0, [
        [
            'name'     => _l('leads_dt_last_call_ended_reason'),
            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-last_call_ended_reason']
        ]
    ]);
    return $columns;
}

hooks()->add_filter('leads_table_sql_columns', 'ai_lead_manager_leads_table_sql_columns');
function ai_lead_manager_leads_table_sql_columns($columns)
{
    array_splice($columns, 9, 0, '(SELECT call_ended_by FROM ' . db_prefix() . 'alm_call_logs WHERE rel_type = "lead" AND rel_id = ' . db_prefix() . 'leads.id ORDER BY id DESC LIMIT 1) as last_call_ended_by');
    return $columns;
}

hooks()->add_filter('leads_table_row_data', 'ai_lead_manager_leads_table_row_data', 10, 2);
function ai_lead_manager_leads_table_row_data($row, $lead)
{
    array_splice($row, 9, 0, alm_format_call_log_status($lead['last_call_ended_by']));
    return $row;
}

hooks()->add_action('app_init', 'ai_lead_manager_actLib');
function ai_lead_manager_actLib()
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/Envatoapi');
    $envato_res = $CI->envatoapi->validatePurchase(AI_LEAD_MANAGER_MODULE_NAME);
    if (!$envato_res) {
        set_alert('danger', "One of your modules failed its verification and got deactivated. Please reactivate or contact support.");
        redirect(admin_url('modules'));
    }
}

hooks()->add_action('pre_activate_module', 'ai_lead_manager_sidecheck');
function ai_lead_manager_sidecheck($module_name)
{
    if ($module_name['system_name'] == AI_LEAD_MANAGER_MODULE_NAME) {
        if (!option_exists(AI_LEAD_MANAGER_MODULE_NAME . '_verified') && empty(get_option(AI_LEAD_MANAGER_MODULE_NAME . '_verified')) && !option_exists(AI_LEAD_MANAGER_MODULE_NAME . '_verification_id') && empty(get_option(AI_LEAD_MANAGER_MODULE_NAME . '_verification_id'))) {
            $CI = &get_instance();
            $data['submit_url'] = $module_name['system_name'] . '/env_ver/activate';
            $data['original_url'] = admin_url('modules/activate/' . AI_LEAD_MANAGER_MODULE_NAME);
            $data['module_name'] = AI_LEAD_MANAGER_MODULE_NAME;
            $data['title']       = $module_name['headers']['module_name'] . " module activation";
            echo $CI->load->view($module_name['system_name'] . '/activate', $data, true);
            exit();
        }
    }
}

hooks()->add_action('pre_deactivate_module', AI_LEAD_MANAGER_MODULE_NAME . '_deregister');
function ai_lead_manager_deregister($module_name)
{
    if ($module_name['system_name'] == AI_LEAD_MANAGER_MODULE_NAME) {
        $CI = &get_instance();
        $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/Envatoapi');
        $CI->envatoapi->deactivateLicense(AI_LEAD_MANAGER_MODULE_NAME);
        delete_option(AI_LEAD_MANAGER_MODULE_NAME . "_verified");
        delete_option(AI_LEAD_MANAGER_MODULE_NAME . "_verification_id");
        delete_option(AI_LEAD_MANAGER_MODULE_NAME . "_last_verification");
        delete_option(AI_LEAD_MANAGER_MODULE_NAME . "_expire_verification");
        if (file_exists(__DIR__ . "/config/token.php")) {
            unlink(__DIR__ . "/config/token.php");
        }
    }
}

hooks()->add_action('register_custom_api_routes', function () {
    register_api_route('ai_lead_manager', 'call_logs', 'GET', function () {
        $CI = &get_instance();
        $CI->load->model(AI_LEAD_MANAGER_MODULE_NAME . '/Call_logs_model', 'call_logs');

        if (!empty($CI->input->get('filters'))) {
            $filters = json_decode($CI->input->get('filters'), true);

            if (!empty($filters['rel_type']) && !empty($filters['rel_id'])) {
                $CI->db->where('rel_type', $filters['rel_type']);
                $CI->db->where('rel_id', $filters['rel_id']);
            }
        }

        $call_logs = $CI->call_logs->get('', ['ai_provider' => get_option('alm_voice_assistant')]);
        foreach ($call_logs as &$call_log) {
            $call_log['transcripts'] = json_decode($call_log['transcripts']);
            $call_log['extra_information'] = json_decode($call_log['extra_information']);
        }

        api_response($call_logs);
    });

    // register_api_route('ai_lead_manager', 'create_example', 'POST', function () {
    //     $CI = &get_instance();
    //     $data = json_decode(file_get_contents('php://input'), true);
    //     api_response(['message' => 'POST request received', 'data' => $data]);
    // });

    // register_api_route('ai_lead_manager', 'update_example', 'PUT', function () {
    //     api_response(['message' => 'PUT request received']);
    // });

    // register_api_route('ai_lead_manager', 'delete_example', 'DELETE', function () {
    //     api_response(['message' => 'DELETE request received']);
    // });
});
