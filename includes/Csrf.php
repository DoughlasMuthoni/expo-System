<?php

declare(strict_types=1);

/**
 * CSRF token helper. Every form (public and admin) must include the
 * hidden field from Csrf::field(), and every POST handler must call
 * Csrf::verify() before doing anything else.
 */
class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }

    public static function verify(?string $submittedToken): bool
    {
        if (empty($_SESSION[self::SESSION_KEY]) || empty($submittedToken)) {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $submittedToken);
    }
}
