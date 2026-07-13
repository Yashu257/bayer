<?php

declare(strict_types=1);

namespace App\Services;

use Core\Database\Database;

class SpeakerService
{
    public function all(): array
    {
        return Database::query(
            'SELECT * FROM speakers WHERE deleted_at IS NULL ORDER BY name ASC'
        );
    }

    public function find(int $id): ?array
    {
        return Database::queryOne(
            'SELECT * FROM speakers WHERE id = ? AND deleted_at IS NULL', [$id]
        );
    }

    public function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO speakers (name, title, bio, photo_url, linkedin_url, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [
                $data['name']         ?? '',
                $data['title']        ?? '',
                $data['bio']          ?? '',
                $data['photo_url']    ?? null,
                $data['linkedin_url'] ?? null,
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        return Database::execute(
            'UPDATE speakers SET name=?, title=?, bio=?, photo_url=?, linkedin_url=?, updated_at=NOW()
              WHERE id = ?',
            [
                $data['name']         ?? '',
                $data['title']        ?? '',
                $data['bio']          ?? '',
                $data['photo_url']    ?? null,
                $data['linkedin_url'] ?? null,
                $id,
            ]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::execute(
            'UPDATE speakers SET deleted_at = NOW() WHERE id = ?', [$id]
        ) > 0;
    }

    public function forEvent(int $eventId): array
    {
        return Database::query(
            'SELECT s.*, es.sort_order, es.role
               FROM speakers s
               JOIN event_speakers es ON es.speaker_id = s.id
              WHERE es.event_id = ?
              ORDER BY es.sort_order ASC',
            [$eventId]
        );
    }

    public function attach(int $eventId, int $speakerId, array $data = []): int
    {
        return Database::insert(
            'INSERT INTO event_speakers (event_id, speaker_id, role, sort_order, created_at)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE role=VALUES(role), sort_order=VALUES(sort_order)',
            [
                $eventId,
                $speakerId,
                $data['role']       ?? 'speaker',
                $data['sort_order'] ?? 0,
            ]
        );
    }

    public function detach(int $eventId, int $speakerId): bool
    {
        return Database::execute(
            'DELETE FROM event_speakers WHERE event_id = ? AND speaker_id = ?',
            [$eventId, $speakerId]
        ) > 0;
    }
}
