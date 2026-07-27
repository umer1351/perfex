<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <table class="table dt-table table-projects" data-order-col="2" data-order-type="desc">
            <thead>
            <tr>
                <th><?php echo _l('#'); ?></th>
                <th><?php echo _l('assetcentral_asset_image_tbl'); ?></th>
                <th><?php echo _l('assetcentral_asset_name'); ?></th>
                <th><?php echo _l('assetcentral_serial_number'); ?></th>
                <th><?php echo _l('assetcentral_model_number'); ?></th>
                <th><?php echo _l('assetcentral_asset_manager'); ?></th>
                <th><?php echo _l('assetcentral_asset_status'); ?></th>
                <th><?php echo _l('assetcentral_category_id'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($assets as $asset) { ?>
                <?php
                $CI = &get_instance();
                $CI->load->model('assetcentral_model');

                $assetMainImages = $CI->assetcentral_model->get_asset_main_image_attachment($asset['asset_id']);
                $categoryData = $CI->assetcentral_model->get_asset_category($asset['category_id']);

                $assetImage = '<img style="max-height: 90px;max-width: 90px;" class="mtop5 img img-responsive text-center" src="' . substr(module_dir_url('assetcentral/uploads/default_image.jpg'), 0, -1) . '">';
                if (!empty($assetMainImages)) {
                    $mainImagePath = FCPATH . 'modules/assetcentral/uploads/asset_images/main_image/' . $asset['asset_id'] . '/' . $assetMainImages[0]['file_name'];
                    $renderedImage = site_url('download/preview_image?path=' . protected_file_url_by_path($mainImagePath) . '&type=');

                    $assetImage = '<img style="max-height: 90px;max-width: 90px;" class="mtop5 img img-responsive text-center" src="' . $renderedImage . '">';
                }

                $assetManager = '';
                if (!empty($asset['asset_manager'])) {
                    $assetManager = '<a href="#">' . staff_profile_image($asset['asset_manager'], [
                            'staff-profile-image-small',
                        ]) . '</a>';
                }

                ?>
                <tr>
                    <td><?php echo $asset['asset_id']; ?></td>
                    <td><?php echo $assetImage; ?></td>
                    <td><?php echo $asset['asset_name']; ?></td>
                    <td><?php echo $asset['serial_number']; ?></td>
                    <td><?php echo $asset['model_number']; ?></td>
                    <td><?php echo $assetManager; ?></td>
                    <td><?php echo assetcentral_get_status_data($asset['asset_status'])['badge'] ?? 'undefined'; ?></td>
                    <td><?php echo $categoryData->category_name; ?></td>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>