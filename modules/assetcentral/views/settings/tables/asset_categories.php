<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'category_name',
    'category_description'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'assetcentral_asset_categories';

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['id'];
        $row[] = '<strong>'.$aRow['category_name'] .'</strong><br>'._l('assetcentral_total_assets_under_category', total_rows(db_prefix() . 'assetcentral_assets', ['category_id' => $aRow['id']]));
        $row[] = $aRow['category_description'];


        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="#" onclick="edit_asset_category('.$aRow['id'].')" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';

        $options .= '<a href="' . admin_url('assetcentral/settings/delete_asset_category/' . $aRow['id']) . '"
    class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[]              = $options;
    }

    $output['aaData'][] = $row;
}
