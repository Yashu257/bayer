<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EventRepository;
use Core\Database\Database;
use Core\Logger\Logger;

class EventService
{
    private readonly EventRepository $repo;

    public function __construct()
    {
        $this->repo = new EventRepository();
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->repo->paginatedList($filters, $page, $perPage);
    }

    public function findById(int $id): ?array
    {
        $event = $this->repo->findById($id);
        return $event?->toArray();
    }

    public function create(array $data): int
    {
        $data['slug']       = $this->uniqueSlug($data['title']);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): bool
    {
        if (isset($data['title'])) {
            $event = $this->repo->findById($id);
            if ($event && $event->title !== $data['title']) {
                $data['slug'] = $this->uniqueSlug($data['title'], $id);
            }
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->repo->update($id, $data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['draft', 'published', 'live', 'ended', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        return $this->repo->updateStatus($id, $status);
    }

    public function delete(int $id): bool
    {
        return $this->repo->softDelete($id);
    }

    public function clone(int $id): int
    {
        return $this->repo->clone($id);
    }

    private function uniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $base = trim($base, '-');
        $slug = $base;
        $i    = 1;

        while (true) {
            $sql     = 'SELECT id FROM events WHERE slug = ? AND deleted_at IS NULL';
            $params  = [$slug];
            if ($excludeId !== null) {
                $sql    .= ' AND id != ?';
                $params[] = $excludeId;
            }
            $exists = Database::queryOne($sql, $params);
            if (!$exists) {
                break;
            }
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
