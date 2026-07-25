<?php

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
 * @param object $lead An associative object containing information about the lead,
 *                    including the lead ID.
 */
function alm_bland_ai_make_call($lead)
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/Bland_ai');
    $bland_ai = new Bland_ai();

    if (!empty($lead->phonenumber) && $lead->status == get_option('alm_lead_status')) {

        $request_data = [
            'customer_name' => $lead->name,
            'customer_email' => $lead->email,
            'customer_address' => $lead->address,
            'customer_city' => $lead->city,
            'customer_state' => $lead->state,
            'customer_zip' => $lead->zip,
            'customer_country' => get_country_name($lead->country),
            'last_contacted' => $lead->lastcontact,
            'lead_description' => $lead->description,
            'company_name' => get_option('invoice_company_name'),
            'company_address' => get_option('invoice_company_address'),
            'company_phone' => get_option('invoice_company_phone'),
            'datetime_now' => date('Y-m-d H:i:s')
        ];

        $lead_custom_fields = get_custom_fields($lead->id, ['active' => 1]);
        foreach ($lead_custom_fields as $lead_custom_field) {
            $value = get_custom_field_value($lead->id, $lead_custom_field['id'], 'leads');
            if (!empty($value)) {
                $request_data[$lead_custom_field['name']] = $value;
            }
        }

        $first_sentence = get_option('alm_first_sentence_outbound');
        $first_sentence = str_replace('{company_name}', get_option('invoice_company_name'), $first_sentence);
        $first_sentence = str_replace('{address}', get_option('invoice_company_address'), $first_sentence);
        $first_sentence = str_replace('{city}', get_option('invoice_company_city'), $first_sentence);
        $first_sentence = str_replace('{state}', get_option('invoice_company_state'), $first_sentence);
        $first_sentence = str_replace('{zip}', get_option('invoice_company_zip'), $first_sentence);
        $first_sentence = str_replace('{country}', get_option('invoice_company_country'), $first_sentence);

        $response = $bland_ai->make_call($lead->phonenumber, get_option('alm_system_prompt_outbound'), [
            'model' => 'enhanced',
            'max_duration' => !empty(get_option('bland_ai_max_duration')) ? get_option('bland_ai_max_duration') : 12,
            'first_sentence' => $first_sentence,
            'voice' => get_option('bland_ai_agent_voice'),
            'temperature' => get_option('bland_ai_temperature') != 0 ? get_option('bland_ai_temperature') : 0.5,
            'summary_prompt' => 'Provide a complete summary of the call.',
            'wait_for_greeting' => false,
            'webhook' => admin_url('ai_lead_manager/webhooks/bland_ai'),
            'webhook_events' => ['call', 'webhook'],
            'record' => true,
            'metadata' => [
                'rel_id' => $lead->id,
                'rel_type' => 'lead',
                'staff_id' => get_staff_user_id()
            ],
            'request_data' => $request_data,
            'tools' => json_decode(get_option('bland_ai_knowledgebase_outbound')),
            'encrypted_key' => get_option('bland_ai_encrypted_key'),
        ], get_option('bland_ai_encrypted_key'));
    }
}

/**
 * Handles the event when a lead's status is changed.
 *
 * This function is triggered when a lead's status is updated. It checks the
 * voice assistant setting and, if set to 'vapi_ai', prepares and sends a
 * request to the Bland AI service to make a call to the lead. The function
 * retrieves and formats lead data and custom fields to be sent as part of the
 * request. It also updates the first sentence with company-related details and
 * configures various call settings.
 *
 * @param object $lead An associative object containing information about the lead,
 *                    including the lead ID.
 */
