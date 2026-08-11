<?php

declare(strict_types=1);

/**
 * expo_submissions.is_possible_duplicate is set at insert time when
 * phone + expo_id already exists for that expo. Flagged, never blocked —
 * the admin decides what to do with it (CLAUDE.md Database Rules).
 */
class Duplicate
{
    public static function isPossible(string $phone, int $expoId): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id FROM expo_submissions WHERE phone = :phone AND expo_id = :expo_id LIMIT 1'
        );
        $stmt->execute(['phone' => $phone, 'expo_id' => $expoId]);

        return (bool) $stmt->fetch();
    }
}
