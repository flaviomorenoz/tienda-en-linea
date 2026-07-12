<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head><title>Database Error</title>
<style>body{background:#fff;margin:40px;font:13px/20px normal Helvetica,Arial,sans-serif;color:#4F5155;}h1{color:#444;border-bottom:1px solid #D0D0D0;font-size:19px;font-weight:normal;margin:0 0 14px;padding:14px 15px 10px;}code{font-family:Consolas,monospace;font-size:12px;background:#f9f9f9;border:1px solid #D0D0D0;color:#002166;display:block;margin:14px 0;padding:12px 10px;}#container{margin:10px;border:1px solid #D0D0D0;box-shadow:0 0 8px #D0D0D0;}p{margin:12px 15px;}</style>
</head>
<body>
<div id="container">
<h1>A Database Error Occurred</h1>
<p>Error Number: <?php echo $error_no; ?></p>
<p><?php echo $error_str; ?></p>
<p>File: <?php echo $error_filename; ?> &mdash; Line: <?php echo $error_line; ?></p>
<?php if ($query !== ""): ?><p>Query: <code><?php echo $query; ?></code></p><?php endif; ?>
</div>
</body>
</html>
