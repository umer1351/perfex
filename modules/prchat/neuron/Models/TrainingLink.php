<?php

namespace PerfexChat\Neuron\Models;

use PerfexChat\Neuron\Concerns\ChatbotTrainable;

/**
 * TrainingLink Model
 * 
 * Represents URL-based training data for chatbots.
 * Content is extracted from the URL and used for training.
 * Supports crawling subpages to extract content from entire sites.
 */
class TrainingLink
{
    use ChatbotTrainable;

    public $id;
    public $chatbot_id;
    public $url;
    public $title;
    public $extracted_content;
    public $processing_status;
    public $processed_at;
    public $training_status;
    public $trained_at;
    public $error_message;
    public $crawl_subpages;
    public $crawl_depth;
    public $parent_link_id;
    public $created_at;
    public $updated_at;

    // Track crawled URLs to avoid duplicates during crawling
    private static $crawledUrls = [];

    /**
     * Create a new TrainingLink instance from database row.
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
     * Find a training link by ID.
     */
    public static function find(int $id): ?self
    {
        $CI = &get_instance();
        $row = $CI->db->where('id', $id)->get(db_prefix() . 'chatbot_training_links')->row();
        return $row ? new self($row) : null;
    }

    /**
     * Get all training links for a chatbot.
     */
    public static function forChatbot(int $chatbotId): array
    {
        $CI = &get_instance();
        $rows = $CI->db->where('chatbot_id', $chatbotId)
            ->order_by('created_at', 'DESC')
            ->get(db_prefix() . 'chatbot_training_links')
            ->result();

        return array_map(fn($row) => new self($row), $rows);
    }

    /**
     * Get pending training links for a chatbot.
     */
    public static function pendingForChatbot(int $chatbotId): array
    {
        $CI = &get_instance();
        $rows = $CI->db->where('chatbot_id', $chatbotId)
            ->where('training_status', 'pending')
            ->where('processing_status', 'completed')
            ->get(db_prefix() . 'chatbot_training_links')
            ->result();

        return array_map(fn($row) => new self($row), $rows);
    }

