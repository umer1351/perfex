<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<div>
    <a href="#"
       class="btn btn-info add-new-location mbot15"><?php echo _l('assetcentral_create_asset_location'); ?></a>
</div>
<div class="row">
    <div class="col-md-12">
        <?php render_datatable([
            _l('id'),
            _l('assetcentral_location_name'),
            _l('assetcentral_location_address'),
            _l('assetcentral_location_city'),
            _l('assetcentral_location_state'),
            _l('assetcentral_location_zip_code'),
            _l('assetcentral_location_country'),
            _l('assetcentral_location_location_manager'),
            _l('options'),
        ], 'asset-locations'); ?>
    </div>
</div>
<div class="clearfix"></div>
<div class="modal fade" id="new-asset-location-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo _l('assetcentral_asset_location_details') ?></h4>
            </div>
            <?php echo form_open_multipart(admin_url('assetcentral/settings/asset_location'), array('id' => 'new-location-form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <?php echo render_input('location_name', 'assetcentral_location_name'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_select('manager_id', $members, ['staffid', ['firstname', 'lastname']], 'assetcentral_location_location_manager'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_input('address', 'assetcentral_location_address'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_input('city', 'assetcentral_location_city'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_input('state', 'assetcentral_location_state'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_input('zip_code', 'assetcentral_location_zip_code'); ?>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_select('country', get_all_countries(), ['country_id', ['short_name']], 'assetcentral_location_country'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_input('lat', 'customer_latitude'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_input('lng', 'customer_longitude'); ?>
                    </div>

                    <div class="col-md-12">
                        <div id="map" style="height: 360px;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
