<?php
require_once __DIR__ . '/../blog/config.php';
require_once __DIR__ . '/../blog/helpers/auth.php';
require_once __DIR__ . '/../blog/helpers/security.php';
require_once __DIR__ . '/../blog/helpers/posts.php';

secure_session_start();
admin_security_headers();
throttle_request(true);
require_auth();

$editSlug = $_GET['slug'] ?? '';
$isEdit = !empty($editSlug);
$post = null;
$body = '';
$success = '';
$error = '';

if ($isEdit) {
    $post = post_get($editSlug);
    if (!$post) {
        header('Location: /blog-admin/');
        exit;
    }
    $body = post_read_body($editSlug) ?? '';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = sanitize_slug($_POST['slug'] ?? '') ?: generate_slug($title);
        $bodyContent = $_POST['body'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $metaDesc = trim($_POST['meta_desc'] ?? '');
        $author = trim($_POST['author'] ?? '') ?: DEFAULT_AUTHOR;
        $tagsRaw = trim($_POST['tags'] ?? '');
        $tags = array_filter(array_map('trim', explode(',', $tagsRaw)));
        $date = $_POST['date'] ?? date('Y-m-d');
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

        if (empty($title)) {
            $error = 'Title is required.';
        } elseif (empty($slug)) {
            $error = 'Slug is required.';
        } else {
            // Check slug uniqueness (if creating new or changing slug)
            if (!$isEdit || $slug !== $editSlug) {
                $existing = post_get($slug);
                if ($existing) {
                    $error = 'A post with this slug already exists.';
                }
            }

            if (empty($error)) {
                // Auto-generate excerpt if empty
                if (empty($excerpt)) {
                    $excerpt = generate_excerpt($bodyContent);
                }
                if (empty($metaDesc)) {
                    $metaDesc = $excerpt;
                }

                // Handle cover image upload
                $coverImage = $post['coverImage'] ?? '';
                $uploaded = upload_cover_image($slug);
                if ($uploaded) {
                    $coverImage = $uploaded;
                }

                $postData = [
                    'slug'       => $slug,
                    'title'      => $title,
                    'excerpt'    => $excerpt,
                    'metaDesc'   => $metaDesc,
                    'author'     => $author,
                    'tags'       => array_values($tags),
                    'date'       => $date,
                    'status'     => $status,
                    'coverImage' => $coverImage,
                    'views'      => $post['views'] ?? 0,
                    'updatedAt'  => date('Y-m-d H:i:s'),
                ];

                // If slug changed, delete old files
                if ($isEdit && $slug !== $editSlug) {
                    post_delete($editSlug);
                }

                post_save($postData);
                post_write_body($slug, $bodyContent);

                $success = 'Post saved successfully!';
                $editSlug = $slug;
                $isEdit = true;
                $post = $postData;
                $body = $bodyContent;
            }
        }
    }
}

