<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Database;

class RegistrationForm extends BaseModel
{
    protected static string $table = 'registration_forms';

    public function isActive(): bool
    {
        return ($this->status ?? '') === 'active';
    }

    public function requiresApproval(): bool
    {
        return (bool) ($this->require_approval ?? false);
    }

    public static function findByEventId(int $eventId): ?self
    {
        $row = Database::queryOne(
            "SELECT * FROM registration_forms WHERE event_id = ? AND status = 'active' LIMIT 1",
            [$eventId]
        );
        return $row ? new self($row) : null;
    }
}
