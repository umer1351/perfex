<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-md-12">
                <div class="mbot15">
                    <a href="<?php echo admin_url('whatsapp_chat'); ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> <?php echo _l('whatsapp_messages'); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="no-margin">
                            <i class="fa fa-cog"></i> <?php echo _l('wac_settings') ?: 'WhatsApp Chat Settings'; ?>
                        </h4>
                    </div>
                    <div class="panel-body">

                        <?php echo form_open(admin_url('whatsapp_chat/settings')); ?>

                        <!-- API Token -->
                        <div class="form-group">
                            <label for="api_token"><?php echo _l('wac_api_token') ?: 'API Token'; ?></label>
                            <div class="input-group">
                                <input type="text" name="api_token" id="api_token" class="form-control"
                                       value="<?php echo htmlspecialchars($api_token ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                       readonly>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" onclick="copyToken()">
                                        <i class="fa fa-copy"></i> Copy
                                    </button>
                                </span>
                            </div>
                            <p class="text-muted mtop5">
                                <?php echo _l('wac_api_token_hint') ?: 'Use this token in your n8n HTTP Request headers as: Authorization: Bearer YOUR_TOKEN'; ?>
                            </p>
                        </div>

                        <!-- API Endpoint -->
                        <div class="form-group">
                            <label><?php echo _l('wac_api_endpoint') ?: 'API Endpoint'; ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="api_endpoint"
                                       value="<?php echo site_url('whatsapp_chat/api/message'); ?>" readonly>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" onclick="copyEndpoint()">
                                        <i class="fa fa-copy"></i> Copy
                                    </button>
                                </span>
                            </div>
                            <p class="text-muted mtop5">
                                <?php echo _l('wac_api_endpoint_hint') ?: 'Send POST requests to this URL from n8n'; ?>
                            </p>
                        </div>

                        <hr>

                        <!-- Generate New Token -->
                        <div class="form-group">
                            <label><?php echo _l('wac_generate_new_token') ?: 'Generate New Token'; ?></label>
                            <div class="input-group">
                                <input type="text" name="new_token" id="new_token" class="form-control"
                                       placeholder="Leave empty to keep current token">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" onclick="generateToken()">
                                        <i class="fa fa-refresh"></i> Generate
                                    </button>
                                </span>
                            </div>
                            <p class="text-muted mtop5">
                                Enter a new token or click Generate to create a random one. Save to apply.
                            </p>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo _l('submit'); ?>
                            </button>
                        </div>

                        <?php echo form_close(); ?>

                    </div>
                </div>

                <!-- n8n Setup Guide -->
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="no-margin">
                            <i class="fa fa-code"></i> n8n Setup Guide
                        </h4>
                    </div>
                    <div class="panel-body">
                        <h5>Inbound Message Payload Example:</h5>
                        <pre class="well">{
  "message_uid": "wamid.xyz123",
  "phone": "+8801719059881",
  "display_name": "John Doe",
  "direction": "inbound",
  "sender_type": "customer",
  "message_type": "text",
  "message_text": "Hello, I need help!",
  "sent_at": "2024-01-15 14:30:00",
  "channel": "whatsapp"
}</pre>

                        <h5>Outbound (Bot) Message Payload Example:</h5>
                        <pre class="well">{
  "message_uid": "bot-1705329000-8801719059881",
  "phone": "+8801719059881",
  "display_name": "John Doe",
  "direction": "outbound",
  "sender_type": "bot",
  "message_type": "text",
  "message_text": "Thanks for contacting us!",
  "sent_at": "2024-01-15 14:30:05",
  "channel": "whatsapp"
}</pre>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
function copyToken() {
    var input = document.getElementById('api_token');
    input.select();
    document.execCommand('copy');
    alert_float('success', 'Token copied to clipboard');
}

function copyEndpoint() {
    var input = document.getElementById('api_endpoint');
    input.select();
    document.execCommand('copy');
    alert_float('success', 'Endpoint copied to clipboard');
}

function generateToken() {
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var token = '';
    for (var i = 0; i < 64; i++) {
        token += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('new_token').value = token;
}
</script>

<?php init_tail(); ?>
