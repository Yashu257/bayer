<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Banned   = 'banned';
    case Pending  = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Banned   => 'Banned',
            self::Pending  => 'Pending Verification',
        };
    }

    public function canLogin(): bool
    {
        return $this === self::Active;
    }
}
