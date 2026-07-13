<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class QuizController
{
    public function show(Request $request): Response
    {
        $quizId = (int) $request->param('id');
        $quiz   = Database::queryOne('SELECT * FROM quizzes WHERE id = ? AND deleted_at IS NULL', [$quizId]);

        if (!$quiz) {
            return Response::json(['error' => 'Not found.'], 404);
        }

        $quiz['questions'] = Database::query(
            'SELECT q.id, q.question, q.sort_order,
                    JSON_ARRAYAGG(JSON_OBJECT(\'id\', o.id, \'text\', o.option_text)) AS options
               FROM quiz_questions q
               LEFT JOIN quiz_options o ON o.question_id = q.id
              WHERE q.quiz_id = ?
              GROUP BY q.id
              ORDER BY q.sort_order',
            [$quizId]
        );

        return Response::json(['quiz' => $quiz]);
    }

    public function startAttempt(Request $request): Response
    {
        $user   = Session::get('auth_user');
        $quizId = (int) $request->param('id');

        $existing = Database::queryOne(
            'SELECT id FROM quiz_attempts WHERE quiz_id=? AND user_id=? AND status=\'in_progress\'',
            [$quizId, $user['id']]
        );

        if ($existing) {
            return Response::json(['attempt_id' => $existing['id']]);
        }

        $attemptId = Database::insert(
            'INSERT INTO quiz_attempts (quiz_id, user_id, status, started_at) VALUES (?,?,\'in_progress\',NOW())',
            [$quizId, $user['id']]
        );

        return Response::json(['attempt_id' => $attemptId], 201);
    }

    public function submitAnswer(Request $request): Response
    {
        $body       = $request->isJson() ? $request->json() : $request->all();
        $attemptId  = (int)($body['attempt_id']  ?? 0);
        $questionId = (int)($body['question_id'] ?? 0);
        $answerId   = (int)($body['answer_id']   ?? 0);

        Database::execute(
            'INSERT INTO quiz_answers (attempt_id, question_id, answer_id, created_at)
             VALUES (?,?,?,NOW())
             ON DUPLICATE KEY UPDATE answer_id = VALUES(answer_id)',
            [$attemptId, $questionId, $answerId]
        );

        return Response::json(['saved' => true]);
    }

    public function submitAttempt(Request $request): Response
    {
        $user      = Session::get('auth_user');
        $attemptId = (int) $request->param('attemptId');

        $answers = Database::query(
            'SELECT qa.question_id, qa.answer_id, qo.is_correct
               FROM quiz_answers qa
               JOIN quiz_options qo ON qo.id = qa.answer_id
              WHERE qa.attempt_id = ?',
            [$attemptId]
        );

        $correct = count(array_filter($answers, fn($a) => (bool)$a['is_correct']));
        $total   = count($answers);
        $score   = $total > 0 ? (int) round($correct / $total * 100) : 0;

        Database::execute(
            'UPDATE quiz_attempts SET status=\'completed\', score=?, completed_at=NOW() WHERE id=? AND user_id=?',
            [$score, $attemptId, $user['id']]
        );

        return Response::json(['score' => $score, 'correct' => $correct, 'total' => $total]);
    }

    public function result(Request $request): Response
    {
        $user      = Session::get('auth_user');
        $attemptId = (int) $request->param('attemptId');

        $attempt = Database::queryOne(
            'SELECT * FROM quiz_attempts WHERE id=? AND user_id=?', [$attemptId, $user['id']]
        );

        if (!$attempt) {
            return Response::json(['error' => 'Not found.'], 404);
        }

        return Response::json(['attempt' => $attempt]);
    }
}
