<?php

declare(strict_types=1);

namespace App\Controllers\Api\Admin;

use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;

class QuestionController
{
    public function index(Request $request): Response
    {
        $eventId = (int) $request->param('eventId');
        $status  = $request->query('status', '');

        $where  = ['event_id = ?'];
        $params = [$eventId];

        if ($status !== '') {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        $questions = Database::query(
            'SELECT q.*, u.first_name, u.last_name,
                    (SELECT COUNT(*) FROM question_upvotes WHERE question_id = q.id) AS upvotes
               FROM questions q
               LEFT JOIN users u ON u.id = q.user_id
              WHERE ' . implode(' AND ', $where) . '
              ORDER BY upvotes DESC, q.created_at ASC',
            $params
        );

        return Response::json(['questions' => $questions]);
    }

    public function approve(Request $request): Response
    {
        $id = (int) $request->param('id');
        Database::execute("UPDATE questions SET status='approved', updated_at=NOW() WHERE id=?", [$id]);
        return Response::json(['status' => 'approved']);
    }

    public function dismiss(Request $request): Response
    {
        $id = (int) $request->param('id');
        Database::execute("UPDATE questions SET status='dismissed', updated_at=NOW() WHERE id=?", [$id]);
        return Response::json(['status' => 'dismissed']);
    }
}
