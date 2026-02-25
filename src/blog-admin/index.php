<?php
require_once __DIR__ . '/../blog/config.php';
require_once __DIR__ . '/../blog/helpers/auth.php';
require_once __DIR__ . '/../blog/helpers/security.php';
require_once __DIR__ . '/../blog/helpers/posts.php';

secure_session_start();
admin_security_headers();
throttle_request(true);
require_auth();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $slug = sanitize_slug($_POST['slug'] ?? '');
        if ($slug) post_delete($slug);
    }
    header('Location: /blog-admin/');
    exit;
}

$posts = posts_get_all();
$totalViews = array_sum(array_column($posts, 'views'));
$publishedCount = count(array_filter($posts, fn($p) => ($p['status'] ?? 'draft') === 'published'));
$draftCount = count($posts) - $publishedCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard — Blog Admin</title>
    <link rel="stylesheet" href="/blog-admin/assets/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <a href="/">ApolloRise</a>
                <span class="sidebar-badge">Blog Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="/blog-admin/" class="sidebar-link active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="/blog-admin/edit.php" class="sidebar-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    New Post
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
                <h1>Dashboard</h1>
                <a href="/blog-admin/edit.php" class="btn btn-primary">+ New Post</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $publishedCount ?></div>
                    <div class="stat-label">Published</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $draftCount ?></div>
                    <div class="stat-label">Drafts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($totalViews) ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>

            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <p>No posts yet. Create your first one!</p>
                    <a href="/blog-admin/edit.php" class="btn btn-primary">Create Post</a>
                </div>
            <?php else: ?>
                <div class="posts-table-wrap">
                    <table class="posts-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Views</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td class="post-title-cell">
                                        <a href="/blog-admin/edit.php?slug=<?= e($post['slug']) ?>">
                                            <?= e($post['title']) ?>
                                        </a>
                                        <?php if (!empty($post['tags'])): ?>
                                            <div class="post-tags-small">
                                                <?php foreach ($post['tags'] as $tag): ?>
                                                    <span class="tag-small"><?= e($tag) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= e($post['status'] ?? 'draft') ?>">
                                            <?= e(ucfirst($post['status'] ?? 'draft')) ?>
                                        </span>
                                    </td>
                                    <td class="date-cell"><?= e(date('M j, Y', strtotime($post['date'] ?? 'now'))) ?></td>
                                    <td class="views-cell"><?= number_format($post['views'] ?? 0) ?></td>
                                    <td class="actions-cell">
                                        <a href="/blog-admin/edit.php?slug=<?= e($post['slug']) ?>" class="btn btn-sm btn-outline" title="Edit">Edit</a>
                                        <?php if (($post['status'] ?? 'draft') === 'published'): ?>
                                            <a href="/blog/<?= e($post['slug']) ?>" class="btn btn-sm btn-ghost" target="_blank" title="View">View</a>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this post permanently?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="slug" value="<?= e($post['slug']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
