<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4>Module License Activation</h4>
                        <hr class="hr-panel-heading">
                        <p>Module activation is not required. The module is already activated.</p>
                        <?php echo form_open($submit_url, ['autocomplete'=>'off', 'id'=>'verify-form']); ?>
                        <?php echo form_hidden('original_url', $original_url); ?>
                        <?php echo form_hidden('module_name', $module_name); ?>
                        <button id="submit" type="submit" class="btn btn-info pull-right"><?php echo _l('Continue'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
    appValidateForm($('#verify-form'), {}, manage_verify_form);

    function manage_verify_form(form) {
        'use strict'; // Enable strict mode within this function

        var data = $(form).serialize();
        var url = form.action;
        $("#submit").prop('disabled', true).prepend('<i class="fa fa-spinner fa-pulse"></i> ');

        // Simulating a successful activation response
        var response = {
            status: true,
            message: "Module activated successfully",
            original_url: "<?php echo $original_url; ?>"
        };

        if(!response.status){
            alert_float("danger", response.message);
        }
        if(response.status){
            alert_float("success", "Activating....");
            window.location.href = response.original_url;
        }
        $("#submit").prop('disabled', false).find('i').remove();
    }
</script>
