<?php

defined('BASEPATH') or exit('No direct script access allowed');

$custom_fields = get_table_custom_fields('assetcentral_as');

$aColumns = [
    'audit_name',
    'audit_by',
    'audit_date',
    'is_finished',
    'created_at'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'assetcentral_asset_audits';
$join = [];
$where = [];

if (!has_permission('assetcentral_audits', '', 'view') && !is_admin()) {
    array_push($where, 'AND ' . db_prefix() . 'assetcentral_asset_audits.audit_by=' . get_staff_user_id());
}

if (isset($postData['audit_by']) && $postData['audit_by']) {
    $assetManagers = $postData['audit_by'];
    $assetManagers = array_filter($assetManagers);

    array_push($where, 'AND ' . db_prefix() . 'assetcentral_asset_audits.audit_by IN (' . implode(',', $assetManagers) . ')');
}

if (isset($postData['assets']) && $postData['assets']) {
    $assetManagers = $postData['assets'];
    $assetManagers = array_filter($assetManagers);

    $jsonContainsClauses = array_map(function ($assetId) {
        return 'JSON_CONTAINS(' . db_prefix() . 'assetcentral_asset_audits.assets_list, \'["' . $assetId . '"]\')';
    }, $assetManagers);

    array_push($where, 'AND (' . implode(' OR ', $jsonContainsClauses) . ')');
}

if (isset($assetSingleId) && $assetSingleId) {

    $jsonContainsClause = 'JSON_CONTAINS(' . db_prefix() . 'assetcentral_asset_audits.assets_list, \'["' . $assetSingleId . '"]\')';
    array_push($where, 'AND (' . $jsonContainsClause . ')');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'assetcentral_asset_audits.id'
]);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $CI = &get_instance();

        $row[] = $aRow['id'];
        $row[] = $aRow['audit_name'];

        if (!empty($aRow['audit_by'])) {
            $row[] = '<a href="' . admin_url('staff/profile/' . $aRow['audit_by']) . '">' . staff_profile_image($aRow['audit_by'], [
                    'staff-profile-image-small',
                ]) . '</a>';
        } else {
            $row[] = '';
        }
        $row[] = $aRow['audit_date'];

        $is_finished = '';
        if ($aRow['is_finished'] == 0) {
            $is_finished = '<span class="label project-status-2" style="color:#99062d;border:1px solid #ba246b;background: #fbfcfc;">' . _l("assetcentral_not_completed") . '</span>';
        }
        if ($aRow['is_finished'] == 1) {
            $is_finished = '<span class="label project-status-5" style="color:#069e10;border:1px solid #6fdb65;background: #fbfcfc;">' . _l("assetcentral_completed") . '</span>';
        }
        $row[] = $is_finished;
        $row[] = $aRow['created_at'];

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="' . admin_url('assetcentral/view_audit/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
                    <i class="fa-regular fa-eye fa-lg"></i>
                </a>';

        if (has_permission('assetcentral_audits', '', 'delete')) {
            $options .= '<a href="' . admin_url('assetcentral/delete_audit/' . $aRow['id']) . '"
                class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                    <i class="fa-regular fa-trash-can fa-lg"></i>
                </a>';
            $options .= '</div>';
        }

        $row[] = $options;

    }

    $output['aaData'][] = $row;
}
