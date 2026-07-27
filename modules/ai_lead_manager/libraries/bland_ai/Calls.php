<?php

namespace modules\ai_lead_manager\libraries\bland_ai;

trait Calls
{
    /**
     * Initiates a simple call with the specified phone number and task.
     *
     * @param string $phone_number The phone number to be called.
     * @param string $task The task to perform during the call.
     *
     * @return array The API response from the call request.
     */
    public function make_simple_call($phone_number, $task)
    {
        $data = [
            'phone_number' => $phone_number,
            'task' => $task
        ];
        return $this->send_request('POST', '/v1/calls', $data);
    }

    /**
     * Initiates a call with the specified phone number and task, with optional additional parameters.
     *
     * @param string $phone_number The phone number to be called.
     * @param string $task The task to perform during the call.
     * @param array $additional_parameters Optional associative array of additional parameters to send with the call request.
     * @return array The API response from the call request.
     */
    public function make_call($phone_number, $task, $additional_parameters = [], $encrypted_key = null)
    {
        $data = [
            'phone_number' => $phone_number,
            'task' => $task,
        ];

        if (!empty($additional_parameters)) {
            $data = array_merge($data, $additional_parameters);
        }

        return $this->send_request('POST', '/v1/calls', $data, ['encrypted_key: ' . $encrypted_key]);
    }

    /**
     * Retrieve a list of calls with optional filters.
     *
     * @param array $filters Optional associative array of filters to apply to the call list.
     * @return array The API response containing the list of calls.
     */
    public function get_calls($filters = [])
    {
        $endpoint = '/v1/calls';

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->send_request('GET', $endpoint);
    }

    /**
     * Retrieve a call by ID.
     *
     * @param string $call_id The ID of the call.
     * @return array The API response containing the call data.
     */
    public function get_call_by_id($call_id)
    {
        return $this->send_request('GET', '/v1/calls/' . $call_id);
    }

    /**
     * Retrieve the recording URL for a specific call.
     *
     * @param string $call_id The ID of the call.
     * @return array The API response containing the recording URL.
     */
    public function get_call_recording($call_id)
    {
        return $this->send_request('GET', '/v1/calls/' . $call_id . '/recording');
    }

    /**
     * Retrieve the corrected transcript for a specific call.
     *
     * @param string $call_id The ID of the call.
     * @return array The API response containing the corrected transcript.
     */
    public function get_corrected_transcript($call_id)
    {
        return $this->send_request('GET', '/v1/calls/corrected-transcript/' . $call_id);
    }
}
