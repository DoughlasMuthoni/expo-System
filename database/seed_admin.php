<?php

declare(strict_types=1);

/**
 * CLI-only tool to create an admin_users row. Never expose this under
 * public/ — it's invoked directly via the php binary.
 *
 * Usage: php database/seed_admin.php <username>
 * (prompts for the password with echo disabled)
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden: CLI only.');
}

require_once __DIR__ . '/../includes/Database.php';

$username = $argv[1] ?? null;

if (!$username) {
    fwrite(STDERR, "Usage: php database/seed_admin.php <username>\n");
    exit(1);
}

echo 'Password: ';
system('stty -echo');
$password = trim((string) fgets(STDIN));
system('stty echo');
echo "\n";

if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    exit(1);
}

$pdo = Database::getConnection();

$check = $pdo->prepare('SELECT id FROM admin_users WHERE username = :username');
$check->execute(['username' => $username]);

if ($check->fetch()) {
    fwrite(STDERR, "Username '{$username}' already exists.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (:username, :hash)');
$insert->execute(['username' => $username, 'hash' => $hash]);

echo "Admin user '{$username}' created.\n";