function alm_vapi_ai_make_call($lead)
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/Vapi_ai');
    $vapi_ai = new Vapi_ai();

    if (!empty($lead->phonenumber) && $lead->status == get_option('alm_lead_status')) {

        $request_data = [
            'customer_name' => $lead->name,
            'customer_email' => $lead->email,
            'customer_address' => $lead->address,
            'customer_city' => $lead->city,
            'customer_state' => $lead->state,
            'customer_zip' => $lead->zip,
            'customer_country' => get_country_name($lead->country),
            'last_contacted' => $lead->lastcontact,
            'lead_description' => $lead->description,
            'company_name' => get_option('invoice_company_name'),
            'company_address' => get_option('invoice_company_address'),
            'company_phone' => get_option('invoice_company_phone'),
            'datetime_now' => date('Y-m-d H:i:s'),
        ];

        $lead_custom_fields = get_custom_fields($lead->id, ['active' => 1]);
        foreach ($lead_custom_fields as $lead_custom_field) {
            $value = get_custom_field_value($lead->id, $lead_custom_field['id'], 'leads');
            if (!empty($value)) {
                $request_data[$lead_custom_field['name']] = $value;
            }
        }

        $customer_info = '';
        foreach ($request_data as $key => $value) {
            if (!empty($value)) {
                $customer_info .= "{$key}: {$value} \n";
            }
        }

        $first_sentence = get_option('alm_first_sentence_outbound');
        $first_sentence = str_replace('{company_name}', get_option('invoice_company_name'), $first_sentence);
        $first_sentence = str_replace('{address}', get_option('invoice_company_address'), $first_sentence);
        $first_sentence = str_replace('{city}', get_option('invoice_company_city'), $first_sentence);
        $first_sentence = str_replace('{state}', get_option('invoice_company_state'), $first_sentence);
        $first_sentence = str_replace('{zip}', get_option('invoice_company_zip'), $first_sentence);
        $first_sentence = str_replace('{country}', get_option('invoice_company_country'), $first_sentence);

        $call_request_data = [
            'phoneNumber' => [
                'twilioAccountSid' => get_option('twilio_account_sid'),
                'twilioAuthToken' => get_option('twilio_auth_token'),
                'twilioPhoneNumber' => get_option('twilio_account_number')
            ],
            'customer' => ['number' => $lead->phonenumber],
            'assistant' => [
                'name' => 'AI Lead Manager - OutBound Assistant',
                "firstMessage" => $first_sentence,
                "transcriber" => [
                    "provider" => "deepgram",
                    "model" => "nova-2",
                    "language" => "en-US",
                ],
                "backchannelingEnabled" =>  get_option('back_channeling_enabled') == 1 ? true : false,
                "voice" => [
                    "fillerInjectionEnabled" => get_option('filler_injection_enabled') == 1 ? true : false,
                    "provider" => get_option('vapi_ai_voice_provider'),
                    "voiceId" => (get_option('vapi_ai_is_custom_voice_id') == 1 ? get_option('vapi_ai_agent_voice_id') : get_option('vapi_ai_agent_voice'))
                ],
                'dialKeypadFunctionEnabled' => get_option('dial_keypad_function_enabled') == 1 ? true : false,
                'endCallFunctionEnabled' => get_option('end_call_function_enabled') == 1 ? true : false,
                "model" => [
                    "provider" => "openai",
                    "model" => "gpt-4o",
                    "maxTokens" => (int) get_option('vapi_ai_max_tokens'),
                    "temperature" =>  (int) get_option('vapi_ai_temperature'),
                    "emotionRecognitionEnabled" => get_option('vapi_ai_detect_emotions') == 1 ? true : false,
                    "messages" => [
                        ["role" => "system", "content" => get_option("alm_system_prompt_outbound") . "\n\n Lead Information:\n {$customer_info}"]
                    ]
                ],
                'metadata' => [
                    'rel_id' => $lead->id,
                    'rel_type' => 'lead',
                    'staff_id' => get_staff_user_id()
                ],
                'serverUrl' => admin_url('ai_lead_manager/webhooks/vapi_ai_outbound'),
                // 'serverUrl' => 'https://webhook.site/7490991a-1dee-44c3-b9e9-af4a0d7967ff',
                'serverMessages' => ['end-of-call-report'],
                'maxDurationSeconds' => (int) (get_option('vapi_ai_max_duration') != 0 ? get_option('vapi_ai_max_duration') : 120),
                'startSpeakingPlan' => [
                    'smartEndpointingEnabled' => true
                ],
                'stopSpeakingPlan' => [
                    'numWords' => 2,
                    'voiceSeconds' => 0.2,
                    'backoffSeconds' => 1
                ]
            ]
        ];

        if (!empty(get_option('vapi_ai_tools_outbound'))) {
            $call_request_data['assistant']['model']['toolIds'] = json_decode(get_option('vapi_ai_tools_outbound'));
        }

        if (!empty(get_option('vapi_ai_knowledgebase_outbound'))) {
            $call_request_data['assistant']['model']['knowledgeBase'] = [
                'topK' => 2,
                'fileIds' => json_decode(get_option('vapi_ai_knowledgebase_outbound')),
                'provider' => 'canonical'
            ];
        }

        $response = $vapi_ai->create_call('AI OutBound Call', $call_request_data);
    }
}

