<?php
/**
 * Copy this file to config.php (same directory) and fill in real values.
 * config.php is git-ignored — never commit real credentials there.
 */

return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'waterlift_expo',
        'user'     => 'waterlift_app',
        'password' => 'CHANGE_ME',
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        // 'development' relaxes cookie 'secure' flag (for local http:// testing)
        // and enables on-screen error display. Always 'production' on the real server.
        'env'          => 'production',
        'base_url'     => 'https://example.com',
        'session_name' => 'waterlift_expo_session',
    ],
];
