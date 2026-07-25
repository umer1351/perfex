<?php

namespace modules\ai_lead_manager\libraries\vapi_ai;

trait Voices
{
    public function get_voice_providers()
    {
        return [
            ['id' => 'cartesia', 'name' => 'Cartesia'],
            ['id' => '11labs', 'name' => '11Labs'],
            ['id' => 'rime-ai', 'name' => 'Rime AI'],
            ['id' => 'playht', 'name' => 'Playht'],
            ['id' => 'lmnt', 'name' => 'Lmnt'],
            ['id' => 'deepgram', 'name' => 'Deepgram'],
            ['id' => 'openai', 'name' => 'OpenAI'],
            ['id' => 'azure', 'name' => 'Azure'],
            ['id' => 'neets', 'name' => 'Neets'],
            ['id' => 'tavus', 'name' => 'Tavus']
        ];
    }
    /**
     * Retrieve a list of voices.
     *
     * @return array The API response.
     */
    public function get_voices($provider)
    {

        return $this->send_request('GET', '/voice-library/' . $provider);
    }
}
