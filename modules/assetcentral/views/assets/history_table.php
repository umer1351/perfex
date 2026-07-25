<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'changed_at',
    'event',
    'field',
    'changed_from',
    'changed_to',
    'changed_by'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'assetcentral_asset_history';
$join = [];
$where = [];

array_push($where, 'AND ' . db_prefix() . 'assetcentral_asset_history.asset_id=' . $asset_id);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'assetcentral_asset_history.id'
]);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $eventBadge = '';
        if (!empty($aRow['event'])) {
            $eventBadge = assetcentral_get_status_data(strtolower($aRow['event']))['badge'] ?? '';
        }

        $changedFromBadge = '';
        if (!empty($aRow['changed_from'])) {
            $changedFromBadge = assetcentral_get_status_data(strtolower($aRow['changed_from']))['badge'] ?? '';
        }

        $changedToBadge = '';
        if (!empty($aRow['changed_to'])) {
            $changedToBadge = assetcentral_get_status_data(strtolower($aRow['changed_to']))['badge'] ?? '';
        }

        $row[] = $aRow['changed_at'];
        $row[] = $eventBadge;
        $row[] = $aRow['field'];
        $row[] = $changedFromBadge;
        $row[] = $changedToBadge;
        if (!empty($aRow['changed_by'])) {
            $row[] = '<a href="' . admin_url('staff/profile/' . $aRow['changed_by']) . '">' . staff_profile_image($aRow['changed_by'], [
                    'staff-profile-image-small',
                ]) . '</a>';
        } else {
            $row[] = '';
        }
    }

    $output['aaData'][] = $row;
}
