<?php

namespace modules\ai_lead_manager\libraries\vapi_ai;

trait Phone_numbers
{
    /**
     * Retrieves a list of all phone numbers with optional filters.
     *
     * @param array $filters Optional associative array of filters to apply to the phone number list.
     * @return array The API response containing the list of phone numbers.
     */
    public function get_phone_numbers($filters = [])
    {
        $endpoint = '/phone-number';

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->send_request('GET', $endpoint);
    }

    /**
     * Creates a new phone number.
     *
     * @param array $data An associative array containing the data for the new phone number.
     * @return array The API response containing the created phone number.
     */
    public function create_phone_number($data = [])
    {
        return $this->send_request('POST', '/phone-number', $data);
    }

    /**
     * Retrieves a phone number by its ID.
     *
     * @param string $id The ID of the phone number to retrieve.
     * @return array The API response containing the phone number data.
     */
    public function get_phone_number_by_id($id)
    {
        return $this->send_request('GET', '/phone-number/' . $id);
    }

    /**
     * Deletes a phone number by its ID.
     *
     * @param string $id The ID of the phone number to delete.
     * @return array The API response from the deletion request.
     */
    public function delete_phone_number($id)
    {
        return $this->send_request('DELETE', '/phone-number/' . $id);
    }

    /**
     * Updates a phone number by its ID.
     *
     * @param string $id The ID of the phone number to update.
     * @param array $data An associative array containing the data for the update.
     * @return array The API response from the update request.
     */
    public function update_phone_number($id, $data)
    {
        return $this->send_request('PATCH', '/phone-number/' . $id, $data);
    }
}
