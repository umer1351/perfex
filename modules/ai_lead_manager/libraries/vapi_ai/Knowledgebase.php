<?php

namespace modules\ai_lead_manager\libraries\vapi_ai;

trait Knowledgebase
{
    /**
     * Retrieves a list of knowledge base entries with optional filters.
     *
     * @param array $filters Optional associative array of filters to apply to the knowledge base list.
     * @return array The API response containing the list of knowledge base entries.
     */

    public function list_knowledgebase($filters = [])
    {
        $endpoint = '/knowledge-base';

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->send_request('GET', $endpoint);
    }

    /**
     * Creates a new knowledge base entry.
     *
     * @param array $data An associative array containing the data for the new knowledge base entry.
     * @return array The API response from the knowledge base creation request.
     */

    public function create_knowledgebase($data = [])
    {
        return $this->send_request('POST', '/knowledge-base', $data);
    }

    /**
     * Retrieves a knowledge base entry by its ID.
     *
     * @param string $id The ID of the knowledge base entry to retrieve.
     * @return array The API response containing the knowledge base entry data.
     */
    public function get_knowledgebase_by_id($id)
    {
        return $this->send_request('GET', '/knowledge-base/' . $id);
    }

    /**
     * Deletes a knowledge base entry by its ID.
     *
     * @param string $id The ID of the knowledge base entry to delete.
     * @return array The API response from the deletion request.
     */
    public function delete_knowledgebase($id)
    {
        return $this->send_request('DELETE', '/knowledge-base/' . $id);
    }

    /**
     * Updates a knowledge base entry by its ID.
     *
     * @param string $id The ID of the knowledge base entry to update.
     * @param array $data An associative array containing the updated data for the knowledge base entry.
     * @return array The API response from the update request.
     */
    public function update_knowledgebase($id, $data)
    {
        return $this->send_request('PATCH', '/knowledge-base/' . $id, $data);
    }
}
