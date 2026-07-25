<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div id="response"></div>
            <?php echo form_open(current_full_url(), ['id' => 'auditForm']); ?>
            <div class="col-md-12">
                <h2>
                    <?php echo isset($type_data) ? $type_data->category_name : ''; ?>
                </h2>
                <p><?php echo isset($type_data) ? $type_data->category_description : ''; ?></p>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-12 mbot10">
                            <h4 class="text-center"><?php echo _l('assetcentral_select_assets_title'); ?></h4>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_select('filter_asset_category', $asset_categories, ['id', 'category_name'], 'assetcentral_select_assets_by_category', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_select('filter_asset_location', $asset_locations, ['id', 'location_name'], 'assetcentral_select_assets_by_location', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_select('filter_asset_status', $asset_statuses, ['id', 'name'], 'assetcentral_select_assets_by_status', '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                        </div>

                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-12">
                            <h4 class="text-center"><?php echo _l('assetcentral_audit_information_title'); ?></h4>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_input('audit_name', 'assetcentral_audit_name'); ?>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_select('audit_by', $members, ['staffid', ['firstname', 'lastname']], 'assetcentral_audit_by', $asset_data->audit_by ?? ''); ?>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_date_input('audit_date', 'assetcentral_audit_date'); ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_select('assets_list[]', $asset_list, ['id', 'asset_name'], 'assetcentral_audit_selected_assets', $asset_data->assets_list ?? '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit"
                                    class="btn btn-primary saveDocument pull-right"><?php echo _l('assetcentral_create_audit_btn'); ?></button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <?php echo render_textarea('notes', 'assetcentral_audit_notes_checklist', '', ['rows' => 10], [], '', 'tinymce'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            echo form_close();
            ?>
        </div>

    </div>
</div>
<?php init_tail(); ?>
<script>
    $(document).ready(function () {
        "use strict";

        appValidateForm($('#auditForm'), {
            audit_name: 'required',
            audit_by: 'required',
            audit_date: 'required',
            assets_list: 'required'
        });

        function fetchFilteredAssets() {
            var categories = $('#filter_asset_category').val();
            var locations = $('#filter_asset_location').val();
            var statuses = $('#filter_asset_status').val();

            $.ajax({
                url: '<?php echo admin_url('assetcentral/filter_audit_assets'); ?>',
                type: 'POST',
                data: {
                    categories: categories,
                    locations: locations,
                    statuses: statuses
                },
                success: function (response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        var assetIds = JSON.parse(res.assets);

                        $('select[name="assets_list[]"]').val(assetIds).trigger('change');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                }
            });
        }

        $('#filter_asset_category, #filter_asset_location, #filter_asset_status').change(function () {
            fetchFilteredAssets();
        });
    });
</script>