/**
 * Retrieve a list of voices from Bland AI.
 *
 * @return array An array of voices, where each voice is an associative array containing the keys 'id' and 'name'.
 */
function alm_bland_ai_get_voices()
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/bland_ai');
    $response = $CI->bland_ai->get_voices();


    $voices = [];
    if (isset($response['voices'])) {
        foreach ($response['voices'] as $voice) {
            if (!$voice['tags']) continue;

            if (in_array('Bland Curated', $voice['tags'])) {
                if (substr($voice['name'], 0, 6) == 'Public') continue;

                $voice['name'] = ucfirst($voice['name']);
                $voices[] = $voice;
            }
        }
    }


    return $voices;
}

/**
 * Retrieve a list of voice providers.
 *
 * @return array An array of voice providers, where each provider is an associative array containing the keys 'id' and 'name'.
 */
function alm_vapi_ai_get_voice_providers()
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/vapi_ai');
    return $CI->vapi_ai->get_voice_providers();
}

/**
 * Retrieve a list of voices from Vapi AI.
 *
 * @param string $provider The provider to retrieve voices for. Defaults to 'openai'.
 * @return array An array of voices, where each voice is an associative array containing the keys 'id' and 'name'.
 */
function alm_vapi_ai_get_voices($provider = 'openai')
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/vapi_ai');
    $response = $CI->vapi_ai->get_voices($provider);

    $voices = [];

    if (!isset($response['error'])) {
        foreach ($response as $voice) {
            $voice['name'] = ucfirst($voice['name']);
            $voices[] = $voice;
        }
    }

    return $voices;
}

/**
 * Retrieve the number of call logs where the specified column equals the specified value.
 *
 * @param string $col_name The column name to query.
 * @param mixed $value The value to query against.
 * @return int The number of call logs matching the specified query.
 */
function get_alm_call_logs_counts($col_name, $value)
{
    $CI = &get_instance();
    return $CI->db->where($col_name, $value)->where('ai_provider', get_option('alm_voice_assistant'))->count_all_results(db_prefix() . 'alm_call_logs');
}

/**
 * Retrieve the number of call logs for the current day or yesterday.
 *
 * If $yesterday is true, the function will return the number of call logs for yesterday.
 * Otherwise, it will return the number of call logs for today.
 *
 * @param bool $yesterday Whether to retrieve the count for yesterday (true) or today (false).
 * @return int The number of call logs for the specified day.
 */
function get_alm_call_logs_total($yesterday = false)
{
    $CI = &get_instance();
    $yesterday_count = $yesterday ? '- INTERVAL 1 DAY' : '';
    return $CI->db->query("SELECT * FROM " . db_prefix() . 'alm_call_logs' . " WHERE DATE(`started_at`) = CURDATE() $yesterday_count AND ai_provider = '" . get_option('alm_voice_assistant') . "'")->num_rows();
}

/**
 * Convert a duration in minutes to a human-readable format.
 *
 * If the duration is less than an hour, the result will be in the format "Xm Ys".
 * If the duration is an hour or more, the result will be in the format "Xh Ym Zs".
 *
 * @param float $duration A duration in minutes to convert.
 * @return string The duration in a human-readable format.
 */
