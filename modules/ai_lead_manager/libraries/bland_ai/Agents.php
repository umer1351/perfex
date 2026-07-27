<?php

namespace modules\ai_lead_manager\libraries\bland_ai;

trait Agents
{
    /**
     * List all web agents and their settings.
     *
     * @return array List of web agents with their details or an error response.
     */
    public function list_web_agents()
    {
        return $this->send_request('GET', '/v1/agents', []);
    }

    /**
     * Create a web agent for Bland AI.
     *
     * @param string $prompt Instructions for the agent's conversation flow.
     * @param string $voice The voice for the agent.
     * @param array|null $analysis_schema Optional analysis schema for custom data extraction.
     * @param array|null $metadata Optional metadata to track or categorize calls.
     * @param string|null $pathway_id The pathway ID to override the prompt (optional).
     * @param string $language The language for the agent (default: 'ENG').
     * @param string $model The model to use (default: 'enhanced').
     * @param string|null $first_sentence The first sentence the agent will say (optional).
     * @param array|null $tools Tools the agent can use (optional).
     * @param array|null $dynamic_data Dynamic data from external sources (optional).
     * @param int $interruption_threshold Threshold for interruption timing (default: 100).
     * @param array $keywords Keywords for transcription accuracy (default: []).
     * @param int|null $max_duration Maximum duration of the call (default: 30 minutes).
     * @return array Response from the API call.
     */
    public function create_web_agent(
        $prompt,
        $voice,
        $analysis_schema = null,
        $metadata = null,
        $pathway_id = null,
        $language = 'ENG',
        $model = 'enhanced',
        $first_sentence = null,
        $tools = null,
        $dynamic_data = null,
        $interruption_threshold = 100,
        $keywords = [],
        $max_duration = 30
    ) {
        // Prepare the data array for the API request
        $data = [
            'prompt'                => $prompt,
            'voice'                 => $voice,
            'analysis_schema'       => $analysis_schema,
            'metadata'              => $metadata,
            'pathway_id'            => $pathway_id,
            'language'              => $language,
            'model'                 => $model,
            'first_sentence'        => $first_sentence,
            'tools'                 => $tools,
            'dynamic_data'          => $dynamic_data,
            'interruption_threshold' => $interruption_threshold,
            'keywords'              => $keywords,
            'max_duration'          => $max_duration
        ];

        // Remove empty values to avoid sending unnecessary null values
        $data = array_filter($data, function ($value) {
            return !is_null($value) && $value !== '';
        });

        // Send the request and return the result
        return $this->send_request('POST', '/v1/agents', $data);
    }

    /**
     * Update the settings of an existing web agent.
     *
     * @param string $agent_id The ID of the agent you want to update.
     * @param string $prompt Provide instructions for the ideal conversation flow.
     * @param string|null $voice Set the agent's voice.
     * @param array|null $analysis_schema Define a schema for information retrieval from the call.
     * @param array|null $metadata Add extra metadata for tracking or categorization.
     * @param string|null $pathway_id The ID of the pathway to follow (optional).
     * @param string $language The language of the agent (default: 'ENG').
     * @param string $model The model to use (default: 'enhanced').
     * @param string|null $first_sentence The first sentence the agent will say (optional).
     * @param array|null $tools Tools that the agent can use (optional).
     * @param array|null $dynamic_data External data for agent knowledge (optional).
     * @param int $interruption_threshold The threshold for interruption timing (default: 100).
     * @param array $keywords Keywords for transcription accuracy (default: []).
     * @param int|null $max_duration Maximum call duration (default: 30 minutes).
     * @return array Response from the API call.
     */
    public function update_web_agent(
        $agent_id,
        $prompt,
        $voice = null,
        $analysis_schema = null,
        $metadata = null,
        $pathway_id = null,
        $language = 'ENG',
        $model = 'enhanced',
        $first_sentence = null,
        $tools = null,
        $dynamic_data = null,
        $interruption_threshold = 100,
        $keywords = [],
        $max_duration = 30
    ) {

        // Prepare the data array for the API request
        $data = [
            'prompt'                => $prompt,
            'voice'                 => $voice,
            'analysis_schema'       => $analysis_schema,
            'metadata'              => $metadata,
            'pathway_id'            => $pathway_id,
            'language'              => $language,
            'model'                 => $model,
            'first_sentence'        => $first_sentence,
            'tools'                 => $tools,
            'dynamic_data'          => $dynamic_data,
            'interruption_threshold' => $interruption_threshold,
            'keywords'              => $keywords,
            'max_duration'          => $max_duration
        ];

        // Remove empty values to avoid sending unnecessary null values
        $data = array_filter($data, function ($value) {
            return !is_null($value) && $value !== '';
        });

        // Send the request and return the result
        return $this->send_request('POST', "/v1/agents/{$agent_id}", $data);
    }

    /**
     * Authorize a web agent by creating a single-use session token.
     *
     * @param string $agent_id The ID of the agent to authorize.
     * @return array Response from the API call, typically containing a token or error message.
     */
    public function authorize_agent($agent_id)
    {
        return $this->send_request('POST', "/v1/agents/{$agent_id}/authorize", []);
    }

    /**
     * Delete a web agent by its agent ID.
     *
     * @param string $agent_id The ID of the agent to delete.
     * @return array Response from the API call, typically indicating success or failure.
     */
    public function delete_web_agent($agent_id)
    {
        return $this->send_request('POST', "/v1/agents/{$agent_id}/delete", []);
    }
}
