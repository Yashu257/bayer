<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Admin;
use Core\Database\Database;

class AdminRepository
{
    public function findById(int $id): ?Admin
    {
        $row = Database::queryOne(
            "SELECT a.*, r.slug AS role_slug
             FROM admins a
             JOIN roles r ON r.id = a.role_id
             WHERE a.id = ? AND a.deleted_at IS NULL LIMIT 1",
            [$id]
        );
        return $row ? new Admin($row) : null;
    }

    public function findByEmail(string $email): ?Admin
    {
        $row = Database::queryOne(
            "SELECT a.*, r.slug AS role_slug
             FROM admins a
             JOIN roles r ON r.id = a.role_id
             WHERE a.email = ? AND a.deleted_at IS NULL LIMIT 1",
            [strtolower(trim($email))]
        );
        return $row ? new Admin($row) : null;
    }

    public function updateLastLogin(int $adminId, string $ip): void
    {
        Database::execute(
            "UPDATE admins SET last_login_at = NOW(), last_login_ip = ?, updated_at = NOW() WHERE id = ?",
            [$ip, $adminId]
        );
    }

    public function updatePassword(int $adminId, string $hash): void
    {
        Database::execute(
            "UPDATE admins SET password_hash = ?, updated_at = NOW() WHERE id = ?",
            [$hash, $adminId]
        );
    }
}
