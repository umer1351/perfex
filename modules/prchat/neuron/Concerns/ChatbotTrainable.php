<?php

namespace PerfexChat\Neuron\Concerns;

/**
 * Trait ChatbotTrainable
 * 
 * Provides common functionality for all training data models.
 */
trait ChatbotTrainable
{
    /**
     * Get the content to be used for embeddings.
     * Must be implemented by each training model.
     */
    abstract public function getEmbeddingContent(): string;

    /**
     * Get the source data for the embedding metadata.
     */
    abstract public function getEmbeddingSourceData(): array;

    /**
     * Get the prefix for embedding content (e.g., section title).
     */
    public function getEmbeddingContentPrefix(): array
    {
        return [];
    }

    /**
     * Check if the model is pending training.
     */
    public function isTrainingPending(): bool
    {
        return $this->training_status === 'pending';
    }

    /**
     * Check if the model is currently training.
     */
    public function isTraining(): bool
    {
        return $this->training_status === 'training';
    }

    /**
     * Check if the model has been trained.
     */
    public function isTrained(): bool
    {
        return $this->training_status === 'trained';
    }

    /**
     * Check if training failed.
     */
    public function isTrainingFailed(): bool
    {
        return $this->training_status === 'failed';
    }

    /**
     * Mark the model as training.
     */
    public function markAsTraining(): void
    {
        $CI = &get_instance();
        $CI->db->where('id', $this->id);
        $CI->db->update($this->getTableName(), [
            'training_status' => 'training',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->training_status = 'training';
    }

    /**
     * Mark the model as trained.
     */
    public function markAsTrained(): void
    {
        $CI = &get_instance();
        $CI->db->where('id', $this->id);
        $CI->db->update($this->getTableName(), [
            'training_status' => 'trained',
            'trained_at' => date('Y-m-d H:i:s'),
            'error_message' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->training_status = 'trained';
        $this->trained_at = date('Y-m-d H:i:s');
    }

    /**
     * Mark the model as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $CI = &get_instance();
        $CI->db->where('id', $this->id);
        $CI->db->update($this->getTableName(), [
            'training_status' => 'failed',
            'trained_at' => null,
            'error_message' => $errorMessage,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->training_status = 'failed';
        $this->error_message = $errorMessage;
    }

    /**
     * Reset training status to pending.
     */
    public function resetTraining(): void
    {
        $CI = &get_instance();
        $CI->db->where('id', $this->id);
        $CI->db->update($this->getTableName(), [
            'training_status' => 'pending',
            'trained_at' => null,
            'error_message' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->training_status = 'pending';
    }

    /**
     * Get the database table name for this model.
     */
    abstract protected function getTableName(): string;
}
