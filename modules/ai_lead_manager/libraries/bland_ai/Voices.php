<?php

namespace modules\ai_lead_manager\libraries\bland_ai;

trait Voices
{
    /**
     * Retrieve a list of voices.
     *
     * @return array The API response.
     */
    public function get_voices()
    {
        return $this->send_request('GET', '/v1/voices');
    }

    /**
     * Retrieve a single voice by ID.
     *
     * @param string $voice_id The ID of the voice.
     * @return array The API response.
     */
    public function get_voice($voice_id)
    {
        return $this->send_request('GET', '/v1/voices/' . $voice_id);
    }

    /**
     * Send a sample text to a specific voice for synthesis.
     *
     * @param string $voice_id The ID of the voice.
     * @param string $text The sample text to be synthesized.
     * @return array The API response.
     */
    public function post_voice_sample($voice_id, $text)
    {
        $data = ['text' => $text];

        return $this->send_request('POST', '/v1/voices/' . $voice_id . '/sample', $data);
    }
}
