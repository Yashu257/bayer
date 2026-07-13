<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Database;

class AdminSession extends BaseModel
{
    protected static string $table = 'admin_sessions';

    public function isExpired(): bool
    {
        return strtotime($this->expires_at ?? '0') < time();
    }

    public static function findByToken(string $token): ?self
    {
        return self::where('token', $token);
    }

    public static function deleteAllForAdmin(int $adminId): void
    {
        Database::execute(
            "DELETE FROM admin_sessions WHERE admin_id = ?",
            [$adminId]
        );
    }
}
