<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/integrations/Queue/DatabaseQueueAdapter.php';

class EventBus
{
    protected $CI;
    protected $queue;
    protected $listeners = [];

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('deals/deals_integration_model', 'deals_integration_model', true);
        $this->queue = new DatabaseQueueAdapter();
    }

    public function registerListener($eventPattern, callable $listener)
    {
        if (!isset($this->listeners[$eventPattern])) {
            $this->listeners[$eventPattern] = [];
        }

        $this->listeners[$eventPattern][] = $listener;
    }

    public function emit($eventName, array $payload = [], array $options = [])
    {
        return $this->queue->push([
            'event_name' => $eventName,
            'integration_id' => $options['integration_id'] ?? null,
            'account_id' => $options['account_id'] ?? null,
            'deal_id' => $options['deal_id'] ?? null,
            'source' => $options['source'] ?? 'deals',
            'direction' => $options['direction'] ?? 'internal',
            'payload' => $payload,
            'available_at' => $options['available_at'] ?? date('Y-m-d H:i:s'),
            'queue_name' => $options['queue_name'] ?? 'default',
        ]);
    }

    public function process($limit = 25)
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
        ];

        $events = $this->queue->reserve($limit);
        foreach ($events as $event) {
            try {
                foreach ($this->resolveListeners($event['event_name']) as $listener) {
                    call_user_func($listener, $event, $event['payload'] ?? []);
                }

                $this->queue->complete($event['id']);
                $results['processed']++;
            } catch (Throwable $e) {
                $this->queue->fail($event['id'], $e->getMessage());
                $this->CI->deals_integration_model->log_message([
                    'integration_id' => $event['integration_id'] ?? null,
                    'account_id' => $event['account_id'] ?? null,
                    'event_id' => $event['id'],
                    'level' => 'error',
                    'action' => 'event.process',
                    'message' => $e->getMessage(),
                    'context' => [
                        'event_name' => $event['event_name'],
                        'trace' => mb_strimwidth($e->getTraceAsString(), 0, 1500, '...'),
                    ],
                ]);
                $results['failed']++;
            }
        }

        return $results;
    }

    protected function resolveListeners($eventName)
    {
        $resolved = [];
        foreach ($this->listeners as $pattern => $listeners) {
            if ($this->matchesPattern($pattern, $eventName)) {
                foreach ($listeners as $listener) {
                    $resolved[] = $listener;
                }
            }
        }

        return $resolved;
    }

    protected function matchesPattern($pattern, $eventName)
    {
        if ($pattern === $eventName) {
            return true;
        }

        if (substr($pattern, -2) === '.*') {
            return strpos($eventName, substr($pattern, 0, -1)) === 0;
        }

        return false;
    }
}
