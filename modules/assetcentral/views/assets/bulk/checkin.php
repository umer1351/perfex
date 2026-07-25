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

                        <?php echo render_select("allocate_to", assetcentral_allocate_to_options(), ["value", "name"], "assetcentral_allocate_to"); ?>
                        <?php echo render_select("staff", $staffMembers, ["staffid", ["firstname", "lastname"]], "assetcentral_allocate_to_staff_label"); ?>
                        <?php echo render_select("projects", $projects, ["id", "name"], "assetcentral_allocate_to_project_label"); ?>
                        <?php echo render_select("customers", $clients, ["userid", "company"], "assetcentral_allocate_to_customer_label"); ?>
                        <?php echo render_select("assets[]", $assets, ["id", ["asset_name", "serial_number"]], "assetcentral", '', ['multiple' => true, 'data-actions-box' => true], [], '', '', false); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="notify" id="notify">
                                    <label for="notify">
                                        <?php echo _l("assetcentral_notify_checkout"); ?>
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
            allocate_to: 'required',
            'assets[]': 'required',
        });

        init_selectpicker();
        init_datepicker();

        $(`[app-field-wrapper="staff"]`).hide();
        $(`[app-field-wrapper="projects"]`).hide();
        $(`[app-field-wrapper="customers"]`).hide();

        $(document).ready(function () {
            let initialValue = $(`select[name="allocate_to"]`).val();
            showRelevantField(initialValue);
        })

        $(`select[name="allocate_to"]`).on("change", function () {
            let selectedValue = $(`select[name="allocate_to"]`).val();
            showRelevantField(selectedValue);
        });

        function showRelevantField(value) {
            if (value == "staff") {
                $(`[app-field-wrapper="staff"]`).show();
                $(`[app-field-wrapper="projects"]`).hide();
                $(`[app-field-wrapper="customers"]`).hide();
                $(`select[name="projects"]`).val("").change();
                $(`select[name="customers"]`).val("").change();
            } else if (value == "project") {
                $(`[app-field-wrapper="projects"]`).show();
                $(`[app-field-wrapper="staff"]`).hide();
                $(`[app-field-wrapper="customers"]`).hide();
                $(`select[name="staff"]`).val("").change();
                $(`select[name="customers"]`).val("").change();
            } else if (value == "customer") {
                $(`[app-field-wrapper="customers"]`).show();
                $(`[app-field-wrapper="staff"]`).hide();
                $(`[app-field-wrapper="projects"]`).hide();
                $(`select[name="staff"]`).val("").change();
                $(`select[name="projects"]`).val("").change();
            } else {
                $(`[app-field-wrapper="staff"]`).hide();
                $(`[app-field-wrapper="projects"]`).hide();
                $(`[app-field-wrapper="customers"]`).hide();
            }
        }
    });
</script>
