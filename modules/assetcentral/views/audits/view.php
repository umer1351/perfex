<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <h4 class="text-center"><?php echo _l('assetcentral_update_audit_notes_checklist'); ?></h4>
                        </div>

                        <?php echo form_open(current_full_url(), ['id' => 'auditForm']); ?>

                        <?php echo render_textarea('notes', 'assetcentral_audit_notes_checklist', $audit->notes ?? '', ['rows' => 10], [], '', 'tinymce'); ?>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary pull-right"><?php echo _l('save'); ?></button>
                        </div>
                        <?php
                        echo form_close();
                        ?>
                    </div>
                </div>
                <?php
                if ($audit->is_finished == 0) {
                    ?>
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="col-md-12">
                                <h4 class="text-center"><?php echo _l('assetcentral_completed_audit'); ?></h4>
                            </div>

                            <?php echo form_open(current_full_url(), ['id' => 'auditDateForm']); ?>

                            <?php echo render_date_input('audit_date', 'assetcentral_audit_date', $audit->audit_date ?? ''); ?>

                            <div class="text-right">
                                <button type="submit"
                                        class="btn btn-primary pull-right"><?php echo _l('save'); ?></button>
                            </div>
                            <?php
                            echo form_close();
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <h4 class="text-center"><?php echo _l('assetcentral_assets_audit_list'); ?></h4>
                        </div>
                        <?php
                        $assetList = json_decode($audit->assets_list);
                        ?>
                        <div class="col-md-12 mbot10">
                            <a target="_blank" href="<?php echo admin_url('assetcentral/qr_scanner'); ?>"
                               class="btn btn-success pull-right">
                                <i class="fa-solid fa-qrcode tw-mr-1"></i>
                                <?php echo _l('assetcentral_qr_scanner'); ?></a>
                        </div>
                        <table class="table border table-striped nomargintop">
                            <td style="background: #eefafa; color: black"><?php echo _l('#'); ?></td>
                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_asset_image_tbl'); ?></td>
                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_asset_name'); ?></td>
                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_serial_number'); ?></td>
                            <td style="background: #eefafa; color: black"><?php echo _l('assetcentral_model_number'); ?></td>
                            <tbody>
                            <?php
                            if (is_array($assetList) && !empty($assetList)) {
                                foreach ($assetList as $asset) {
                                    $CI = &get_instance();
                                    $CI->load->model('assetcentral_model');

                                    $assetData = $CI->assetcentral_model->get_asset($asset);

                                    if (!isset($assetData->asset_name) && empty($assetData->asset_name)) {
                                        continue;
                                    }

                                    $assetMainImages = $CI->assetcentral_model->get_asset_main_image_attachment($asset);
                                    $url = admin_url('assetcentral/view_asset/' . $asset);

                                    $assetImage = '<img style="max-height: 90px;max-width: 90px;" class="mtop5 img img-responsive text-center" src="' . substr(module_dir_url('assetcentral/uploads/default_image.jpg'), 0, -1) . '">';
                                    if (!empty($assetMainImages)) {
                                        $mainImagePath = FCPATH . 'modules/assetcentral/uploads/asset_images/main_image/' . $asset . '/' . $assetMainImages[0]['file_name'];
                                        $renderedImage = site_url('download/preview_image?path=' . protected_file_url_by_path($mainImagePath) . '&type=');

                                        $assetImage = '<img style="max-height: 90px;max-width: 90px;" class="mtop5 img img-responsive text-center" src="' . $renderedImage . '">';
                                    }
                                    ?>
                                    <tr class="project-overview">
                                        <td><?php echo $assetData->id; ?></td>
                                        <td><?php echo $assetImage; ?></td>
                                        <td class="bold"><a target="_blank"
                                                            href="<?php echo $url; ?>"><?php echo $assetData->asset_name; ?></a>
                                        </td>
                                        <td><?php echo $assetData->serial_number; ?></td>
                                        <td><?php echo $assetData->model_number; ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(document).ready(function () {
        "use strict";

        appValidateForm($('#auditDateForm'), {
            audit_date: 'required'
        });
    });
</script>
</html>
