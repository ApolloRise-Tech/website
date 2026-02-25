<?php
/**
 * SEO Helper
 * ----------
 * Auto-generates meta tags, OG, Twitter Card, canonical, and JSON-LD for blog pages.
 */

/**
 * Output SEO meta tags for a blog post
 */
function seo_post_head(array $post): string {
    $e_site_name = e(SITE_NAME);
    $e_og_image = e(OG_IMAGE);
    $title = e($post['title']) . ' — ' . SITE_NAME . ' Blog';
    $desc = e($post['metaDesc'] ?? $post['excerpt'] ?? '');
    $url = BLOG_URL . '/' . e($post['slug']);
    $image = !empty($post['coverImage'])
        ? SITE_URL . $post['coverImage']
        : OG_IMAGE;
    $date = date('c', strtotime($post['date'] ?? 'now'));
    $author = e($post['author'] ?? DEFAULT_AUTHOR);

    $tags = '';
    foreach (($post['tags'] ?? []) as $tag) {
        $tags .= '    <meta property="article:tag" content="' . e($tag) . '">' . "\n";
    }

    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post['title'],
        'description' => $post['metaDesc'] ?? $post['excerpt'] ?? '',
        'image' => $image,
        'url' => BLOG_URL . '/' . $post['slug'],
        'datePublished' => $date,
        'dateModified' => $post['updatedAt'] ?? $date,
        'author' => [
            '@type' => ($post['author'] ?? DEFAULT_AUTHOR) === DEFAULT_AUTHOR ? 'Organization' : 'Person',
            'name' => $post['author'] ?? DEFAULT_AUTHOR,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => SITE_NAME,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => OG_IMAGE,
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => BLOG_URL . '/' . $post['slug'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    return <<<HTML
    <title>{$title}</title>
    <meta name="description" content="{$desc}">
    <meta name="author" content="{$author}">
    <link rel="canonical" href="{$url}">

    <meta property="og:type" content="article">
    <meta property="og:title" content="{$title}">
    <meta property="og:description" content="{$desc}">
    <meta property="og:url" content="{$url}">
    <meta property="og:image" content="{$image}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="{$e_site_name}">
    <meta property="article:published_time" content="{$date}">
{$tags}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{$title}">
    <meta name="twitter:description" content="{$desc}">
    <meta name="twitter:image" content="{$image}">

    <script type="application/ld+json">{$jsonLd}</script>
HTML;
}

/**
 * Output SEO meta tags for the blog listing page
 */
function seo_blog_index_head(int $page = 1): string {
    $e_site_name = e(SITE_NAME);
    $e_og_image = e(OG_IMAGE);
    $title = ($page > 1 ? "Page {$page} — " : '') . BLOG_TITLE;
    $desc = 'Latest insights on AI, engineering, and technology from ' . SITE_NAME . '. Read our articles about software development, Agentic AI, and digital product building.';
    $url = BLOG_URL . '/' . ($page > 1 ? "?page={$page}" : '');

    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        'name' => BLOG_TITLE,
        'description' => $desc,
        'url' => BLOG_URL . '/',
        'publisher' => [
            '@type' => 'Organization',
            'name' => SITE_NAME,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => OG_IMAGE,
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    return <<<HTML
    <title>{$title}</title>
    <meta name="description" content="{$desc}">
    <link rel="canonical" href="{$url}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{$title}">
    <meta property="og:description" content="{$desc}">
    <meta property="og:url" content="{$url}">
    <meta property="og:image" content="{$e_og_image}">
    <meta property="og:site_name" content="{$e_site_name}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{$title}">
    <meta name="twitter:description" content="{$desc}">
    <meta name="twitter:image" content="{$e_og_image}">

    <script type="application/ld+json">{$jsonLd}</script>
HTML;
}
