<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons">
                    <?php if (has_permission('assetcentral', '', 'create')) { ?>
                        <a href="<?php echo admin_url('assetcentral/create_asset'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('assetcentral_create'); ?>
                        </a>
                    <?php } ?>
                    <?php
                    if (is_admin()){
                    ?>
                    <a href="<?php echo admin_url('assetcentral/import_assets'); ?>" class="btn btn-success">
                        <i class="fa-solid fa-upload tw-mr-1"></i>
                        <?php echo _l('assetcentral_import_assets_menu'); ?></a>
                    <a href="<?php echo admin_url('assetcentral/bulk_checkout_assets'); ?>" class="btn btn-secondary">
                        <i class="fa-solid fa-sign-out-alt tw-mr-1"></i>
                        <?php echo _l('assetcentral_bulk_checkout_assets'); ?></a>
                    <a href="<?php echo admin_url('assetcentral/bulk_checkin_assets'); ?>" class="btn btn-secondary">
                        <i class="fa-solid fa-sign-in-alt tw-mr-1"></i>
                        <?php echo _l('assetcentral_bulk_checkin_assets'); ?></a>
                    <a href="<?php echo admin_url('assetcentral/bulk_manager_assets_transfer'); ?>"
                       class="btn btn-secondary">
                        <i class="fa-solid fa-exchange-alt tw-mr-1"></i>
                        <?php echo _l('assetcentral_bulk_asset_manager_transfer'); ?></a>
                </div>
                <?php
                }
                ?>
            </div>
            <br>
            <br>
            <br>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <div class="display-block">
                            <hr>
                            <div class="col-md-4 col-xs-6 border-right">
                                <?php echo render_select('asset_categories', $asset_categories, ['id', 'category_name'], 'assetcentral_category_id', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                            </div>
                            <div class="col-md-4 col-xs-6 border-right">
                                <?php echo render_select('asset_locations', $asset_locations, ['id', 'location_name'], 'assetcentral_location_id', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                            </div>
                            <div class="col-md-4 col-xs-6">
                                <?php echo render_select('asset_managers', $members, ['staffid', ['firstname', 'lastname']], 'assetcentral_asset_manager', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                            </div>
                            <div class="col-md-4 col-xs-6">
                                <?php echo render_select('asset_staff', $members, ['staffid', ['firstname', 'lastname']], 'staff', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                            </div>
                            <div class="col-md-4 col-xs-6">
                                <?php echo render_select('projects', $projects_list, ["id", "name"], 'projects', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                            </div>
                            <div class="col-md-4 col-xs-6">
                                <?php echo render_select('customers', $client_list, ["userid", "company"], 'customers', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                            </div>
                            <div class="col-md-4 col-xs-6">
                                <?php echo render_select("allocate_to", assetcentral_allocate_to_options(), ["value", "name"], "assetcentral_allocate_to_event", '', ['multiple' => true, 'data-actions-box' => true], [], '', ''); ?>
                            </div>
                            <div class="col-md-4 col-xs-6">
                                <?php echo render_select("asset_status", assetcentral_asset_statuses(), ["id", "name"], "assetcentral_asset_status", '', ['multiple' => true, 'data-actions-box' => true], [], '', ''); ?>
                            </div>
                            <div class="col-md-4 col-xs-6">
                                <div id="date-range" class="mbot15 fadeIn">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="report-from"
                                                   class="control-label"><?php echo _l('assetcentral_asset_from_purchase_date') ?></label>
                                            <div class="input-group date">
                                                <input type="text" class="form-control datepicker" id="report-from"
                                                       name="report-from">
                                                <div class="input-group-addon">
                                                    <i class="fa-regular fa-calendar calendar-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="report-to"
                                                   class="control-label"><?php echo _l('assetcentral_asset_to_purchase_date') ?></label>
                                            <div class="input-group date">
                                                <input type="text" class="form-control datepicker" disabled="disabled"
                                                       id="report-to" name="report-to">
                                                <div class="input-group-addon">
                                                    <i class="fa-regular fa-calendar calendar-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('#'),
                            _l('assetcentral_asset_image_tbl'),
                            _l('assetcentral_asset_name'),
                            _l('assetcentral_serial_number'),
                            _l('assetcentral_model_number'),
                            _l('assetcentral_asset_manager'),
                            _l('assetcentral_asset_status'),
                            _l('assetcentral_location_id'),
                            _l('assetcentral_category_id'),
                            _l('assetcentral_purchase_cost'),
                            _l('assetcentral_purchase_date'),
                            _l('assetcentral_created_at')
                        ];

                        $custom_fields = get_custom_fields('assetcentral_as', ['show_on_table' => 1]);
                        foreach ($custom_fields as $field) {
                            array_push($table_data, [
                                'name' => $field['name'],
                                'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                            ]);
                        }

                        render_datatable($table_data, 'assetcentral-assets'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";

    var fnServerParams;

    $(function () {

        var report_from = $('input[name="report-from"]');
        var report_to = $('input[name="report-to"]');

        report_from.on('change', function () {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
            } else {
                report_to.attr('disabled', true);
            }

            if ((report_to_val != '' && val != '') || (val == '' && report_to_val == '') || (val == '' &&
                report_to_val != '')) {
                generate_table();
            }
        });

        report_to.on('change', function () {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
            } else {
                report_to.attr('disabled', true);
            }

            if ((report_to_val != '' && val != '') || (val == '' && report_to_val == '') || (val == '' &&
                report_to_val != '')) {
                generate_table();
            }
        });

        generate_table();

        fnServerParams = {
            "asset_categories": '[name="asset_categories"]',
            "asset_locations": '[name="asset_locations"]',
            "asset_managers": '[name="asset_managers"]',
            'asset_projects': '[name="projects"]',
            'asset_customers': '[name="customers"]',
            'asset_allocate_to': '[name="allocate_to"]',
            'asset_report_from': '[name="report-from"]',
            'asset_report_to': '[name="report-to"]',
            'asset_staff': '[name="asset_staff"]',
            'asset_status': '[name="asset_status"]',
        }

        $('select[name="asset_categories"]').on('change', function () {
            generate_table();
        });

        $('select[name="asset_locations"]').on('change', function () {
            generate_table();
        });

        $('select[name="projects"]').on('change', function () {
            generate_table();
        });

        $('select[name="customers"]').on('change', function () {
            generate_table();
        });

        $('select[name="allocate_to"]').on('change', function () {
            generate_table();
        });

        $('select[name="asset_managers"]').on('change', function () {
            generate_table();
        });

        $('select[name="asset_staff"]').on('change', function () {
            generate_table();
        });

        $('select[name="asset_status"]').on('change', function () {
            generate_table();
        });

    });

    function generate_table() {

        const tableClass = $('.table-assetcentral-assets');

        if ($.fn.DataTable.isDataTable(tableClass)) {
            tableClass.DataTable().destroy();
        }
        initDataTable(tableClass, window.location.href, [0], [0], fnServerParams, [0, 'desc']);

    }
</script>