<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div>
    <a href="#" class="btn btn-info add-new-asset-category mbot15"><?php echo _l('assetcentral_create_asset_category'); ?></a>
</div>
<div class="row">
    <div class="col-md-12">
        <?php render_datatable([
            _l('id'),
            _l('assetcentral_asset_categories_name'),
            _l('assetcentral_asset_categories_description'),
            _l('options'),
        ], 'asset-categories'); ?>
    </div>
</div>
<div class="clearfix"></div>
<div class="modal fade" id="new-asset-category-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo _l('assetcentral_asset_categories_details')?></h4>
            </div>
            <?php echo form_open_multipart(admin_url('assetcentral/settings/asset_categories'),array('id'=>'new-asset-category-form'));?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <?php echo render_input('category_name','assetcentral_asset_categories_name'); ?>
                <div class="row">
                    <div class="col-md-12">
                        <p class="bold"><?php echo _l('assetcentral_asset_categories_description'); ?></p>
                        <?php echo render_textarea('category_description','',''); ?>
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