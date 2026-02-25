<?php
/**
 * Blog Configuration
 * ------------------
 * IMPORTANT: Change ADMIN_PASSWORD_HASH before deploying to production!
 * Generate a new hash: php -r "echo password_hash('your-password', PASSWORD_BCRYPT);"
 */

// Error reporting — disable in production
define('BLOG_DEBUG', false);
if (BLOG_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Site
define('SITE_URL', 'https://apollorise.tech');
define('BLOG_URL', SITE_URL . '/blog');
define('BLOG_TITLE', 'Blog — ApolloRise Tech');
define('SITE_NAME', 'ApolloRise Tech');
define('DEFAULT_AUTHOR', 'ApolloRise Tech');
define('OG_IMAGE', SITE_URL . '/sources/images/linkImage.png');

// Paths (relative to blog/ directory)
define('BLOG_ROOT', __DIR__);
define('DATA_DIR', dirname(__DIR__) . '/blog-data');
define('POSTS_DIR', DATA_DIR . '/posts');
define('UPLOADS_DIR', DATA_DIR . '/uploads');
define('POSTS_INDEX', DATA_DIR . '/posts.json');

// Admin auth
// Default password: "ApolloRise2025!" — CHANGE THIS!
define('ADMIN_PASSWORD_HASH', '$2y$12$To/XwS2wdDIl56G0moav0uPywJlhFoydx1nmB6N0qCDr/JJ0OPctO');
define('ADMIN_SESSION_LIFETIME', 3600); // 1 hour
define('LOGIN_RATE_LIMIT', 5); // max attempts
define('LOGIN_RATE_WINDOW', 900); // 15 minutes

// Pagination
define('POSTS_PER_PAGE', 10);

// Uploads
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

// Ensure data directories exist
foreach ([DATA_DIR, POSTS_DIR, UPLOADS_DIR] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Initialize empty posts index if missing
if (!file_exists(POSTS_INDEX)) {
    file_put_contents(POSTS_INDEX, json_encode([], JSON_PRETTY_PRINT));
}
