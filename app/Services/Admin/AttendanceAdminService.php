<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Core\Database\Database;

class AttendanceAdminService
{
    public function summaryForEvent(int $eventId): array
    {
        $total     = (int)(Database::queryOne('SELECT COUNT(*) AS n FROM attendance_logs WHERE event_id = ?', [$eventId])['n'] ?? 0);
        $avgWatch  = (int)(Database::queryOne('SELECT AVG(watch_seconds) AS s FROM attendance_logs WHERE event_id = ?', [$eventId])['s'] ?? 0);
        $peakTime  = Database::queryOne(
            'SELECT last_ping_at FROM attendance_logs WHERE event_id = ? ORDER BY watch_seconds DESC LIMIT 1',
            [$eventId]
        );

        return [
            'total_attended' => $total,
            'avg_watch_mins' => round($avgWatch / 60, 1),
            'peak_viewer_time' => $peakTime['last_ping_at'] ?? null,
        ];
    }

    public function listForEvent(int $eventId, int $page = 1, int $perPage = 30): array
    {
        $rows = Database::query(
            'SELECT r.attendee_id, r.first_name, r.last_name, r.email, r.company,
                    al.joined_at, al.last_ping_at,
                    ROUND(al.watch_seconds / 60, 1) AS watch_minutes
               FROM attendance_logs al
               JOIN registrations r ON r.id = al.registration_id
              WHERE al.event_id = ?
              ORDER BY al.watch_seconds DESC
              LIMIT ? OFFSET ?',
            [$eventId, $perPage, ($page - 1) * $perPage]
        );

        $total = (int)(Database::queryOne(
            'SELECT COUNT(*) AS n FROM attendance_logs WHERE event_id = ?', [$eventId]
        )['n'] ?? 0);

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => max(1, (int)ceil($total / $perPage))];
    }

    public function liveCount(int $eventId): int
    {
        return (int)(Database::queryOne(
            'SELECT COUNT(*) AS n FROM attendance_logs
              WHERE event_id = ? AND last_ping_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)',
            [$eventId]
        )['n'] ?? 0);
    }
}
