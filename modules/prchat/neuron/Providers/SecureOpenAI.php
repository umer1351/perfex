<?php

namespace PerfexChat\Neuron\Providers;

use GuzzleHttp\Client;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * OpenAI Provider with SSL verification disabled for local development.
 * Extends the NeuronAI OpenAI provider and overrides the HTTP client.
 */
class SecureOpenAI extends OpenAI
{
    public function __construct(string $key, string $model = 'gpt-4o-mini', array $parameters = [])
    {
        parent::__construct($key, $model, $parameters);

        // Override the client with SSL verification disabled
        $this->setClient(new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type' => 'application/json',
            ],
        ]));
    }
}
