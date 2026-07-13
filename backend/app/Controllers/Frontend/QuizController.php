<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class QuizController extends BaseController
{
    public function show(Request $request): Response
    {
        $quizId = (int) $request->param('id');
        $quiz   = Database::queryOne(
            'SELECT * FROM quizzes WHERE id = ? AND deleted_at IS NULL', [$quizId]
        );

        if (!$quiz) {
            return Response::redirect('/404');
        }

        return $this->view('frontend.quiz.show', [
            'quiz'      => $quiz,
            'pageTitle' => $quiz['title'],
        ]);
    }

    public function start(Request $request): Response
    {
        $user   = Session::get('auth_user');
        $quizId = (int) $request->param('id');

        $existing = Database::queryOne(
            'SELECT id FROM quiz_attempts WHERE quiz_id = ? AND user_id = ? AND status = \'in_progress\'',
            [$quizId, $user['id']]
        );

        if ($existing) {
            return Response::redirect('/quiz/' . $quizId . '/question/1?attempt=' . $existing['id']);
        }

        $attemptId = Database::insert(
            'INSERT INTO quiz_attempts (quiz_id, user_id, status, started_at) VALUES (?,?,\'in_progress\',NOW())',
            [$quizId, $user['id']]
        );

        return Response::redirect('/quiz/' . $quizId . '/question/1?attempt=' . $attemptId);
    }

    public function answer(Request $request): Response
    {
        $attemptId  = (int) $request->input('attempt_id');
        $questionId = (int) $request->input('question_id');
        $answerId   = (int) $request->input('answer_id');

        Database::execute(
            'INSERT INTO quiz_answers (attempt_id, question_id, answer_id, created_at)
             VALUES (?,?,?,NOW())
             ON DUPLICATE KEY UPDATE answer_id = VALUES(answer_id)',
            [$attemptId, $questionId, $answerId]
        );

        return Response::json(['saved' => true]);
    }

    public function submit(Request $request): Response
    {
        $user      = Session::get('auth_user');
        $quizId    = (int) $request->param('id');
        $attemptId = (int) $request->input('attempt_id');

        $answers = Database::query(
            'SELECT qa.question_id, qa.answer_id, qo.is_correct
               FROM quiz_answers qa
               JOIN quiz_options qo ON qo.id = qa.answer_id
              WHERE qa.attempt_id = ?',
            [$attemptId]
        );

        $correct = count(array_filter($answers, fn($a) => (bool) $a['is_correct']));
        $total   = count($answers);
        $score   = $total > 0 ? (int) round($correct / $total * 100) : 0;

        Database::execute(
            'UPDATE quiz_attempts SET status=\'completed\', score=?, completed_at=NOW() WHERE id=? AND user_id=?',
            [$score, $attemptId, $user['id']]
        );

        return Response::redirect('/quiz/' . $quizId . '/result/' . $attemptId);
    }

    public function result(Request $request): Response
    {
        $user      = Session::get('auth_user');
        $quizId    = (int) $request->param('id');
        $attemptId = (int) $request->param('attemptId');

        $attempt = Database::queryOne(
            'SELECT * FROM quiz_attempts WHERE id = ? AND user_id = ? AND quiz_id = ?',
            [$attemptId, $user['id'], $quizId]
        );

        if (!$attempt) {
            return Response::redirect('/');
        }

        return $this->view('frontend.quiz.result', [
            'attempt'   => $attempt,
            'pageTitle' => 'Quiz Result',
        ]);
    }
}
