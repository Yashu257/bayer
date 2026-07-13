<?php

declare(strict_types=1);

namespace App\Controllers\Api\Admin;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;

class AttendanceController
{
    public function live(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');

        $rows = Database::query(
            'SELECT al.user_id, u.first_name, u.last_name, u.email,
                    al.joined_at, al.last_heartbeat, al.watch_seconds,
                    TIMESTAMPDIFF(SECOND, al.last_heartbeat, NOW()) AS seconds_ago
               FROM attendance_logs al
               JOIN users u ON u.id = al.user_id
              WHERE al.event_id = ?
              ORDER BY al.last_heartbeat DESC',
            [$eventId]
        );

        $liveCount = count(array_filter($rows, fn($r) => $r['seconds_ago'] < 120));

        return Response::json(['attendees' => $rows, 'live_count' => $liveCount]);
    }
}
