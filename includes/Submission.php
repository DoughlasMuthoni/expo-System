<?php

declare(strict_types=1);

/**
 * Data access for expo_submissions + the submission_interests junction.
 * Server-side validation/authority lives in the caller (public/expo/index.php);
 * this class only persists already-validated data.
 */
class Submission
{
    /**
     * Basic rate limiting: same IP + expo submitting again within the window.
     * The cutoff is computed by MySQL itself (NOW() - INTERVAL ...) rather than
     * in PHP, so a PHP/MySQL clock or timezone mismatch can't produce false positives.
     */
    public static function recentlySubmitted(string $ipAddress, int $expoId, int $withinSeconds = 10): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id FROM expo_submissions
             WHERE ip_address = :ip AND expo_id = :expo_id
               AND submitted_at > (NOW() - INTERVAL :seconds SECOND)
             LIMIT 1'
        );
        $stmt->bindValue('ip', $ipAddress, PDO::PARAM_STR);
        $stmt->bindValue('expo_id', $expoId, PDO::PARAM_INT);
        $stmt->bindValue('seconds', $withinSeconds, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    /**
     * @param array $data full_name, phone, project_location, follow_up_method, email, message, ip_address
     * @param int[] $interestIds already validated against active interests
     */
    public static function create(int $expoId, array $data, array $interestIds, ?string $otherText): int
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $isDuplicate = Duplicate::isPossible($data['phone'], $expoId);

            $stmt = $pdo->prepare(
                'INSERT INTO expo_submissions
                    (expo_id, full_name, phone, project_location, follow_up_method, email, message, is_possible_duplicate, ip_address)
                 VALUES
                    (:expo_id, :full_name, :phone, :project_location, :follow_up_method, :email, :message, :is_possible_duplicate, :ip_address)'
            );
            $stmt->execute([
                'expo_id'               => $expoId,
                'full_name'             => $data['full_name'],
                'phone'                 => $data['phone'],
                'project_location'      => $data['project_location'],
                'follow_up_method'      => $data['follow_up_method'],
                'email'                 => $data['email'] !== '' ? $data['email'] : null,
                'message'               => $data['message'] !== '' ? $data['message'] : null,
                'is_possible_duplicate' => $isDuplicate ? 1 : 0,
                'ip_address'            => $data['ip_address'],
            ]);

            $submissionId = (int) $pdo->lastInsertId();

            if (!empty($interestIds)) {
                $placeholders = implode(',', array_fill(0, count($interestIds), '?'));
                $namesStmt = $pdo->prepare("SELECT id, name FROM interests WHERE id IN ({$placeholders})");
                $namesStmt->execute($interestIds);
                $names = $namesStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $interestStmt = $pdo->prepare(
                    'INSERT INTO submission_interests (submission_id, interest_id, other_text)
                     VALUES (:submission_id, :interest_id, :other_text)'
                );

                foreach ($interestIds as $interestId) {
                    $interestStmt->execute([
                        'submission_id' => $submissionId,
                        'interest_id'   => $interestId,
                        'other_text'    => (($names[$interestId] ?? '') === 'Other') ? $otherText : null,
                    ]);
                }
            }

            $pdo->commit();

            return $submissionId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** List rows for the admin table, optionally scoped to one expo and/or flagged-only. Interests are comma-joined for the summary column. */
    public static function allWithExpo(?int $expoId = null, bool $duplicatesOnly = false): array
    {
        $sql = "SELECT
                    s.id, s.full_name, s.phone, s.project_location, s.follow_up_method,
                    s.email, s.message, s.is_possible_duplicate, s.submitted_at,
                    e.id AS expo_id, e.name AS expo_name,
                    GROUP_CONCAT(DISTINCT i.name ORDER BY i.sort_order SEPARATOR ', ') AS interests_summary
                FROM expo_submissions s
                JOIN expos e ON e.id = s.expo_id
                LEFT JOIN submission_interests si ON si.submission_id = s.id
                LEFT JOIN interests i ON i.id = si.interest_id";
        $conditions = [];
        $params = [];

        if ($expoId !== null) {
            $conditions[] = 's.expo_id = :expo_id';
            $params['expo_id'] = $expoId;
        }

        if ($duplicatesOnly) {
            $conditions[] = 's.is_possible_duplicate = 1';
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY s.id ORDER BY s.submitted_at DESC';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT s.*, e.name AS expo_name, e.slug AS expo_slug
             FROM expo_submissions s
             JOIN expos e ON e.id = s.expo_id
             WHERE s.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** Admin-editable: add or correct the message on an existing submission. */
    public static function updateMessage(int $id, ?string $message): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE expo_submissions SET message = :message WHERE id = :id'
        );
        $stmt->execute(['message' => $message, 'id' => $id]);
    }

    /**
     * Other submissions sharing the same phone + expo, so an admin reviewing
     * a flagged submission can jump straight to what it's a possible duplicate
     * of — this works in both directions: viewing the *first* submission also
     * surfaces a later one that got flagged against it, even though the first
     * one's own is_possible_duplicate is 0.
     */
    public static function siblingsByPhone(int $submissionId, string $phone, int $expoId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id, full_name, submitted_at, is_possible_duplicate
             FROM expo_submissions
             WHERE phone = :phone AND expo_id = :expo_id AND id != :id
             ORDER BY submitted_at'
        );
        $stmt->execute(['phone' => $phone, 'expo_id' => $expoId, 'id' => $submissionId]);

        return $stmt->fetchAll();
    }

    /** Interests attached to one submission, each with other_text where it applies. */
    public static function interestsFor(int $submissionId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT i.name, si.other_text
             FROM submission_interests si
             JOIN interests i ON i.id = si.interest_id
             WHERE si.submission_id = :submission_id
             ORDER BY i.sort_order'
        );
        $stmt->execute(['submission_id' => $submissionId]);

        return $stmt->fetchAll();
    }
}
