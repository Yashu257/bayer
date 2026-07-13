<?php

declare(strict_types=1);

namespace App\Controllers\Api\Admin;

use App\Services\WebcastService;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;

class WebcastController
{
    private readonly WebcastService $webcastService;

    public function __construct()
    {
        $this->webcastService = new WebcastService();
    }

    public function liveStats(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');

        $stats = [
            'live_viewers'   => (int)(Database::queryOne(
                'SELECT COUNT(*) AS n FROM attendance_logs WHERE event_id=? AND TIMESTAMPDIFF(SECOND,last_heartbeat,NOW())<120',
                [$eventId]
            )['n'] ?? 0),
            'total_joins'    => (int)(Database::queryOne(
                'SELECT COUNT(*) AS n FROM attendance_logs WHERE event_id=?', [$eventId]
            )['n'] ?? 0),
            'pending_questions' => (int)(Database::queryOne(
                "SELECT COUNT(*) AS n FROM questions WHERE event_id=? AND status='pending'", [$eventId]
            )['n'] ?? 0),
        ];

        return Response::json(['stats' => $stats]);
    }

    public function goLive(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $this->webcastService->goLive($eventId);
        return Response::json(['status' => 'live']);
    }

    public function end(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $this->webcastService->end($eventId);
        return Response::json(['status' => 'ended']);
    }
}
