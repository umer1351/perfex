<?php

namespace PerfexChat\Neuron\Embeddings;

use GuzzleHttp\Client;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Document;

/**
 * OpenAI Embeddings Provider with SSL verification disabled for local development.
 */
class SecureOpenAIEmbeddings implements EmbeddingsProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected int $dimensions;
    protected Client $client;

    public function __construct(string $apiKey, string $model = 'text-embedding-3-small', int $dimensions = 1536)
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->dimensions = $dimensions;

        // Create Guzzle client with SSL verification disabled for local dev
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'verify' => false, // Disable SSL verification for local development
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Embed a single text string.
     */
    public function embedText(string $text): array
    {
        $response = $this->client->post('embeddings', [
            'json' => [
                'input' => $text,
                'model' => $this->model,
                'dimensions' => $this->dimensions,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['data'][0]['embedding'])) {
            throw new \RuntimeException('Invalid response from OpenAI embeddings API');
        }

        return $data['data'][0]['embedding'];
    }

    /**
     * Embed multiple texts at once.
     */
    public function embedTexts(array $texts): array
    {
        $response = $this->client->post('embeddings', [
            'json' => [
                'input' => $texts,
                'model' => $this->model,
                'dimensions' => $this->dimensions,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['data'])) {
            throw new \RuntimeException('Invalid response from OpenAI embeddings API');
        }

        return array_map(fn($item) => $item['embedding'], $data['data']);
    }

    /**
     * Embed a single document (interface method).
     */
    public function embedDocument(Document $document): Document
    {
        $document->embedding = $this->embedText($document->content);
        return $document;
    }

    /**
     * Embed multiple documents (interface method).
     */
    public function embedDocuments(array $documents): array
    {
        $texts = array_map(fn($doc) => $doc->content, $documents);
        $embeddings = $this->embedTexts($texts);

        foreach ($documents as $i => $doc) {
            $doc->embedding = $embeddings[$i];
        }

        return $documents;
    }
}
