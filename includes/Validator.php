<?php

declare(strict_types=1);

/**
 * Generic, reusable input validation. Server-side validation is
 * authoritative everywhere — see CLAUDE.md Core Business Rules.
 */
class Validator
{
    public static function required(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }

    public static function maxLength(?string $value, int $max): bool
    {
        return $value === null || mb_strlen($value) <= $max;
    }

    public static function slugFormat(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $value);
    }

    /** Empty string/null is considered valid — pair with required() when the field is mandatory. */
    public static function dateOrEmpty(?string $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    public static function isEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