// Get all existing tags for autocomplete
$allTags = array_keys(posts_get_all_tags());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $isEdit ? 'Edit Post' : 'New Post' ?> — Blog Admin</title>
    <link rel="stylesheet" href="/blog-admin/assets/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <a href="/">ApolloRise</a>
                <span class="sidebar-badge">Blog Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="/blog-admin/" class="sidebar-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="/blog-admin/edit.php" class="sidebar-link active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    <?= $isEdit ? 'Edit Post' : 'New Post' ?>
                </a>
                <a href="/blog/" class="sidebar-link" target="_blank">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15,3 21,3 21,9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    View Blog
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="/blog-admin/logout.php" class="sidebar-link logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
            </div>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1><?= $isEdit ? 'Edit Post' : 'New Post' ?></h1>
                <?php if ($isEdit && ($post['status'] ?? 'draft') === 'published'): ?>
                    <a href="/blog/<?= e($post['slug']) ?>" class="btn btn-outline" target="_blank">View Live →</a>
                <?php endif; ?>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="editor-form">
                <?= csrf_field() ?>

                <div class="editor-grid">
                    <div class="editor-main">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title"
                                   value="<?= e($post['title'] ?? '') ?>"
                                   placeholder="Post title"
                                   required
                                   oninput="autoSlug(this.value)">
                        </div>

                        <div class="form-group">
                            <label for="body">Content</label>
                            <textarea id="body" name="body" class="tinymce-editor"><?= e($body) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="excerpt">Excerpt <span class="hint">(auto-generated if empty)</span></label>
                            <textarea id="excerpt" name="excerpt" rows="3" placeholder="Short preview text for cards..."><?= e($post['excerpt'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="editor-sidebar">
                        <div class="sidebar-card">
                            <h3>Publish</h3>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="date" id="date" name="date"
                                       value="<?= e($post['date'] ?? date('Y-m-d')) ?>">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-full">
                                    <?= $isEdit ? 'Update Post' : 'Save Post' ?>
                                </button>
                            </div>
                        </div>

                        <div class="sidebar-card">
                            <h3>Cover Image</h3>
                            <?php if (!empty($post['coverImage'])): ?>
                                <div class="current-cover">
                                    <img src="<?= e($post['coverImage']) ?>" alt="Cover">
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <input type="file" id="cover_image" name="cover_image" accept="image/*">
                                <span class="hint">Max 5MB. JPG, PNG, WebP, GIF, SVG.</span>
                            </div>
                        </div>

                        <div class="sidebar-card">
                            <h3>Details</h3>
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" name="slug"
                                       value="<?= e($post['slug'] ?? '') ?>"
                                       placeholder="post-url-slug">
                            </div>
                            <div class="form-group">
                                <label for="author">Author</label>
                                <input type="text" id="author" name="author"
                                       value="<?= e($post['author'] ?? DEFAULT_AUTHOR) ?>"
                                       placeholder="<?= e(DEFAULT_AUTHOR) ?>">
                            </div>
                            <div class="form-group">
                                <label for="tags">Tags <span class="hint">(comma-separated)</span></label>
                                <input type="text" id="tags" name="tags"
                                       value="<?= e(implode(', ', $post['tags'] ?? [])) ?>"
                                       placeholder="AI, Engineering, News">
                            </div>
                        </div>

                        <div class="sidebar-card">
                            <h3>SEO</h3>
                            <div class="form-group">
                                <label for="meta_desc">Meta Description <span class="hint">(auto from excerpt if empty)</span></label>
                                <textarea id="meta_desc" name="meta_desc" rows="3" placeholder="Custom meta description..."><?= e($post['metaDesc'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <?php if ($isEdit): ?>
                            <div class="sidebar-card sidebar-card-muted">
                                <div class="meta-info">
                                    <span>Views: <?= number_format($post['views'] ?? 0) ?></span>
                                    <?php if (!empty($post['updatedAt'])): ?>
                                        <span>Updated: <?= e($post['updatedAt']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Auto-generate slug from title
        function autoSlug(title) {
            const slugField = document.getElementById('slug');
            if (slugField.dataset.manual === 'true') return;
            slugField.value = title
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }

        // Mark slug as manually edited
        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.manual = 'true';
        });

        // TinyMCE initialization
        tinymce.init({
            selector: '.tinymce-editor',
            height: 500,
            menubar: false,
            plugins: 'lists link image blockquote code fullscreen',
            toolbar: 'blocks | bold italic underline strikethrough | forecolor | bullist numlist | blockquote | link image | code fullscreen',
            block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
            content_style: `
                @import url('/sources/fonts/fonts.css');
                body {
                    font-family: 'Cygre-R', sans-serif;
                    font-size: 18px;
                    line-height: 1.7;
                    color: #23221F;
                    max-width: 100%;
                    padding: 16px;
                }
                h1, h2, h3, h4, h5, h6 { font-family: 'Cygre-M', sans-serif; margin: 1em 0 0.5em; }
                h1 { font-size: 2em; }
                h2 { font-size: 1.6em; }
                h3 { font-size: 1.3em; }
                blockquote {
                    border-left: 4px solid #FFC901;
                    padding: 12px 20px;
                    margin: 1em 0;
                    background: #FCFBF8;
                    color: #4F4B40;
                    font-style: italic;
                }
                img { max-width: 100%; height: auto; border-radius: 8px; }
                a { color: #F1BF04; }
            `,
            images_upload_url: '/blog-admin/api.php?action=upload_image',
            images_upload_credentials: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            color_map: [
                '23221F', 'Black',
                '918A72', 'Gray',
                'FFC901', 'Yellow',
                'F1BF04', 'Yellow Dark',
                'FF3838', 'Red',
                'FCFBF8', 'Light',
            ],
            skin: 'oxide',
            promotion: false,
            branding: false,
        });
    </script>
</body>
</html>
