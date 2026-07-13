<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Core\Database\Database;

class QuestionAdminService
{
    public function listForEvent(int $eventId, string $filter = 'all'): array
    {
        $where  = ['q.event_id = ?', 'q.deleted_at IS NULL'];
        $params = [$eventId];

        if ($filter === 'pending')  { $where[] = 'q.status = "pending"'; }
        if ($filter === 'approved') { $where[] = 'q.status = "approved"'; }
        if ($filter === 'answered') { $where[] = 'q.is_answered = 1'; }

        return Database::query(
            'SELECT q.id, q.question_text, q.asked_by_name, q.upvote_count,
                    q.status, q.is_answered, q.answer_text, q.created_at
               FROM questions q
              WHERE ' . implode(' AND ', $where) . '
              ORDER BY q.is_answered ASC, q.upvote_count DESC, q.created_at DESC',
            $params
        );
    }

    public function approve(int $questionId): void
    {
        Database::execute(
            'UPDATE questions SET status = "approved", updated_at = NOW() WHERE id = ?',
            [$questionId]
        );
    }

    public function dismiss(int $questionId): void
    {
        Database::execute(
            'UPDATE questions SET status = "dismissed", updated_at = NOW() WHERE id = ?',
            [$questionId]
        );
    }

    public function answer(int $questionId, string $answerText): void
    {
        Database::execute(
            'UPDATE questions SET is_answered = 1, answer_text = ?, updated_at = NOW() WHERE id = ?',
            [$answerText, $questionId]
        );
    }

    public function delete(int $questionId): void
    {
        Database::execute(
            'UPDATE questions SET deleted_at = NOW() WHERE id = ?',
            [$questionId]
        );
    }
}
