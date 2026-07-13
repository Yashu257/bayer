<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Database;

class UserSession extends BaseModel
{
    protected static string $table = 'user_sessions';

    public function isExpired(): bool
    {
        return strtotime($this->expires_at ?? '0') < time();
    }

    public static function findByToken(string $token): ?self
    {
        return self::where('token', $token);
    }

    public static function deleteExpiredForUser(int $userId): void
    {
        Database::execute(
            "DELETE FROM user_sessions WHERE user_id = ? AND expires_at < NOW()",
            [$userId]
        );
    }

    public static function deleteAllForUser(int $userId): void
    {
        Database::execute(
            "DELETE FROM user_sessions WHERE user_id = ?",
            [$userId]
        );
    }
}