    /**
     * Create a new training link.
     */
    public static function create(array $data): self
    {
        $CI = &get_instance();
        $CI->db->insert(db_prefix() . 'chatbot_training_links', [
            'chatbot_id' => $data['chatbot_id'],
            'url' => $data['url'],
            'title' => $data['title'] ?? null,
            'processing_status' => 'pending',
            'training_status' => 'pending',
            'crawl_subpages' => $data['crawl_subpages'] ?? 0,
            'crawl_depth' => $data['crawl_depth'] ?? 1,
            'parent_link_id' => $data['parent_link_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return self::find($CI->db->insert_id());
    }

    /**
     * Update the training link.
     */
    public function update(array $data): bool
    {
        $CI = &get_instance();
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $CI->db->where('id', $this->id)
            ->update(db_prefix() . 'chatbot_training_links', $data);
    }

    /**
     * Delete the training link.
     */
    public function delete(): bool
    {
        $CI = &get_instance();
        return $CI->db->where('id', $this->id)
            ->delete(db_prefix() . 'chatbot_training_links');
    }

    /**
     * Process the URL and extract content.
     * If crawl_subpages is enabled, also crawl and create entries for subpages.
     */
    public function process(): bool
    {
        $this->update(['processing_status' => 'processing']);

        try {
            // Reset crawled URLs tracker for this crawl session
            if (!$this->parent_link_id) {
                self::$crawledUrls = [];
            }

            // Mark this URL as crawled
            self::$crawledUrls[$this->normalizeUrl($this->url)] = true;

            $html = $this->fetchHtml($this->url);
            if (!$html) {
                $this->update([
                    'processing_status' => 'failed',
                    'error_message' => 'Could not fetch content from URL',
                ]);
                return false;
            }

            $content = $this->extractContentFromHtml($html);

            if (empty($content)) {
                $this->update([
                    'processing_status' => 'failed',
                    'error_message' => 'Could not extract content from URL',
                ]);
                return false;
            }

            // Extract title if not set
            $title = $this->title;
            if (empty($title)) {
                $title = $this->extractTitleFromHtml($html);
            }

            $this->update([
                'title' => $title,
                'extracted_content' => $content,
                'processing_status' => 'completed',
                'processed_at' => date('Y-m-d H:i:s'),
                'error_message' => null,
            ]);

            // If crawl_subpages is enabled, discover subpages (don't process them yet)
            if ($this->crawl_subpages && $this->crawl_depth > 0 && !$this->parent_link_id) {
                $this->discoverSubpages($html);
            }

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
     * Discover subpages from the given HTML (creates entries without processing).
     */
    protected function discoverSubpages(string $html): void
    {
        $links = $this->extractLinks($html);
        $baseUrl = $this->getBaseUrl($this->url);
        $baseDomain = parse_url($this->url, PHP_URL_HOST);

        $createdCount = 0;
        // Limit based on crawl depth: depth 1 = 50, depth 2 = 100, depth 3 = 150
        $maxSubpages = min(150, 50 * max(1, (int)$this->crawl_depth));

        foreach ($links as $link) {
            if ($createdCount >= $maxSubpages) {
                break;
            }

            // Normalize the link
            $fullUrl = $this->resolveUrl($link, $baseUrl);
            if (!$fullUrl) {
                continue;
            }

            // Check if same domain
            $linkDomain = parse_url($fullUrl, PHP_URL_HOST);
            if ($linkDomain !== $baseDomain) {
                continue;
            }

            // Skip already discovered URLs
            $normalizedUrl = $this->normalizeUrl($fullUrl);
            if (isset(self::$crawledUrls[$normalizedUrl])) {
                continue;
            }

            // Skip non-content URLs (images, downloads, etc.)
            if ($this->isNonContentUrl($fullUrl)) {
                continue;
            }

            // Check if this URL already exists for this chatbot
            $CI = &get_instance();
            $exists = $CI->db->where('chatbot_id', $this->chatbot_id)
                ->where('url', $fullUrl)
                ->get(db_prefix() . 'chatbot_training_links')
                ->num_rows() > 0;

            if ($exists) {
                self::$crawledUrls[$normalizedUrl] = true;
                continue;
            }

            // Mark as discovered
            self::$crawledUrls[$normalizedUrl] = true;

            // Create new training link entry (pending - will be processed during training)
            // If depth > 1, enable crawling on subpages too (recursive)
            $subpageCrawlDepth = max(0, (int)$this->crawl_depth - 1);
            try {
                self::create([
                    'chatbot_id' => $this->chatbot_id,
                    'url' => $fullUrl,
                    'crawl_subpages' => $subpageCrawlDepth > 0 ? 1 : 0,
                    'crawl_depth' => $subpageCrawlDepth,
                    'parent_link_id' => $this->id,
                ]);
                $createdCount++;
            } catch (\Exception $e) {
                log_message('error', 'Failed to create subpage link: ' . $e->getMessage());
            }
        }
    }

    /**
     * Fetch HTML from URL.
     */
    protected function fetchHtml(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'follow_location' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        return @file_get_contents($url, false, $context) ?: '';
    }

    /**
     * Extract links from HTML.
     */
    protected function extractLinks(string $html): array
    {
        $links = [];

        // Match all href attributes
        if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            $links = array_unique($matches[1]);
        }

        return $links;
    }

    /**
     * Get base URL from a full URL.
     */
    protected function getBaseUrl(string $url): string
    {
        $parts = parse_url($url);
        return ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
    }

    /**
     * Resolve a potentially relative URL to an absolute URL.
     */
    protected function resolveUrl(string $link, string $baseUrl): ?string
    {
        // Skip anchors, javascript, mailto, etc.
        if (
            empty($link) ||
            str_starts_with($link, '#') ||
            str_starts_with($link, 'javascript:') ||
            str_starts_with($link, 'mailto:') ||
            str_starts_with($link, 'tel:')
        ) {
            return null;
        }

        // Already absolute URL
        if (str_starts_with($link, 'http://') || str_starts_with($link, 'https://')) {
            return $link;
        }

        // Protocol-relative URL
        if (str_starts_with($link, '//')) {
            return 'https:' . $link;
        }

        // Absolute path
        if (str_starts_with($link, '/')) {
            return $baseUrl . $link;
        }

        // Relative path
        return $baseUrl . '/' . $link;
    }

    /**
     * Normalize URL for comparison (remove fragments, trailing slashes).
     */
    protected function normalizeUrl(string $url): string
    {
        // Remove fragment
        $url = preg_replace('/#.*$/', '', $url);
        // Remove trailing slash
        $url = rtrim($url, '/');
        // Lowercase
        return strtolower($url);
    }

    /**
     * Check if URL is a non-content URL (image, download, etc.)
     */
    protected function isNonContentUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $nonContentExtensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'svg',
            'webp',
            'ico',
            'bmp',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'zip',
            'rar',
            'tar',
            'gz',
            '7z',
            'mp3',
            'mp4',
            'avi',
            'mov',
            'wmv',
            'flv',
            'css',
            'js',
            'json',
            'xml',
            'rss',
            'atom'
        ];

        return in_array($ext, $nonContentExtensions);
    }

    /**
     * Extract content from HTML.
     */
    protected function extractContentFromHtml(string $html): string
    {
        // Try to find main content areas
        $mainContent = '';

        if (preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $match)) {
            $mainContent = $match[1];
        } elseif (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $match)) {
            $mainContent = $match[1];
        } elseif (preg_match('/<div[^>]*class="[^"]*content[^"]*"[^>]*>(.*?)<\/div>/is', $html, $match)) {
            $mainContent = $match[1];
        } elseif (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $match)) {
            $mainContent = $match[1];
        } else {
            $mainContent = $html;
        }

