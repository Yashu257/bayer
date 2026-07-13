<?php

declare(strict_types=1);

namespace App\Controllers\Api\Admin;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;

class ReportController
{
    public function generate(Request $request): Response
    {
        $body    = $request->isJson() ? $request->json() : $request->all();
        $eventId = (int)($body['event_id'] ?? 0);
        $type    = (string)($body['type']    ?? 'registrations');

        $id = Database::insert(
            'INSERT INTO reports (event_id, type, status, created_at) VALUES (?,?,\'pending\',NOW())',
            [$eventId, $type]
        );

        return Response::json(['report_id' => $id, 'status' => 'pending'], 202);
    }

    public function status(Request $request): Response
    {
        $report = Database::queryOne(
            'SELECT id, status, file_path, created_at, updated_at FROM reports WHERE id = ?',
            [(int) $request->param('id')]
        );

        if (!$report) {
            return Response::json(['error' => 'Not found.'], 404);
        }

        return Response::json(['report' => $report]);
    }
}
