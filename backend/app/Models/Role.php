<?php

declare(strict_types=1);

namespace App\Models;

class Role extends BaseModel
{
    protected static string $table = 'roles';

    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug);
    }
}
