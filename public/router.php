<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// Serve existing files directly (css, js, images, etc.).
if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Emulate the .htaccess rewrite rule for clean URLs.
$_GET['url'] = ltrim($path, '/');

require __DIR__ . '/index.php';
