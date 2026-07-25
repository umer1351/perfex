<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h2>Facebook Leads Setup Wizard</h2>
                        <p>This is a test page to verify the view loads correctly.</p>
                        <p>If you see this message, the basic view structure is working.</p>
                        <hr>
                        <p><strong>App ID:</strong> <?php echo htmlspecialchars($app_id); ?></p>
                        <p><strong>Webhook URL:</strong> <?php echo htmlspecialchars($webhook_url); ?></p>
                        <hr>
                        <a href="<?php echo admin_url('facebookleadsintegration'); ?>" class="btn btn-default">Back to Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