function convert_duration($duration)
{
    // Ensure the input is in minutes (not hours).
    $minutes = floor($duration);  // Get the whole number part (minutes)
    $seconds = round(($duration - $minutes) * 60);  // Get the remaining seconds

    // Format the result.
    if ($minutes >= 60) {
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        return $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
    }

    // If less than an hour, just show minutes and seconds
    return $minutes . 'm ' . $seconds . 's';
}


function alm_bland_ai_get_knowledgebase()
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/bland_ai');

    $bland_ai = new Bland_ai();

    $response = $bland_ai->list_knowledgebase();

    return (!empty($response['data']['vectors']) ? $response['data']['vectors'] : []);
}

function alm_vapi_ai_get_knowledgebase()
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/vapi_ai');

    $vapi_ai = new Vapi_ai();

    $response = $vapi_ai->list_knowledgebase();

    print_r($response);
    return (!empty($response) ? $response : []);
}

function alm_vapi_ai_get_files()
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/vapi_ai');

    $vapi_ai = new Vapi_ai();

    $response = $vapi_ai->list_files();

    // print_r($response);
    return (!empty($response) ? $response : []);
}

function alm_vapi_ai_get_tools()
{
    $CI = &get_instance();
    $CI->load->library(AI_LEAD_MANAGER_MODULE_NAME . '/vapi_ai');

    $vapi_ai = new Vapi_ai();

    $response = $vapi_ai->get_tools();

    foreach ($response as &$tool) {
        if (isset($tool['function']['name'])) {
            $tool['name'] = $tool['function']['name'];
        }
    }

    // print_r($response);
    return (!empty($response) ? $response : []);
}

function alm_format_bytes($bytes, $precision = 2)
{
    // Define the units of measurement
    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

    // Handle zero bytes
    if ($bytes == 0) {
        return '0 Bytes';
    }

    // Calculate the power to determine the unit
    $power = floor(log($bytes, 1024));
    $power = min($power, count($units) - 1); // Ensure it doesn't exceed the available units

    // Convert bytes to the chosen unit
    $convertedValue = $bytes / pow(1024, $power);

    // Return the formatted string with the appropriate unit
    return round($convertedValue, $precision) . ' ' . $units[$power];
}

function alm_format_call_log_status($reason, $classes = '', $label = true)
{
    if (empty($reason)) return '';

    $statuses = [
        'customer-ended-call' => [
            'name' => 'Customer Ended Call',
            'class' => 'success'
        ],
        'assistant-ended-call' => [
            'name' => 'Assistant Ended Call',
            'class' => 'info'
        ],
        'assistant-said-end-call-phrase' => [
            'name' => 'Assistant Said End Call Phrase',
            'class' => 'default'
        ],
        'assistant-forwarded-call' => [
            'name' => 'Assistant Forwarded Call',
            'class' => 'default'
        ],
        'customer-busy' => [
            'name' => 'Customer Busy',
            'class' => 'warning'
        ],
        'customer-did-not-answer' => [
            'name' => 'Customer Did Not Answer',
            'class' => 'warning'
        ],
        'silence-timed-out' => [
            'name' => 'Silence Timed Out',
            'class' => 'warning'
        ],
        'voicemail' => [
            'name' => 'Voicemail',
            'class' => 'warning'
        ],
        'max-duration-exceeded' => [
            'name' => 'Max Duration Exceeded',
            'class' => 'warning'
        ],
        'customer-did-not-give-microphone-permission' => [
            'name' => 'No Microphone Permission',
            'class' => 'danger'
        ],
        'twilio-failed-to-connect-call' => [
            'name' => 'Twilio Connection Failed',
            'class' => 'danger'
        ],
        'unknown-error' => [
            'name' => 'Unknown Error',
            'class' => 'danger'
        ]
    ];

    if ($label == true) {
        return '<span class="label label-' . $statuses[$reason]['class'] . ' ' . $classes . ' s-status">' . $statuses[$reason]['name'] . '</span>';
    }

    return $statuses[$reason]['name'];
}
