<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class SurveyController extends BaseController
{
    public function show(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $user    = Session::get('auth_user');

        $survey = Database::queryOne(
            'SELECT * FROM surveys WHERE event_id = ? AND status = \'active\' LIMIT 1',
            [$eventId]
        );

        if (!$survey) {
            return Response::redirect('/events/' . $eventId);
        }

        $alreadySubmitted = Database::queryOne(
            'SELECT id FROM survey_responses WHERE survey_id = ? AND user_id = ?',
            [$survey['id'], $user['id']]
        );

        if ($alreadySubmitted) {
            return Response::redirect('/events/' . $eventId . '/survey/thankyou');
        }

        $questions = Database::query(
            'SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY sort_order ASC',
            [$survey['id']]
        );

        return $this->view('frontend.survey.show', [
            'survey'    => $survey,
            'questions' => $questions,
            'pageTitle' => 'Post-Event Survey',
        ]);
    }

    public function submit(Request $request): Response
    {
        $user     = Session::get('auth_user');
        $eventId  = (int) $request->param('eventId');
        $surveyId = (int) $request->input('survey_id');
        $answers  = $request->input('answers', []);

        $responseId = Database::insert(
            'INSERT INTO survey_responses (survey_id, user_id, submitted_at) VALUES (?,?,NOW())',
            [$surveyId, $user['id']]
        );

        foreach ((array) $answers as $questionId => $answer) {
            Database::insert(
                'INSERT INTO survey_answers (response_id, question_id, answer, created_at) VALUES (?,?,?,NOW())',
                [$responseId, (int) $questionId, (string) $answer]
            );
        }

        return Response::redirect('/events/' . $eventId . '/survey/thankyou');
    }

    public function thankyou(Request $request): Response
    {
        return $this->view('frontend.survey.thankyou', [
            'pageTitle' => 'Thank You',
        ]);
    }
}
