<?php echo form_open(admin_url('assetcentral/settings/save_settings')); ?>
    <div class="col-md-12">

        <div class="col-md-6">
            <?php echo render_yes_no_option('assetcentral_show_assets_on_clients_menu', 'asset_show_assets_on_clients_menu'); ?>
        </div>

        <div class="btn-bottom-toolbar text-right">
            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
        </div>
    </div>
<?php echo form_close(); ?>