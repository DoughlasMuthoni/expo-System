<?php

declare(strict_types=1);

/**
 * Shared bootstrap: session hardening + core class loading.
 * require_once this as the very first line of every entry point under public/.
 */

error_reporting(E_ALL);

// Match MySQL's system timezone so PHP-side dates never drift against
// DB timestamps (MySQL here uses SYSTEM tz; see includes/Submission.php
// for why the rate-limit check does its comparison in SQL regardless).
date_default_timezone_set('Africa/Nairobi');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

$config = require __DIR__ . '/../config/config.php';
$isDev  = ($config['app']['env'] ?? 'production') === 'development';

// display_errors=0 in production; errors are still logged server-side.
ini_set('display_errors', $isDev ? '1' : '0');
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        // 'secure' requires HTTPS; relaxed only in local dev (plain http://localhost).
        'secure'   => !$isDev,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_name($config['app']['session_name'] ?? 'waterlift_expo_session');
    session_start();
}

// Public form language toggle (see t() in helpers.php). Set here, before any
// page logic runs, so validation-error messages generated later in the same
// request already come out in the right language.
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'sw'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Csrf.php';
require_once __DIR__ . '/Auth.php';
