<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SyncEngine
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('deals/deals_integration_model', 'deals_integration_model', true);
    }

    public function queueResourceSync($providerKey, $accountId, $resourceType, array $context = [])
    {
        $availableAt = date('Y-m-d H:i:s', time() + (int) ($context['delay_seconds'] ?? 0));

        return $this->CI->deals_integration_model->create_event([
            'event_name' => 'sync.requested',
            'account_id' => $accountId,
            'deal_id' => $context['deal_id'] ?? null,
            'source' => 'sync_engine',
            'payload' => [
                'provider_key' => $providerKey,
                'resource_type' => $resourceType,
                'context' => $context,
            ],
            'available_at' => $availableAt,
            'queue_name' => 'sync',
        ]);
    }

    public function handleSyncRequest(array $event, array $payload, $provider)
    {
        $accountId = (int) ($event['account_id'] ?? 0);
        $resourceType = (string) ($payload['resource_type'] ?? '');
        $context = $payload['context'] ?? [];
        $account = $this->CI->deals_integration_model->get_account($accountId);
        if (!$account) {
            throw new RuntimeException('Integration account not found for sync.');
        }

        $currentStateRows = $this->CI->deals_integration_model->get_sync_states($accountId);
        $currentState = [];
        foreach ($currentStateRows as $row) {
            if (($row['resource_type'] ?? '') === $resourceType) {
                $currentState = $row;
                break;
            }
        }

        $syncResult = $provider->sync($account, $resourceType, $currentState, $context);
        $this->CI->deals_integration_model->save_sync_state($provider->getKey(), $accountId, $resourceType, [
            'cursor_token' => $syncResult['cursor_token'] ?? ($currentState['cursor_token'] ?? null),
            'external_channel_id' => $syncResult['external_channel_id'] ?? ($currentState['external_channel_id'] ?? null),
            'external_resource_id' => $syncResult['external_resource_id'] ?? ($currentState['external_resource_id'] ?? null),
            'sync_status' => !empty($syncResult['success']) ? 'synced' : 'failed',
            'last_synced_at' => date('Y-m-d H:i:s'),
            'next_sync_at' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
            'state' => $syncResult['state'] ?? [],
        ]);

        $this->CI->deals_integration_model->touch_account_sync($accountId);
        $this->CI->deals_integration_model->log_message([
            'integration_id' => $account['integration_id'] ?? null,
            'account_id' => $accountId,
            'event_id' => $event['id'] ?? null,
            'level' => !empty($syncResult['success']) ? 'info' : 'warning',
            'action' => 'sync.' . $resourceType,
            'message' => $syncResult['message'] ?? 'Sync processed.',
            'context' => $syncResult,
        ]);
    }
}
