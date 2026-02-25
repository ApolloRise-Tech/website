<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/posts.php';
require_once __DIR__ . '/helpers/seo.php';
require_once __DIR__ . '/helpers/assets.php';

security_headers();

$slug = sanitize_slug($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: /blog/');
    exit;
}

$post = post_get($slug);
if (!$post || ($post['status'] ?? 'draft') !== 'published') {
    http_response_code(404);
    header('Location: /404.html');
    exit;
}

// Increment view counter
post_increment_views($slug);

// Get post body
$body = post_read_body($slug) ?? '';

// Get prev/next
$neighbors = post_get_neighbors($slug);
$prevPost = $neighbors['prev'];
$nextPost = $neighbors['next'];
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

    <?= seo_post_head($post) ?>

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
        <article class="blogPost wrapper">
            <!-- Post Header -->
            <header class="blogPost__header">
                <a href="/blog/" class="blogPost__back">← Back to Blog</a>

                <div class="blogPost__meta">
                    <time datetime="<?= e($post['date']) ?>"><?= date('F j, Y', strtotime($post['date'])) ?></time>
                    <span class="blogPost__metaSep">·</span>
                    <span class="blogPost__author"><?= e($post['author'] ?? DEFAULT_AUTHOR) ?></span>
                </div>

                <h1 class="h1 blogPost__title"><?= e($post['title']) ?></h1>

                <?php if (!empty($post['tags'])): ?>
                    <div class="blogPost__tags">
                        <?php foreach ($post['tags'] as $tag): ?>
                            <a href="/blog/?tag=<?= urlencode($tag) ?>" class="blogPost__tag"><?= e($tag) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </header>

            <!-- Cover Image -->
            <?php if (!empty($post['coverImage'])): ?>
                <div class="blogPost__cover">
                    <img src="<?= e($post['coverImage']) ?>" alt="<?= e($post['title']) ?>">
                </div>
            <?php endif; ?>

            <!-- Post Body -->
            <div class="blogPost__body">
                <?= $body ?>
            </div>

            <!-- Author & Date Footer -->
            <footer class="blogPost__footer">
                <div class="blogPost__authorBlock">
                    <div class="blogPost__authorAvatar">AR</div>
                    <div>
                        <div class="blogPost__authorName"><?= e($post['author'] ?? DEFAULT_AUTHOR) ?></div>
                        <div class="blogPost__authorDate">Published <?= date('F j, Y', strtotime($post['date'])) ?></div>
                    </div>
                </div>
            </footer>
        </article>

        <!-- Navigation: Prev / Next -->
        <section class="blogNav wrapper">
            <div class="blogNav__wrapper">
                <?php if ($prevPost): ?>
                    <a href="/blog/<?= e($prevPost['slug']) ?>" class="blogNav__link blogNav__prev">
                        <span class="blogNav__label">← Previous</span>
                        <span class="blogNav__linkTitle"><?= e($prevPost['title']) ?></span>
                    </a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>

                <?php if ($nextPost): ?>
                    <a href="/blog/<?= e($nextPost['slug']) ?>" class="blogNav__link blogNav__next">
                        <span class="blogNav__label">Next →</span>
                        <span class="blogNav__linkTitle"><?= e($nextPost['title']) ?></span>
                    </a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
