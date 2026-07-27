<?php

namespace PerfexChat\Neuron\VectorStore;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\FileVectorStore;
use NeuronAI\RAG\VectorStore\VectorSimilarity;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use PerfexChat\Neuron\Models\Chatbot;
use PerfexChat\Neuron\Models\TrainingFile;
use PerfexChat\Neuron\Models\TrainingLink;
use PerfexChat\Neuron\Models\TrainingQA;

/**
 * File-based vector store using JSONL (one JSON object per line).
 * Compatible with parent FileVectorStore format.
 */
class ChatbotVectorStore extends FileVectorStore
{
    protected Chatbot $chatbot;

    public function __construct(Chatbot $chatbot)
    {
        $this->chatbot = $chatbot;

        $directory = module_dir_path('prchat', 'uploads/vectors/');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        parent::__construct($directory, 5, 'chatbot_' . $chatbot->id, '.json');
    }

    /**
     * Only accept documents that have embeddings.
     */
    public function addDocuments(array $documents): VectorStoreInterface
    {
        $valid = array_filter($documents, function (Document $doc) {
            return !empty($doc->embedding);
        });

        if (empty($valid)) {
            return $this;
        }

        return parent::addDocuments(array_values($valid));
    }

    /**
     * Remove all JSONL lines belonging to a specific training model.
     */
    public function deleteByModel(TrainingFile|TrainingQA|TrainingLink $model): void
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            return;
        }

        $className = get_class($model);
        $modelId = $model->id;
        $tmpFile = $filePath . '.tmp';
        $out = fopen($tmpFile, 'w');

        foreach ($this->getLine($filePath) as $line) {
            $line = trim((string) $line);
            if (empty($line)) {
                continue;
            }
            $doc = json_decode($line, true);
            if (!is_array($doc)) {
                continue;
            }
            $meta = $doc['metadata'] ?? [];
            if (($meta['model'] ?? '') === $className && ($meta['model_id'] ?? '') == $modelId) {
                continue;
            }
            fwrite($out, $line . PHP_EOL);
        }

        fclose($out);
        rename($tmpFile, $filePath);
    }

    /**
     * Truncate the store file.
     */
    public function clear(): void
    {
        $filePath = $this->getFilePath();
        file_put_contents($filePath, '');
    }

    /**
     * Similarity search that gracefully skips documents with null embeddings.
     */
    public function similaritySearch(array $embedding): array
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            return [];
        }

        $topItems = [];

        foreach ($this->getLine($filePath) as $line) {
            $line = trim((string) $line);
            if (empty($line)) {
                continue;
            }
            $document = json_decode($line, true);
            if (!is_array($document) || empty($document['embedding'])) {
                continue;
            }

            $dist = VectorSimilarity::cosineDistance($embedding, $document['embedding']);
            $topItems[] = ['dist' => $dist, 'document' => $document];
            usort($topItems, fn(array $a, array $b): int => $a['dist'] <=> $b['dist']);

            if (count($topItems) > $this->topK) {
                $topItems = array_slice($topItems, 0, $this->topK, true);
            }
        }

        return array_map(function (array $item): Document {
            $d = $item['document'];
            $doc = new Document($d['content']);
            $doc->embedding = $d['embedding'];
            $doc->sourceType = $d['sourceType'] ?? '';
            $doc->sourceName = $d['sourceName'] ?? '';
            $doc->id = $d['id'] ?? 0;
            $doc->score = VectorSimilarity::similarityFromDistance($item['dist']);
            $doc->metadata = $d['metadata'] ?? [];
            return $doc;
        }, $topItems);
    }

    public function getFilePath(): string
    {
        return module_dir_path('prchat', 'uploads/vectors/') . 'chatbot_' . $this->chatbot->id . '.json';
    }

    protected function getStoreFilePath(): string
    {
        return $this->getFilePath();
    }

    public function hasDocuments(): bool
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            return false;
        }
        return filesize($filePath) > 0;
    }

    /**
     * Read first valid JSONL line to get embedding dimensions.
     */
    public function getStoredEmbeddingDimensions(): int
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            return 0;
        }

        $f = @fopen($filePath, 'r');
        if (!$f) {
            return 0;
        }

        try {
            while ($line = fgets($f)) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $doc = json_decode($line, true);
                if (is_array($doc) && !empty($doc['embedding']) && is_array($doc['embedding'])) {
                    return count($doc['embedding']);
                }
            }
        } finally {
            fclose($f);
        }

        return 0;
    }

    public function getDocumentCount(): int
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            return 0;
        }

        $count = 0;
        foreach ($this->getLine($filePath) as $line) {
            if (!empty(trim((string) $line))) {
                $count++;
            }
        }
        return $count;
    }
}
