<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\UserSession;
use App\Models\AdminSession;
use Core\Database\Database;

class SessionRepository
{
    // -------------------------------------------------------------------------
    // User sessions
    // -------------------------------------------------------------------------

    public function createUserSession(int $userId, string $token, int $lifetimeMinutes, string $ip, string $userAgent): int
    {
        $expires = date('Y-m-d H:i:s', time() + ($lifetimeMinutes * 60));
        return Database::insert(
            "INSERT INTO user_sessions (user_id, token, ip_address, user_agent, expires_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
            [$userId, $token, $ip, $userAgent, $expires]
        );
    }

    public function findUserSession(string $token): ?UserSession
    {
        $row = Database::queryOne(
            "SELECT * FROM user_sessions WHERE token = ? AND expires_at > NOW() LIMIT 1",
            [$token]
        );
        return $row ? new UserSession($row) : null;
    }

    public function touchUserSession(string $token, int $lifetimeMinutes): void
    {
        $expires = date('Y-m-d H:i:s', time() + ($lifetimeMinutes * 60));
        Database::execute(
            "UPDATE user_sessions SET expires_at = ?, updated_at = NOW() WHERE token = ?",
            [$expires, $token]
        );
    }

    public function deleteUserSession(string $token): void
    {
        Database::execute("DELETE FROM user_sessions WHERE token = ?", [$token]);
    }

    public function deleteAllUserSessions(int $userId): void
    {
        Database::execute("DELETE FROM user_sessions WHERE user_id = ?", [$userId]);
    }

    public function purgeExpiredUserSessions(): void
    {
        Database::execute("DELETE FROM user_sessions WHERE expires_at < NOW()");
    }

    // -------------------------------------------------------------------------
    // Admin sessions
    // -------------------------------------------------------------------------

    public function createAdminSession(int $adminId, string $token, int $lifetimeMinutes, string $ip, string $userAgent): int
    {
        $expires = date('Y-m-d H:i:s', time() + ($lifetimeMinutes * 60));
        return Database::insert(
            "INSERT INTO admin_sessions (admin_id, token, ip_address, user_agent, expires_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
            [$adminId, $token, $ip, $userAgent, $expires]
        );
    }

    public function findAdminSession(string $token): ?AdminSession
    {
        $row = Database::queryOne(
            "SELECT * FROM admin_sessions WHERE token = ? AND expires_at > NOW() LIMIT 1",
            [$token]
        );
        return $row ? new AdminSession($row) : null;
    }

    public function touchAdminSession(string $token, int $lifetimeMinutes): void
    {
        $expires = date('Y-m-d H:i:s', time() + ($lifetimeMinutes * 60));
        Database::execute(
            "UPDATE admin_sessions SET expires_at = ?, updated_at = NOW() WHERE token = ?",
            [$expires, $token]
        );
    }

    public function deleteAdminSession(string $token): void
    {
        Database::execute("DELETE FROM admin_sessions WHERE token = ?", [$token]);
    }

    public function deleteAllAdminSessions(int $adminId): void
    {
        Database::execute("DELETE FROM admin_sessions WHERE admin_id = ?", [$adminId]);
    }
}