        // Remove unwanted elements
        $mainContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $mainContent);
        $mainContent = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $mainContent);
        $mainContent = preg_replace('/<nav\b[^>]*>(.*?)<\/nav>/is', '', $mainContent);
        $mainContent = preg_replace('/<header\b[^>]*>(.*?)<\/header>/is', '', $mainContent);
        $mainContent = preg_replace('/<footer\b[^>]*>(.*?)<\/footer>/is', '', $mainContent);
        $mainContent = preg_replace('/<aside\b[^>]*>(.*?)<\/aside>/is', '', $mainContent);
        $mainContent = preg_replace('/<form\b[^>]*>(.*?)<\/form>/is', '', $mainContent);

        // Strip HTML tags
        $text = strip_tags($mainContent);

        // Clean whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim($text);

        return $text;
    }

    /**
     * Extract content from a URL (legacy method for compatibility).
     */
    protected function extractContentFromUrl(string $url): string
    {
        $html = $this->fetchHtml($url);
        return $html ? $this->extractContentFromHtml($html) : '';
    }

    /**
     * Extract title from HTML.
     */
    protected function extractTitleFromHtml(string $html): string
    {
        if (preg_match('/<title>([^<]+)<\/title>/i', $html, $match)) {
            return html_entity_decode(trim($match[1]), ENT_QUOTES, 'UTF-8');
        }

        // Try h1
        if (preg_match('/<h1[^>]*>([^<]+)<\/h1>/i', $html, $match)) {
            return html_entity_decode(trim($match[1]), ENT_QUOTES, 'UTF-8');
        }

        // Fallback to URL path
        $path = parse_url($this->url, PHP_URL_PATH);
        return ucwords(str_replace(['-', '_', '/'], ' ', basename($path ?: '')));
    }

    /**
     * Extract title from URL (legacy method for compatibility).
     */
    protected function extractTitleFromUrl(string $url): string
    {
        $html = $this->fetchHtml($url);
        return $html ? $this->extractTitleFromHtml($html) : basename($url);
    }

    /**
     * Get the content for embeddings.
     */
    public function getEmbeddingContent(): string
    {
        $content = preg_replace("/\n+/", "\n", $this->extracted_content);
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        $content = preg_replace('/[\x00-\x1F\x7F]/u', '', $content);
        return html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Get the content prefix for embeddings.
     */
    public function getEmbeddingContentPrefix(): array
    {
        $prefix = ['Section: ' . ($this->title ?: 'Website Content')];
        $prefix[] = 'Link: ' . $this->url;
        return $prefix;
    }

    /**
     * Get source data for embedding metadata.
     */
    public function getEmbeddingSourceData(): array
    {
        return [
            'type' => 'link',
            'name' => $this->url,
        ];
    }

    /**
     * Get the table name.
     */
    protected function getTableName(): string
    {
        return db_prefix() . 'chatbot_training_links';
    }
}
