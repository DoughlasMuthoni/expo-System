<?php

declare(strict_types=1);

/**
 * Read access to the interests lookup table. Adding an option is a data
 * change (see CLAUDE.md Database Rules) — this class never hardcodes options.
 */
class Interest
{
    public static function active(): array
    {
        return Database::getConnection()
            ->query('SELECT * FROM interests WHERE is_active = 1 ORDER BY sort_order, name')
            ->fetchAll();
    }
}
