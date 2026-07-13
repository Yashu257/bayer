<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class QuizQuestionController extends BaseController
{
    public function store(Request $request): Response
    {
        $quizId  = (int) $request->param('quizId');
        $data    = $request->only(['question', 'explanation', 'sort_order']);
        $options = $request->input('options', []);

        $questionId = Database::insert(
            'INSERT INTO quiz_questions (quiz_id, question, explanation, sort_order, created_at)
             VALUES (?,?,?,?,NOW())',
            [$quizId, $data['question'] ?? '', $data['explanation'] ?? '', (int)($data['sort_order'] ?? 0)]
        );

        foreach ((array) $options as $opt) {
            Database::insert(
                'INSERT INTO quiz_options (question_id, option_text, is_correct, sort_order, created_at)
                 VALUES (?,?,?,?,NOW())',
                [$questionId, $opt['text'] ?? '', (int)($opt['is_correct'] ?? 0), (int)($opt['sort_order'] ?? 0)]
            );
        }

        Session::flash('success', 'Question added.');
        return Response::redirect('/admin/quizzes/' . $quizId . '/questions');
    }

    public function update(Request $request): Response
    {
        $quizId     = (int) $request->param('quizId');
        $questionId = (int) $request->param('questionId');
        $data       = $request->only(['question', 'explanation', 'sort_order']);

        Database::execute(
            'UPDATE quiz_questions SET question=?, explanation=?, sort_order=?, updated_at=NOW()
              WHERE id=? AND quiz_id=?',
            [$data['question'] ?? '', $data['explanation'] ?? '', (int)($data['sort_order'] ?? 0), $questionId, $quizId]
        );

        Session::flash('success', 'Question updated.');
        return Response::redirect('/admin/quizzes/' . $quizId . '/questions');
    }

    public function destroy(Request $request): Response
    {
        $quizId     = (int) $request->param('quizId');
        $questionId = (int) $request->param('questionId');

        Database::execute('DELETE FROM quiz_questions WHERE id=? AND quiz_id=?', [$questionId, $quizId]);
        Session::flash('success', 'Question deleted.');
        return Response::redirect('/admin/quizzes/' . $quizId . '/questions');
    }

    public function reorder(Request $request): Response
    {
        $quizId = (int) $request->param('quizId');
        $order  = $request->input('order', []);

        foreach ((array) $order as $sort => $questionId) {
            Database::execute(
                'UPDATE quiz_questions SET sort_order=? WHERE id=? AND quiz_id=?',
                [(int)$sort, (int)$questionId, $quizId]
            );
        }

        return Response::json(['reordered' => true]);
    }
}
