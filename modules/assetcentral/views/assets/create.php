<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
            if (isset($asset_data)) {
                $requestUrl = 'assetcentral/create_asset/' . $asset_data->id;
            } else {
                $requestUrl = 'assetcentral/create_asset';
            }

            echo form_open_multipart(admin_url($requestUrl), ['id' => 'assetForm']);
            ?>
            <div class="col-md-6">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo _l('assetcentral_asset_information'); ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <?php if (isset($asset_data) && !empty($asset_main_image) != '') { ?>
                                <div class="row">
                                    <div class="col-md-9">
                                        <a href="<?php echo substr(module_dir_url('assetcentral/uploads/asset_images/main_image/' . $asset_data->id . '/' . $asset_main_image[0]['file_name']), 0, -1); ?>"
                                           class="display-block mbot5">
                                            <i class=""></i>
                                            <?php
                                            $path = FCPATH . 'modules/assetcentral/uploads/asset_images/main_image/' . $asset_data->id . '/' . $asset_main_image[0]['file_name'];
                                            ?>
                                            <img style="max-height: 200px;max-width: 200px;"
                                                 class="mtop5 img img-responsive text-center"
                                                 src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path($path) . '&type='); ?>">
                                        </a>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            <?php } ?>
                            <div class="form-group">
                                <label for="asset_image"
                                       class="control-label"><?php echo _l('assetcentral_asset_image'); ?></label>
                                <input type="file" name="file" class="form-control" value=""
                                       data-toggle="tooltip">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_input('asset_name', 'assetcentral_asset_name', $asset_data->asset_name ?? ''); ?>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_select('asset_manager', $members, ['staffid', ['firstname', 'lastname']], 'assetcentral_asset_manager', $asset_data->asset_manager ?? '', [], [], '', '', false); ?>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_select('category_id', $asset_categories, ['id', 'category_name'], 'assetcentral_category_id', $asset_data->category_id ?? '', [], [], '', '', false); ?>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purchase_cost"><?php echo _l('assetcentral_purchase_cost'); ?></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="purchase_cost"
                                           value="<?php echo $asset_data->purchase_cost ?? ''; ?>">
                                    <div class="input-group-addon">
                                        <?php echo get_base_currency()->symbol; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_textarea('asset_description', 'assetcentral_asset_description', $asset_data->asset_description ?? ''); ?>
                        </div>

                        <div class="col-md-12">
                            <div class="panel-group" role="tablist" aria-multiselectable="false">
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="assetdetailsid">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" href="#asset_details"
                                               aria-expanded="true">
                                                <?php echo _l('assetcentral_asset_information'); ?> <span
                                                        class="pull-right"><i class="fa fa-sort-down"></i></span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="asset_details" class="panel-collapse collapse in" role="tabpanel"
                                         aria-labelledby="assetdetailsid" aria-expanded="true" style="">
                                        <div class="panel-body">
                                            <div class="col-md-6">
                                                <?php echo render_input('serial_number', 'assetcentral_serial_number', $asset_data->serial_number ?? ''); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <?php echo render_input('model_number', 'assetcentral_model_number', $asset_data->model_number ?? ''); ?>
                                            </div>

                                            <div class="col-md-12">
                                                <?php echo render_input('warranty_period_month', 'assetcentral_warranty_period_month', $asset_data->warranty_period_month ?? '', 'number'); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <?php echo render_select('location_id', $asset_locations, ['id', 'location_name'], 'assetcentral_location_id', $asset_data->location_id ?? ''); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <?php

                                                $purchaseDate = '';
                                                if (isset($asset_data) && !empty($asset_data->purchase_date)) {
                                                    $purchaseDate = (new DateTime($asset_data->purchase_date))->format('Y-m-d');
                                                    if ($purchaseDate < 0) {
                                                        $purchaseDate = '';
                                                    }
                                                }

                                                echo render_date_input('purchase_date', 'assetcentral_purchase_date', $purchaseDate)
                                                ?>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="panel-group" role="tablist" aria-multiselectable="false">
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="depreciationid">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" href="#depreciation_information"
                                               aria-expanded="true">
                                                <?php echo _l('assetcentral_depreciation_information'); ?> <span
                                                        class="pull-right"><i class="fa fa-sort-down"></i></span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="depreciation_information" class="panel-collapse collapse <?php echo isset($asset_data) ? 'in' : ''; ?>" role="tabpanel"
                                         aria-labelledby="depreciationid" aria-expanded="<?php echo isset($asset_data) ? 'true' : ''; ?>" style="">
                                        <div class="panel-body">
                                            <div class="col-md-6">
                                                <?php echo render_input('depreciation_months', 'assetcentral_depreciation_months', $asset_data->depreciation_months ?? '', 'number'); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <?php echo render_input('depreciation_percentage', 'assetcentral_depreciation_percentage', $asset_data->depreciation_percentage ?? ''); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <?php echo render_select('depreciation_method', assetcentral_depreciation_methods(), ['value', 'name'], 'assetcentral_depreciation_method', $asset_data->depreciation_method ?? '', [], [], '', '', false); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="residual_value"><?php echo _l('assetcentral_residual_value'); ?></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="residual_value"
                                                               value="<?php echo $asset_data->residual_value ?? ''; ?>">
                                                        <div class="input-group-addon">
                                                            <?php echo get_base_currency()->symbol; ?>
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
                            <div class="panel-group" role="tablist" aria-multiselectable="false">
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="appreciationid">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" href="#appreciation_information"
                                               aria-expanded="true">
                                                <?php echo _l('assetcentral_appreication_information'); ?> <span
                                                        class="pull-right"><i class="fa fa-sort-down"></i></span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="appreciation_information" class="panel-collapse collapse <?php echo isset($asset_data) ? 'in' : ''; ?>" role="tabpanel"
                                         aria-labelledby="appreciationid" aria-expanded="<?php echo isset($asset_data) ? 'true' : ''; ?>" style="">
                                        <div class="panel-body">
                                            <div class="col-md-6">
                                                <?php echo render_input('appreciation_rate', 'assetcentral_appreciation_rate', $asset_data->appreciation_rate ?? '', 'number'); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <?php echo render_input('appreciation_periods', 'assetcentral_appreciation_period', $asset_data->appreciation_periods ?? '', 'number'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="panel-group" role="tablist" aria-multiselectable="false">
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="supplierinformationid">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" href="#supplier_information"
                                               aria-expanded="true">
                                                <?php echo _l('assetcentral_supplier_information'); ?> <span
                                                        class="pull-right"><i class="fa fa-sort-down"></i></span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="supplier_information" class="panel-collapse collapse <?php echo isset($asset_data) ? 'in' : ''; ?>" role="tabpanel"
                                         aria-labelledby="supplierinformationid" aria-expanded="<?php echo isset($asset_data) ? 'true' : ''; ?>" style="">
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <?php echo render_input('supplier_name', 'assetcentral_supplier_name', $asset_data->supplier_name ?? ''); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <?php echo render_input('supplier_phone_number', 'assetcentral_supplier_phone_number', $asset_data->supplier_phone_number ?? ''); ?>
                                            </div>

                                            <div class="col-md-6">
                                                <?php echo render_input('supplier_address', 'assetcentral_supplier_address', $asset_data->supplier_address ?? ''); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo _l('assetcentral_asset_custom_fields_title') ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php
                        $customFields = render_custom_fields('assetcentral_as', $asset_data->id ?? 0);
                        if (empty($customFields)) {
                            echo _l('assetcentral_asset_no_custom_fields_available');
                        } else {
                            echo $customFields;
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="btn-bottom-toolbar text-right">
                <button type="submit"
                        class="btn btn-primary"><?php echo !isset($asset_data) ? _l('assetcentral_create') : _l('save'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $("#assetForm").appFormValidator({
        rules: {
            asset_name: "required",
            asset_manager: "required",
            category_id: "required",
        },
    });
</script>

