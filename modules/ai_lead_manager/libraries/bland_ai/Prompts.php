<?php

namespace modules\ai_lead_manager\libraries\bland_ai;

trait Prompts
{
    /**
     * Retrieve all saved prompts.
     *
     * @return array The response from the API containing the list of prompts.
     */
    public function list_prompts()
    {
        return $this->send_request('GET', '/v1/prompts');
    }

    /**
     * Retrieve details of a specific prompt.
     *
     * @param string $prompt_id The unique identifier of the prompt to retrieve.
     * @return array The response from the API containing the prompt details.
     */
    public function get_prompt_details($prompt_id)
    {
        return $this->send_request('GET', "/v1/prompts/{$prompt_id}");
    }

    /**
     * Create and store a prompt for future use.
     *
     * @param string $prompt The content of the prompt to store.
     * @param string $name Optional name for the prompt.
     * @return array The response from the API containing the created prompt details.
     */
    public function create_prompt($prompt, $name = null)
    {
        // Prepare the body of the request
        $body = ['prompt' => $prompt];
        if (!empty($name)) {
            $body['name'] = $name;
        }

        // Send POST request to create the prompt
        return $this->send_request('POST', '/v1/prompts', $body);
    }
}
