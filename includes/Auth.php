<?php

declare(strict_types=1);

/**
 * Admin authentication. Public visitors never touch this class —
 * they don't have accounts (see CLAUDE.md Core Business Rules).
 */
class Auth
{
    private const SESSION_ADMIN_ID       = 'admin_id';
    private const SESSION_ADMIN_USERNAME = 'admin_username';

    public static function attempt(string $username, string $password): bool
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Prevent session fixation: new session ID on every successful login.
        session_regenerate_id(true);

        $_SESSION[self::SESSION_ADMIN_ID]       = (int) $user['id'];
        $_SESSION[self::SESSION_ADMIN_USERNAME] = $user['username'];

        $update = $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id');
        $update->execute(['id' => $user['id']]);

        return true;
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION[self::SESSION_ADMIN_ID]);
    }

    /**
     * Call as the first action of every file under public/admin/.
     */
    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public static function currentUsername(): ?string
    {
        return $_SESSION[self::SESSION_ADMIN_USERNAME] ?? null;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
