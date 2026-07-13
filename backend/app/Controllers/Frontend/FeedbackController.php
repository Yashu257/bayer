<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Services\FeedbackService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class FeedbackController extends BaseController
{
    private readonly FeedbackService $feedbackService;

    public function __construct()
    {
        $this->feedbackService = new FeedbackService();
    }

    public function show(Request $request): Response
    {
        $eventId        = (int) $request->param('eventId');
        $attendeeId     = $request->query('attendee_id', '');

        return $this->view('frontend.feedback.show', [
            'eventId'    => $eventId,
            'attendeeId' => $attendeeId,
            'pageTitle'  => 'Session Feedback',
        ]);
    }

    public function submit(Request $request): Response
    {
        $user    = Session::get('auth_user');
        $eventId = (int) $request->param('eventId');

        $registration = \Core\Database\Database::queryOne(
            'SELECT id FROM registrations WHERE event_id = ? AND user_id = ?',
            [$eventId, $user['id']]
        );

        $this->feedbackService->store([
            'event_id'        => $eventId,
            'registration_id' => $registration['id'] ?? null,
            'attendee_id'     => $request->input('attendee_id'),
            'rating'          => $request->input('rating'),
            'comment'         => $request->input('comment'),
            'nps_score'       => $request->input('nps_score'),
        ]);

        Session::flash('success', 'Thank you for your feedback!');
        return Response::redirect('/events/' . $eventId . '/feedback/thankyou');
    }
}
