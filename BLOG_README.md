# Blog System — Deployment & Maintenance Guide

## Architecture

```
build/                          ← Upload entire folder contents to Hostinger
├── blog/                       ← Public blog (PHP)
│   ├── index.php               ← Blog listing page
│   ├── post.php                ← Single post page
│   ├── .htaccess               ← Apache rewrite: /blog/slug → post.php?slug=slug
│   ├── config.php              ← Configuration (password, paths, settings)
│   ├── router.php              ← Local dev only (not used on Hostinger)
│   ├── assets/
│   │   ├── blog.css            ← Blog styles
│   │   ├── logo.svg            ← Header logo
│   │   └── logo-light.svg      ← Footer logo (light version)
│   ├── helpers/
│   │   ├── auth.php            ← Login/logout logic
│   │   ├── security.php        ← CSRF, rate limiting, throttling, logging
│   │   ├── posts.php           ← CRUD for JSON-based posts
│   │   ├── seo.php             ← Auto SEO meta tags + JSON-LD
│   │   └── assets.php          ← Finds hashed webpack CSS files
│   └── includes/
│       ├── header.php          ← Site header (matches main site nav)
│       └── footer.php          ← Site footer
├── blog-admin/                 ← Admin panel (password protected)
│   ├── login.php               ← Login page
│   ├── index.php               ← Dashboard (list/delete posts)
│   ├── edit.php                ← Post editor (TinyMCE)
│   ├── api.php                 ← Image upload API
│   ├── logout.php
│   ├── generate-password.php   ← CLI password hash tool (DELETE after use!)
│   └── assets/admin.css        ← Admin styles
├── blog-data/                  ← Data storage (must be writable!)
│   ├── .htaccess               ← Blocks direct access to JSON files
│   ├── posts.json              ← Posts index (metadata)
│   ├── posts/                  ← Post body HTML files (one per post)
│   └── uploads/                ← Uploaded images (covers + inline)
└── (other site files: index.html, css/, js/, sources/, etc.)
```

---

## Deployment to Hostinger

### Step 1: Build

```bash
npm run build:prod
```

### Step 2: Upload

Upload the **contents of `build/`** to Hostinger's `public_html/` via:
- **Hostinger File Manager** (drag & drop), or
- **FTP/SFTP** (e.g. FileZilla), or
- **SSH** + `rsync` (if available on your plan)

The structure on Hostinger should be:
```
public_html/
├── index.html
├── css/
├── js/
├── sources/
├── blog/
├── blog-admin/
├── blog-data/
├── sitemap.xml
├── .htaccess
└── ...
```

### Step 3: Set permissions

SSH into Hostinger or use File Manager to set permissions:

```bash
chmod -R 755 public_html/blog-data/
chmod -R 755 public_html/blog-data/uploads/
chmod -R 755 public_html/blog-data/posts/
```

This allows PHP to write JSON files and save uploaded images.

### Step 4: Change the admin password

**Do this immediately after first deploy!**

Option A — via SSH:
```bash
cd public_html
php blog-admin/generate-password.php YourNewSecurePassword123!
```
Copy the output hash, then edit `blog/config.php`:
```php
define('ADMIN_PASSWORD_HASH', '$2y$12$YOUR_NEW_HASH_HERE');
```

Option B — generate locally:
```bash
php -r "echo password_hash('YourNewSecurePassword123!', PASSWORD_BCRYPT, ['cost' => 12]);"
```
Then paste the hash into `blog/config.php` on the server.

### Step 5: Delete the password generator

```bash
rm public_html/blog-admin/generate-password.php
```

### Step 6: Verify

1. Open `https://apollorise.tech/blog/` — should show the blog listing
2. Open `https://apollorise.tech/blog-admin/` — should redirect to login
3. Log in with your new password
4. Create a test post, upload a cover image, publish it
5. Verify clean URL works: `https://apollorise.tech/blog/your-post-slug`

---

## Day-to-Day Maintenance

### Creating a post

1. Go to `https://apollorise.tech/blog-admin/`
2. Log in
3. Click **"New Post"**
4. Fill in: title, content (rich text editor), tags, cover image
5. Set status to **Published**, click **Save**
6. Post is immediately live at `/blog/your-slug`

### Editing a post

1. Dashboard → click post title → edit → save

### Deleting a post

1. Dashboard → click **Delete** button → confirms deletion
2. This removes the post from the index, deletes its body file and cover image

### Cover images

- Max 5MB, formats: JPG, PNG, WebP, GIF, SVG
- Uploaded to `blog-data/uploads/`
- Old cover is automatically replaced when uploading a new one

