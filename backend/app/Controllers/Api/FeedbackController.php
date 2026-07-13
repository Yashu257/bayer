<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\FeedbackService;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class FeedbackController
{
    private readonly FeedbackService $feedbackService;

    public function __construct()
    {
        $this->feedbackService = new FeedbackService();
    }

    public function store(Request $request): Response
    {
        $user    = Session::get('auth_user');
        $eventId = (int) $request->param('eventId');
        $body    = $request->isJson() ? $request->json() : $request->all();

        $registration = Database::queryOne(
            'SELECT id FROM registrations WHERE event_id = ? AND user_id = ?', [$eventId, $user['id']]
        );

        $id = $this->feedbackService->store([
            'event_id'        => $eventId,
            'registration_id' => $registration['id'] ?? null,
            'attendee_id'     => $body['attendee_id'] ?? null,
            'rating'          => $body['rating']      ?? null,
            'comment'         => $body['comment']     ?? '',
            'nps_score'       => $body['nps_score']   ?? null,
        ]);

        return Response::json(['id' => $id], 201);
    }
}
