<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'asset_name',
    'serial_number',
    'asset_status',
    'asset_image',
    'appreciation_rate',
    'appreciation_periods',
    'purchase_cost',
    'purchase_date',
    'asset_manager'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'assetcentral_assets';
$join = [];
$where = ['AND purchase_date != ""', 'AND purchase_cost != ""', 'AND appreciation_rate != ""', 'AND appreciation_periods != ""'];

if (isset($postData['asset_categories']) && $postData['asset_categories']) {
    $assetCategories = $postData['asset_categories'];
    $assetCategories = array_filter($assetCategories);

    array_push($where, 'AND '.db_prefix().'assetcentral_assets.category_id IN (' . implode(',', $assetCategories) . ')');
}

if (isset($postData['asset_locations']) && $postData['asset_locations']) {
    $assetLocations = $postData['asset_locations'];
    $assetLocations = array_filter($assetLocations);

    array_push($where, 'AND '.db_prefix().'assetcentral_assets.location_id IN (' . implode(',', $assetLocations) . ')');
}

if (isset($postData['asset_managers']) && $postData['asset_managers']) {
    $assetManagers = $postData['asset_managers'];
    $assetManagers = array_filter($assetManagers);

    array_push($where, 'AND '.db_prefix().'assetcentral_assets.asset_manager IN (' . implode(',', $assetManagers) . ')');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'assetcentral_assets.id'
]);

$output = $result['output'];
$rResult = $result['rResult'];

$current_date = new DateTime();
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $CI = &get_instance();
        $CI->load->model('assetcentral_model');

        $assetMainImages = $CI->assetcentral_model->get_asset_main_image_attachment($aRow['id']);

        $row[] = $aRow['id'];

        $assetName = $aRow['asset_name'];
        $url = admin_url('assetcentral/create_asset/' . $aRow['id']);
        $assetImage = '<img style="max-height: 90px;max-width: 90px;" class="mtop5 img img-responsive text-center" src="' . substr(module_dir_url('assetcentral/uploads/default_image.jpg'), 0, -1) . '">';
        if (!empty($assetMainImages)) {
            $mainImagePath = FCPATH . 'modules/assetcentral/uploads/asset_images/main_image/' . $aRow['id'] . '/' . $assetMainImages[0]['file_name'];
            $renderedImage = site_url('download/preview_image?path=' . protected_file_url_by_path($mainImagePath) . '&type=');

            $assetImage = '<img style="max-height: 90px;max-width: 90px;" class="mtop5 img img-responsive text-center" src="' . $renderedImage . '">';
        }
        $row[] = $assetImage;

        $asset = '<a href="' . $url . '">' . $assetName . '</a>';

        $asset .= '<div class="row-options">';
        $asset .= '<a href="' . admin_url('assetcentral/view_asset/' . $aRow['id']) . '">' . _l('view') . '</a>';
        $asset .= '</div>';

        $row[] = $asset;


        $row[] = $aRow['serial_number'];
        $row[] = $aRow['asset_status'];
        $row[] = '<span style="color: darkred;font-weight: bold">'.app_format_money($aRow['purchase_cost'], get_base_currency()->id).'</span>';
        $purchaseDate = (new DateTime($aRow['purchase_date']))->format('Y-m-d');
        if ($purchaseDate < 0) {
            $row[] = '';
        } else {
            $row[] = $purchaseDate;
        }

        $row[] = $aRow['appreciation_periods'];
        $appreciationPercentage = $aRow['appreciation_rate'] ?: '0';
        $row[] = $appreciationPercentage . '%';

        if (!empty($purchaseDate) && !empty($aRow['purchase_cost']) && !empty($aRow['appreciation_rate']) && !empty($aRow['appreciation_periods'])) {
            $purchase_date = new DateTime($purchaseDate);
            $elapsed_months = $purchase_date->diff($current_date)->m + ($purchase_date->diff($current_date)->y * 12);

            $appreciated_value = assetcentral_calculate_appreciation($aRow['purchase_cost'], $aRow['appreciation_rate'], $elapsed_months, $aRow['appreciation_periods']);
            $gainedValue = $appreciated_value - $aRow['purchase_cost'];

            $increasePercentage = assetcentral_calculate_gain_percentage($aRow['purchase_cost'], $appreciated_value);

            $color1 = 'abc3ff';
            $color2 = '2dceff';
            $color3 = 'f7feff';

            $row[] = '<span style="color: green;font-weight: bold">' .app_format_money($appreciated_value, get_base_currency()->id) . '</span> <span style="color: darkgreen"><br>(+' . app_format_money($gainedValue, get_base_currency()->id) . ')</span> <br> <span style="color: dodgerblue">'.$increasePercentage.'%</span>';
        } else {
            $row[] = '<span style="color: dodgerblue;font-weight: bold">' .app_format_money('0', get_base_currency()->id). '</span>';
        }

    }

    $output['aaData'][] = $row;
}