### Inline images (in post body)

- Use the TinyMCE toolbar image button
- Images are uploaded via AJAX to `blog-data/uploads/`

---

## Updating the Site (Re-deploying)

When you change the main site (Pug templates, SCSS, JS):

1. `npm run build:prod`
2. Upload **only the changed files** to Hostinger:
   - `index.html`, `css/`, `js/`, `sources/` — main site files
   - `blog/`, `blog-admin/` — blog PHP files (if changed)
3. **DO NOT overwrite `blog-data/`** on the server — it contains your live posts and uploads!

### Safe re-deploy checklist

| Upload | Skip |
|--------|------|
| `index.html`, `*.html` | `blog-data/posts.json` |
| `css/`, `js/` | `blog-data/posts/` |
| `blog/*.php`, `blog/assets/` | `blog-data/uploads/` |
| `blog-admin/*.php`, `blog-admin/assets/` | `blog-data/.security.log` |
| `sources/`, `sitemap.xml` | |

---

## Security Features

| Feature | Description |
|---------|-------------|
| **Password** | bcrypt hash (cost 12), never stored in plaintext |
| **Session fingerprinting** | Sessions bound to IP + User-Agent |
| **CSRF protection** | One-time tokens on every form |
| **Login rate limiting** | 5 attempts per 15 min per IP |
| **Request throttling** | 60 req/min public, 30 req/min admin |
| **Auto IP ban** | >200 req/min → 10 min block |
| **CSP headers** | Content-Security-Policy on admin pages |
| **Security logging** | All auth events logged to `.security.log` |
| **No-cache** | Admin pages not cached by browser |
| **File validation** | MIME type + extension check on uploads |
| **Directory protection** | `.htaccess` blocks direct JSON file access |

### Monitoring security

Check the security log on the server:
```bash
cat public_html/blog-data/.security.log
```

Example entries:
```
[2025-02-25 12:00:00] [LOGIN_SUCCESS] IP=1.2.3.4 URI=/blog-admin/login.php
[2025-02-25 12:01:00] [LOGIN_FAILED] IP=5.6.7.8 URI=/blog-admin/login.php DETAIL=Invalid password attempt
[2025-02-25 12:02:00] [IP_BANNED] IP=5.6.7.8 DETAIL=IP banned for excessive requests: 201/min
```

---

## Local Development

```bash
# 1. Build
npm run build:prod

# 2. Start PHP dev server with router (handles clean URLs)
npm run dev:blog

# 3. Open browser
#    Blog:     http://127.0.0.1:8080/blog/
#    Post:     http://127.0.0.1:8080/blog/your-slug
#    Admin:    http://127.0.0.1:8080/blog-admin/
#    Default password: ApolloRise2025!
```

⚠️ **Always use `npm run dev:blog`** — it starts the PHP server WITH the router file that handles clean URLs. Running `php -S` without the router means `/blog/slug` won't work.

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Blog page shows no styles | Check that `css/index.*.css` exists in build. The blog auto-detects it. |
| Clean URLs don't work on Hostinger | Check that `mod_rewrite` is enabled. Hostinger enables it by default. Verify `blog/.htaccess` is uploaded. |
| Cover image doesn't upload | Check `blog-data/uploads/` has write permissions (`chmod 755`). Check file is under 5MB. |
| Login doesn't work | Verify password hash in `config.php`. Check rate limit file: `blog-data/.rate_limits.json` — delete it to reset. |
| 429 Too Many Requests | Your IP was throttled. Wait 1 min, or delete `blog-data/.throttle.json` to reset. |
| Post body is empty | Check `blog-data/posts/your-slug.json` exists and contains `{"body": "..."}`. |
| Fonts look wrong | Check `sources/fonts/fonts.css` is accessible on the server. |

---

## Configuration Reference

All settings are in `blog/config.php`:

```php
SITE_URL              → 'https://apollorise.tech'
BLOG_TITLE            → 'Blog — ApolloRise Tech'
DEFAULT_AUTHOR        → 'ApolloRise Tech'
ADMIN_PASSWORD_HASH   → bcrypt hash (change this!)
ADMIN_SESSION_LIFETIME → 3600 (1 hour)
LOGIN_RATE_LIMIT      → 5 attempts
LOGIN_RATE_WINDOW     → 900 seconds (15 min)
POSTS_PER_PAGE        → 10
MAX_UPLOAD_SIZE       → 5MB
ALLOWED_EXTENSIONS    → jpg, jpeg, png, gif, webp, svg
BLOG_DEBUG            → false (set true to see PHP errors)
```
