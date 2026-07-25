<?php

namespace PerfexChat\Neuron\Models;

use PerfexChat\Neuron\Concerns\ChatbotTrainable;

/**
 * TrainingFile Model
 * 
 * Represents file-based training data for chatbots.
 * Supports PDF, TXT, DOC, DOCX files.
 */
class TrainingFile
{
    use ChatbotTrainable;

    public $id;
    public $chatbot_id;
    public $original_name;
    public $file_path;
    public $file_type;
    public $file_size;
    public $file_data;
    public $extracted_content;
    public $processing_status;
    public $processed_at;
    public $training_status;
    public $trained_at;
    public $error_message;
    public $created_at;
    public $updated_at;

    /**
     * Create a new TrainingFile instance from database row.
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
     * Find a training file by ID.
     */
    public static function find(int $id): ?self
    {
        $CI = &get_instance();
        $row = $CI->db->where('id', $id)->get(db_prefix() . 'chatbot_training_files')->row();
        return $row ? new self($row) : null;
    }

    /**
     * Get all training files for a chatbot.
     */
    public static function forChatbot(int $chatbotId): array
    {
        $CI = &get_instance();
        $rows = $CI->db->select('id, chatbot_id, original_name, file_path, file_type, file_size, extracted_content, processing_status, processed_at, training_status, trained_at, error_message, created_at, updated_at')
            ->where('chatbot_id', $chatbotId)
            ->order_by('created_at', 'DESC')
            ->get(db_prefix() . 'chatbot_training_files')
            ->result();

        return array_map(fn($row) => new self($row), $rows);
    }

    /**
     * Get pending training files for a chatbot.
     */
    public static function pendingForChatbot(int $chatbotId): array
    {
        $CI = &get_instance();
        $rows = $CI->db->select('id, chatbot_id, original_name, file_path, file_type, file_size, extracted_content, processing_status, processed_at, training_status, trained_at, error_message, created_at, updated_at')
            ->where('chatbot_id', $chatbotId)
            ->where('training_status', 'pending')
            ->where('processing_status', 'completed')
            ->get(db_prefix() . 'chatbot_training_files')
            ->result();

        return array_map(fn($row) => new self($row), $rows);
    }

    /**
     * Get supported file extensions.
     */
    public static function supportedExtensions(): array
    {
        return ['txt', 'pdf', 'doc', 'docx', 'md'];
    }

    /**
     * Create a new training file.
     */
    public static function create(array $data): self
    {
        $CI = &get_instance();
        $row = [
            'chatbot_id' => $data['chatbot_id'],
            'original_name' => $data['original_name'],
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'processing_status' => 'pending',
            'training_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if (!empty($data['file_data'])) {
            $row['file_data'] = $data['file_data'];
        }
        $CI->db->insert(db_prefix() . 'chatbot_training_files', $row);

        return self::find($CI->db->insert_id());
    }

    /**
     * Update the training file.
     */
    public function update(array $data): bool
    {
        $CI = &get_instance();
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $CI->db->where('id', $this->id)
            ->update(db_prefix() . 'chatbot_training_files', $data);
    }

    /**
     * Delete the training file.
     */
    public function delete(): bool
    {
        // Delete the physical file
        if ($this->file_path && file_exists(FCPATH . $this->file_path)) {
            @unlink(FCPATH . $this->file_path);
        }

        $CI = &get_instance();
        return $CI->db->where('id', $this->id)
            ->delete(db_prefix() . 'chatbot_training_files');
    }

    /**
     * Process the file and extract content.
     */
    public function process(): bool
    {
        $this->update(['processing_status' => 'processing']);

        try {
            $fullPath = FCPATH . $this->file_path;

            if (!file_exists($fullPath) && !empty($this->file_data)) {
                $dir = dirname($fullPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($fullPath, base64_decode($this->file_data));
            }

            if (!file_exists($fullPath)) {
                throw new \Exception('File not found: ' . $this->file_path);
            }

            $content = $this->extractContentFromFile($fullPath);

            if (empty($content)) {
                throw new \Exception('Could not extract content from file');
            }

            $this->update([
                'extracted_content' => $content,
                'processing_status' => 'completed',
                'processed_at' => date('Y-m-d H:i:s'),
                'error_message' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->update([
                'processing_status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Extract content from file based on type.
     */
    protected function extractContentFromFile(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'txt':
            case 'md':
                return file_get_contents($filePath);

            case 'pdf':
                return $this->extractFromPdf($filePath);

            case 'doc':
            case 'docx':
                return $this->extractFromWord($filePath);

            default:
                throw new \Exception('Unsupported file type: ' . $extension);
        }
    }

    /**
     * Extract text from PDF file.
     */
    protected function extractFromPdf(string $filePath): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            if (empty(trim($text))) {
                throw new \Exception('PDF appears to be empty or image-based');
            }

            $text = preg_replace('/\s+/', ' ', $text);
            return trim($text);
        } catch (\Exception $e) {
            throw new \Exception('Failed to extract PDF content: ' . $e->getMessage());
        }
    }

    /**
     * Extract text from Word document.
     */
    protected function extractFromWord(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'docx') {
            // DOCX is a ZIP file containing XML
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $content = '';

                // Read document.xml
                $xml = $zip->getFromName('word/document.xml');
                if ($xml) {
                    // Strip XML tags but preserve text
                    $content = strip_tags($xml);
                    $content = preg_replace('/\s+/', ' ', $content);
                }

                $zip->close();
                return trim($content);
            }
        }

        // DOC format - more complex, try antiword if available
        $output = [];
        exec("which antiword", $output, $returnCode);

        if ($returnCode === 0) {
            $content = shell_exec("antiword " . escapeshellarg($filePath));
            return $content ?: '';
        }

        throw new \Exception('Cannot extract content from .doc file. Please install antiword or use .docx format.');
    }

    /**
     * Get the content for embeddings.
     */
    public function getEmbeddingContent(): string
    {
        return $this->extracted_content ?: '';
    }

    /**
     * Get the content prefix for embeddings.
     */
    public function getEmbeddingContentPrefix(): array
    {
        return ['Document: ' . $this->original_name];
    }

    /**
     * Get source data for embedding metadata.
     */
    public function getEmbeddingSourceData(): array
    {
        return [
            'type' => 'file',
            'name' => $this->original_name,
        ];
    }

    /**
     * Get the table name.
     */
    protected function getTableName(): string
    {
        return db_prefix() . 'chatbot_training_files';
    }
}
