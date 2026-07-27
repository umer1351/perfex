<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Deals_integration_model extends App_Model
{
    protected $tableIntegrations = 'tbl_integrations';
    protected $tableAccounts = 'tbl_integration_accounts';
    protected $tableEvents = 'tbl_integration_events';
    protected $tableLogs = 'tbl_integration_logs';
    protected $tableSyncStates = 'tbl_sync_states';

    public function get_integrations()
    {
        if (!$this->db->table_exists($this->tableIntegrations)) {
            return [];
        }

        return $this->db->order_by('name', 'ASC')->get($this->tableIntegrations)->result_array();
    }

    public function get_integration_by_key($providerKey)
    {
        if (!$this->db->table_exists($this->tableIntegrations)) {
            return null;
        }

        return $this->db->where('provider_key', $providerKey)->get($this->tableIntegrations)->row_array();
    }

    public function ensure_default_integrations(array $definitions)
    {
        if (!$this->db->table_exists($this->tableIntegrations)) {
            return;
        }

        foreach ($definitions as $definition) {
            $existing = $this->get_integration_by_key($definition['provider_key']);
            $payload = [
                'provider_key' => $definition['provider_key'],
                'name' => $definition['name'],
                'driver_class' => $definition['driver_class'],
                'supports_oauth' => !empty($definition['supports_oauth']) ? 1 : 0,
                'supports_webhooks' => !empty($definition['supports_webhooks']) ? 1 : 0,
                'supports_sync' => !empty($definition['supports_sync']) ? 1 : 0,
                'status' => $definition['status'] ?? 'active',
                'config_json' => json_encode($definition['config'] ?? []),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->db->where('id', $existing['id'])->update($this->tableIntegrations, $payload);
                continue;
            }

            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->tableIntegrations, $payload);
        }
    }

    public function get_accounts($providerKey = null)
    {
        if (!$this->db->table_exists($this->tableAccounts)) {
            return [];
        }

        $syncCountSelect = $this->db->table_exists($this->tableSyncStates)
            ? '(SELECT COUNT(*) FROM ' . $this->tableSyncStates . ' WHERE ' . $this->tableSyncStates . '.account_id = ' . $this->tableAccounts . '.id) as sync_state_count'
            : '0 as sync_state_count';
        $integrationNameSelect = $this->db->table_exists($this->tableIntegrations)
            ? $this->tableIntegrations . '.name as integration_name'
            : 'NULL as integration_name';
        $this->db->select($this->tableAccounts . '.*, ' . $integrationNameSelect . ', ' . $syncCountSelect, false);
        $this->db->from($this->tableAccounts);
        if ($this->db->table_exists($this->tableIntegrations)) {
            $this->db->join($this->tableIntegrations, $this->tableIntegrations . '.id = ' . $this->tableAccounts . '.integration_id', 'left');
        }
        if (!empty($providerKey)) {
            $this->db->where($this->tableAccounts . '.provider_key', $providerKey);
        }
        $this->db->order_by($this->tableAccounts . '.provider_key', 'ASC');
        $this->db->order_by($this->tableAccounts . '.account_label', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_account($id)
    {
        if (!$this->db->table_exists($this->tableAccounts)) {
            return null;
        }

        return $this->db->where('id', $id)->get($this->tableAccounts)->row_array();
    }

    public function get_account_by_state($providerKey, $oauthState)
    {
        if (!$this->db->table_exists($this->tableAccounts) || $providerKey === '' || $oauthState === '') {
            return null;
        }

        return $this->db
            ->where('provider_key', $providerKey)
            ->where('oauth_state', $oauthState)
            ->get($this->tableAccounts)
            ->row_array();
    }

    public function save_account(array $data, $id = null)
    {
        if (!$this->db->table_exists($this->tableAccounts)) {
            return false;
        }

        $integration = $this->get_integration_by_key($data['provider_key']);
        $existing = $id ? $this->get_account($id) : null;
        $existingMeta = json_decode($existing['account_meta_json'] ?? '[]', true) ?: [];
        $incomingMeta = $data['account_meta'] ?? [];
        foreach (['client_id', 'client_secret'] as $metaKey) {
            if (array_key_exists($metaKey, $incomingMeta) && $incomingMeta[$metaKey] === '' && array_key_exists($metaKey, $existingMeta)) {
                unset($incomingMeta[$metaKey]);
            }
        }

        $payload = [
            'integration_id' => $integration['id'] ?? null,
            'provider_key' => $data['provider_key'],
            'staff_id' => $data['staff_id'] ?? get_staff_user_id(),
            'account_label' => trim((string) ($data['account_label'] ?? '')),
            'external_account_id' => trim((string) ($data['external_account_id'] ?? '')),
            'connection_status' => $data['connection_status'] ?? 'pending_auth',
            'scopes_json' => json_encode(array_values(array_filter($data['scopes'] ?? []))),
            'access_token_encrypted' => array_key_exists('access_token', $data) && $data['access_token'] !== ''
                ? $this->encrypt_value($data['access_token'])
                : ($existing['access_token_encrypted'] ?? null),
            'refresh_token_encrypted' => array_key_exists('refresh_token', $data) && $data['refresh_token'] !== ''
                ? $this->encrypt_value($data['refresh_token'])
                : ($existing['refresh_token_encrypted'] ?? null),
            'token_expires_at' => !empty($data['token_expires_at']) ? $data['token_expires_at'] : ($existing['token_expires_at'] ?? null),
            'webhook_secret' => !empty($data['webhook_secret']) ? $data['webhook_secret'] : ($existing['webhook_secret'] ?? bin2hex(random_bytes(16))),
            'oauth_state' => !empty($data['oauth_state']) ? $data['oauth_state'] : ($existing['oauth_state'] ?? bin2hex(random_bytes(12))),
            'account_meta_json' => json_encode(array_merge($existingMeta, $incomingMeta)),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->db->where('id', $id)->update($this->tableAccounts, $payload);
            return $id;
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->tableAccounts, $payload);

        return $this->db->insert_id();
    }

    public function disconnect_account($id)
    {
        if (!$this->db->table_exists($this->tableAccounts)) {
            return false;
        }

        return $this->db->where('id', $id)->update($this->tableAccounts, [
            'connection_status' => 'disconnected',
            'is_active' => 0,
            'access_token_encrypted' => null,
            'refresh_token_encrypted' => null,
            'token_expires_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function touch_account_webhook($id)
    {
        if (!$this->db->table_exists($this->tableAccounts)) {
            return false;
        }

        return $this->db->where('id', $id)->update($this->tableAccounts, [
            'last_webhook_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function touch_account_sync($id)
    {
        if (!$this->db->table_exists($this->tableAccounts)) {
            return false;
        }

        return $this->db->where('id', $id)->update($this->tableAccounts, [
            'last_synced_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function create_event(array $data)
    {
        if (!$this->db->table_exists($this->tableEvents)) {
            return false;
        }

        $payload = [
            'event_uuid' => $data['event_uuid'] ?? bin2hex(random_bytes(16)),
            'event_name' => $data['event_name'],
            'integration_id' => $data['integration_id'] ?? null,
            'account_id' => $data['account_id'] ?? null,
            'deal_id' => $data['deal_id'] ?? null,
            'source' => $data['source'] ?? 'deals',
            'direction' => $data['direction'] ?? 'internal',
            'payload_json' => json_encode($data['payload'] ?? []),
            'status' => $data['status'] ?? 'queued',
            'attempts' => (int) ($data['attempts'] ?? 0),
            'available_at' => $data['available_at'] ?? date('Y-m-d H:i:s'),
            'processed_at' => $data['processed_at'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'queue_name' => $data['queue_name'] ?? 'default',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->tableEvents, $payload);

        return $this->db->insert_id();
    }

    public function get_recent_events($limit = 50)
    {
        if (!$this->db->table_exists($this->tableEvents)) {
            return [];
        }

        return $this->db->order_by('id', 'DESC')->limit((int) $limit)->get($this->tableEvents)->result_array();
    }

    public function reserve_events($limit = 25)
    {
        if (!$this->db->table_exists($this->tableEvents)) {
            return [];
        }

        $items = $this->db
            ->where('status', 'queued')
            ->where('available_at <=', date('Y-m-d H:i:s'))
            ->order_by('id', 'ASC')
            ->limit((int) $limit)
            ->get($this->tableEvents)
            ->result_array();

        $reserved = [];
        foreach ($items as $item) {
            $this->db->where('id', $item['id'])->where('status', 'queued')->update($this->tableEvents, [
                'status' => 'processing',
                'attempts' => (int) $item['attempts'] + 1,
            ]);
            if ($this->db->affected_rows() > 0) {
                $item['payload'] = json_decode($item['payload_json'] ?? '[]', true) ?: [];
                $reserved[] = $item;
            }
        }

        return $reserved;
    }

    public function complete_event($id)
    {
        if (!$this->db->table_exists($this->tableEvents)) {
            return false;
        }

        return $this->db->where('id', $id)->update($this->tableEvents, [
            'status' => 'completed',
            'processed_at' => date('Y-m-d H:i:s'),
            'error_message' => null,
        ]);
    }

    public function fail_event($id, $message)
    {
        if (!$this->db->table_exists($this->tableEvents)) {
            return false;
        }

        return $this->db->where('id', $id)->update($this->tableEvents, [
            'status' => 'failed',
            'processed_at' => date('Y-m-d H:i:s'),
            'error_message' => mb_strimwidth((string) $message, 0, 1000, '...'),
        ]);
    }

    public function save_sync_state($providerKey, $accountId, $resourceType, array $data = [])
    {
        if (!$this->db->table_exists($this->tableSyncStates)) {
            return false;
        }

        $existing = $this->db
            ->where('provider_key', $providerKey)
            ->where('account_id', $accountId)
            ->where('resource_type', $resourceType)
            ->get($this->tableSyncStates)
            ->row_array();

        $payload = [
            'provider_key' => $providerKey,
            'account_id' => $accountId,
            'resource_type' => $resourceType,
            'cursor_token' => $data['cursor_token'] ?? ($existing['cursor_token'] ?? null),
            'external_channel_id' => $data['external_channel_id'] ?? ($existing['external_channel_id'] ?? null),
            'external_resource_id' => $data['external_resource_id'] ?? ($existing['external_resource_id'] ?? null),
            'sync_status' => $data['sync_status'] ?? ($existing['sync_status'] ?? 'idle'),
            'last_synced_at' => $data['last_synced_at'] ?? ($existing['last_synced_at'] ?? null),
            'next_sync_at' => $data['next_sync_at'] ?? ($existing['next_sync_at'] ?? null),
            'last_webhook_at' => $data['last_webhook_at'] ?? ($existing['last_webhook_at'] ?? null),
            'state_json' => json_encode($data['state'] ?? (json_decode($existing['state_json'] ?? '[]', true) ?: [])),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->where('id', $existing['id'])->update($this->tableSyncStates, $payload);
            return $existing['id'];
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->tableSyncStates, $payload);
        return $this->db->insert_id();
    }

    public function get_sync_states($accountId = null)
    {
        if (!$this->db->table_exists($this->tableSyncStates)) {
            return [];
        }

        if ($accountId !== null) {
            $this->db->where('account_id', $accountId);
        }

        return $this->db->order_by('provider_key', 'ASC')->order_by('resource_type', 'ASC')->get($this->tableSyncStates)->result_array();
    }

    public function log_message(array $data)
    {
        if (!$this->db->table_exists($this->tableLogs)) {
            return false;
        }

        $payload = [
            'integration_id' => $data['integration_id'] ?? null,
            'account_id' => $data['account_id'] ?? null,
            'event_id' => $data['event_id'] ?? null,
            'level' => $data['level'] ?? 'info',
            'action' => $data['action'] ?? 'system',
            'message' => $data['message'] ?? '',
            'context_json' => json_encode($data['context'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->tableLogs, $payload);
        return $this->db->insert_id();
    }

    public function get_recent_logs($limit = 100)
    {
        if (!$this->db->table_exists($this->tableLogs)) {
            return [];
        }

        return $this->db->order_by('id', 'DESC')->limit((int) $limit)->get($this->tableLogs)->result_array();
    }

    public function get_dashboard_summary()
    {
        $summary = [
            'providers' => 0,
            'connected_accounts' => 0,
            'active_accounts' => 0,
            'queued_events' => 0,
            'failed_events' => 0,
            'sync_states' => 0,
        ];

        if ($this->db->table_exists($this->tableIntegrations)) {
            $summary['providers'] = (int) $this->db->count_all($this->tableIntegrations);
        }

        if ($this->db->table_exists($this->tableAccounts)) {
            $summary['connected_accounts'] = (int) $this->db->where('connection_status', 'connected')->count_all_results($this->tableAccounts);
            $summary['active_accounts'] = (int) $this->db->where('is_active', 1)->count_all_results($this->tableAccounts);
        }

        if ($this->db->table_exists($this->tableEvents)) {
            $summary['queued_events'] = (int) $this->db->where('status', 'queued')->count_all_results($this->tableEvents);
            $summary['failed_events'] = (int) $this->db->where('status', 'failed')->count_all_results($this->tableEvents);
        }

        if ($this->db->table_exists($this->tableSyncStates)) {
            $summary['sync_states'] = (int) $this->db->count_all($this->tableSyncStates);
        }

        return $summary;
    }

    public function encrypt_value($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return function_exists('encrypt') ? encrypt($value) : base64_encode((string) $value);
    }

    public function decrypt_value($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return function_exists('decrypt') ? decrypt($value) : base64_decode((string) $value, true);
    }
}
