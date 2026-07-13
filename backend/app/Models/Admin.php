<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdminRole;
use Core\Database\Database;

class Admin extends BaseModel
{
    protected static string $table = 'admins';

    public function fullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function role(): ?Role
    {
        return Role::find((int) $this->role_id);
    }

    public function roleSlug(): string
    {
        return $this->role()?->slug ?? '';
    }

    public function roleEnum(): AdminRole
    {
        return AdminRole::from($this->roleSlug());
    }

    /** Check whether this admin has at least the given role level. */
    public function hasRole(AdminRole $minimum): bool
    {
        return $this->roleEnum()->atLeast($minimum);
    }

    public function isActive(): bool
    {
        return ($this->status ?? '') === 'active';
    }

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', strtolower(trim($email)));
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        unset($data['password_hash'], $data['two_fa_secret']);
        return $data;
    }
}
