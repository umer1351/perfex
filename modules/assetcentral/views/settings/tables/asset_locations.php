<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'location_name',
    'address',
    'city',
    'state',
    'country',
    'zip_code',
    'manager_id',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'assetcentral_asset_locations';

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
        $row[] = '<strong>' . $aRow['location_name'] . '</strong><br>' . _l('assetcentral_total_assets_under_category', total_rows(db_prefix() . 'assetcentral_assets', ['location_id' => $aRow['id']]));

        $row[] = $aRow['address'];
        $row[] = $aRow['city'];
        $row[] = $aRow['state'];
        $row[] = $aRow['zip_code'];
        $row[] = get_country_name($aRow['country']);

        $row[] = '<a href="' . admin_url('staff/profile/' . $aRow['manager_id']) . '">' . staff_profile_image($aRow['manager_id'], [
                'staff-profile-image-small',
            ]) . '</a>';

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="#" onclick="edit_asset_location(' . $aRow['id'] . ')" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';

        $options .= '<a href="' . admin_url('assetcentral/settings/delete_asset_location/' . $aRow['id']) . '"
    class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[] = $options;
    }

    $output['aaData'][] = $row;
}
