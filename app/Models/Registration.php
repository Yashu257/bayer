<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Database;

class Registration extends BaseModel
{
    protected static string $table = 'registrations';

    // --- Domain helpers ------------------------------------------------------

    public function fullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function isApproved(): bool
    {
        return ($this->approval_status ?? '') === 'approved';
    }

    public function isPending(): bool
    {
        return ($this->approval_status ?? '') === 'pending';
    }

    public function isActive(): bool
    {
        return ($this->status ?? '') === 'active';
    }

    // --- Scoped finders ------------------------------------------------------

    public static function findByCode(string $code): ?self
    {
        $row = Database::queryOne(
            "SELECT * FROM registrations WHERE registration_code = ? AND deleted_at IS NULL LIMIT 1",
            [$code]
        );
        return $row ? new self($row) : null;
    }

    public static function findByEventAndEmail(int $eventId, string $email): ?self
    {
        $row = Database::queryOne(
            "SELECT * FROM registrations
             WHERE event_id = ? AND email = ? AND deleted_at IS NULL LIMIT 1",
            [$eventId, strtolower(trim($email))]
        );
        return $row ? new self($row) : null;
    }

    public function toArray(): array
    {
        return parent::toArray();
    }
}
