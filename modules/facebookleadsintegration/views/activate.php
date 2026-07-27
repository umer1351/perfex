<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Module Activation'; ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        body {
            background: linear-gradient(135deg, #3b5998 0%, #8b5cf6 50%, #E1306C 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .activation-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }
        .activation-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .activation-header i {
            font-size: 60px;
            color: #3b5998;
            margin-bottom: 15px;
        }
        .activation-header h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .activation-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        .btn-activate {
            background: #3b5998;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-activate:hover {
            background: #2d4373;
        }
        .help-text {
            font-size: 12px;
            color: #888;
            margin-top: 20px;
            text-align: center;
        }
        .help-text a {
            color: #3b5998;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="activation-card">
        <div class="activation-header">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-instagram" style="color: #E1306C;"></i>
            <h2>Facebook & Instagram Leads Integration</h2>
            <p>Enter your purchase code to activate the module</p>
        </div>
        
        <div id="activation-message"></div>
        
        <form id="activation-form" method="POST" action="<?php echo isset($submit_url) ? htmlspecialchars($submit_url) : ''; ?>">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="original_url" value="<?php echo isset($original_url) ? htmlspecialchars($original_url) : ''; ?>">
            
            <div class="form-group">
                <label for="purchase_key">Purchase Code</label>
                <input type="text" class="form-control" id="purchase_key" name="purchase_key" 
                       placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required
                       pattern="[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}">
                <small class="text-muted">Find your purchase code in your CodeCanyon downloads.</small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-activate btn-block" id="btn-activate">
                <i class="fa fa-key"></i> Activate Module
            </button>
        </form>
        
        <div class="help-text">
            <p>
                <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank">
                    <i class="fa fa-question-circle"></i> Where do I find my purchase code?
                </a>
            </p>
            <p>
                Need help? <a href="https://themesic.com/support" target="_blank">Contact Support</a>
            </p>
        </div>
    </div>
    
    <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
    <script>
        $(document).ready(function() {
            $('#activation-form').on('submit', function(e) {
                e.preventDefault();
                
                var btn = $('#btn-activate');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Activating...');
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success || response.status === true) {
                            $('#activation-message').html('<div class="alert alert-success"><i class="fa fa-check"></i> Module activated successfully! Redirecting...</div>');
                            setTimeout(function() {
                                window.location.href = '<?php echo isset($original_url) ? htmlspecialchars($original_url) : admin_url('modules'); ?>';
                            }, 1500);
                        } else {
                            $('#activation-message').html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + (response.message || 'Activation failed. Please check your purchase code.') + '</div>');
                            btn.prop('disabled', false).html('<i class="fa fa-key"></i> Activate Module');
                        }
                    },
                    error: function() {
                        $('#activation-message').html('<div class="alert alert-danger"><i class="fa fa-times"></i> An error occurred. Please try again.</div>');
                        btn.prop('disabled', false).html('<i class="fa fa-key"></i> Activate Module');
                    }
                });
            });
        });
    </script>
</body>
</html>
