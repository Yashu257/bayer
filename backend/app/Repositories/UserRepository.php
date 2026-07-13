<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Core\Database\Database;

/**
 * UserRepository — all SQL for the users table lives here.
 * No SQL outside this class. No exceptions.
 */
class UserRepository
{
    public function findById(int $id): ?User
    {
        $row = Database::queryOne(
            "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1",
            [$id]
        );
        return $row ? new User($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = Database::queryOne(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1",
            [strtolower(trim($email))]
        );
        return $row ? new User($row) : null;
    }

    public function findByVerificationToken(string $token): ?User
    {
        $row = Database::queryOne(
            "SELECT * FROM users WHERE verification_token = ? AND deleted_at IS NULL LIMIT 1",
            [$token]
        );
        return $row ? new User($row) : null;
    }

    public function findByResetToken(string $token): ?User
    {
        $row = Database::queryOne(
            "SELECT * FROM users
             WHERE reset_token = ?
               AND reset_token_expires > NOW()
               AND deleted_at IS NULL
             LIMIT 1",
            [$token]
        );
        return $row ? new User($row) : null;
    }

    public function emailExists(string $email): bool
    {
        $row = Database::queryOne(
            "SELECT id FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1",
            [strtolower(trim($email))]
        );
        return $row !== null;
    }

    public function create(array $data): int
    {
        return Database::insert(
            "INSERT INTO users
                (role_id, first_name, last_name, email, phone, password_hash,
                 job_title, company, country, verification_token, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['role_id']            ?? $this->defaultRoleId(),
                $data['first_name'],
                $data['last_name'],
                strtolower(trim($data['email'])),
                $data['phone']              ?? null,
                $data['password_hash'],
                $data['job_title']          ?? null,
                $data['company']            ?? null,
                $data['country']            ?? null,
                $data['verification_token'],
                $data['status']             ?? 'pending',
            ]
        );
    }

    public function markEmailVerified(int $userId): void
    {
        Database::execute(
            "UPDATE users
             SET email_verified_at = NOW(),
                 verification_token = NULL,
                 status = 'active',
                 updated_at = NOW()
             WHERE id = ?",
            [$userId]
        );
    }

    public function setResetToken(int $userId, string $token, string $expiresAt): void
    {
        Database::execute(
            "UPDATE users
             SET reset_token = ?, reset_token_expires = ?, updated_at = NOW()
             WHERE id = ?",
            [$token, $expiresAt, $userId]
        );
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        Database::execute(
            "UPDATE users
             SET password_hash = ?,
                 reset_token = NULL,
                 reset_token_expires = NULL,
                 updated_at = NOW()
             WHERE id = ?",
            [$passwordHash, $userId]
        );
    }

    public function updateLastLogin(int $userId): void
    {
        Database::execute(
            "UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$userId]
        );
    }

    public function updateStatus(int $userId, string $status): void
    {
        Database::execute(
            "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $userId]
        );
    }

    public function softDelete(int $userId): void
    {
        Database::execute(
            "UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$userId]
        );
    }

    private function defaultRoleId(): int
    {
        // The 'viewer' role slug is the default for self-registered attendees
        $row = Database::queryOne("SELECT id FROM roles WHERE slug = 'viewer' LIMIT 1");
        return (int) ($row['id'] ?? 1);
    }
}
