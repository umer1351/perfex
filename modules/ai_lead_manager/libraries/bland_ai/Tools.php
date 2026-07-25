<?php

namespace modules\ai_lead_manager\libraries\bland_ai;

trait Tools
{
    /**
     * Retrieve a list of Custom Tools you’ve created.
     *
     * @return array The response from the API containing the list of tools.
     */
    public function list_custom_tools()
    {
        return $this->send_request('GET', '/v1/tools');
    }

    /**
     * Create a Custom Tool that can call external APIs.
     *
     * @param string $name The name of the custom tool.
     * @param string $description The description of what the tool does.
     * @param string $speech The speech that the AI will say while using the tool.
     * @param string $url The endpoint of the external API to call.
     * @param string $method The HTTP method to use for the request (GET or POST).
     * @param array $headers Optional headers to include in the API request.
     * @param array $body Optional body to include in the API request (for POST).
     * @param array $query Optional query parameters to append to the URL (for GET).
     * @param array $input_schema The schema for validating AI input before using the tool.
     * @param array $response Optional path to extract specific data from the API response.
     * @param int $timeout Timeout for the API request in milliseconds (default 10000).
     * @return array The response from the API when creating the tool.
     */
    public function create_custom_tool(
        $name,
        $description,
        $speech,
        $url,
        $method = 'GET',
        $headers = [],
        $body = [],
        $query = [],
        $input_schema = [],
        $response = [],
        $timeout = 10000
    ) {
        // Prepare the tool data
        $tool_data = [
            'name' => $name,
            'description' => $description,
            'speech' => $speech,
            'url' => $url,
            'method' => $method,
            'headers' => $headers,
            'body' => $body,
            'query' => $query,
            'input_schema' => $input_schema,
            'response' => $response,
            'timeout' => $timeout
        ];

        // Send POST request to create the custom tool
        return $this->send_request('POST', '/v1/tools', $tool_data);
    }

    /**
     * Update a Custom Tool with new parameters.
     *
     * @param string $tool_id The ID of the custom tool to update.
     * @param string|null $name The new name of the custom tool (optional).
     * @param string|null $description The new description of the tool (optional).
     * @param string|null $speech The new speech that the AI will say while using the tool (optional).
     * @param string|null $url The new endpoint of the external API to call (optional).
     * @param string|null $method The new HTTP method to use for the request (GET or POST, optional).
     * @param array|null $headers New headers to include in the API request (optional).
     * @param array|null $body New body to include in the API request (optional).
     * @param array|null $query New query parameters to append to the URL (optional).
     * @param array|null $input_schema New schema for validating AI input (optional).
     * @param array|null $response New path to extract specific data from the API response (optional).
     * @param int|null $timeout New timeout for the API request in milliseconds (optional).
     * @return array The response from the API when updating the tool.
     */
    public function update_custom_tool(
        $tool_id,
        $name = null,
        $description = null,
        $speech = null,
        $url = null,
        $method = null,
        $headers = null,
        $body = null,
        $query = null,
        $input_schema = null,
        $response = null,
        $timeout = null
    ) {
        // Prepare the tool data with only the fields to update
        $tool_data = array_filter([
            'name' => $name,
            'description' => $description,
            'speech' => $speech,
            'url' => $url,
            'method' => $method,
            'headers' => $headers,
            'body' => $body,
            'query' => $query,
            'input_schema' => $input_schema,
            'response' => $response,
            'timeout' => $timeout
        ], function ($value) {
            return $value !== null;
        });

        // Send POST request to update the custom tool
        return $this->send_request('POST', "/v1/tools/{$tool_id}", $tool_data);
    }

    /**
     * Delete a Custom Tool.
     *
     * @param string $tool_id The ID of the custom tool to delete.
     * @return array The response from the API after deleting the tool.
     */
    public function delete_custom_tool($tool_id)
    {
        return $this->send_request('DELETE', "/v1/tools/{$tool_id}");
    }
}
