<?php

declare(strict_types=1);

/**
 * Aggregate read-only queries for the admin dashboard. Date boundaries are
 * computed in SQL (CURDATE()), not PHP, so a PHP/MySQL timezone mismatch
 * can't skew "today" — same lesson as the rate limiter in Submission.php.
 */
class Stats
{
    public static function totalExpos(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM expos')->fetchColumn();
    }

    public static function activeExpos(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM expos WHERE is_active = 1')->fetchColumn();
    }

    public static function totalSubmissions(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM expo_submissions')->fetchColumn();
    }

    public static function submissionsToday(): int
    {
        return (int) Database::getConnection()
            ->query('SELECT COUNT(*) FROM expo_submissions WHERE DATE(submitted_at) = CURDATE()')
            ->fetchColumn();
    }

    public static function possibleDuplicates(): int
    {
        return (int) Database::getConnection()
            ->query('SELECT COUNT(*) FROM expo_submissions WHERE is_possible_duplicate = 1')
            ->fetchColumn();
    }

    /** One row per expo with its submission + duplicate counts, newest expo first. */
    public static function perExpoBreakdown(): array
    {
        return Database::getConnection()->query(
            'SELECT
                e.id, e.name, e.slug, e.is_active, e.created_at,
                COUNT(s.id) AS submission_count,
                COALESCE(SUM(s.is_possible_duplicate), 0) AS duplicate_count
             FROM expos e
             LEFT JOIN expo_submissions s ON s.expo_id = e.id
             GROUP BY e.id, e.name, e.slug, e.is_active, e.created_at
             ORDER BY e.created_at DESC'
        )->fetchAll();
    }
}
