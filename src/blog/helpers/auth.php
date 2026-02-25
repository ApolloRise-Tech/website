<?php
/**
 * Authentication Helper
 * ---------------------
 * Session-based admin authentication with rate limiting.
 */

require_once __DIR__ . '/security.php';

/**
 * Check if user is logged in
 */
function is_authenticated(): bool {
    secure_session_start();
    if (empty($_SESSION['admin_authenticated'])) return false;

    // Check session expiry
    if (time() - ($_SESSION['admin_auth_time'] ?? 0) > ADMIN_SESSION_LIFETIME) {
        session_destroy();
        return false;
    }

    return true;
}

/**
 * Attempt login with password
 */
function attempt_login(string $password): bool {
    secure_session_start();

    // Rate limiting
    if (!rate_limit_check('login')) {
        security_log('LOGIN_RATE_LIMITED', 'Login blocked by rate limit');
        return false;
    }

    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_auth_time'] = time();
        $_SESSION['_created'] = time();
        security_log('LOGIN_SUCCESS');
        return true;
    }

    rate_limit_record('login');
    security_log('LOGIN_FAILED', 'Invalid password attempt');
    return false;
}

/**
 * Logout
 */
function logout(): void {
    secure_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Require authentication — redirect to login if not authenticated
 */
function require_auth(): void {
    if (!is_authenticated()) {
        header('Location: /blog-admin/login.php');
        exit;
    }
}
