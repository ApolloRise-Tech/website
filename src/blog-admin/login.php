<?php
require_once __DIR__ . '/../blog/config.php';
require_once __DIR__ . '/../blog/helpers/auth.php';
require_once __DIR__ . '/../blog/helpers/security.php';

secure_session_start();
security_headers();
throttle_request(true);

// Already logged in?
if (is_authenticated()) {
    header('Location: /blog-admin/');
    exit;
}

$error = '';
$rateLimited = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request. Please try again.';
    } elseif (!rate_limit_check('login')) {
        $rateLimited = true;
        $error = 'Too many login attempts. Please wait 15 minutes.';
    } else {
        $password = $_POST['password'] ?? '';
        if (attempt_login($password)) {
            header('Location: /blog-admin/');
            exit;
        } else {
            $error = 'Invalid password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login — ApolloRise Blog</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #F7F6F3;
            color: #23221F;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #FFFFFF;
            border: 1px solid #E8E6E1;
            border-radius: 16px;
            padding: 48px 40px;
            width: 100%;
            max-width: 400px;
            margin: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .login-card h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #23221F;
        }

        .login-card p.subtitle {
            font-size: 14px;
            color: #7B7561;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: #7B7561;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: #FAFAF8;
            border: 1px solid #E8E6E1;
            border-radius: 10px;
            color: #23221F;
            font-size: 16px;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #F1BF04;
        }

        .error-msg {
            background: rgba(220, 38, 38, 0.06);
            border: 1px solid rgba(220, 38, 38, 0.2);
            color: #DC2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #F1BF04;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background: #D4A904;
        }

        .btn-login:active {
            background: #C09B03;
        }

        .btn-login:disabled {
            background: #E8E6E1;
            color: #918A72;
            cursor: not-allowed;
        }

        .logo-link {
            display: block;
            text-align: center;
            margin-bottom: 24px;
            color: #7B7561;
            font-size: 13px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <a href="/" class="logo-link">← apollorise.tech</a>
        <h1>Blog Admin</h1>
        <p class="subtitle">Enter your password to continue</p>

        <?php if ($error): ?>
            <div class="error-msg"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autofocus
                       <?= $rateLimited ? 'disabled' : '' ?>
                       placeholder="Enter admin password">
            </div>
            <button type="submit" class="btn-login" <?= $rateLimited ? 'disabled' : '' ?>>
                Sign In
            </button>
        </form>
    </div>
</body>
</html>
