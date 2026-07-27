<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $asset->asset_name; ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="horizontal-scrollable-tabs preview-tabs-top">
                                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                                <div class="horizontal-tabs">
                                    <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">

                                        <li role="presentation" class="active">
                                            <a href="#general_infor" aria-controls="general_infor" role="tab"
                                               data-toggle="tab" aria-expanded="true">
                                                <?php echo _l('assetcentral_general_details'); ?> </a>
                                        </li>

                                        <li role="presentation" class="">
                                            <a href="#asset_events" aria-controls="asset_events" role="tab"
                                               data-toggle="tab" aria-expanded="false">
                                                <?php
                                                $totalEvents = total_rows(
                                                    db_prefix() . 'assetcentral_asset_events',
                                                    [
                                                        'asset_id' => $asset->id,
                                                    ]
                                                );
                                                ?>
                                                <span class="badge <?php echo $totalEvents === 0 ? 'hide' : ''; ?>"><?php echo $totalEvents ?></span>
                                                <?php echo _l('assetcentral_history_events'); ?>
                                            </a>
                                        </li>

                                        <li role="presentation" class="">
                                            <a href="#asset_history" aria-controls="asset_history" role="tab"
                                               data-toggle="tab" aria-expanded="false">
                                                <?php
                                                $totalHistory = total_rows(
                                                    db_prefix() . 'assetcentral_asset_history',
                                                    [
                                                        'asset_id' => $asset->id,
                                                    ]
                                                );
                                                ?>
                                                <span class="badge <?php echo $totalHistory === 0 ? 'hide' : ''; ?>"><?php echo $totalHistory ?></span>
                                                <?php echo _l('assetcentral_history'); ?>
                                            </a>
                                        </li>

                                        <li role="presentation" class="">
                                            <a href="#asset_files" aria-controls="asset_files" role="tab"
                                               data-toggle="tab" aria-expanded="false">
                                                <?php
                                                $totalFiles = total_rows(
                                                    db_prefix() . 'files',
                                                    [
                                                        'rel_id' => $asset->id,
                                                        'rel_type' => 'asset_attachment',
                                                    ]
                                                );
                                                ?>
                                                <span class="badge <?php echo $totalFiles === 0 ? 'hide' : ''; ?>"><?php echo $totalFiles ?></span>
                                                <?php echo ucfirst(_l('files')); ?>
                                            </a>
                                        </li>

                                        <li role="presentation" class="">
                                            <a href="#asset_appreciations" aria-controls="asset_appreciations"
                                               role="tab"
                                               data-toggle="tab" aria-expanded="false">
                                                <?php echo _l('assetcentral_appreciations_menu'); ?>
                                            </a>
                                        </li>

                                        <li role="presentation" class="">
                                            <a href="#asset_depreciation" aria-controls="asset_depreciation" role="tab"
                                               data-toggle="tab" aria-expanded="false">
                                                <?php echo _l('assetcentral_depreciation_menu'); ?>
                                            </a>
                                        </li>

                                        <li role="presentation" class="">
                                            <a href="#asset_reminders" aria-controls="asset_reminders" role="tab"
                                               data-toggle="tab" aria-expanded="false"
                                               onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $asset->id; ?> + '/' + 'asset', undefined, undefined, undefined,[1,'asc']); return false;"
                                            >
                                                <?php
                                                $totalReminders = total_rows(
                                                    db_prefix() . 'reminders',
                                                    [
                                                        'rel_id' => $asset->id,
                                                        'rel_type' => 'asset',
                                                    ]
                                                );
                                                ?>
                                                <span class="badge <?php echo $totalReminders === 0 ? 'hide' : ''; ?>"><?php echo $totalReminders ?></span>
                                                <?php echo _l('reminders'); ?>
                                            </a>
                                        </li>

                                        <li role="presentation" class="">
                                            <a href="#tab_notes"
                                               onclick="get_sales_notes(<?php echo $asset->id; ?>,'assetcentral'); return false"
                                               aria-controls="tab_notes" role="tab" data-toggle="tab">
                                                <?php
                                                $totalReminders = total_rows(
                                                    db_prefix() . 'notes',
                                                    [
                                                        'rel_id' => $asset->id,
                                                        'rel_type' => 'assets',
                                                    ]
                                                );
                                                ?>
                                                <span class="badge <?php echo $totalReminders === 0 ? 'hide' : ''; ?>"><?php echo $totalReminders ?></span>
                                                <?php echo _l('contract_notes'); ?>
                                            </a>
                                        </li>

                                        <li role="presentation" class="">
                                            <a href="#tab_audits"
                                               aria-controls="tab_audits" role="tab" data-toggle="tab">
                                                <?php
                                                $jsonContainsCondition = 'JSON_CONTAINS(' . db_prefix() . 'assetcentral_asset_audits.assets_list, \'["' . $asset->id . '"]\')';
                                                $totalReminders = total_rows(
                                                    db_prefix() . 'assetcentral_asset_audits',
                                                    $jsonContainsCondition
                                                );
                                                ?>
                                                <span class="badge <?php echo $totalReminders === 0 ? 'hide' : ''; ?>"><?php echo $totalReminders ?></span>
                                                <?php echo _l('assetcentral_asset_audits'); ?>
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content">

                                <div role="tabpanel" class="tab-pane active" id="general_infor">
                                    <div class="panel panel-info">
                                        <div class="panel-body">

                                            <div class="row mtop20">
                                                <div class="col-md-3">
                                                    <h3><?php echo _l('assetcentral_asset_information'); ?></h3></div>
                                                <div class="col-md-9 _buttons">
                                                    <div class="visible-xs">
                                                        <div class="mtop10"></div>
                                                    </div>
                                                    <div class="pull-right">
                                                        <a target="_blank"
                                                           href="<?php echo admin_url('assetcentral/create_asset/' . $asset->id); ?>"
                                                           class="btn btn-default"><i
                                                                    class="fa-regular fa-pen-to-square"></i></a>
                                                        <span>
                                                        <a href="#" onclick="generateAssetPDF()"
                                                           class="btn btn-default"><i
                                                                    class="fa fa-file-pdf"></i> <?php echo _l('print'); ?> PDF</a>
                                                        </span>
                                                        <span>
                                                        <a href="<?php echo $generatedQr; ?>" class="btn btn-success"
                                                           download><i
                                                                    class="fa fa-qrcode"></i> <?php echo _l('download'); ?> QR</a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row col-md-12 mtop20">
                                                <?php
                                                foreach (assetcentral_asset_statuses() as $status) {

                                                    if ($status['value'] === 'available') {
                                                        continue;
                                                    }

                                                    if ($status['value'] != 'revocate') {
                                                        ?>
                                                        <button type="button" class="btn btn-secondary" id="statusBtn"
                                                                onclick="openAssetStatusModal('<?php echo $status['value']; ?>')">
                                                            <span class="<?php echo $status['icon'] ?>"></span> <?php echo $status['name']; ?>
                                                        </button>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <a href="<?php echo admin_url('assetcentral/revocate_asset/' . $asset->id); ?>"
                                                           type="button" class="btn btn-secondary _delete"
                                                           id="statusBtn">
                                                            <span class="<?php echo $status['icon'] ?>"></span> <?php echo $status['name']; ?>
                                                        </a>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                                </button>
                                                <hr>
                                            </div>

                                            <div class="col-md-6">
                                                <?php
                                                $assetImage = substr(module_dir_url('assetcentral/uploads/default_image.jpg'), 0, -1);
                                                if (!empty($asset_main_image)) {
                                                    $mainImagePath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/asset_images/main_image/' . $asset->id . '/' . $asset_main_image[0]['file_name'];
                                                    $renderedImage = site_url('download/preview_image?path=' . protected_file_url_by_path($mainImagePath) . '&type=');

                                                    $assetImage = $renderedImage;
                                                }
                                                ?>
                                                <div class="col-md-12">
                                                    <img src="<?php echo $assetImage; ?>"
                                                         class="img-thumbnail img-responsive zoom"
                                                         style="width: 250px; height: 150px;object-fit: contain;">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-md-12">
                                                    <img src="<?php echo $generatedQr; ?>"
                                                         class="img-thumbnail img-responsive zoom pull-right"
                                                         style="width: 150px; height: 150px;">
                                                </div>
                                            </div>

                                            <div class="row" id="assetDetailsDiv">

                                                <div class="col-md-6">
                                                    <div class="col-md-12 noleftrightpadding">
                                                        <table class="table border table-striped nomargintop">
                                                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_asset_details'); ?></td>
                                                            <td style="background: #eefafa; color: black"><a
                                                                        target="_blank"
                                                                        href="<?php echo admin_url('assetcentral/create_asset/' . $asset->id); ?>"
                                                                        class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 pull-right">
                                                                    <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                                </a></td>
                                                            <tbody>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_asset_id'); ?></td>
                                                                <td><?php echo $asset->id; ?></td>
                                                            </tr>
                                                            <?php
                                                            if (!empty($assetAssigned)) {
                                                                ?>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('assetcentral_assigned_to'); ?></td>
                                                                    <td><?php echo assetcentral_get_asset_assigned_data($assetAssigned); ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            ?>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_asset_name'); ?></td>
                                                                <td><?php echo $asset->asset_name; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_asset_status'); ?></td>
                                                                <td><?php echo assetcentral_get_status_data($asset->asset_status)['badge']; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_model_number'); ?></td>
                                                                <td><?php echo $asset->model_number; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_serial_number'); ?></td>
                                                                <td><?php echo $asset->serial_number; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_category_id'); ?></td>
                                                                <td><?php echo $asset_category->category_name ?? ''; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_purchase_cost'); ?></td>
                                                                <td><?php echo app_format_money($asset->purchase_cost, get_base_currency()->id); ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_purchase_date'); ?></td>
                                                                <td><?php
                                                                    $purchaseDate = (new DateTime($asset->purchase_date))->format('Y-m-d');
                                                                    if ($purchaseDate < 0) {
                                                                        echo '';
                                                                    } else {
                                                                        echo $purchaseDate;
                                                                    }
                                                                    ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_location_id'); ?></td>
                                                                <td><?php echo $asset_location->location_name ?? ''; ?>
                                                                    <div id="map"
                                                                         style="height: 150px;width: 450px"></div>
                                                                </td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_asset_manager'); ?></td>
                                                                <td>
                                                                    <a href="<?php echo admin_url('staff/profile/' . $asset->asset_manager) ?>"><?php echo staff_profile_image($asset->asset_manager, [
                                                                            'staff-profile-image-small',
                                                                        ]) ?></a> <?php echo get_staff_full_name($asset->asset_manager); ?>
                                                                </td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_warranty_period_month'); ?></td>
                                                                <td><?php echo $asset->warranty_period_month; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_asset_description'); ?></td>
                                                                <td><?php echo $asset->asset_description; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_created_at'); ?></td>
                                                                <td><?php echo $asset->created_at; ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="col-md-12 noleftrightpadding">
                                                        <table class="table border table-striped nomargintop">
                                                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_custom_fields_details'); ?></td>
                                                            <td style="background: #eefafa; color: black"><a
                                                                        target="_blank"
                                                                        href="<?php echo admin_url('assetcentral/create_asset/' . $asset->id); ?>"
                                                                        class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 pull-right">
                                                                    <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                                </a></td>
                                                            <tbody>
                                                            <?php
                                                            $cf = get_custom_fields('assetcentral_as');

                                                            foreach ($cf as $custom_field) {
                                                                $val = get_custom_field_value($asset->id, $custom_field['id'], 'assetcentral_as');
                                                                if (empty($val)) {
                                                                    continue;
                                                                }
                                                                ?>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo $custom_field['name']; ?></td>
                                                                    <td><?php echo $val; ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="col-md-12 noleftrightpadding">
                                                        <table class="table border table-striped nomargintop">
                                                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_depreciation_details'); ?></td>
                                                            <td style="background: #eefafa; color: black"><a
                                                                        target="_blank"
                                                                        href="<?php echo admin_url('assetcentral/create_asset/' . $asset->id); ?>"
                                                                        class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 pull-right">
                                                                    <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                                </a></td>
                                                            <tbody>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_depreciation_months'); ?></td>
                                                                <td><?php echo $asset->depreciation_months; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_depreciation_percentage'); ?></td>
                                                                <td><?php echo $asset->depreciation_percentage ?? ''; ?>
                                                                    %
                                                                </td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_depreciation_method'); ?></td>
                                                                <td><?php echo assetcentral_get_depreciation_status_data($asset->depreciation_method)['badge'] ?? ''; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_residual_value'); ?></td>
                                                                <td><?php echo app_format_money($asset->residual_value, get_base_currency()->id) ?? ''; ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="col-md-12 noleftrightpadding">
                                                        <table class="table border table-striped nomargintop">
                                                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_appreciation_details'); ?></td>
                                                            <td style="background: #eefafa; color: black"><a
                                                                        target="_blank"
                                                                        href="<?php echo admin_url('assetcentral/create_asset/' . $asset->id); ?>"
                                                                        class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 pull-right">
                                                                    <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                                </a></td>
                                                            <tbody>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_appreciation_rate'); ?></td>
                                                                <td><?php echo $asset->appreciation_rate; ?> %</td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_appreciation_period'); ?></td>
                                                                <td><?php echo $asset->appreciation_periods ?? ''; ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="col-md-12 noleftrightpadding">
                                                        <table class="table border table-striped nomargintop">
                                                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_supplier_details'); ?></td>
                                                            <td style="background: #eefafa; color: black"><a
                                                                        target="_blank"
                                                                        href="<?php echo admin_url('assetcentral/create_asset/' . $asset->id); ?>"
                                                                        class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 pull-right">
                                                                    <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                                </a></td>
                                                            <tbody>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_supplier_name'); ?></td>
                                                                <td><?php echo $asset->supplier_name; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_supplier_phone_number'); ?></td>
                                                                <td><?php echo $asset->supplier_phone_number ?? ''; ?></td>
                                                            </tr>
                                                            <tr class="project-overview">
                                                                <td class="bold"><?php echo _l('assetcentral_supplier_address'); ?></td>
                                                                <td><?php echo $asset->supplier_address; ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="asset_events">
                                    <?php
                                    foreach ($asset_events as $event) {
                                        $eventForm = json_decode($event['event_form']);

                                        unset(
                                            $eventForm->asset_id,
                                            $eventForm->id,
                                            $eventForm->notify
                                        );
                                        $assetStatusData = assetcentral_get_status_data($eventForm->status);
                                        $eventForm->status = '<span class="' . $assetStatusData['icon'] . '"></span> ' . ucfirst($assetStatusData['value']);
                                        ?>
                                        <table class="table table-bordered table-sm tblEvent" aria-label="Events"
                                               role="presentation">
                                            <tbody>
                                            <tr>
                                                <td class="dataCell dateCell">
                                                    <div class="text_label"><?php echo _l('assetcentral_history_changed_at') ?></div><?php echo $event['created_at']; ?>
                                                </td>
                                                <td class="dataCell dateCell">
                                                    <div class="text_label"><?php echo _l('assetcentral_history_action_by') ?></div>
                                                    <a target="_blank"
                                                       href="<?php echo admin_url('profile/' . $event['event_by']); ?>">
                                                        <?php echo staff_profile_image($event['event_by'], [
                                                            'staff-profile-image-small',
                                                            'mright5',
                                                        ]);
                                                        echo get_staff_full_name($event['event_by']); ?></a>
                                                </td>
                                                <?php
                                                $index = 0;
                                                foreach ($eventForm as $key => $value) {
                                                    $index++;

                                                    if (empty($value)) {
                                                        continue;
                                                    }

                                                    if ($key === 'status') {
                                                        $key = '';
                                                    }
                                                    if ($key === 'allocate_to') {
                                                        $key = _l('assetcentral_allocate_to_event');
                                                    }

                                                    if ($key === 'date_lost') {
                                                        $key = _l('assetcentral_asset_date_lost');
                                                    }

                                                    if ($key === 'date_found') {
                                                        $key = _l('assetcentral_asset_date_found');
                                                    }

                                                    if ($key === 'date_broken') {
                                                        $key = _l('assetcentral_asset_date_broken');
                                                    }

                                                    if ($key === 'date_disposed') {
                                                        $key = _l('assetcentral_asset_date_dispose');
                                                    }

                                                    if ($key === 'dispose_to') {
                                                        $key = _l('assetcentral_asset_date_dispose_to');
                                                    }

                                                    if ($key === 'date_donated') {
                                                        $key = _l('assetcentral_asset_date_donate');
                                                    }

                                                    if ($key === 'donate_to') {
                                                        $key = _l('assetcentral_asset_date_donate_to');
                                                    }

                                                    if ($key === 'donate_value') {
                                                        $key = _l('assetcentral_asset_date_donate_value');
                                                        $value = app_format_money($value, get_base_currency()->id);
                                                    }

                                                    if ($key === 'date_sold') {
                                                        $key = _l('assetcentral_asset_date_sold');
                                                    }

                                                    if ($key === 'sold_to') {
                                                        $key = _l('assetcentral_asset_date_sold_to');
                                                    }

                                                    if ($key === 'sale_amount') {
                                                        $key = _l('assetcentral_asset_date_sale_amount');
                                                        $value = app_format_money($value, get_base_currency()->id);
                                                    }

                                                    if ($key === 'date_scheduled') {
                                                        $key = _l('assetcentral_asset_date_schedule');
                                                    }

                                                    if ($key === 'assigned_to') {
                                                        $key = _l('assetcentral_asset_date_assigned_to');
                                                    }

                                                    if ($key === 'date_completed') {
                                                        $key = _l('assetcentral_asset_date_completed');
                                                    }

                                                    if ($key === 'repair_cost') {
                                                        $key = _l('assetcentral_asset_date_repair_cost');
                                                        $value = app_format_money($value, get_base_currency()->id);
                                                    }

                                                    if ($key === 'maintenance_title') {
                                                        $key = _l('assetcentral_asset_date_maintenance_title');
                                                    }

                                                    if ($key === 'maintenance_details') {
                                                        $key = _l('assetcentral_asset_date_maintenance_details');
                                                    }

                                                    if ($key === 'maintenance_due_date') {
                                                        $key = _l('assetcentral_asset_date_maintenance_due_date');
                                                    }

                                                    if ($key === 'maintenance_by') {
                                                        $key = _l('assetcentral_asset_date_maintenance_by');
                                                    }

                                                    if ($key === 'maintenance_date_completed') {
                                                        $key = _l('assetcentral_asset_date_maintenance_date_completed');
                                                    }

                                                    if ($key === 'maintenance_cost') {
                                                        $key = _l('assetcentral_asset_date_maintenance_cost');
                                                        $value = app_format_money($value, get_base_currency()->id);
                                                    }

                                                    if ($key === 'maintenance_status') {
                                                        $key = _l('assetcentral_asset_date_maintenance_status');
                                                        $value = assetcentral_get_maintenance_status_data($value)['name'] ?? '';
                                                    }

                                                    if ($key === 'signature' && !empty($value)) {
                                                        $mainImagePath = FCPATH . 'modules/' . ASSETCENTRAL_MODULE_NAME . '/uploads/signatures/' . $event['id'] . '/signature.png';
                                                        $renderedImage = site_url('download/preview_image?path=' . protected_file_url_by_path($mainImagePath) . '&type=');

                                                        $assetImage = $renderedImage;
                                                        $value = '<img style="max-height: 40px; max-width: 90px; border: 1px solid #ccc; padding: 2px;" src="' . $assetImage . '">';
                                                    }

                                                    if ($key == 'customers') {
                                                        $value = '<a href="' . admin_url('clients/client/' . $value) . '">' . get_client($value)->company . '</a>';
                                                    }
                                                    if ($key == 'staff') {
                                                        $value = '<a href="' . admin_url('staff/profile/' . $value) . '">' . get_staff_full_name($value) . '</a>';
                                                    }
                                                    if ($key == 'projects') {
                                                        $value = '<a href="' . admin_url('projects/view/' . $value) . '">' . get_project($value)->name . '</a>';
                                                    }
                                                    if ($key == 'allocation_note') {
                                                        $key = _l('assetcentral_allocate_to_note');
                                                    }

                                                    ?>
                                                    <td class="dataCell dateCell">
                                                        <div class="text_label"><?php echo _l($key) ?></div>
                                                        <strong style="color: #4581ff"><?php echo ucfirst($value); ?></strong>
                                                    </td>
                                                    <?php
                                                }
                                                ?>
                                                <td class="actionCell">
                                                    <div class="tw-flex tw-items-center tw-space-x-3">
                                                        <?php
                                                        if ($assetStatusData['value'] !== 'revocate') {
                                                            ?>
                                                            <a onclick='openAssetStatusModal("<?php echo $assetStatusData["value"]; ?>", "<?php echo $event['id']; ?>")'
                                                               class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
                                                                <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                            </a>
                                                            <?php
                                                        }
                                                        ?>
                                                        <?php
                                                        if (has_permission('assetcentral', '', 'delete')) {
                                                            ?>
                                                            <a onclick='deleteAssetEvent("<?php echo $event['id']; ?>")'
                                                               class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                                                                <i class="fa-regular fa-trash-can fa-lg"></i>
                                                            </a>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <?php
                                    }
                                    ?>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="asset_history">
                                    <?php
                                    $table_data = [
                                        _l('assetcentral_history_changed_at'),
                                        _l('assetcentral_history_event'),
                                        _l('assetcentral_history_field'),
                                        _l('assetcentral_history_changed_from'),
                                        _l('assetcentral_history_changed_to'),
                                        _l('assetcentral_history_action_by')
                                    ];

                                    render_datatable($table_data, 'assetcentral-history'); ?>
                                </div>

                                <div class="tab-pane" role="tabpanel" id="asset_files">
                                    <?php echo form_open_multipart(admin_url('assetcentral/upload_attachment/' . $asset->id), ['class' => 'dropzone', 'id' => 'client-attachments-upload']); ?>
                                    <input type="file" name="file" multiple/>
                                    <?php echo form_close(); ?>
                                    <div class="attachments">
                                        <div class="mtop25">

                                            <table class="table dt-table" data-order-col="2" data-order-type="desc">
                                                <thead>
                                                <tr>
                                                    <th width="30%"><?php echo _l('customer_attachments_file'); ?></th>
                                                    <th><?php echo _l('customer_attachments_show_in_customers_area'); ?></th>
                                                    <th><?php echo _l('file_date_uploaded'); ?></th>
                                                    <th><?php echo _l('options'); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($attachments as $type => $attachment) {
                                                    $download_indicator = 'file_name';
                                                    $key_indicator = 'rel_id';
                                                    $upload_path = FCPATH . 'modules/assetcentral/uploads/asset_images/attachments/' . $asset->id . '/';

                                                    $url = substr(module_dir_url('assetcentral/uploads/asset_images/attachments/' . $asset->id . '/'), 0, -1);

                                                    ?>
                                                    <?php foreach ($attachment as $_att) {
                                                        ?>
                                                        <tr id="tr_file_<?php echo $_att['id']; ?>">
                                                        <td>
                                                            <?php
                                                            $path = $upload_path . $_att['file_name'];
                                                            $is_image = false;
                                                            if (!isset($_att['external'])) {
                                                                $attachment_url = $upload_path . $_att[$download_indicator];
                                                                $is_image = is_image($path);
                                                                $downloadPath = substr(module_dir_url('assetcentral/uploads/asset_images/attachments/'), 0, -1);
                                                                $img_url = site_url('download/preview_image?path=' . protected_file_url_by_path($attachment_url, true) . '&type=' . $_att['filetype']);

                                                                $lightBoxUrl = site_url('download/preview_image?path=' . protected_file_url_by_path($attachment_url) . '&type=' . $_att['filetype']);
                                                            }

                                                            if (!$is_image) {
                                                                $attachment_url = $url . $_att[$download_indicator];
                                                            }

                                                            if ($is_image) {
                                                                echo '<div class="preview_image">';
                                                            } ?>
                                                            <a target="_blank" href="<?php if ($is_image) {
                                                                echo isset($lightBoxUrl) ? $lightBoxUrl : $img_url;
                                                            } else {
                                                                echo $attachment_url;
                                                            } ?>" <?php if ($is_image) { ?> data-lightbox="customer-profile" <?php } ?>
                                                               class="display-block mbot5">
                                                                <?php if ($is_image) { ?>
                                                                    <div class="table-image">
                                                                        <div class="text-center"><i
                                                                                    class="fa fa-spinner fa-spin mtop30"></i>
                                                                        </div>
                                                                        <img src="#" class="img-table-loading"
                                                                             data-orig="<?php echo $img_url; ?>">
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <i class="<?php echo get_mime_class($_att['filetype']); ?>"></i>
                                                                    <?php echo $_att['file_name']; ?>
                                                                <?php } ?>
                                                            </a>
                                                            <?php if ($is_image) {
                                                                echo '</div>';
                                                            } ?>
                                                        </td>
                                                        <td>
                                                            <div class="onoffswitch">
                                                                <input type="checkbox" id="<?php echo $_att['id']; ?>"
                                                                       data-id="<?php echo $_att['id']; ?>"
                                                                       class="onoffswitch-checkbox customer_file"
                                                                       data-switch-url="<?php echo admin_url(); ?>misc/toggle_file_visibility" <?php if (isset($_att['visible_to_customer']) && $_att['visible_to_customer'] == 1) {
                                                                    echo 'checked';
                                                                } ?>>
                                                                <label class="onoffswitch-label"
                                                                       for="<?php echo $_att['id']; ?>"></label>
                                                            </div>
                                                        </td>
                                                        <td data-order="<?php echo $_att['dateadded']; ?>"><?php echo _dt($_att['dateadded']); ?></td>
                                                        <td>
                                                            <div class="tw-flex tw-items-center tw-space-x-3">
                                                                <a href="<?php echo admin_url('clients/delete_attachment/' . $_att['rel_id'] . '/' . $_att['id']); ?>"
                                                                   class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                                                                    <i class="fa-regular fa-trash-can fa-lg"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <?php
                                                    } ?>
                                                    </tr>
                                                    <?php
                                                } ?>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="asset_reminders">
                                    <a href="#" data-toggle="modal" class="btn btn-default"
                                       data-target=".reminder-modal-asset-<?php echo $asset->id; ?>"><i
                                                class="fa-regular fa-bell"></i>
                                        <?php echo _l('assetcentral_set_asset_reminder'); ?></a>
                                    <hr/>

                                    <?php render_datatable([_l('reminder_description'), _l('reminder_date'), _l('reminder_staff'), _l('reminder_is_notified')], 'reminders'); ?>

                                    <div class="modal fade modal-reminder reminder-modal-asset-<?php echo $asset->id; ?>"
                                         tabindex="-1" role="dialog"
                                         aria-labelledby="myModalLabel">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <?php echo form_open('admin/misc/add_reminder/' . $asset->id . '/asset', ['id' => 'form-reminder-asset']); ?>
                                                <div class="modal-header">
                                                    <button type="button" class="close close-reminder-modal"
                                                            data-rel-id="<?php echo $asset->id; ?>"
                                                            data-rel-type="asset" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title" id="myModalLabel"><i
                                                                class="fa-regular fa-circle-question"
                                                                data-toggle="tooltip"
                                                                title="<?php echo _l('set_reminder_tooltip'); ?>"
                                                                data-placement="bottom"></i>
                                                        <?php echo _l('assetcentral_set_asset_reminder'); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <?php echo form_hidden('rel_id', $asset->id); ?>
                                                            <?php echo form_hidden('rel_type', 'asset'); ?>
                                                            <?php echo render_datetime_input('date', 'set_reminder_date', '', array('data-date-min-date' => _d(date('Y-m-d')), 'data-step' => 30)); ?>
                                                            <?php echo render_select('staff', $members, array('staffid', array('firstname', 'lastname')), 'reminder_set_to', get_staff_user_id(), array('data-current-staff' => get_staff_user_id())); ?>
                                                            <?php echo render_textarea('description', 'reminder_description'); ?>
                                                            <?php if (is_email_template_active('reminder-email-staff')) { ?>
                                                                <div class="form-group">
                                                                    <div class="checkbox checkbox-primary">
                                                                        <input type="checkbox" name="notify_by_email"
                                                                               id="notify_by_email">
                                                                        <label for="notify_by_email"><?php echo _l('reminder_notify_me_by_email'); ?></label>
                                                                    </div>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default close-reminder-modal"
                                                            data-rel-id="<?php echo $asset->id; ?>"
                                                            data-rel-type="asset"><?php echo _l('close'); ?></button>
                                                    <button type="submit"
                                                            class="btn btn-primary"><?php echo _l('submit'); ?></button>
                                                </div>
                                                <?php echo form_close(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="asset_depreciation">
                                    <h1 class="text-center"><?php echo _l('assetcentral_depreciation_menu'); ?></h1>
                                    <hr>
                                    <div class="table-responsive mb-4 mb-md-0">
                                        <table class="table table-bordered table-sm" aria-label="Depreciation">
                                            <thead>
                                            <tr style="background: #eefafa;color: black;">
                                                <th><?php echo _l('assetcentral_purchase_date'); ?></th>
                                                <th><?php echo _l('assetcentral_purchase_cost'); ?></th>
                                                <th><?php echo _l('assetcentral_residual_value'); ?></th>
                                                <th><?php echo _l('assetcentral_depreciation_months'); ?></th>
                                                <th><?php echo _l('assetcentral_depreciation_method'); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="fw-bold"><?php
                                                    $purchaseDate = (new DateTime($asset->purchase_date))->format('Y-m-d');
                                                    if ($purchaseDate < 0) {
                                                        echo '';
                                                    } else {
                                                        echo $purchaseDate;
                                                    }
                                                    ?></td>
                                                <td class="fw-bold"><?php echo app_format_money($asset->purchase_cost, get_base_currency()->id); ?></td>
                                                <td class="fw-bold"><?php echo app_format_money($asset->residual_value, get_base_currency()->id) ?? ''; ?></td>
                                                <td class="fw-bold"><?php echo $asset->depreciation_months; ?></td>
                                                <td class="fw-bold"><?php echo assetcentral_get_depreciation_status_data($asset->depreciation_method)['badge'] ?? ''; ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <canvas id="depreciationChart" width="400" height="80"></canvas>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm text-center"
                                               aria-label="Depreciation">
                                            <thead class="thead-light">
                                            <tr style="background: #eefafa;color: black;">
                                                <th><?php echo _l('assetcentral_year'); ?></th>
                                                <th><?php echo _l('assetcentral_depreciation_expense'); ?></th>
                                                <th><?php echo _l('assetcentral_accumulated_depreciation'); ?></th>
                                                <th><?php echo _l('assetcentral_book_value'); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($annual_data as $year => $data): ?>
                                                <tr>
                                                    <td><?php echo $data['year']; ?></td>
                                                    <td style="color: darkred"><?php echo app_format_money($data['depreciation_expense'], get_base_currency()->id); ?></td>
                                                    <td style="color: darkred"><?php echo app_format_money($data['accumulated_depreciation'], get_base_currency()->id); ?></td>
                                                    <td style="color: dodgerblue"><?php echo app_format_money($data['book_value'], get_base_currency()->id); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="asset_appreciations">
                                    <h1 class="text-center"><?php echo _l('assetcentral_appreciations_menu'); ?></h1>
                                    <hr>
                                    <div class="table-responsive mb-4 mb-md-0">
                                        <table class="table table-bordered table-sm" aria-label="Depreciation">
                                            <thead>
                                            <tr style="background: #eefafa;color: black;">
                                                <th><?php echo _l('assetcentral_purchase_date'); ?></th>
                                                <th><?php echo _l('assetcentral_purchase_cost'); ?></th>
                                                <th><?php echo _l('assetcentral_appreciation_rate'); ?></th>
                                                <th><?php echo _l('assetcentral_appreciation_period'); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="fw-bold"><?php
                                                    $purchaseDate = (new DateTime($asset->purchase_date))->format('Y-m-d');
                                                    if ($purchaseDate < 0) {
                                                        echo '';
                                                    } else {
                                                        echo $purchaseDate;
                                                    }
                                                    ?></td>
                                                <td class="fw-bold"><?php echo app_format_money($asset->purchase_cost, get_base_currency()->id); ?></td>
                                                <td class="fw-bold"><?php echo $asset->appreciation_rate; ?> %</td>
                                                <td class="fw-bold"><?php echo $asset->appreciation_periods ?? ''; ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <canvas id="appreciationChart" width="400" height="80"></canvas>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm text-center"
                                               aria-label="Depreciation">
                                            <thead class="thead-light">
                                            <tr style="background: #eefafa;color: black;">
                                                <th><?php echo _l('assetcentral_year'); ?></th>
                                                <th><?php echo _l('assetcentral_monthly_appreciation'); ?></th>
                                                <th><?php echo _l('assetcentral_total_appreciation'); ?></th>
                                                <th><?php echo _l('assetcentral_book_value'); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($appreciation_data_table as $data): ?>
                                                <tr>
                                                    <td><?php echo $data['date']; ?></td>
                                                    <td style="color: darkgreen"><?php echo app_format_money($data['monthly_appreciation'], get_base_currency()->id); ?></td>
                                                    <td style="color: darkgreen"><?php echo app_format_money($data['total_appreciation'], get_base_currency()->id); ?></td>
                                                    <td style="color: dodgerblue"><?php echo app_format_money($data['book_value'], get_base_currency()->id); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                                <div role="tabpanel" class="tab-pane" id="tab_notes">
                                    <?php echo form_open(admin_url('assetcentral/add_note/' . $asset->id), ['id' => 'sales-notes', 'class' => 'contract-notes-form mtop15']); ?>
                                    <?php echo render_textarea('description'); ?>
                                    <div class="text-right">
                                        <button type="submit"
                                                class="btn btn-primary mtop15 mbot15 addAssetNote"><?php echo _l('contract_add_note'); ?></button>
                                    </div>
                                    <?php echo form_close(); ?>
                                    <hr/>
                                    <div class="mtop20" id="sales_notes_area"></div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="tab_audits">
                                    <?php
                                    $table_data = [
                                        _l('#'),
                                        _l('assetcentral_audit_name'),
                                        _l('assetcentral_audit_by'),
                                        _l('assetcentral_audit_date'),
                                        _l('assetcentral_audit_status'),
                                        _l('assetcentral_created_at'),
                                        _l('options')
                                    ];

                                    render_datatable($table_data, 'assetcentral-audits');
                                    ?>
                                </div>
                            </div>

                            <div class="modal fade" id="new-asset-category-modal">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                                &times;
                                            </button>
                                            <h4 class="modal-title"><?php echo _l('assetcentral_asset_status_details') ?></h4>
                                        </div>
                                        <?php echo form_open_multipart(admin_url('assetcentral/save_asset_status'), array('id' => 'new-allocate-asset-form')); ?>
                                        <?php echo form_hidden('id'); ?>
                                        <?php echo form_hidden('asset_id', $asset->id); ?>
                                        <?php echo form_hidden('status'); ?>
                                        <div class="modal-body">
                                            <div class="modalAssetStatusForm"></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal"><?php echo _l('close'); ?></button>
                                            <button type="button" onclick="saveAssetStatusModal()"
                                                    class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
                                        </div>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.3.4/signature_pad.min.js"
            integrity="sha512-Mtr2f9aMp/TVEdDWcRlcREy9NfgsvXvApdxrm3/gK8lAMWnXrFsYaoW01B5eJhrUpBT7hmIjLeaQe0hnL7Oh1w=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        init_tabs_scrollable();
        init_form_reminder();
        Dropzone.options.clientAttachmentsUpload = false;

        $(document).ready(function () {

            const tableClass = $('.table-assetcentral-history');
            initDataTable(tableClass, window.location.href, [0], [0], [], [0, 'desc']);

            const auditsTableClass = $('.table-assetcentral-audits');
            initDataTable(auditsTableClass, admin_url + 'assetcentral/load_asset_single_audits/<?php echo $asset->id; ?>', [0], [0], [], [0, 'desc']);

            initDepreciationChartData();
            initAppreciationChartData();

            if ($('#client-attachments-upload').length > 0) {
                new Dropzone('#client-attachments-upload', appCreateDropzoneOptions({
                    paramName: "file",
                    accept: function (file, done) {
                        done();
                    },
                    success: function (file, response) {
                        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length ===
                            0) {
                            window.location.reload();
                        }
                    }
                }));
            }

            <?php
            if (!empty($asset_location->lat) && !empty($asset_location->lng)) {
            ?>
            initMap('<?php echo $asset_location->lat ?>', '<?php echo $asset_location->lng; ?>')
            <?php
            }
            ?>

        })

        $('.addAssetNote').on('click', function () {
            setTimeout(() => {
                get_sales_notes(<?php echo $asset->id; ?>, 'assetcentral');
            }, 300);
        })

        function initMap(lat = '51.505', lng = ' -0.09') {
            var defaultLocation = [lat, lng];

            map = L.map('map', {
                center: defaultLocation,
                zoom: 13
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var marker = L.marker(defaultLocation, {
                draggable: true
            }).addTo(map);

            marker.on('dragend', function (e) {
                var latLng = marker.getLatLng();
                updateLatLngInputs(latLng.lat, latLng.lng);
            });

            map.on('click', function (e) {
                var latLng = e.latlng;
                marker.setLatLng(latLng);
                updateLatLngInputs(latLng.lat, latLng.lng);
            });
        }

        async function generateAssetPDF() {
            try {
                const {jsPDF} = window.jspdf;
                const content = document.getElementById('assetDetailsDiv');

                if (!content) {
                    console.error("Element with id 'assetDetailsDiv' not found.");
                    return;
                }

                const canvas = await html2canvas(content);
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 210;
                const pageHeight = 297;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;

                const doc = new jsPDF('p', 'mm', 'a4');
                let position = 0;

                doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    doc.addPage();
                    doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                doc.save('Asset-<?php echo $asset->asset_name; ?>.pdf');

            } catch (error) {
                console.error("Error generating PDF:", error);
            }
        }

        function openAssetStatusModal(status, event_id = '') {

            $('.modalAssetStatusForm').empty();

            $.ajax({
                type: 'POST',
                url: admin_url + "assetcentral/asset_status",
                data: {status: status, id: event_id},
            }).done(function (response) {
                response = JSON.parse(response);

                $('.modalAssetStatusForm').append(response.form);
                $('input[name="status"]').val(status);
                $('input[name="id"]').val(event_id);

                $('#new-asset-category-modal').modal('show');

            }).fail(function (error) {

                alert_float('danger', JSON.parse(error.mesage));

            });

            return false;
        }

        function saveAssetStatusModal() {
            "use strict";

            $('#new-asset-category-modal').find('button[type="submit"]').prop('disabled', true);

            var form = '#new-allocate-asset-form';
            var formURL = $(form).attr('action');
            var formData = new FormData($(form)[0]);

            $.ajax({
                type: $(form).attr('method'),
                data: formData,
                mimeType: $(form).attr('enctype'),
                contentType: false,
                cache: false,
                processData: false,
                url: formURL
            }).done(function (response) {
                response = JSON.parse(response);

                if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
                    alert_float('success', response.message);
                    $('#new-asset-category-modal').modal('hide');

                    setTimeout(function () {
                        location.reload();
                    }, 500)

                } else {
                    alert_float('danger', response.message);
                }

            }).fail(function (error) {
                alert_float('danger', JSON.parse(error.mesage));
            });

            return false;
        }

        function deleteAssetEvent(event_id) {
            $.ajax({
                type: 'GET',
                url: admin_url + "assetcentral/delete_asset_event/" + event_id
            }).done(function (response) {
                response = JSON.parse(response);

                if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
                    alert_float('success', response.message);

                    setTimeout(function () {
                        location.reload();
                    }, 500)
                } else {
                    alert_float('danger', response.message);
                }

            }).fail(function (error) {

                alert_float('danger', JSON.parse(error.mesage));

            });

            return false;
        }

        function initDepreciationChartData() {
            const ctx = document.getElementById('depreciationChart').getContext('2d');
            const data = {
                labels: <?php echo json_encode(array_keys($depreciation_values)); ?>,
                datasets: [{
                    label: '<?php echo assetcentral_get_depreciation_status_data($asset->depreciation_method)['name'] ?? ''; ?>',
                    data: <?php echo json_encode(array_values($depreciation_values)); ?>,
                    borderColor: 'rgb(192,75,85)',
                    backgroundColor: 'rgba(192,75,85,0.2)',
                    fill: true,
                }]
            };

            new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    title: {
                        display: false
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function (tooltipItem, data) {
                                let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                return format_money(value);
                            }
                        }
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: true
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Value'
                            }
                        }
                    }
                }
            });
        }

        function initAppreciationChartData() {
            var ctx = document.getElementById('appreciationChart').getContext('2d');
            var appreciationData = <?php echo json_encode($appreciation_data); ?>;

            var labels = appreciationData.map(function (entry) {
                return entry.date;
            });

            var data = appreciationData.map(function (entry) {
                return entry.value;
            });

            var format_money = function (value) {
                return new Intl.NumberFormat('en-US', {style: 'currency', currency: 'USD'}).format(value);
            };

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?php echo _l("assetcentral_appreciation_value"); ?>',
                        data: data,
                        borderColor: 'rgb(96,192,75)',
                        backgroundColor: 'rgba(83,192,75,0.2)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'month',
                                tooltipFormat: 'YYYY-MM'
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function (tooltipItem, data) {
                                let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                return format_money(value);
                            }
                        }
                    }
                }
            });
        }

    </script>

