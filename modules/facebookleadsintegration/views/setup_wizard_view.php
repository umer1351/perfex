<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
// Ensure variables are set with defaults
$current_step = isset($current_step) ? (int)$current_step : 1;
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Header -->
                        <div class="text-center">
                            <h3>
                                <i class="fab fa-facebook" style="color: #3b5998; font-size: 48px;"></i>
                                <i class="fab fa-instagram" style="color: #E1306C; font-size: 48px; margin-left: 10px;"></i>
                            </h3>
                            <h2><?php echo _l('fb_leads_setup_wizard_title'); ?></h2>
                            <p class="text-muted"><?php echo _l('fb_leads_setup_wizard_description'); ?></p>
                        </div>
                        <hr />

                        <!-- Progress Steps -->
                        <div class="row mtop20 mbot20">
                            <div class="col-md-12">
                                <div style="height: 30px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                                    <div id="fb-leads-progress-bar" style="width: <?php echo ($current_step / 5) * 100; ?>%; height: 100%; background: #5cb85c; line-height: 30px; text-align: center; color: #fff; transition: width 0.3s;">
                                        <?php echo _l('fb_leads_step'); ?> <span id="fb-leads-step-num"><?php echo $current_step; ?></span> / 5
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Create Facebook App -->
                        <div class="fb-leads-step <?php echo $current_step == 1 ? 'active' : ($current_step > 1 ? 'completed' : ''); ?>" id="step-1">
                            <h4>
                                <span class="step-number">1</span>
                                <?php echo _l('fb_leads_step1_title'); ?>
                            </h4>
                            <p class="text-muted"><?php echo _l('fb_leads_step1_description'); ?></p>
                            
                            <div class="step-content" <?php if ($current_step != 1) echo 'style="display:none;"'; ?>>
                                <ol>
                                    <li><?php echo _l('fb_leads_step1_instruction1'); ?></li>
                                    <li><?php echo _l('fb_leads_step1_instruction2'); ?></li>
                                    <li><?php echo _l('fb_leads_step1_instruction3'); ?></li>
                                    <li><?php echo _l('fb_leads_step1_instruction4'); ?></li>
                                    <li><?php echo _l('fb_leads_step1_instruction5'); ?></li>
                                </ol>
                                <a href="https://developers.facebook.com/apps/create/" target="_blank" class="btn btn-info">
                                    <i class="fa fa-external-link"></i> <?php echo _l('fb_leads_create_app_button'); ?>
                                </a>
                                <button type="button" class="btn btn-success pull-right next-step" data-step="1">
                                    <?php echo _l('fb_leads_next_step'); ?> <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Enter App Credentials -->
                        <div class="fb-leads-step <?php echo $current_step == 2 ? 'active' : ($current_step > 2 ? 'completed' : ''); ?>" id="step-2">
                            <h4>
                                <span class="step-number">2</span>
                                <?php echo _l('fb_leads_step2_title'); ?>
                            </h4>
                            <p class="text-muted"><?php echo _l('fb_leads_step2_description'); ?></p>
                            
                            <div class="step-content" <?php if ($current_step != 2) echo 'style="display:none;"'; ?>>
                                <div class="form-group">
                                    <label><?php echo _l('fb_leads_app_id'); ?></label>
                                    <input type="text" class="form-control" id="wizard_app_id" 
                                           value="<?php echo htmlspecialchars($app_id); ?>" placeholder="123456789012345">
                                </div>
                                <div class="form-group">
                                    <label><?php echo _l('fb_leads_app_secret'); ?></label>
                                    <input type="text" class="form-control" id="wizard_app_secret" 
                                           value="<?php echo htmlspecialchars($app_secret); ?>" placeholder="abcdef123456789...">
                                </div>
                                <div id="step2-validation" class="mtop10"></div>
                                <button type="button" class="btn btn-default prev-step" data-step="2">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('fb_leads_prev_step'); ?>
                                </button>
                                <button type="button" class="btn btn-primary" id="btn-validate-credentials">
                                    <i class="fa fa-check"></i> <?php echo _l('fb_leads_validate_credentials'); ?>
                                </button>
                                <button type="button" class="btn btn-success pull-right next-step" data-step="2" id="btn-step2-next" disabled>
                                    <?php echo _l('fb_leads_next_step'); ?> <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Configure Webhooks -->
                        <div class="fb-leads-step <?php echo $current_step == 3 ? 'active' : ($current_step > 3 ? 'completed' : ''); ?>" id="step-3">
                            <h4>
                                <span class="step-number">3</span>
                                <?php echo _l('fb_leads_step3_title'); ?>
                            </h4>
                            <p class="text-muted"><?php echo _l('fb_leads_step3_description'); ?></p>
                            
                            <div class="step-content" <?php if ($current_step != 3) echo 'style="display:none;"'; ?>>
                                <div class="alert alert-info">
                                    <strong><?php echo _l('fb_leads_webhook_callback_url'); ?>:</strong>
                                    <div class="input-group mtop5">
                                        <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($webhook_url); ?>" id="wizard-webhook-url">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" onclick="copyToClipboard('wizard-webhook-url')">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <strong><?php echo _l('fb_leads_verify_token'); ?>:</strong>
                                    <div class="input-group mtop5">
                                        <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($webhook_token); ?>" id="wizard-verify-token">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" onclick="copyToClipboard('wizard-verify-token')">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <ol>
                                    <li><?php echo _l('fb_leads_step3_instruction1'); ?></li>
                                    <li><?php echo _l('fb_leads_step3_instruction2'); ?></li>
                                    <li><?php echo _l('fb_leads_step3_instruction3'); ?></li>
                                    <li><?php echo _l('fb_leads_step3_instruction4'); ?></li>
                                </ol>
                                <button type="button" class="btn btn-default prev-step" data-step="3">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('fb_leads_prev_step'); ?>
                                </button>
                                <button type="button" class="btn btn-success pull-right next-step" data-step="3">
                                    <?php echo _l('fb_leads_next_step'); ?> <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Connect Facebook & Subscribe Pages -->
                        <div class="fb-leads-step <?php echo $current_step == 4 ? 'active' : ($current_step > 4 ? 'completed' : ''); ?>" id="step-4">
                            <h4>
                                <span class="step-number">4</span>
                                <?php echo _l('fb_leads_step4_title'); ?>
                            </h4>
                            <p class="text-muted"><?php echo _l('fb_leads_step4_description'); ?></p>
                            
                            <div class="step-content" <?php if ($current_step != 4) echo 'style="display:none;"'; ?>>
                                <button type="button" class="btn btn-info btn-lg" id="btn-wizard-connect">
                                    <i class="fab fa-facebook"></i> <?php echo _l('fb_leads_connect_facebook'); ?>
                                </button>
                                
                                <div id="wizard-pages-container" class="mtop20" style="display: none;">
                                    <h5><?php echo _l('fb_leads_select_pages_to_monitor'); ?></h5>
                                    <div id="wizard-pages-list"></div>
                                </div>
                                
                                <hr />
                                <button type="button" class="btn btn-default prev-step" data-step="4">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('fb_leads_prev_step'); ?>
                                </button>
                                <button type="button" class="btn btn-success pull-right next-step" data-step="4" id="btn-step4-next" disabled>
                                    <?php echo _l('fb_leads_next_step'); ?> <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 5: Configure Lead Settings -->
                        <div class="fb-leads-step <?php echo $current_step == 5 ? 'active' : ''; ?>" id="step-5">
                            <h4>
                                <span class="step-number">5</span>
                                <?php echo _l('fb_leads_step5_title'); ?>
                            </h4>
                            <p class="text-muted"><?php echo _l('fb_leads_step5_description'); ?></p>
                            
                            <div class="step-content" <?php if ($current_step != 5) echo 'style="display:none;"'; ?>>
                                <div class="form-group">
                                    <label><?php echo _l('fb_leads_default_assigned'); ?></label>
                                    <select class="form-control selectpicker" id="wizard_assigned" data-live-search="true">
                                        <option value=""><?php echo _l('fb_leads_no_assignment'); ?></option>
                                        <?php foreach ($staff as $member): ?>
                                            <option value="<?php echo $member['staffid']; ?>" <?php echo $default_assigned == $member['staffid'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($member['firstname'] . ' ' . $member['lastname']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><?php echo _l('fb_leads_default_source'); ?></label>
                                    <select class="form-control selectpicker" id="wizard_source" data-live-search="true">
                                        <?php foreach ($sources as $source): ?>
                                            <option value="<?php echo $source['id']; ?>" <?php echo $default_source == $source['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($source['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><?php echo _l('fb_leads_default_status'); ?></label>
                                    <select class="form-control selectpicker" id="wizard_status" data-live-search="true">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status['id']; ?>" <?php echo $default_status == $status['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($status['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <hr />
                                <button type="button" class="btn btn-default prev-step" data-step="5">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('fb_leads_prev_step'); ?>
                                </button>
                                <button type="button" class="btn btn-success btn-lg pull-right" id="btn-complete-setup">
                                    <i class="fa fa-check"></i> <?php echo _l('fb_leads_complete_setup'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Skip Setup Link -->
                        <div class="text-center mtop20">
                            <a href="<?php echo admin_url('facebookleadsintegration?skip_wizard=1'); ?>" class="text-muted">
                                <?php echo _l('fb_leads_skip_setup_wizard'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
(function initWizard() {
    if (typeof window.jQuery === 'undefined' || !window.jQuery.fn) {
        setTimeout(initWizard, 100);
        return;
    }

    window.jQuery(function($) {
        var currentStep = <?php echo isset($current_step) ? (int)$current_step : 1; ?>;

    // Copy to clipboard
    window.copyToClipboard = function(fieldId) {
        var field = document.getElementById(fieldId);
        field.select();
        document.execCommand('copy');
        alert_float('success', '<?php echo _l('fb_leads_copied_to_clipboard'); ?>');
    };

    // Navigate steps
    function showStep(step) {
        // Validate step is a number between 1 and 5
        step = parseInt(step);
        if (isNaN(step) || step < 1) step = 1;
        if (step > 5) step = 5;
        
        currentStep = step;
        $('#fb-leads-progress-bar').css('width', (step / 5 * 100) + '%');
        $('#fb-leads-step-num').text(step);
        history.pushState({}, '', '?step=' + step);

        $('.fb-leads-step').each(function() {
            var stepNum = parseInt($(this).attr('id').replace('step-', ''));
            if (stepNum < step) {
                $(this).removeClass('active').addClass('completed');
                $(this).find('.step-content').slideUp();
            } else if (stepNum == step) {
                $(this).removeClass('completed').addClass('active');
                $(this).find('.step-content').slideDown();
            } else {
                $(this).removeClass('active completed');
                $(this).find('.step-content').slideUp();
            }
        });
    }

    // Next step
    $('.next-step').on('click', function() {
        var step = parseInt($(this).data('step')) || currentStep;
        showStep(step + 1);
    });

    // Previous step
    $('.prev-step').on('click', function() {
        var step = parseInt($(this).data('step')) || currentStep;
        showStep(step - 1);
    });

    // Validate credentials
    $('#btn-validate-credentials').on('click', function() {
        var btn = $(this);
        var appId = $('#wizard_app_id').val();
        var appSecret = $('#wizard_app_secret').val();

        if (!appId || !appSecret) {
            alert_float('warning', '<?php echo _l('fb_leads_enter_credentials'); ?>');
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_validating'); ?>');

        FBLeads.saveSettings({
            fb_leads_app_id: appId,
            fb_leads_app_secret: appSecret
        }, function(saveResponse) {
            FBLeads.testConnection(function(response) {
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> <?php echo _l('fb_leads_validate_credentials'); ?>');

                if (response.success && response.results.credentials.valid) {
                    $('#step2-validation').html('<div class="alert alert-success"><i class="fa fa-check"></i> <?php echo _l('fb_leads_credentials_valid'); ?></div>');
                    $('#btn-step2-next').prop('disabled', false);
                } else {
                    $('#step2-validation').html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + (response.results.credentials.message || '<?php echo _l('fb_leads_credentials_invalid'); ?>') + '</div>');
                    $('#btn-step2-next').prop('disabled', true);
                }
            });
        });
    });

    // Connect Facebook
    $('#btn-wizard-connect').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_connecting'); ?>');

        FBLeads.loginWithFacebook(function(response) {
            btn.prop('disabled', false).html('<i class="fab fa-facebook"></i> <?php echo _l('fb_leads_connect_facebook'); ?>');

            if (response.success) {
                alert_float('success', '<?php echo _l('fb_leads_connected_successfully'); ?>');
                $('#wizard-pages-container').show();
                $('#wizard-pages-list').html(response.html);
                $('#btn-step4-next').prop('disabled', false);
            } else {
                alert_float('danger', response.message);
            }
        });
    });

    // Page action in wizard
    $(document).on('click', '#wizard-pages-list .page-action-btn', function() {
        var btn = $(this);
        var pageId = btn.data('page-id');
        var action = btn.data('action');

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        FBLeads.pageAction(pageId, action, function(response) {
            if (response.success) {
                alert_float('success', response.message);
                if (action === 'subscribe') {
                    btn.removeClass('btn-success').addClass('btn-danger')
                       .data('action', 'unsubscribe')
                       .html('<?php echo _l('fb_leads_unsubscribe'); ?>');
                    btn.closest('tr').find('.label').removeClass('label-default').addClass('label-success').text('<?php echo _l('fb_leads_monitoring'); ?>');
                } else {
                    btn.removeClass('btn-danger').addClass('btn-success')
                       .data('action', 'subscribe')
                       .html('<?php echo _l('fb_leads_subscribe'); ?>');
                    btn.closest('tr').find('.label').removeClass('label-success').addClass('label-default').text('<?php echo _l('fb_leads_not_monitoring'); ?>');
                }
            } else {
                alert_float('danger', response.message);
            }
            btn.prop('disabled', false);
        });
    });

    // Complete setup
    $('#btn-complete-setup').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_completing'); ?>');

        var settings = {
            fb_leads_default_assigned: $('#wizard_assigned').val(),
            fb_leads_default_source: $('#wizard_source').val(),
            fb_leads_default_status: $('#wizard_status').val()
        };

        FBLeads.saveSettings(settings, function(response) {
            $.ajax({
                url: admin_url + 'facebookleadsintegration/complete_setup',
                type: 'POST',
                dataType: 'json',
                data: { <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>' },
                success: function(response) {
                    if (response.success) {
                        alert_float('success', '<?php echo _l('fb_leads_setup_complete'); ?>');
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1000);
                    }
                }
            });
        });
    }); // End #btn-complete-setup click handler
    }); // End document ready
})(); // End initWizard IIFE
</script>
