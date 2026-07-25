<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/SyncEngine.php';

class AutomationEngine
{
    protected $CI;
    protected $syncEngine;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('deals/deals_integration_model', 'deals_integration_model', true);
        $this->syncEngine = new SyncEngine();
    }

    public function handleBusinessEvent(array $event, array $payload)
    {
        $accounts = $this->CI->deals_integration_model->get_accounts();
        foreach ($accounts as $account) {
            if (empty($account['is_active']) || ($account['connection_status'] ?? '') !== 'connected') {
                continue;
            }

            foreach ($this->resolveResourcesForEvent($account['provider_key'], $event['event_name']) as $resourceType) {
                $this->syncEngine->queueResourceSync($account['provider_key'], (int) $account['id'], $resourceType, [
                    'deal_id' => $event['deal_id'] ?? null,
                    'event_name' => $event['event_name'],
                    'event_payload' => $payload,
                ]);
            }
        }
    }

    protected function resolveResourcesForEvent($providerKey, $eventName)
    {
        $map = [
            'google_workspace' => [
                'deal.created' => ['gmail', 'calendar'],
                'deal.updated' => ['gmail', 'calendar'],
                'email.received' => ['gmail'],
                'meeting.created' => ['calendar'],
                'file.uploaded' => ['drive'],
            ],
            'slack' => [
                'deal.created' => ['messaging'],
                'deal.updated' => ['messaging'],
                'email.received' => ['messaging'],
                'meeting.created' => ['messaging'],
                'file.uploaded' => ['messaging'],
            ],
        ];

        return $map[$providerKey][$eventName] ?? [];
    }
}
