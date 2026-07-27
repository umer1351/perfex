<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'type_name',
    'type_description',
    'is_enabled'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'assetcentral_asset_request_types';

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['id'];
        $row[] = '<strong>' . $aRow['type_name'] . '</strong><br>' . _l('assetcentral_total_requests_under_request_type', total_rows(db_prefix() . 'assetcentral_asset_requests', ['request_type_id' => $aRow['id']]));

        $row[] = $aRow['type_description'];

        $checked = '';
        if ($aRow['is_enabled'] == 1) {
            $checked = 'checked';
        }
        $row[] = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . 'assetcentral/settings/update_asset_request_type_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';


        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="#" onclick="edit_asset_request_type(' . $aRow['id'] . ')" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';

        $options .= '<a href="' . admin_url('assetcentral/settings/delete_asset_request_type/' . $aRow['id']) . '"
    class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[] = $options;
    }

    $output['aaData'][] = $row;
}
