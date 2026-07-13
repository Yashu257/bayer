<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Core\Database\Database;

class RegistrationReportService
{
    public function summaryByEvent(): array
    {
        return Database::query(
            'SELECT e.id, e.title, e.slug, e.status,
                    COUNT(r.id)                                          AS total,
                    SUM(r.approval_status = "approved")                  AS approved,
                    SUM(r.approval_status = "pending")                   AS pending,
                    SUM(r.approval_status = "rejected")                  AS rejected,
                    COUNT(DISTINCT al.registration_id)                   AS attended
               FROM events e
               LEFT JOIN registrations r  ON r.event_id = e.id AND r.deleted_at IS NULL
               LEFT JOIN attendance_logs al ON al.event_id = e.id
              WHERE e.deleted_at IS NULL
              GROUP BY e.id
              ORDER BY e.created_at DESC'
        );
    }

    public function listForEvent(int $eventId, string $search = '', string $status = '', int $page = 1, int $perPage = 30): array
    {
        $where  = ['r.event_id = ?', 'r.deleted_at IS NULL'];
        $params = [$eventId];

        if ($search !== '') {
            $where[]  = '(r.first_name LIKE ? OR r.last_name LIKE ? OR r.email LIKE ? OR r.company LIKE ? OR r.attendee_id LIKE ?)';
            $like     = '%' . $search . '%';
            $params   = array_merge($params, [$like, $like, $like, $like, $like]);
        }

        if ($status !== '') {
            $where[]  = 'r.approval_status = ?';
            $params[] = $status;
        }

        $sql = 'SELECT r.id, r.attendee_id, r.first_name, r.last_name, r.email,
                       r.company, r.designation, r.city, r.country,
                       r.approval_status, r.created_at,
                       al.joined_at, al.watch_seconds
                  FROM registrations r
                  LEFT JOIN attendance_logs al ON al.registration_id = r.id AND al.event_id = r.event_id
                 WHERE ' . implode(' AND ', $where) . '
                 ORDER BY r.created_at DESC
                 LIMIT ? OFFSET ?';

        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $countSql = 'SELECT COUNT(*) AS n FROM registrations r WHERE ' . implode(' AND ', $where);
        $total    = (int) (Database::queryOne($countSql, array_slice($params, 0, -2))['n'] ?? 0);

        return [
            'rows'  => Database::query($sql, $params),
            'total' => $total,
            'page'  => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function approve(int $registrationId): void
    {
        Database::execute(
            'UPDATE registrations SET approval_status = "approved", updated_at = NOW() WHERE id = ?',
            [$registrationId]
        );
    }

    public function reject(int $registrationId): void
    {
        Database::execute(
            'UPDATE registrations SET approval_status = "rejected", updated_at = NOW() WHERE id = ?',
            [$registrationId]
        );
    }
}
