<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Core\Database\Database;

class AttendeeService
{
    public function list(string $search = '', string $status = '', int $page = 1, int $perPage = 25): array
    {
        $where  = ['u.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[]  = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.company LIKE ?)';
            $like     = '%' . $search . '%';
            $params   = array_merge($params, [$like, $like, $like, $like]);
        }

        if ($status !== '') {
            $where[]  = 'u.status = ?';
            $params[] = $status;
        }

        $sql = 'SELECT u.id, u.first_name, u.last_name, u.email, u.company,
                       u.status, u.created_at,
                       COUNT(DISTINCT r.id) AS event_count
                  FROM users u
                  LEFT JOIN registrations r ON r.email = u.email AND r.deleted_at IS NULL
                 WHERE ' . implode(' AND ', $where) . '
                 GROUP BY u.id
                 ORDER BY u.created_at DESC
                 LIMIT ? OFFSET ?';

        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $countSql = 'SELECT COUNT(*) AS n FROM users u WHERE ' . implode(' AND ', $where);

        return [
            'rows'  => Database::query($sql, $params),
            'total' => (int) (Database::queryOne($countSql, array_slice($params, 0, -2))['n'] ?? 0),
            'page'  => $page,
            'pages' => max(1, (int) ceil(((int)(Database::queryOne($countSql, array_slice($params, 0, -2))['n'] ?? 0)) / $perPage)),
        ];
    }

    public function updateStatus(int $userId, string $status): void
    {
        Database::execute('UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $userId]);
    }
}
