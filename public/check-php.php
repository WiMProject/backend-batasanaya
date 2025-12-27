<!DOCTYPE html>
<html>
<head>
    <title>PHP Settings Check</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .info { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .value { color: #667eea; font-weight: bold; }
    </style>
</head>
<body>
    <h1>PHP Upload Settings</h1>
    <div class="info">
        <strong>upload_max_filesize:</strong> 
        <span class="value"><?php echo ini_get('upload_max_filesize'); ?></span>
    </div>
    <div class="info">
        <strong>post_max_size:</strong> 
        <span class="value"><?php echo ini_get('post_max_size'); ?></span>
    </div>
    <div class="info">
        <strong>max_file_uploads:</strong> 
        <span class="value"><?php echo ini_get('max_file_uploads'); ?></span>
    </div>
    <div class="info">
        <strong>memory_limit:</strong> 
        <span class="value"><?php echo ini_get('memory_limit'); ?></span>
    </div>
    <div class="info">
        <strong>max_execution_time:</strong> 
        <span class="value"><?php echo ini_get('max_execution_time'); ?> seconds</span>
    </div>
</body>
</html>
