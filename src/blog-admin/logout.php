<?php
require_once __DIR__ . '/../blog/config.php';
require_once __DIR__ . '/../blog/helpers/auth.php';
require_once __DIR__ . '/../blog/helpers/security.php';

secure_session_start();
logout();
header('Location: /blog-admin/login.php');
exit;
