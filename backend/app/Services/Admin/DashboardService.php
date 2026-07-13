<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Core\Database\Database;

/**
 * DashboardService — aggregates all stats needed for the admin overview page.
 * No HTTP. No HTML. Only DB reads.
 */
class DashboardService
{
    /** Top-level KPI cards. */
    public function getKpis(): array
    {
        return [
            'total_registrations' => (int) (Database::queryOne(
                'SELECT COUNT(*) AS n FROM registrations WHERE deleted_at IS NULL'
            )['n'] ?? 0),

            'approved' => (int) (Database::queryOne(
                'SELECT COUNT(*) AS n FROM registrations WHERE approval_status = "approved" AND deleted_at IS NULL'
            )['n'] ?? 0),

            'pending' => (int) (Database::queryOne(
                'SELECT COUNT(*) AS n FROM registrations WHERE approval_status = "pending" AND deleted_at IS NULL'
            )['n'] ?? 0),

            'live_viewers' => (int) (Database::queryOne(
                'SELECT COUNT(*) AS n FROM attendance_logs WHERE last_ping_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)'
            )['n'] ?? 0),

            'total_questions' => (int) (Database::queryOne(
                'SELECT COUNT(*) AS n FROM questions WHERE deleted_at IS NULL'
            )['n'] ?? 0),

            'avg_rating' => round((float) (Database::queryOne(
                'SELECT AVG(overall_rating) AS r FROM feedback WHERE deleted_at IS NULL'
            )['r'] ?? 0), 1),

            'total_events' => (int) (Database::queryOne(
                'SELECT COUNT(*) AS n FROM events WHERE deleted_at IS NULL'
            )['n'] ?? 0),

            'active_events' => (int) (Database::queryOne(
                'SELECT COUNT(*) AS n FROM events WHERE status = "live" AND deleted_at IS NULL'
            )['n'] ?? 0),
        ];
    }

    /** Daily registration counts for the last 14 days (line chart). */
    public function getRegistrationTrend(int $days = 14): array
    {
        $rows = Database::query(
            'SELECT DATE(created_at) AS day, COUNT(*) AS total
               FROM registrations
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND deleted_at IS NULL
              GROUP BY DATE(created_at)
              ORDER BY day ASC',
            [$days]
        );

        $map = [];
        foreach ($rows as $r) {
            $map[$r['day']] = (int) $r['total'];
        }

        $labels = [];
        $data   = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d        = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($d));
            $data[]   = $map[$d] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /** Peak viewer count per event (bar chart). */
    public function getAttendanceByEvent(int $limit = 8): array
    {
        $rows = Database::query(
            'SELECT e.title, COUNT(al.id) AS viewers
               FROM events e
               LEFT JOIN attendance_logs al ON al.event_id = e.id
              WHERE e.deleted_at IS NULL
              GROUP BY e.id, e.title
              ORDER BY viewers DESC
              LIMIT ?',
            [$limit]
        );

        return [
            'labels' => array_map(fn($r) => mb_strimwidth($r['title'], 0, 22, '…'), $rows),
            'data'   => array_map(fn($r) => (int) $r['viewers'], $rows),
        ];
    }

    /** Registration status breakdown (doughnut). */
    public function getStatusBreakdown(): array
    {
        $rows = Database::query(
            'SELECT approval_status, COUNT(*) AS n
               FROM registrations
              WHERE deleted_at IS NULL
              GROUP BY approval_status'
        );

        $map = [];
        foreach ($rows as $r) {
            $map[$r['approval_status']] = (int) $r['n'];
        }

        return [
            'labels' => ['Approved', 'Pending', 'Rejected', 'Other'],
            'data'   => [
                $map['approved'] ?? 0,
                $map['pending']  ?? 0,
                $map['rejected'] ?? 0,
                $map['other']    ?? 0,
            ],
        ];
    }

    /** Feedback rating distribution 1–5 (bar chart). */
    public function getFeedbackDistribution(): array
    {
        $rows = Database::query(
            'SELECT overall_rating, COUNT(*) AS n
               FROM feedback
              WHERE deleted_at IS NULL AND overall_rating BETWEEN 1 AND 5
              GROUP BY overall_rating
              ORDER BY overall_rating ASC'
        );

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['overall_rating']] = (int) $r['n'];
        }

        return [
            'labels' => ['★ 1', '★★ 2', '★★★ 3', '★★★★ 4', '★★★★★ 5'],
            'data'   => [$map[1]??0, $map[2]??0, $map[3]??0, $map[4]??0, $map[5]??0],
        ];
    }

    /** Latest 8 registrations for the activity feed. */
    public function getRecentRegistrations(int $limit = 8): array
    {
        return Database::query(
            'SELECT r.first_name, r.last_name, r.email, r.company, r.attendee_id,
                    r.approval_status, r.created_at, e.title AS event_title
               FROM registrations r
               JOIN events e ON e.id = r.event_id
              WHERE r.deleted_at IS NULL
              ORDER BY r.created_at DESC
              LIMIT ?',
            [$limit]
        );
    }

    /** Build the full chart payload for window.ADMIN_CHARTS. */
    public function buildChartData(): array
    {
        return [
            'registrations' => $this->getRegistrationTrend(),
            'attendance'    => $this->getAttendanceByEvent(),
            'status'        => $this->getStatusBreakdown(),
            'feedback'      => $this->getFeedbackDistribution(),
        ];
    }
}
