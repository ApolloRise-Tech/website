<?php
/**
 * Local Dev Router
 * ----------------
 * Replaces .htaccess rewrite for PHP built-in server.
 * Usage: php -S 127.0.0.1:8080 -t build build/blog/router.php
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Serve existing files directly (CSS, JS, images, static HTML, etc.)
$filePath = $_SERVER['DOCUMENT_ROOT'] . $path;
if ($path !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false; // Let the built-in server handle it
}

// Blog post clean URL: /blog/{slug}
if (preg_match('#^/blog/([a-z0-9][a-z0-9-]+)/?$#', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require $_SERVER['DOCUMENT_ROOT'] . '/blog/post.php';
    exit;
}

// Blog index: /blog/ or /blog
if (preg_match('#^/blog/?$#', $path)) {
    require $_SERVER['DOCUMENT_ROOT'] . '/blog/index.php';
    exit;
}

// Blog admin routes
if (preg_match('#^/blog-admin/?$#', $path)) {
    require $_SERVER['DOCUMENT_ROOT'] . '/blog-admin/index.php';
    exit;
}
if (preg_match('#^/blog-admin/(login|edit|logout|api)\.php#', $path)) {
    return false; // Let built-in server handle .php files
}

// Directory index files
if (is_dir($filePath)) {
    // Try index.html
    if (file_exists($filePath . '/index.html')) {
        require $filePath . '/index.html';
        exit;
    }
    // Try index.php
    if (file_exists($filePath . '/index.php')) {
        require $filePath . '/index.php';
        exit;
    }
}

// Default: let built-in server try to serve the file
return false;
