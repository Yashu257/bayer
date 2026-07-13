<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Core\Database\Database;

class SettingsService
{
    public function getAll(?int $eventId = null): array
    {
        $rows = Database::query(
            'SELECT `key`, `value`, `type`, `group` FROM settings WHERE event_id ' . ($eventId ? '= ?' : 'IS NULL') . ' ORDER BY `group`, `key`',
            $eventId ? [$eventId] : []
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['group']][$row['key']] = [
                'value' => $row['value'],
                'type'  => $row['type'],
            ];
        }

        return $grouped;
    }

    public function save(array $data, ?int $eventId = null): void
    {
        foreach ($data as $key => $value) {
            Database::execute(
                'INSERT INTO settings (`key`, `value`, event_id, updated_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = NOW()',
                [$key, $value, $eventId]
            );
        }
    }
}
