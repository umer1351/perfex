<?php

namespace PerfexChat\Neuron\Models;

use PerfexChat\Neuron\Concerns\ChatbotTrainable;

/**
 * TrainingQA Model
 * 
 * Represents Q&A-based training data for chatbots.
 */
class TrainingQA
{
    use ChatbotTrainable;

    public $id;
    public $chatbot_id;
    public $question;
    public $answer;
    public $training_status;
    public $trained_at;
    public $error_message;
    public $created_at;
    public $updated_at;

    /**
     * Create a new TrainingQA instance from database row.
     */
    public function __construct($data = null)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Find a training Q&A by ID.
     */
    public static function find(int $id): ?self
    {
        $CI = &get_instance();
        $row = $CI->db->where('id', $id)->get(db_prefix() . 'chatbot_training_qa')->row();
        return $row ? new self($row) : null;
    }

    /**
     * Get all training Q&As for a chatbot.
     */
    public static function forChatbot(int $chatbotId): array
    {
        $CI = &get_instance();
        $rows = $CI->db->where('chatbot_id', $chatbotId)
            ->order_by('created_at', 'DESC')
            ->get(db_prefix() . 'chatbot_training_qa')
            ->result();

        return array_map(fn($row) => new self($row), $rows);
    }

    /**
     * Get pending training Q&As for a chatbot.
     */
    public static function pendingForChatbot(int $chatbotId): array
    {
        $CI = &get_instance();
        $rows = $CI->db->where('chatbot_id', $chatbotId)
            ->where('training_status', 'pending')
            ->get(db_prefix() . 'chatbot_training_qa')
            ->result();

        return array_map(fn($row) => new self($row), $rows);
    }

    /**
     * Create a new training Q&A.
     */
    public static function create(array $data): self
    {
        $CI = &get_instance();
        $CI->db->insert(db_prefix() . 'chatbot_training_qa', [
            'chatbot_id' => $data['chatbot_id'],
            'question' => $data['question'],
            'answer' => $data['answer'],
            'training_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return self::find($CI->db->insert_id());
    }

    /**
     * Update the training Q&A.
     */
    public function update(array $data): bool
    {
        $CI = &get_instance();
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Reset training if content changed
        if ((isset($data['question']) && $data['question'] !== $this->question) ||
            (isset($data['answer']) && $data['answer'] !== $this->answer)
        ) {
            $data['training_status'] = 'pending';
            $data['trained_at'] = null;
        }

        return $CI->db->where('id', $this->id)
            ->update(db_prefix() . 'chatbot_training_qa', $data);
    }

    /**
     * Delete the training Q&A.
     */
    public function delete(): bool
    {
        $CI = &get_instance();
        return $CI->db->where('id', $this->id)
            ->delete(db_prefix() . 'chatbot_training_qa');
    }

    /**
     * Get the content for embeddings.
     */
    public function getEmbeddingContent(): string
    {
        return "Question: {$this->question}\nAnswer: {$this->answer}";
    }

    /**
     * Get the content prefix for embeddings.
     */
    public function getEmbeddingContentPrefix(): array
    {
        return ['FAQ'];
    }

    /**
     * Get source data for embedding metadata.
     */
    public function getEmbeddingSourceData(): array
    {
        return [
            'type' => 'qa',
            'name' => substr($this->question, 0, 100),
        ];
    }

    /**
     * Get the table name.
     */
    protected function getTableName(): string
    {
        return db_prefix() . 'chatbot_training_qa';
    }
}
