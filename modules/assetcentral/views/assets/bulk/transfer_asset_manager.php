<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <h4 class="text-center"><?php echo $title; ?></h4>
                        </div>

                        <?php echo form_open(current_full_url(), ['id' => 'firstForm']); ?>

                        <?php echo render_select("transfer_from", $staffMembers, ["staffid", ["firstname", "lastname"]], "assetcentral_transfer_from"); ?>
                        <?php echo render_select("transfer_to", $staffMembers, ["staffid", ["firstname", "lastname"]], "assetcentral_transfer_to"); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="notify" id="notify">
                                    <label for="notify">
                                        <?php echo _l("assetcentral_notify_new_manager"); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary pull-right"><?php echo _l('save'); ?></button>
                        </div>
                        <?php
                        echo form_close();
                        ?>
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

        appValidateForm($('#firstForm'), {
            transfer_from: 'required',
            transfer_to: 'required'
        });
    });
</script>
