<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Event;
use Core\Database\Database;

class EventRepository
{
    public function findBySlug(string $slug): ?Event
    {
        $row = Database::queryOne(
            "SELECT * FROM events WHERE slug = ? AND deleted_at IS NULL LIMIT 1",
            [$slug]
        );
        return $row ? new Event($row) : null;
    }

    public function findById(int $id): ?Event
    {
        $row = Database::queryOne(
            "SELECT * FROM events WHERE id = ? AND deleted_at IS NULL LIMIT 1",
            [$id]
        );
        return $row ? new Event($row) : null;
    }

    /** All published/live events ordered by start date. */
    public function allPublished(): array
    {
        $rows = Database::query(
            "SELECT * FROM events
             WHERE status IN ('published','live')
               AND deleted_at IS NULL
             ORDER BY starts_at ASC"
        );
        return array_map(fn($r) => new Event($r), $rows);
    }

    // ── Admin CRUD ────────────────────────────────────────────────────────────

    public function paginatedList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(title LIKE ? OR slug LIKE ?)';
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }

        $clause = 'WHERE ' . implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $total = (int) (Database::queryOne(
            "SELECT COUNT(*) AS n FROM events $clause", $params
        )['n'] ?? 0);

        $rows = Database::query(
            "SELECT * FROM events $clause ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'rows'  => array_map(fn($r) => new Event($r), $rows),
            'total' => $total,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'page'  => $page,
        ];
    }

    public function create(array $data): int
    {
        $cols   = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        return Database::insert(
            "INSERT INTO events ($cols) VALUES ($placeholders)",
            array_values($data)
        );
    }

    public function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        return Database::execute(
            "UPDATE events SET $sets WHERE id = ? AND deleted_at IS NULL",
            array_merge(array_values($data), [$id])
        ) > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Database::execute(
            'UPDATE events SET status = ?, updated_at = NOW() WHERE id = ? AND deleted_at IS NULL',
            [$status, $id]
        ) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::execute(
            'UPDATE events SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL',
            [$id]
        ) > 0;
    }

    public function clone(int $sourceId): int
    {
        $row = Database::queryOne(
            'SELECT * FROM events WHERE id = ? AND deleted_at IS NULL', [$sourceId]
        );
        if (!$row) {
            throw new \RuntimeException("Event $sourceId not found for cloning.");
        }

        unset($row['id'], $row['created_at'], $row['updated_at'], $row['deleted_at']);
        $row['title']      = $row['title'] . ' (Copy)';
        $row['slug']       = $row['slug'] . '-copy-' . time();
        $row['status']     = 'draft';
        $row['created_at'] = date('Y-m-d H:i:s');

        $cols         = implode(', ', array_keys($row));
        $placeholders = implode(', ', array_fill(0, count($row), '?'));
        return Database::insert(
            "INSERT INTO events ($cols) VALUES ($placeholders)",
            array_values($row)
        );
    }
}
