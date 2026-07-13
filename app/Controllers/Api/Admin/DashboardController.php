<?php

declare(strict_types=1);

namespace App\Controllers\Api\Admin;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;

class DashboardController
{
    public function stats(Request $request): Response
    {
        $stats = [
            'total_registrations' => (int)(Database::queryOne('SELECT COUNT(*) AS n FROM registrations')['n'] ?? 0),
            'approved'            => (int)(Database::queryOne("SELECT COUNT(*) AS n FROM registrations WHERE approval_status='approved'")['n'] ?? 0),
            'pending'             => (int)(Database::queryOne("SELECT COUNT(*) AS n FROM registrations WHERE approval_status='pending'")['n'] ?? 0),
            'live_viewers'        => (int)(Database::queryOne('SELECT COUNT(*) AS n FROM attendance_logs WHERE TIMESTAMPDIFF(SECOND, last_heartbeat, NOW()) < 120')['n'] ?? 0),
        ];

        return Response::json(['stats' => $stats]);
    }

    public function activity(Request $request): Response
    {
        $logs = Database::query(
            'SELECT action, message, created_at FROM activity_logs ORDER BY id DESC LIMIT 20'
        );

        return Response::json(['activity' => $logs]);
    }
}
