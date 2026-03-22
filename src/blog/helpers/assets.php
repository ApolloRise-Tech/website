<?php
/**
 * Assets Helper
 * -------------
 * Resolves hashed CSS/JS filenames from webpack build output.
 * Uses SITE_ROOT for filesystem lookups (works when DOCUMENT_ROOT != site root).
 * URL paths stay root-relative — server .htaccess handles rewriting.
 */

/**
 * Find the hashed CSS file for a given entry name (e.g. 'index')
 * Looks in /css/ directory for files matching pattern: {name}.{hash}.css
 */
function find_css(string $entryName): string {
    $cssDir = SITE_ROOT . '/css';
    if (!is_dir($cssDir)) return '';

    $pattern = $cssDir . '/' . $entryName . '.*.css';
    $files = glob($pattern);

    if (!empty($files)) {
        return '/css/' . basename($files[0]);
    }
    return '';
}

/**
 * Find the hashed JS file for a given entry name
 */
function find_js(string $entryName): string {
    $jsDir = SITE_ROOT . '/js';
    if (!is_dir($jsDir)) return '';

    $pattern = $jsDir . '/' . $entryName . '.*.js';
    $files = glob($pattern);

    if (!empty($files)) {
        return '/js/' . basename($files[0]);
    }
    return '';
}
