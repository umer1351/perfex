<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'asset_name',
    'serial_number',
    'asset_status',
    'asset_image',
    'depreciation_months',
    'depreciation_percentage',
    'purchase_cost',
    'purchase_date',
    'asset_manager',
    'residual_value',
    'depreciation_method'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'assetcentral_assets';
$join = [];
$where = ['AND purchase_date != ""', 'AND purchase_cost != ""', 'AND depreciation_months != ""', 'AND depreciation_percentage != ""'];

if (isset($postData['asset_categories']) && $postData['asset_categories']) {
    $assetCategories = $postData['asset_categories'];
    $assetCategories = array_filter($assetCategories);

    array_push($where, 'AND ' . db_prefix() . 'assetcentral_assets.category_id IN (' . implode(',', $assetCategories) . ')');
}

if (isset($postData['asset_locations']) && $postData['asset_locations']) {
    $assetLocations = $postData['asset_locations'];
    $assetLocations = array_filter($assetLocations);

    array_push($where, 'AND ' . db_prefix() . 'assetcentral_assets.location_id IN (' . implode(',', $assetLocations) . ')');
}

if (isset($postData['asset_managers']) && $postData['asset_managers']) {
    $assetManagers = $postData['asset_managers'];
    $assetManagers = array_filter($assetManagers);

    array_push($where, 'AND ' . db_prefix() . 'assetcentral_assets.asset_manager IN (' . implode(',', $assetManagers) . ')');
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
        $row[] = '<span style="color: darkred;font-weight: bold">' . app_format_money($aRow['purchase_cost'], get_base_currency()->id) . '</span>';
        $purchaseDate = (new DateTime($aRow['purchase_date']))->format('Y-m-d');
        if ($purchaseDate < 0) {
            $row[] = '';
        } else {
            $row[] = $purchaseDate;
        }

        $row[] = $aRow['depreciation_months'];
        $depreciationPercentage = $aRow['depreciation_percentage'] ?: '0';
        $row[] = $depreciationPercentage . '%';
        $row[] = app_format_money($aRow['residual_value'], get_base_currency()->id) ?: app_format_money('0', get_base_currency()->id);

        if (!empty($purchaseDate) && !empty($aRow['purchase_cost']) && !empty($aRow['depreciation_months']) && !empty($aRow['depreciation_percentage']) && !empty($aRow['depreciation_method'])) {
            $purchase_date = new DateTime($purchaseDate);
            $elapsed_months = $purchase_date->diff($current_date)->m + ($purchase_date->diff($current_date)->y * 12);

            if ($aRow['depreciation_method'] == 'straight_line') {

                $residualValue = $aRow['residual_value'] ?: 0;

                $depreciated_value = assetcentral_calculate_straight_line_depreciation($aRow['purchase_cost'], $residualValue, $aRow['depreciation_months'], $elapsed_months);
            } else {
                $depreciated_value = assetcentral_calculate_reducing_balance_depreciation($aRow['purchase_cost'], $aRow['depreciation_percentage'], $elapsed_months, $aRow['depreciation_months'], $aRow['residual_value']);
            }

            $lostValue = $aRow['purchase_cost'] - $depreciated_value;

            $depreciationBadge = assetcentral_get_depreciation_status_data($aRow['depreciation_method'])['badge'];

            $row[] = '<span style="color: dodgerblue;font-weight: bold">' . app_format_money($depreciated_value, get_base_currency()->id) . '</span> <span style="color: darkred"><br>(-' . app_format_money($lostValue, get_base_currency()->id) . ')</span><br> ' . $depreciationBadge . '</span>';
        } else {
            $row[] = '<span style="color: dodgerblue;font-weight: bold">' . app_format_money('0', get_base_currency()->id) . '</span>';
        }

    }

    $output['aaData'][] = $row;
}
