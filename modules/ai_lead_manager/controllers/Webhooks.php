<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Webhooks extends App_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model(AI_LEAD_MANAGER_MODULE_NAME . '/Call_logs_model', 'call_logs');
        $this->load->model('leads_model');
    }

    /**
     * Handle Bland AI webhook
     *
     * @return void
     */
    public function bland_ai()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, TRUE);

        if (empty($data) || empty($data['completed'])) {
            return false;
        }

        $call_log_data = [
            'call_id' => $data['call_id'],
            'to_number' => $data['to'],
            'from_number' => $data['from'],
            'status' => $data['status'],
            'call_length' => $data['call_length'],
            'recording_url' => $data['recording_url'],
            'transcripts' => json_encode($data['transcripts']),
            'summary' => $data['summary'],
            'call_ended_by' => $data['call_ended_by'],
            'direction' => $data['inbound'] ? 0 : 1,
            'price' => $data['price'],
            'rel_type' => $data['metadata']['rel_type'],
            'rel_id' => $data['metadata']['rel_id'],
            'staff_id' => $data['metadata']['staff_id'] ?? null,
            'sid' => $data['sid'],
            'twilio_account_sid' => $data['twilio_account_sid'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['end_at'],
            'created_at' => $data['created_at'],
            'ai_provider' => 'bland_ai'
        ];

        foreach ($call_log_data as $key => $value) {
            unset($data[$key]);
        }
        unset($data['metadata'], $data['end_at'], $data['inbound'], $data['from'], $data['to']);

        $call_log_data['extra_information'] = json_encode($data);

        $call_log_id = $this->call_logs->add($call_log_data);

        if ($call_log_id) {
            if ($call_log_data['rel_type'] == 'lead' && $call_log_data['rel_id']) {
                $this->leads_model->update_lead_status([
                    'leadid' => $call_log_data['rel_id'],
                    'status' => get_option('alm_lead_status_after_follow_up')
                ]);
            }
        }
    }

    /**
     * Handle VAPI.ai webhook for inbound calls
     *
     * @return void
     */
    public function vapi_ai_inbound()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, TRUE);

        if (!empty($data) && !empty($data['message']['type'])) {
            if ($data['message']['type'] == 'end-of-call-report') {
                $_data = $data['message'];
                $structured_data = $_data['analysis']['structuredData'];

                if (!empty($structured_data['customer_name'])) {
                    $lead_data = [
                        'name' => $structured_data['customer_name'],
                        'email' => $structured_data['customer_email'],
                        'phonenumber' => $_data['customer']['number'],
                        'description' => $structured_data['description'],
                        'address' => $structured_data['customer_address'] ?? '',
                        'status' => get_option('alm_lead_status_after_follow_up'),
                        'source' => get_option('alm_lead_source'),
                        'assigned' => 0,
                    ];

                    if (!empty($structured_data['customer_city'])) {
                        $lead_data['city'] = $structured_data['customer_city'];
                    }
                    if (!empty($structured_data['customer_state'])) {
                        $lead_data['state'] = $structured_data['customer_state'];
                    }
                    if (!empty($structured_data['customer_country'])) {
                        $lead_data['country'] = $structured_data['customer_country'];
                    }
                    if (!empty($structured_data['customer_zip'])) {
                        $lead_data['zip'] = $structured_data['customer_zip'];
                    }

                    $lead_id = $this->leads_model->add($lead_data);

                    if ($lead_id) {
                        $call_log_data = [
                            'call_id' => $_data['call']['id'], //
                            'to_number' => $_data['phoneNumber']['number'], //
                            'from_number' => $_data['customer']['number'], //
                            //    'status' => $data['status'],
                            'call_length' => $_data['durationMinutes'], //
                            'recording_url' => $_data['recordingUrl'], //
                            'transcripts' => json_encode($_data['messages']), //
                            'summary' => $_data['summary'], //
                            'call_ended_by' => $_data['endedReason'], //
                            'direction' => $_data['call']['type'] == 'inboundPhoneCall' ? 0 : 1, //
                            'price' => $_data['cost'], //
                            'rel_type' => 'lead', //
                            'rel_id' => $lead_id, //
                            'sid' => $_data['call']['phoneCallProviderId'], //
                            'twilio_account_sid' => $_data['phoneNumber']['twilioAccountSid'], //
                            'started_at' => $_data['startedAt'],
                            'ended_at' => $_data['endedAt'],
                            'created_at' => date('Y-m-d H:i:s', (int) ($_data['timestamp'] / 1000)),
                            'ai_provider' => 'vapi_ai' //
                        ];

                        foreach (['call', 'phoneNumber', 'customer', 'summary', 'cost', 'recordingUrl', 'timestamp', 'endedReason', 'messages', 'durationMinutes', 'startedAt', 'endedAt'] as $key => $value) {
                            unset($_data[$key]);
                        }

                        $call_log_data['extra_information'] = json_encode($_data);
                        $call_log_id = $this->call_logs->add($call_log_data);
                    }
                }
            }
        }
    }

    /**
     * Handle VAPI.ai webhook for outbound calls
     *
     * @return void
     */
    public function vapi_ai_outbound()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, TRUE);

        if (!empty($data) && !empty($data['message']['type'])) {
            if ($data['message']['type'] == 'end-of-call-report') {
                $_data = $data['message'];
                $meta_data = $_data['assistant']['metadata'];

                $call_log_data = [
                    'call_id' => $_data['call']['id'],
                    'to_number' => $_data['customer']['number'],
                    'from_number' => $_data['phoneNumber']['number'],
                    //    'status' => $data['status'],
                    'call_length' => $_data['durationMinutes'],
                    'recording_url' => $_data['recordingUrl'],
                    'transcripts' => json_encode($_data['messages']),
                    'summary' => $_data['summary'],
                    'call_ended_by' => $_data['endedReason'],
                    'direction' => $_data['call']['type'] == 'inboundPhoneCall' ? 0 : 1,
                    'price' => $_data['cost'],
                    'rel_type' => $meta_data['rel_type'],
                    'rel_id' => $meta_data['rel_id'],
                    'staff_id' => $meta_data['staff_id'],
                    'sid' => $_data['call']['phoneCallProviderId'],
                    'twilio_account_sid' => $_data['phoneNumber']['twilioAccountSid'],
                    'started_at' => $_data['startedAt'],
                    'ended_at' => $_data['endedAt'],
                    'created_at' => date('Y-m-d H:i:s', (int) ($_data['timestamp'] / 1000)),
                    'ai_provider' => 'vapi_ai',
                ];

                foreach (['call', 'phoneNumber', 'customer', 'summary', 'cost', 'recordingUrl', 'timestamp', 'endedReason', 'messages', 'durationMinutes', 'startedAt', 'endedAt'] as $key => $value) {
                    unset($_data[$key]);
                }

                $call_log_data['extra_information'] = json_encode($_data);
                $call_log_id = $this->call_logs->add($call_log_data);

                if ($call_log_id) {
                    if ($call_log_data['rel_type'] == 'lead' && $call_log_data['rel_id']) {
                        $this->leads_model->update_lead_status([
                            'leadid' => $call_log_data['rel_id'],
                            'status' => get_option('alm_lead_status_after_follow_up')
                        ]);
                    }
                }
            }
        }
    }
}
