<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;

class QuestionController
{
    public function index(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $user    = Session::get('auth_user');

        $questions = Database::query(
            'SELECT q.id, q.question, q.status,
                    (SELECT COUNT(*) FROM question_upvotes WHERE question_id = q.id) AS upvotes,
                    EXISTS(SELECT 1 FROM question_upvotes WHERE question_id = q.id AND user_id = ?) AS upvoted
               FROM questions q
              WHERE q.event_id = ? AND q.status IN (\'approved\',\'answered\')
              ORDER BY upvotes DESC, q.created_at ASC',
            [$user['id'] ?? 0, $eventId]
        );

        return Response::json(['questions' => $questions]);
    }

    public function store(Request $request): Response
    {
        $user    = Session::get('auth_user');
        $eventId = (int) $request->param('eventId');
        $body    = $request->isJson() ? $request->json() : $request->all();
        $text    = trim((string)($body['question'] ?? ''));

        if (strlen($text) < 5 || strlen($text) > 500) {
            return Response::json(['error' => 'Question must be 5–500 characters.'], 422);
        }

        $id = Database::insert(
            'INSERT INTO questions (event_id, user_id, question, status, created_at) VALUES (?,?,?,\'pending\',NOW())',
            [$eventId, $user['id'], $text]
        );

        return Response::json(['id' => $id], 201);
    }

    public function upvote(Request $request): Response
    {
        $user       = Session::get('auth_user');
        $questionId = (int) $request->param('id');

        $exists = Database::queryOne(
            'SELECT id FROM question_upvotes WHERE question_id = ? AND user_id = ?',
            [$questionId, $user['id']]
        );

        if ($exists) {
            Database::execute('DELETE FROM question_upvotes WHERE question_id = ? AND user_id = ?',
                [$questionId, $user['id']]);
            $upvoted = false;
        } else {
            Database::insert('INSERT INTO question_upvotes (question_id, user_id, created_at) VALUES (?,?,NOW())',
                [$questionId, $user['id']]);
            $upvoted = true;
        }

        $count = (int)(Database::queryOne(
            'SELECT COUNT(*) AS n FROM question_upvotes WHERE question_id = ?', [$questionId]
        )['n'] ?? 0);

        return Response::json(['upvoted' => $upvoted, 'count' => $count]);
    }
}
