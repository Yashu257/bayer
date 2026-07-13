<?php

declare(strict_types=1);

namespace App\Services;

use Core\Database\Database;

class FeedbackService
{
    public function store(array $data): int
    {
        return Database::insert(
            'INSERT INTO feedback (event_id, registration_id, attendee_id, rating, comment,
                                   nps_score, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['event_id']       ?? null,
                $data['registration_id'] ?? null,
                $data['attendee_id']    ?? null,
                $data['rating']         ?? null,
                $data['comment']        ?? '',
                $data['nps_score']      ?? null,
            ]
        );
    }

    public function list(int $eventId, array $filters = [], int $page = 1, int $perPage = 30): array
    {
        $where  = ['f.event_id = ?'];
        $params = [$eventId];

        if (!empty($filters['rating'])) {
            $where[]  = 'f.rating = ?';
            $params[] = (int) $filters['rating'];
        }
        if (!empty($filters['flagged'])) {
            $where[]  = 'f.flagged = 1';
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        $offset      = ($page - 1) * $perPage;

        $total = (int) (Database::queryOne(
            "SELECT COUNT(*) AS n FROM feedback f $whereClause", $params
        )['n'] ?? 0);

        $rows = Database::query(
            "SELECT f.*, r.first_name, r.last_name, r.email
               FROM feedback f
               LEFT JOIN registrations r ON r.id = f.registration_id
              $whereClause
              ORDER BY f.created_at DESC
              LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return ['total' => $total, 'rows' => $rows,
                'pages' => max(1, (int) ceil($total / $perPage)), 'page' => $page];
    }

    public function flag(int $id): bool
    {
        return Database::execute(
            'UPDATE feedback SET flagged = 1, updated_at = NOW() WHERE id = ?', [$id]
        ) > 0;
    }

    public function hide(int $id): bool
    {
        return Database::execute(
            'UPDATE feedback SET hidden = 1, updated_at = NOW() WHERE id = ?', [$id]
        ) > 0;
    }

    public function exportCsv(int $eventId): string
    {
        $rows = Database::query(
            'SELECT f.id, r.first_name, r.last_name, r.email,
                    f.rating, f.nps_score, f.comment, f.created_at
               FROM feedback f
               LEFT JOIN registrations r ON r.id = f.registration_id
              WHERE f.event_id = ?
              ORDER BY f.created_at ASC',
            [$eventId]
        );

        $out = "ID,First Name,Last Name,Email,Rating,NPS Score,Comment,Submitted At\n";
        foreach ($rows as $r) {
            $out .= implode(',', [
                $r['id'],
                '"' . str_replace('"', '""', $r['first_name'] ?? '') . '"',
                '"' . str_replace('"', '""', $r['last_name']  ?? '') . '"',
                '"' . str_replace('"', '""', $r['email']      ?? '') . '"',
                $r['rating']    ?? '',
                $r['nps_score'] ?? '',
                '"' . str_replace('"', '""', $r['comment'] ?? '') . '"',
                $r['created_at'] ?? '',
            ]) . "\n";
        }

        return $out;
    }
}
