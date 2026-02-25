<?php
/**
 * Admin API Endpoints
 * -------------------
 * Handles AJAX requests: image upload, etc.
 */
require_once __DIR__ . '/../blog/config.php';
require_once __DIR__ . '/../blog/helpers/auth.php';
require_once __DIR__ . '/../blog/helpers/security.php';
require_once __DIR__ . '/../blog/helpers/posts.php';

secure_session_start();
admin_security_headers();
throttle_request(true);
header('Content-Type: application/json');

// All API actions require authentication
if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'upload_image':
        // TinyMCE image upload handler
        $url = upload_inline_image();
        if ($url) {
            echo json_encode(['location' => $url]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Upload failed. Check file type and size (max 5MB).']);
        }
        break;

    case 'generate_sitemap':
        // Regenerate blog sitemap
        generate_blog_sitemap();
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}

/**
 * Generate blog sitemap XML
 */
function generate_blog_sitemap(): void {
    $posts = posts_get_published();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Blog index
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . BLOG_URL . "/</loc>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.7</priority>\n";
    $xml .= "  </url>\n";

    // Individual posts
    foreach ($posts as $post) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . BLOG_URL . "/" . htmlspecialchars($post['slug']) . "</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d', strtotime($post['date'])) . "</lastmod>\n";
        $xml .= "    <changefreq>monthly</changefreq>\n";
        $xml .= "    <priority>0.6</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= "</urlset>\n";

    file_put_contents(dirname(DATA_DIR) . '/blog/sitemap-blog.xml', $xml, LOCK_EX);
}
