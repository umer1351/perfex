<?php

namespace modules\ai_lead_manager\libraries\vapi_ai;

trait Tools
{
    /**
     * Retrieve a list of tools with optional filters.
     *
     * @param array $filters Optional associative array of filters to apply to the tool list.
     * @return array The API response containing the list of tools.
     */
    public function get_tools($filters = [])
    {
        $endpoint = '/tool';

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->send_request('GET', $endpoint);
    }

    /**
     * Creates a new tool with the specified data.
     *
     * @param array $data An associative array containing the data for the new tool.
     * @return array The API response from the tool creation request.
     */
    public function create_tool($data = [])
    {
        return $this->send_request('POST', '/tool', $data);
    }

    /**
     * Retrieves a tool by its ID.
     *
     * @param string $tool_id The ID of the tool to retrieve.
     * @return array The API response containing the tool data.
     */
    public function get_tool_by_id($tool_id)
    {
        return $this->send_request('GET', '\/tool/' . $tool_id);
    }

    /**
     * Deletes a tool by its ID.
     *
     * @param string $tool_id The ID of the tool to delete.
     * @return array The API response from the deletion request.
     */
    public function delete_tool($tool_id)
    {
        return $this->send_request('DELETE', '\/tool/' . $tool_id);
    }

    /**
     * Updates an existing tool by its ID with the specified data.
     *
     * @param string $tool_id The ID of the tool to update.
     * @param array $data An associative array containing the data for the update.
     * @return array The API response from the tool update request.
     */
    public function update_tool($tool_id, $data)
    {
        return $this->send_request('PUT', '\/tool/' . $tool_id, $data);
    }
}
