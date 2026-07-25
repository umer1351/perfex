<?php

namespace modules\ai_lead_manager\libraries\vapi_ai;

trait Calls
{
    /**
     * Retrieve a list of calls with optional filters.
     *
     * @param array $filters Optional associative array of filters to apply to the call list.
     * @return array The API response containing the list of calls.
     */
    public function get_calls($filters = [])
    {
        $endpoint = '/call';

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->send_request('GET', $endpoint);
    }

    /**
     * Initiates a call with the specified assistant and optional parameters.
     *
     * @param string $name The name associated with the call (optional).
     * @param array $additional_parameters Optional associative array of additional parameters for the call request.
     * @return array The API response from the call request.
     */
    public function create_call($name = '', $additional_parameters = [])
    {
        $data = [
            'name' => $name,
        ];

        if (!empty($additional_parameters)) {
            $data = array_merge($data, $additional_parameters);
        }

        return $this->send_request('POST', '/call', $data);
    }

    /**
     * Retrieve a call by ID.
     *
     * @param string $call_id The ID of the call.
     * @return array The API response containing the call data.
     */
    public function get_call_by_id($call_id)
    {
        return $this->send_request('GET', '\/call/' . $call_id);
    }

    /**
     * Deletes a call by its ID.
     *
     * @param string $call_id The ID of the call to delete.
     * @return array The API response from the deletion request.
     */
    public function delete_call($call_id)
    {
        return $this->send_request('DELETE', '\/call/' . $call_id);
    }

    /**
     * Updates an existing call by its ID with the specified data.
     *
     * @param string $call_id The ID of the call to update.
     * @param array $data An associative array containing the data for the update.
     * @return array The API response from the call update request.
     */
    public function update_call($call_id, $data)
    {
        return $this->send_request('PUT', '\/call/' . $call_id, $data);
    }
}
