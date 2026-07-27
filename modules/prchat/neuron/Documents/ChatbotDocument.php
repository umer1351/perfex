<?php

namespace PerfexChat\Neuron\Documents;

use NeuronAI\RAG\Document;
use PerfexChat\Neuron\Models\TrainingFile;
use PerfexChat\Neuron\Models\TrainingLink;
use PerfexChat\Neuron\Models\TrainingQA;

/**
 * ChatbotDocument
 * 
 * Extends NeuronAI Document to include training model metadata.
 */
class ChatbotDocument extends Document
{
    /**
     * The training model class name.
     */
    public string $trainingModelName;

    /**
     * Create a new ChatbotDocument instance.
     */
    public function __construct(
        string $content,
        TrainingFile|TrainingQA|TrainingLink $trainingModel
    ) {
        $this->trainingModelName = get_class($trainingModel);

        $source = $trainingModel->getEmbeddingSourceData();
        $this->sourceName = $source['name'] ?? 'manual';
        $this->sourceType = $source['type'] ?? 'manual';

        parent::__construct($content);

        // Add metadata for filtering and retrieval
        $this->addMetadata('chatbot_id', $trainingModel->chatbot_id);
        $this->addMetadata('model', get_class($trainingModel));
        $this->addMetadata('model_id', $trainingModel->id);
    }

    /**
     * Create a document from a training model.
     */
    public static function fromTrainingModel($model): self
    {
        $prefixes = $model->getEmbeddingContentPrefix();
        $content = $model->getEmbeddingContent();

        // Prepend prefixes to content
        if (!empty($prefixes)) {
            $content = implode("\n", $prefixes) . "\n\n" . $content;
        }

        return new self($content, $model);
    }
}
