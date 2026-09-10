<?php
/**
 * ===================================================================
 * Earthen Beauty by Nupur - Universal Root Router & Nginx Fallback
 * ===================================================================
 * Ensures full compatibility with CloudPanel, Nginx, Apache, LiteSpeed,
 * and built-in PHP development server without manual vhost rewrites.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. API Route Delegation (pass /api/* directly to the API router)
if (strpos($uri, '/api') === 0 || isset($_GET['route'])) {
    require __DIR__ . '/api/index.php';
    exit;
}

// 2. Direct installer link
if ($uri === '/install.php') {
    require __DIR__ . '/install.php';
    exit;
}

// 3. Serve root storefront
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    require __DIR__ . '/index.html';
    exit;
}

// 4. If requested without .html extension (e.g. /shop or /about)
$htmlFile = __DIR__ . $uri . '.html';
if (file_exists($htmlFile)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($htmlFile);
    exit;
}

// 5. If static file exists, serve with correct MIME type
$staticFile = __DIR__ . $uri;
if (file_exists($staticFile) && !is_dir($staticFile)) {
    $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    $mimes = [
        'html' => 'text/html; charset=UTF-8',
        'css'  => 'text/css; charset=UTF-8',
        'js'   => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon'
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    readfile($staticFile);
    exit;
}

// 6. Fallback to index.html
require __DIR__ . '/index.html';
