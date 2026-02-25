<?php
/**
 * Password Hash Generator
 * -----------------------
 * Run this script from the command line to generate a bcrypt hash:
 *   php generate-password.php YourNewPassword
 *
 * Then copy the output hash into blog/config.php → ADMIN_PASSWORD_HASH
 *
 * DO NOT leave this file accessible on production!
 * Delete it after generating your hash, or protect it via .htaccess.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo 'This script must be run from the command line.';
    exit;
}

if ($argc < 2) {
    echo "Usage: php generate-password.php <your-password>\n";
    exit(1);
}

$password = $argv[1];
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "\n";
echo "Password: {$password}\n";
echo "Hash:     {$hash}\n";
echo "\n";
echo "Copy the hash into blog/config.php → ADMIN_PASSWORD_HASH\n";
echo "\n";
