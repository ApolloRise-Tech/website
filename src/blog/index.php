<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/posts.php';
require_once __DIR__ . '/helpers/seo.php';
require_once __DIR__ . '/helpers/assets.php';

security_headers();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$tagFilter = trim($_GET['tag'] ?? '');

// Get posts
$allPublished = posts_get_published();

// Filter by tag if provided
if ($tagFilter) {
    $allPublished = array_filter($allPublished, function($p) use ($tagFilter) {
        return in_array($tagFilter, $p['tags'] ?? []);
    });
    $allPublished = array_values($allPublished);
}

$totalPosts = count($allPublished);
$totalPages = max(1, ceil($totalPosts / POSTS_PER_PAGE));
$page = min($page, $totalPages);
$offset = ($page - 1) * POSTS_PER_PAGE;
$posts = array_slice($allPublished, $offset, POSTS_PER_PAGE);
$allTags = posts_get_all_tags();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#FCFBF8">

    <?= seo_blog_index_head($page) ?>

    <link rel="icon" type="image/png" href="/sources/images/favicon.png">
    <link rel="shortcut icon" href="/sources/images/favicon.ico">
    <link rel="stylesheet" href="/sources/fonts/fonts.css">
    <?php $siteCss = find_css('index'); if ($siteCss): ?>
    <link rel="stylesheet" href="<?= $siteCss ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/blog/assets/blog.css">

    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-PXHCM38Q');
    </script>
</head>
<body>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PXHCM38Q" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <!-- Hero -->
        <section class="blogHero wrapper">
            <div class="blogHero__wrapper">
                <p class="subtitle blogHero__subtitle">Blog</p>
                <h1 class="h1 blogHero__title">Insights & Updates</h1>
                <p class="text_2 blogHero__desc">Company news, leadership insights, partnership stories, and lessons from building world-class digital products.</p>
            </div>
        </section>

        <!-- Tags filter (top 5 most popular) -->
        <?php if (!empty($allTags)):
            $topTags = array_slice($allTags, 0, 5, true);
        ?>
        <section class="blogTags wrapper">
            <div class="blogTags__wrapper">
                <a href="/blog/" class="blogTags__tag <?= empty($tagFilter) ? 'active' : '' ?>">All</a>
                <?php foreach ($topTags as $tag => $count): ?>
                    <a href="/blog/?tag=<?= urlencode($tag) ?>" class="blogTags__tag <?= $tagFilter === $tag ? 'active' : '' ?>">
                        <?= e($tag) ?> <span class="blogTags__count"><?= $count ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Posts Grid -->
        <section class="blogGrid wrapper">
            <div class="blogGrid__wrapper">
                <?php if (empty($posts)): ?>
                    <div class="blogGrid__empty">
                        <p class="text_2">No posts yet. Stay tuned!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $i => $post): ?>
                        <a href="/blog/<?= e($post['slug']) ?>" class="blogCard <?= $i === 0 && $page === 1 && empty($tagFilter) ? 'blogCard--featured' : '' ?>">
                            <?php if (!empty($post['coverImage'])): ?>
                                <div class="blogCard__image">
                                    <img src="<?= e($post['coverImage']) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                                </div>
                            <?php else: ?>
                                <div class="blogCard__image blogCard__image--placeholder">
                                    <span>AR</span>
                                </div>
                            <?php endif; ?>
                            <div class="blogCard__content">
                                <div class="blogCard__meta">
                                    <time datetime="<?= e($post['date']) ?>"><?= date('M j, Y', strtotime($post['date'])) ?></time>
                                    <?php if (($post['author'] ?? DEFAULT_AUTHOR) !== DEFAULT_AUTHOR): ?>
                                        <span class="blogCard__author">by <?= e($post['author']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h2 class="blogCard__title"><?= e($post['title']) ?></h2>
                                <p class="blogCard__excerpt"><?= e($post['excerpt'] ?? '') ?></p>
                                <?php if (!empty($post['tags'])): ?>
                                    <div class="blogCard__tags">
                                        <?php foreach ($post['tags'] as $tag): ?>
                                            <span class="blogCard__tag"><?= e($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <section class="blogPagination wrapper">
            <div class="blogPagination__wrapper">
                <?php if ($page > 1): ?>
                    <a href="/blog/?page=<?= $page - 1 ?><?= $tagFilter ? '&tag=' . urlencode($tagFilter) : '' ?>" class="blogPagination__link blogPagination__prev">← Newer</a>
                <?php endif; ?>

                <span class="blogPagination__info">Page <?= $page ?> of <?= $totalPages ?></span>

                <?php if ($page < $totalPages): ?>
                    <a href="/blog/?page=<?= $page + 1 ?><?= $tagFilter ? '&tag=' . urlencode($tagFilter) : '' ?>" class="blogPagination__link blogPagination__next">Older →</a>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
