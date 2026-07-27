<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <div class="display-block">
                            <div class="col-md-4 col-xs-6 border-right">
                                <?php echo render_select('asset_categories', $asset_categories, ['id', 'category_name'], 'assetcentral_category_id', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                            </div>
                            <div class="col-md-4 col-xs-6 border-right">
                                <?php echo render_select('asset_locations', $asset_locations, ['id', 'location_name'], 'assetcentral_location_id', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
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

        generate_table();

        fnServerParams = {
            "asset_categories": '[name="asset_categories"]',
            "asset_locations": '[name="asset_locations"]',
            "asset_managers": '[name="asset_managers"]'
        }

        $('select[name="asset_categories"]').on('change', function () {
            generate_table();
        });

        $('select[name="asset_locations"]').on('change', function () {
            generate_table();
        });

        $('select[name="asset_managers"]').on('change', function () {
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