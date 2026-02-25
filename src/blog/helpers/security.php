<?php
/**
 * Security Helpers
 * ----------------
 * Session hardening, CSRF, rate limiting, request throttling,
 * input sanitization, XSS prevention, logging, CSP.
 */

// ─── Constants for throttling ────────────────────────────────────
define('THROTTLE_REQUESTS_PER_MINUTE', 60);
define('THROTTLE_ADMIN_REQUESTS_PER_MINUTE', 30);
define('BAN_THRESHOLD', 200);     // requests/minute triggers temp ban
define('BAN_DURATION', 600);      // 10 minutes

// ─── Session ─────────────────────────────────────────────────────

function secure_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => ADMIN_SESSION_LIFETIME,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly'  => true,
        'samesite'  => 'Strict',
    ]);

    session_name('ARSESSID');
    session_start();

    // Session fingerprinting — bind session to browser + IP
    $fingerprint = hash('sha256',
        ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . '|' .
        ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')
    );

    if (!isset($_SESSION['_fingerprint'])) {
        $_SESSION['_fingerprint'] = $fingerprint;
    } elseif (!hash_equals($_SESSION['_fingerprint'], $fingerprint)) {
        // Possible session hijacking — destroy session
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['_fingerprint'] = $fingerprint;
    }

    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

// ─── CSRF ────────────────────────────────────────────────────────

function csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function csrf_verify(): bool {
    $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (empty($token) || !hash_equals(csrf_token(), $token)) {
        return false;
    }
    // Rotate token after successful verification
    unset($_SESSION['_csrf_token']);
    return true;
}

// ─── Rate Limiting (login) ───────────────────────────────────────

function rate_limit_check(string $action = 'login'): bool {
    $ip = get_client_ip();
    $file = DATA_DIR . '/.rate_limits.json';

    $data = _rate_file_read($file);
    $key = $action . ':' . hash('sha256', $ip);
    $now = time();

    // Clean old entries
    if (isset($data[$key])) {
        $data[$key] = array_values(array_filter(
            $data[$key],
            fn($t) => ($now - $t) < LOGIN_RATE_WINDOW
        ));
        _rate_file_write($file, $data);
    }

    $attempts = count($data[$key] ?? []);
    return $attempts < LOGIN_RATE_LIMIT;
}

function rate_limit_record(string $action = 'login'): void {
    $ip = get_client_ip();
    $file = DATA_DIR . '/.rate_limits.json';

    $data = _rate_file_read($file);
    $key = $action . ':' . hash('sha256', $ip);
    $data[$key][] = time();

    _rate_file_write($file, $data);
}

// ─── Request Throttling (per IP, any endpoint) ───────────────────

function throttle_request(bool $isAdmin = false): void {
    $ip = get_client_ip();
    $file = DATA_DIR . '/.throttle.json';
    $now = time();

    $data = _rate_file_read($file);

    // Check if IP is temporarily banned
    $banKey = 'ban:' . hash('sha256', $ip);
    if (isset($data[$banKey]) && $data[$banKey] > $now) {
        http_response_code(429);
        header('Retry-After: ' . ($data[$banKey] - $now));
        die('Too many requests. Try again later.');
    }

    $key = 'req:' . hash('sha256', $ip);
    $window = 60; // 1 minute window
    $limit = $isAdmin ? THROTTLE_ADMIN_REQUESTS_PER_MINUTE : THROTTLE_REQUESTS_PER_MINUTE;

    // Clean old entries
    if (isset($data[$key])) {
        $data[$key] = array_values(array_filter(
            $data[$key],
            fn($t) => ($now - $t) < $window
        ));
    }

    $count = count($data[$key] ?? []);

    // Auto-ban on extreme abuse
    if ($count >= BAN_THRESHOLD) {
        $data[$banKey] = $now + BAN_DURATION;
        _rate_file_write($file, $data);
        security_log('IP_BANNED', "IP banned for excessive requests: {$count}/min");
        http_response_code(429);
        header('Retry-After: ' . BAN_DURATION);
        die('Too many requests. You have been temporarily blocked.');
    }

    // Normal throttle
    if ($count >= $limit) {
        http_response_code(429);
        header('Retry-After: 60');
        die('Rate limit exceeded. Please slow down.');
    }

    $data[$key][] = $now;

    // Periodic cleanup: remove entries older than 10 minutes
    if (rand(1, 50) === 1) {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = array_values(array_filter($v, fn($t) => ($now - $t) < 600));
                if (empty($data[$k])) unset($data[$k]);
            } elseif (str_starts_with($k, 'ban:') && $v < $now) {
                unset($data[$k]);
            }
        }
    }

    _rate_file_write($file, $data);
}

// ─── Security Logging ────────────────────────────────────────────

function security_log(string $event, string $detail = ''): void {
    $file = DATA_DIR . '/.security.log';
    $ip = get_client_ip();
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200);
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $ts = date('Y-m-d H:i:s');

    $line = "[{$ts}] [{$event}] IP={$ip} URI={$uri} UA={$ua}";
    if ($detail) $line .= " DETAIL={$detail}";
    $line .= "\n";

    file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

    // Rotate log if > 1MB
    if (file_exists($file) && filesize($file) > 1048576) {
        $backup = DATA_DIR . '/.security.log.old';
        if (file_exists($backup)) @unlink($backup);
        @rename($file, $backup);
    }
}

// ─── Directory Traversal Protection ──────────────────────────────

function safe_path(string $base, string $userInput): ?string {
    $resolved = realpath($base . '/' . $userInput);
    if ($resolved === false) return null;
    $realBase = realpath($base);
    if ($realBase === false) return null;

    // Ensure resolved path starts with the base directory
    if (str_starts_with($resolved, $realBase . DIRECTORY_SEPARATOR) || $resolved === $realBase) {
        return $resolved;
    }
    return null;
}

// ─── Input Sanitization ─────────────────────────────────────────

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize_slug(string $str): string {
    $str = mb_strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9\-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

function generate_slug(string $title): string {
    $slug = transliterate($title);
    return sanitize_slug($slug);
}

function transliterate(string $str): string {
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
        'ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m',
        'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
        'ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch',
        'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'Yo',
        'Ж'=>'Zh','З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M',
        'Н'=>'N','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U',
        'Ф'=>'F','Х'=>'Kh','Ц'=>'Ts','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Shch',
        'Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya',
    ];
    return strtr($str, $map);
}

// ─── Security Headers ────────────────────────────────────────────

function security_headers(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
}

function admin_security_headers(): void {
    security_headers();
    // Strict CSP for admin: no inline scripts except TinyMCE
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: blob:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self'; frame-src 'none';");
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
}

// ─── IP Detection ────────────────────────────────────────────────

function get_client_ip(): string {
    // Trust X-Forwarded-For only if behind known proxy (Hostinger/Cloudflare)
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        $ip = trim($ip);
        return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ─── Internal Helpers ────────────────────────────────────────────

function _rate_file_read(string $file): array {
    if (!file_exists($file)) return [];
    $content = @file_get_contents($file);
    if ($content === false) return [];
    return json_decode($content, true) ?: [];
}

function _rate_file_write(string $file, array $data): void {
    @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
}
