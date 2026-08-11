<?php

declare(strict_types=1);

/**
 * Data access for the expos table. One expo = one slug = one QR code =
 * one isolated set of submissions (CLAUDE.md Core Business Rules).
 */
class Expo
{
    public static function all(): array
    {
        return Database::getConnection()
            ->query('SELECT * FROM expos ORDER BY created_at DESC')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM expos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM expos WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM expos WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch();
    }

    /** Appends -2, -3, ... until the slug is free. */
    public static function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while (self::slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO expos (name, slug, location, start_date, end_date, is_active)
             VALUES (:name, :slug, :location, :start_date, :end_date, :is_active)'
        );
        $stmt->execute(self::bindParams($data));

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $params = self::bindParams($data);
        $params['id'] = $id;

        $stmt = Database::getConnection()->prepare(
            'UPDATE expos SET name = :name, slug = :slug, location = :location,
             start_date = :start_date, end_date = :end_date, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function setActive(int $id, bool $active): void
    {
        $stmt = Database::getConnection()->prepare('UPDATE expos SET is_active = :active WHERE id = :id');
        $stmt->execute(['active' => $active ? 1 : 0, 'id' => $id]);
    }

    private static function bindParams(array $data): array
    {
        return [
            'name'       => $data['name'],
            'slug'       => $data['slug'],
            'location'   => $data['location'] !== '' ? $data['location'] : null,
            'start_date' => $data['start_date'] !== '' ? $data['start_date'] : null,
            'end_date'   => $data['end_date'] !== '' ? $data['end_date'] : null,
            'is_active'  => !empty($data['is_active']) ? 1 : 0,
        ];
    }
}
