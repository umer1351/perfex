<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Assetcentral_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    //ASSET
    public function create_asset($data)
    {

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $this->db->insert(db_prefix() . 'assetcentral_assets', $data);
        $insert_id = $this->db->insert_id();

        if (isset($custom_fields)) {
            handle_custom_fields_post($insert_id, $custom_fields);
        }

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_asset($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'assetcentral_assets')->row();
    }

    public function get_all_asset()
    {
        return $this->db->get(db_prefix() . 'assetcentral_assets')->result_array();
    }

    public function update_asset($id, $data)
    {

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];

            handle_custom_fields_post($id, $custom_fields);

            unset($data['custom_fields']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assetcentral_assets', $data);

        return true;
    }

    public function delete_asset($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'assetcentral_assets');

        if ($this->db->affected_rows() > 0) {

            $this->delete_asset_assign('', $id);
            $this->delete_asset_history('', $id);
            $this->delete_asset_signature_files($id);
            $this->delete_asset_event('', $id);

            $this->delete_asset_main_image_attachment($id);
            $this->delete_asset_attachments($id);
            $this->delete_asset_notes($id);
            $this->delete_asset_reminders($id);

            return true;
        }

        return false;
    }

    public function get_asset_main_image_attachment($asset_id, $id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $asset_id);
        }

        $this->db->where('rel_type', 'asset_main_image');
        $result = $this->db->get(db_prefix() . 'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    public function delete_asset_main_image_attachment($id)
    {
        $dir = FCPATH . 'modules/assetcentral/uploads/asset_images/main_image/' . $id . '/';
        if (is_dir($dir)) {
            if (delete_dir($dir)) {

                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'asset_main_image');
                $this->db->delete(db_prefix() . 'files');

                return true;
            }
        }

        return false;
    }

    public function delete_asset_attachments($id)
    {
        $dir = FCPATH . 'modules/assetcentral/uploads/asset_images/attachments/' . $id . '/';
        if (is_dir($dir)) {
            if (delete_dir($dir)) {

                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'asset_attachment');
                $this->db->delete(db_prefix() . 'files');

                return true;
            }
        }

        return false;
    }

    public function delete_asset_signature_files($id)
    {
        $events = $this->get_all_asset_event($id);

        foreach ($events as $event) {
            $dir = FCPATH . 'modules/assetcentral/uploads/signatures/' . $event['id'] . '/';

            if (is_dir($dir)) {
                delete_dir($dir);
            }
        }

        return true;
    }

    public function delete_asset_notes($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'assets');
        $this->db->delete(db_prefix() . 'notes');

        return true;
    }

    public function delete_asset_reminders($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'asset');
        $this->db->delete(db_prefix() . 'reminders');

        return true;
    }

    public function get_assigned_assets_for_client()
    {
        $this->db->select('assets.*, assigned.*');
        $this->db->from(db_prefix() . 'assetcentral_assets AS assets');
        $this->db->join(db_prefix() . 'assetcentral_asset_assigned AS assigned', 'assets.id = assigned.asset_id');
        $this->db->where('assigned.assigned_to', 'customers');

        return $this->db->get()->result_array();
    }

    public function update_asset_manager($manager_id, $data = [])
    {
        $this->db->where('asset_manager', $manager_id);
        $this->db->update(db_prefix() . 'assetcentral_assets', $data);

        return true;
    }

    public function checkout_related_assets($rel_id, $related_to)
    {
        $this->db->where('assigned_to', $related_to);
        $this->db->where('assigned_id', $rel_id);
        $this->db->delete(db_prefix() . 'assetcentral_asset_assigned');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    //ASSET AUDITS
    public function create_asset_audit($data)
    {

        if (isset($data['filter_asset_category'])) {
            unset($data['filter_asset_category']);
        }
        if (isset($data['filter_asset_location'])) {
            unset($data['filter_asset_location']);
        }
        if (isset($data['filter_asset_status'])) {
            unset($data['filter_asset_status']);
        }

        $this->db->insert(db_prefix() . 'assetcentral_asset_audits', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_asset_audit($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'assetcentral_asset_audits')->row();
    }

    public function get_all_asset_audits()
    {
        return $this->db->get(db_prefix() . 'assetcentral_asset_audits')->result_array();
    }

    public function update_asset_audit($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assetcentral_asset_audits', $data);

        return true;
    }

    public function delete_asset_audit($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'assetcentral_asset_audits');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function get_audit_filtered_assets($categories = [], $locations = [], $statuses = [])
    {
        $this->db->select('id');
        $this->db->from(db_prefix() . 'assetcentral_assets');

        if (!empty($categories)) {
            $this->db->where_in('category_id', $categories);
        }

        if (!empty($locations)) {
            $this->db->where_in('location_id', $locations);
        }

        if (!empty($statuses)) {
            $this->db->where_in('asset_status', $statuses);
        }

        return $this->db->get()->result();
    }

    //ASSET ASSIGN
    public function create_asset_assign($data)
    {
        if (is_reference_in_table('asset_id', db_prefix() . 'assetcentral_asset_assigned', $data['asset_id'])) {
            $this->delete_asset_assign('', $data['asset_id']);
        }

        $this->db->insert(db_prefix() . 'assetcentral_asset_assigned', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_asset_assign($id)
    {
        $this->db->where('asset_id', $id);
        return $this->db->get(db_prefix() . 'assetcentral_asset_assigned')->row();
    }

    public function get_related_assets_list($rel_id, $related_to)
    {
        $this->db->where('assigned_to', $related_to);
        $this->db->where('assigned_id', $rel_id);

        return $this->db->get(db_prefix() . 'assetcentral_asset_assigned')->result_array();
    }

    public function get_all_asset_assign()
    {
        return $this->db->get(db_prefix() . 'assetcentral_asset_assigned')->result_array();
    }

    public function update_asset_assign($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assetcentral_asset_assigned', $data);

        return true;
    }

    public function delete_asset_assign($id = '', $asset_id = '')
    {
        if (!empty($asset_id)) {
            $this->db->where('asset_id', $asset_id);
        } else {
            $this->db->where('id', $id);
        }
        $this->db->delete(db_prefix() . 'assetcentral_asset_assigned');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    //ASSET HISTORY
    public function create_asset_history($data)
    {
        $this->db->insert(db_prefix() . 'assetcentral_asset_history', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_asset_history($id)
    {
        $this->db->where('asset_id', $id);
        return $this->db->get(db_prefix() . 'assetcentral_asset_history')->row();
    }

    public function get_all_asset_history()
    {
        return $this->db->get(db_prefix() . 'assetcentral_asset_history')->result_array();
    }

    public function update_asset_history($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assetcentral_asset_history', $data);

        return true;
    }

    public function delete_asset_history($id = '', $asset_id = '')
    {
        if (!empty($asset_id)) {
            $this->db->where('asset_id', $asset_id);
        } else {
            $this->db->where('id', $id);
        }

        $this->db->delete(db_prefix() . 'assetcentral_asset_history');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    //ASSET EVENTS
    public function create_asset_event($data)
    {
        $data += ['event_by' => get_staff_user_id()];

        $this->db->insert(db_prefix() . 'assetcentral_asset_events', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_asset_event($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'assetcentral_asset_events')->row();
    }

    public function get_all_asset_event($asset_id)
    {
        $this->db->where('asset_id', $asset_id);
        $this->db->order_by('created_at', 'desc');

        return $this->db->get(db_prefix() . 'assetcentral_asset_events')->result_array();
    }

    public function update_asset_event($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assetcentral_asset_events', $data);

        return true;
    }

    public function delete_asset_event($id = '', $asset_id = '')
    {
        if (!empty($asset_id)) {
            $this->db->where('asset_id', $asset_id);
        } else {
            $this->db->where('id', $id);
        }
        $this->db->delete(db_prefix() . 'assetcentral_asset_events');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    //ASSET CATEGORIES
    public function create_asset_category($data)
    {

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $this->db->insert(db_prefix() . 'assetcentral_asset_categories', $data);
        $insert_id = $this->db->insert_id();

        if (isset($custom_fields)) {
            handle_custom_fields_post($insert_id, $custom_fields);
        }

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_asset_category($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'assetcentral_asset_categories')->row();
    }

    public function get_all_asset_category()
    {
        return $this->db->get(db_prefix() . 'assetcentral_asset_categories')->result_array();
    }

    public function update_asset_category($id, $data)
    {

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];

            handle_custom_fields_post($id, $custom_fields);

            unset($data['custom_fields']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assetcentral_asset_categories', $data);

        return true;
    }

    public function delete_asset_category($id)
    {

        if (is_reference_in_table('category_id', db_prefix() . 'assetcentral_assets', $id)) {
            return [
                'referenced' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'assetcentral_asset_categories');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    //ASSET LOCATIONS
    public function create_asset_location($data)
    {

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $this->db->insert(db_prefix() . 'assetcentral_asset_locations', $data);
        $insert_id = $this->db->insert_id();

        if (isset($custom_fields)) {
            handle_custom_fields_post($insert_id, $custom_fields);
        }

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_asset_location($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'assetcentral_asset_locations')->row();
    }

    public function get_all_asset_location()
    {
        return $this->db->get(db_prefix() . 'assetcentral_asset_locations')->result_array();
    }

    public function update_asset_location($id, $data)
    {

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];

            handle_custom_fields_post($id, $custom_fields);

            unset($data['custom_fields']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assetcentral_asset_locations', $data);

        return true;
    }

    public function delete_asset_location($id)
    {

        if (is_reference_in_table('location_id', db_prefix() . 'assetcentral_assets', $id)) {
            return [
                'referenced' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'assetcentral_asset_locations');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    //ASSET REQUEST TYPES
    public function create_asset_request_type($data)
    {

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $this->db->insert(db_prefix() . 'assetcentral_asset_request_types', $data);
        $insert_id = $this->db->insert_id();

        if (isset($custom_fields)) {
            handle_custom_fields_post($insert_id, $custom_fields);
        }

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_asset_request_type($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'assetcentral_asset_request_types')->row();
    }

    public function get_all_asset_request_type()
    {
        return $this->db->get(db_prefix() . 'assetcentral_asset_request_types')->result_array();
    }

    public function update_asset_request_type($id, $data)
    {

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];

            handle_custom_fields_post($id, $custom_fields);

            unset($data['custom_fields']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assetcentral_asset_request_types', $data);

        return true;
    }

    public function delete_asset_request_type($id)
    {

        if (is_reference_in_table('request_type_id', db_prefix() . 'assetcentral_asset_requests', $id)) {
            return [
                'referenced' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'assetcentral_asset_request_types');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function update_asset_request_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('assetcentral_asset_request_types', [
            'is_enabled' => $status,
        ]);

        return $this->db->affected_rows() > 0;
    }

    //ASSET REPORTS
    public function assetStatusReport()
    {
        $query = $this->db->select('asset_status, COUNT(*) as count')
            ->from(db_prefix() . 'assetcentral_assets')
            ->group_by('asset_status')
            ->get();

        return $query->result_array();
    }

    public function assetLocationReport()
    {
        $query = $this->db->select('a.location_id, COALESCE(l.location_name, "No Location") as location_name, COUNT(*) as count')
            ->from(db_prefix() . 'assetcentral_assets a')
            ->join(db_prefix() . 'assetcentral_asset_locations l', 'a.location_id = l.id', 'left')
            ->group_by('a.location_id')
            ->get();

        return $query->result_array();
    }

    public function assetsBoughtByYear()
    {
        $current_year = date('Y');

        $query = $this->db->select('MONTH(purchase_date) as month, COUNT(*) as count')
            ->from(db_prefix() . 'assetcentral_assets')
            ->where('YEAR(purchase_date)', $current_year)
            ->group_by('MONTH(purchase_date)')
            ->order_by('month')
            ->get();

        $result = $query->result_array();
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $data[$month] = ['month' => $month, 'count' => 0];
        }

        foreach ($result as $row) {
            $data[$row['month']]['count'] = $row['count'];
        }

        return array_values($data);
    }

    public function assetsAssignedByChart()
    {
        $this->db->select('assigned_to, COUNT(*) as total');
        $this->db->group_by('assigned_to');

        return $this->db->get(db_prefix() . 'assetcentral_asset_assigned')
            ->result_array();
    }

    public function assetsLocationOnMapChart()
    {
        $this->db->select('l.id, l.location_name, l.lat, l.lng, COUNT(a.id) as asset_count');
        $this->db->from(db_prefix() . 'assetcentral_assets a');
        $this->db->join(db_prefix() . 'assetcentral_asset_locations l', 'a.location_id = l.id', 'left');
        $this->db->where('l.lat IS NOT NULL');
        $this->db->where('l.lng IS NOT NULL');
        $this->db->group_by('l.id');

        return $this->db
            ->get()
            ->result_array();
    }

    public function getAssetsAddedYearChart()
    {
        $this->db->select('MONTH(created_at) as month, COUNT(id) as asset_count');
        $this->db->from(db_prefix() . 'assetcentral_assets');
        $this->db->where('YEAR(created_at)', date('Y'));
        $this->db->group_by('MONTH(created_at)');

        return $this->db
            ->get()
            ->result_array();
    }

    public function getAssetSummaryData()
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'assetcentral_assets');
        $query = $this->db->get();

        $assets = $query->result_array();

        $baseAssetsValue = 0;
        $totalAppreciation = 0;
        $totalDepreciation = 0;
        $current_date = new DateTime();

        foreach ($assets as $asset) {
            $purchaseCost = (float)$asset['purchase_cost'];
            $baseAssetsValue += $purchaseCost;

            if (!empty($asset['purchase_date']) && !empty($asset['purchase_cost']) && !empty($asset['appreciation_rate']) && !empty($asset['appreciation_periods'])) {
                $purchase_date = new DateTime($asset['purchase_date']);
                $elapsed_months = $purchase_date->diff($current_date)->m + ($purchase_date->diff($current_date)->y * 12);
                $appreciated_value = assetcentral_calculate_appreciation($asset['purchase_cost'], $asset['appreciation_rate'], $elapsed_months, $asset['appreciation_periods']);
                $totalAppreciation += ($appreciated_value - $purchaseCost);
            }

            if (!empty($asset['purchase_date']) && !empty($asset['purchase_cost']) && !empty($asset['depreciation_months']) && !empty($asset['depreciation_percentage']) && !empty($asset['depreciation_method'])) {
                $purchase_date = new DateTime($asset['purchase_date']);
                $elapsed_months = $purchase_date->diff($current_date)->m + ($purchase_date->diff($current_date)->y * 12);

                if ($asset['depreciation_method'] == 'straight_line') {
                    $residualValue = $asset['residual_value'] ?: 0;
                    $depreciated_value = assetcentral_calculate_straight_line_depreciation($asset['purchase_cost'], $residualValue, $asset['depreciation_months'], $elapsed_months);
                } else {
                    $depreciated_value = assetcentral_calculate_reducing_balance_depreciation($asset['purchase_cost'], $asset['depreciation_percentage'], $elapsed_months, $asset['depreciation_months'], $asset['residual_value']);
                }
                $totalDepreciation += ($purchaseCost - $depreciated_value);
            }
        }

        $finalAssetValue = $baseAssetsValue + $totalAppreciation - $totalDepreciation;

        return [
            'base_assets_value' => app_format_money($baseAssetsValue, get_base_currency()->id),
            'total_appreciation' => app_format_money($totalAppreciation, get_base_currency()->id),
            'total_depreciation' => app_format_money($totalDepreciation, get_base_currency()->id),
            'final_asset_value' => app_format_money($finalAssetValue, get_base_currency()->id)
        ];
    }

}
