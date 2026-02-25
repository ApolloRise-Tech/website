<?php
/**
 * Posts Data Layer
 * ----------------
 * CRUD operations on JSON-based post storage with file locking.
 */

/**
 * Read the posts index (all posts metadata)
 */
function posts_read_index(): array {
    if (!file_exists(POSTS_INDEX)) return [];
    $json = file_get_contents(POSTS_INDEX);
    return json_decode($json, true) ?: [];
}

/**
 * Write the posts index atomically
 */
function posts_write_index(array $posts): void {
    $json = json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tmp = POSTS_INDEX . '.tmp';
    file_put_contents($tmp, $json, LOCK_EX);
    rename($tmp, POSTS_INDEX);
}

/**
 * Read a single post body (HTML content)
 */
function post_read_body(string $slug): ?string {
    $file = POSTS_DIR . '/' . sanitize_slug($slug) . '.json';
    if (!file_exists($file)) return null;
    $data = json_decode(file_get_contents($file), true);
    return $data['body'] ?? null;
}

/**
 * Save a single post body
 */
function post_write_body(string $slug, string $body): void {
    $file = POSTS_DIR . '/' . sanitize_slug($slug) . '.json';
    $data = json_encode(['body' => $body], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($file, $data, LOCK_EX);
}

/**
 * Delete a post body file
 */
function post_delete_body(string $slug): void {
    $file = POSTS_DIR . '/' . sanitize_slug($slug) . '.json';
    if (file_exists($file)) unlink($file);
}

/**
 * Get a single post from index by slug
 */
function post_get(string $slug): ?array {
    $posts = posts_read_index();
    foreach ($posts as $post) {
        if ($post['slug'] === $slug) return $post;
    }
    return null;
}

/**
 * Get published posts, sorted by date descending
 */
function posts_get_published(): array {
    $posts = posts_read_index();
    $published = array_filter($posts, fn($p) => ($p['status'] ?? 'draft') === 'published');
    usort($published, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
    return array_values($published);
}

/**
 * Get all posts sorted by date descending (for admin)
 */
function posts_get_all(): array {
    $posts = posts_read_index();
    usort($posts, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
    return $posts;
}

/**
 * Save or update a post in the index
 */
function post_save(array $postData): void {
    $posts = posts_read_index();
    $found = false;

    foreach ($posts as $i => $p) {
        if ($p['slug'] === $postData['slug']) {
            $posts[$i] = array_merge($p, $postData);
            $found = true;
            break;
        }
    }

    if (!$found) {
        $posts[] = $postData;
    }

    posts_write_index($posts);
}

/**
 * Delete a post from the index
 */
function post_delete(string $slug): void {
    $posts = posts_read_index();
    $posts = array_filter($posts, fn($p) => $p['slug'] !== $slug);
    posts_write_index(array_values($posts));
    post_delete_body($slug);

    // Also delete cover image if exists
    $coverPattern = UPLOADS_DIR . '/cover-' . sanitize_slug($slug) . '.*';
    foreach (glob($coverPattern) as $file) {
        unlink($file);
    }
}

/**
 * Increment view counter for a post
 */
function post_increment_views(string $slug): void {
    $posts = posts_read_index();
    foreach ($posts as $i => $p) {
        if ($p['slug'] === $slug) {
            $posts[$i]['views'] = ($p['views'] ?? 0) + 1;
            break;
        }
    }
    posts_write_index($posts);
}

/**
 * Get previous and next published posts relative to given slug
 */
function post_get_neighbors(string $slug): array {
    $published = posts_get_published();
    $prev = null;
    $next = null;

    foreach ($published as $i => $p) {
        if ($p['slug'] === $slug) {
            $prev = $published[$i + 1] ?? null; // older = next in descending array
            $next = $published[$i - 1] ?? null;  // newer
            break;
        }
    }

    return ['prev' => $prev, 'next' => $next];
}

/**
 * Get all unique tags from published posts
 */
function posts_get_all_tags(): array {
    $posts = posts_get_published();
    $tags = [];
    foreach ($posts as $p) {
        foreach (($p['tags'] ?? []) as $tag) {
            $tags[$tag] = ($tags[$tag] ?? 0) + 1;
        }
    }
    arsort($tags);
    return $tags;
}

/**
 * Handle cover image upload, returns relative path or null
 */
function upload_cover_image(string $slug): ?string {
    if (empty($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES['cover_image'];

    // Validate size
    if ($file['size'] > MAX_UPLOAD_SIZE) return null;

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return null;

    // Validate MIME type
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowedMimes)) return null;

    // Ensure uploads directory exists
    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0755, true);
    }

    $filename = 'cover-' . sanitize_slug($slug) . '-' . time() . '.' . $ext;
    $dest = UPLOADS_DIR . '/' . $filename;

    // Remove old cover images for this slug
    foreach (glob(UPLOADS_DIR . '/cover-' . sanitize_slug($slug) . '-*') as $old) {
        @unlink($old);
    }

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return '/blog-data/uploads/' . $filename;
    }

    return null;
}

/**
 * Handle inline image upload (from TinyMCE), returns URL or null
 */
function upload_inline_image(): ?string {
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES['file'];
    if ($file['size'] > MAX_UPLOAD_SIZE) return null;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return null;

    $mime = mime_content_type($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    if (!in_array($mime, $allowedMimes)) return null;

    // Ensure uploads directory exists
    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0755, true);
    }

    $filename = 'img-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOADS_DIR . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return '/blog-data/uploads/' . $filename;
    }

    return null;
}

/**
 * Generate excerpt from HTML body
 */
function generate_excerpt(string $html, int $maxLen = 200): string {
    $text = strip_tags($html);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    if (mb_strlen($text) <= $maxLen) return $text;
    return mb_substr($text, 0, $maxLen) . '…';
}
