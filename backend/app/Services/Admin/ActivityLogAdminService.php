<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Core\Database\Database;

class ActivityLogAdminService
{
    public function list(string $search = '', string $action = '', int $page = 1, int $perPage = 40): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($search !== '') {
            $where[]  = '(al.actor_email LIKE ? OR al.description LIKE ? OR al.ip_address LIKE ?)';
            $like     = '%' . $search . '%';
            $params   = array_merge($params, [$like, $like, $like]);
        }

        if ($action !== '') {
            $where[]  = 'al.action = ?';
            $params[] = $action;
        }

        $sql = 'SELECT al.id, al.actor_type, al.actor_id, al.actor_email,
                       al.action, al.description, al.ip_address, al.created_at
                  FROM activity_logs al
                 WHERE ' . implode(' AND ', $where) . '
                 ORDER BY al.created_at DESC
                 LIMIT ? OFFSET ?';

        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $total = (int)(Database::queryOne(
            'SELECT COUNT(*) AS n FROM activity_logs al WHERE ' . implode(' AND ', $where),
            array_slice($params, 0, -2)
        )['n'] ?? 0);

        return [
            'rows'  => Database::query($sql, $params),
            'total' => $total,
            'page'  => $page,
            'pages' => max(1, (int)ceil($total / $perPage)),
        ];
    }

    public function distinctActions(): array
    {
        return array_column(
            Database::query('SELECT DISTINCT action FROM activity_logs ORDER BY action ASC'),
            'action'
        );
    }
}
