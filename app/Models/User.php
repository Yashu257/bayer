<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;

class User extends BaseModel
{
    protected static string $table = 'users';

    protected static array $fillable = [
        'role_id', 'first_name', 'last_name', 'email', 'phone',
        'password_hash', 'job_title', 'company', 'country',
        'email_verified_at', 'verification_token', 'status',
    ];

    // --- Domain helpers ------------------------------------------------------

    public function fullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function statusEnum(): UserStatus
    {
        return UserStatus::from($this->status ?? 'pending');
    }

    public function canLogin(): bool
    {
        return $this->statusEnum()->canLogin();
    }

    /** Never expose password_hash in serialised output. */
    public function toArray(): array
    {
        $data = parent::toArray();
        unset($data['password_hash'], $data['verification_token'], $data['reset_token']);
        return $data;
    }

    // --- Scoped finders ------------------------------------------------------

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', strtolower(trim($email)));
    }

    public static function findByVerificationToken(string $token): ?self
    {
        return self::where('verification_token', $token);
    }

    public static function findByResetToken(string $token): ?self
    {
        return self::where('reset_token', $token);
    }
}
