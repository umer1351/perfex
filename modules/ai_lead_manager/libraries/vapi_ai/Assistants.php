<?php

namespace modules\ai_lead_manager\libraries\vapi_ai;

trait Assistants
{
    /**
     * Retrieves a list of all available assistants, with optional filters.
     *
     * @param array $filters Optional associative array of filters to apply to the assistant list.
     * @return array The API response containing the list of assistants.
     */
    public function get_assistants($filters = [])
    {
        $endpoint = '/assistant';

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->send_request('GET', $endpoint);
    }

    /**
     * Creates a new assistant with the specified data.
     *
     * @param array $data An associative array containing the data for the new assistant.
     * @return array The API response from the assistant creation request.
     */
    public function create_assistant($data = [])
    {
        return $this->send_request('POST', '/assistant', $data);
    }

    /**
     * Retrieve an assistant by its ID.
     *
     * @param string $assistant_id The ID of the assistant to retrieve.
     * @return array The API response containing the assistant data.
     */
    public function get_assistant_by_id($assistant_id)
    {
        return $this->send_request('GET', '/assistant/' . $assistant_id);
    }

    /**
     * Deletes an assistant by its ID.
     *
     * @param string $assistant_id The ID of the assistant to delete.
     * @return array The API response from the deletion request.
     */
    public function delete_assistant($assistant_id)
    {
        return $this->send_request('DELETE', '/assistant/' . $assistant_id);
    }

    /**
     * Updates an existing assistant by its ID with the specified data.
     *
     * @param string $assistant_id The ID of the assistant to update.
     * @param array $data An associative array containing the data for the update.
     * @return array The API response from the assistant update request.
     */
    public function update_assistant($assistant_id, $data)
    {
        return $this->send_request('PATCH', "/assistant/{$assistant_id}", $data);
    }
}
