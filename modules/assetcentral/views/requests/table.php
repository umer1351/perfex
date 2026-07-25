<?php

defined('BASEPATH') or exit('No direct script access allowed');

$custom_fields = get_table_custom_fields('assetcentral_as');

$aColumns = [
    'asset_name',
    'serial_number',
    'model_number',
    'asset_status',
    'asset_image',
    '(SELECT location_name FROM ' . db_prefix() . 'assetcentral_asset_locations WHERE id=' . db_prefix() . 'assetcentral_assets.location_id) as location_name',
    '(SELECT category_name FROM ' . db_prefix() . 'assetcentral_asset_categories WHERE id=' . db_prefix() . 'assetcentral_assets.category_id) as category_name',
    'purchase_cost',
    'purchase_date',
    'asset_manager',
    'created_at'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'assetcentral_assets';
$join = [];
$where = [];

if (!is_admin()) {
    array_push($where, 'AND ' . db_prefix() . 'assetcentral_assets.asset_manager=' . get_staff_user_id());
}

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

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'assetcentral_assets.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'assetcentral_assets.id'
]);

$output = $result['output'];
$rResult = $result['rResult'];


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
        $asset .= '<a href="' . admin_url('assetcentral/create_asset/' . $aRow['id']) . '"> | ' . _l('edit') . '</a>';

        if (has_permission('assetcentral', '', 'delete')) {
            $asset .= ' | <a href="' . admin_url('assetcentral/delete_asset/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
        }

        $asset .= '</div>';

        $row[] = $asset;


        $row[] = $aRow['serial_number'];
        $row[] = $aRow['model_number'];
        if (!empty($aRow['asset_manager'])) {
            $row[] = '<a href="' . admin_url('staff/profile/' . $aRow['asset_manager']) . '">' . staff_profile_image($aRow['asset_manager'], [
                    'staff-profile-image-small',
                ]) . '</a>';
        } else {
            $row[] = '';
        }
        $row[] = $aRow['asset_status'];
        $row[] = $aRow['location_name'];
        $row[] = $aRow['category_name'];
        $row[] = app_format_money($aRow['purchase_cost'], get_base_currency()->id);
        $purchaseDate = (new DateTime($aRow['purchase_date']))->format('Y-m-d');
        if ($purchaseDate < 0) {
            $row[] = '';
        } else {
            $row[] = $purchaseDate;
        }
        $row[] = $aRow['created_at'];

        foreach ($customFieldsColumns as $customFieldColumn) {
            $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
        }
    }

    $output['aaData'][] = $row;
}
