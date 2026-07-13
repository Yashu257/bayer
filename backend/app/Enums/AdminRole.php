<?php

declare(strict_types=1);

namespace App\Enums;

enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin      = 'admin';
    case Moderator  = 'moderator';

    /** Roles that have access to destructive / irreversible actions. */
    public function atLeast(self $minimum): bool
    {
        $hierarchy = [
            self::Moderator->value => 1,
            self::Admin->value     => 2,
            self::SuperAdmin->value => 3,
        ];

        return ($hierarchy[$this->value] ?? 0) >= ($hierarchy[$minimum->value] ?? 0);
    }

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::Admin      => 'Administrator',
            self::Moderator  => 'Moderator',
        };
    }
}
