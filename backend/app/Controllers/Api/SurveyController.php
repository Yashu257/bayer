<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class SurveyController
{
    public function show(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $survey  = Database::queryOne(
            "SELECT * FROM surveys WHERE event_id = ? AND status = 'active' LIMIT 1", [$eventId]
        );

        if (!$survey) {
            return Response::json(['survey' => null]);
        }

        $survey['questions'] = Database::query(
            'SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY sort_order', [$survey['id']]
        );

        return Response::json(['survey' => $survey]);
    }

    public function submit(Request $request): Response
    {
        $user     = Session::get('auth_user');
        $eventId  = (int) $request->param('eventId');
        $body     = $request->isJson() ? $request->json() : $request->all();
        $surveyId = (int)($body['survey_id'] ?? 0);
        $answers  = (array)($body['answers'] ?? []);

        $exists = Database::queryOne(
            'SELECT id FROM survey_responses WHERE survey_id = ? AND user_id = ?', [$surveyId, $user['id']]
        );

        if ($exists) {
            return Response::json(['error' => 'Already submitted.'], 409);
        }

        $responseId = Database::insert(
            'INSERT INTO survey_responses (survey_id, user_id, submitted_at) VALUES (?,?,NOW())',
            [$surveyId, $user['id']]
        );

        foreach ($answers as $questionId => $answer) {
            Database::insert(
                'INSERT INTO survey_answers (response_id, question_id, answer, created_at) VALUES (?,?,?,NOW())',
                [$responseId, (int)$questionId, (string)$answer]
            );
        }

        return Response::json(['submitted' => true], 201);
    }
}
