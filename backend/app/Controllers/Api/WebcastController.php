<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\WebcastService;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class WebcastController
{
    private readonly WebcastService $webcastService;

    public function __construct()
    {
        $this->webcastService = new WebcastService();
    }

    public function show(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $webcast = $this->webcastService->getForEvent($eventId);

        if (!$webcast) {
            return Response::json(['error' => 'Webcast not found.'], 404);
        }

        return Response::json(['webcast' => $webcast]);
    }

    public function streamToken(Request $request): Response
    {
        $user    = Session::get('auth_user');
        $eventId = (int) $request->param('eventId');

        $registration = Database::queryOne(
            'SELECT id FROM registrations WHERE event_id = ? AND user_id = ? AND approval_status = \'approved\'',
            [$eventId, $user['id']]
        );

        if (!$registration) {
            return Response::json(['error' => 'Not registered or not approved.'], 403);
        }

        $token = bin2hex(random_bytes(32));
        return Response::json(['token' => $token, 'expires_in' => 3600]);
    }

    public function heartbeat(Request $request): Response
    {
        $user    = Session::get('auth_user');
        $eventId = (int) $request->param('eventId');

        Database::execute(
            'INSERT INTO attendance_logs (event_id, user_id, last_heartbeat, updated_at)
             VALUES (?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE last_heartbeat = NOW(), updated_at = NOW()',
            [$eventId, $user['id']]
        );

        return Response::json(['ok' => true]);
    }
}
