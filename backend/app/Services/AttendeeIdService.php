<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RegistrationRepository;

/**
 * AttendeeIdService — generates collision-free, human-readable attendee IDs.
 *
 * Format: PW-{YEAR}-{6-digit-zero-padded}
 * Examples: PW-2025-000001  PW-2025-000142  PW-2026-000001
 *
 * Strategy:
 *   1. Base the suffix on total registration count + 1 (sequential intent).
 *   2. Verify uniqueness in the DB — increment and retry on collision.
 *   3. Maximum 20 retries before falling back to a cryptographically random suffix.
 *
 * This keeps IDs short, readable, and unique across years.
 */
class AttendeeIdService
{
    private const PREFIX     = 'PW';
    private const MAX_TRIES  = 20;
    private const SUFFIX_LEN = 6;

    private readonly RegistrationRepository $repository;

    public function __construct()
    {
        $this->repository = new RegistrationRepository();
    }

    /**
     * Generate and return a unique attendee registration code.
     */
    public function generate(): string
    {
        $year    = (int) date('Y');
        $base    = $this->repository->totalCount() + 1;

        for ($attempt = 0; $attempt < self::MAX_TRIES; $attempt++) {
            $suffix = str_pad((string) ($base + $attempt), self::SUFFIX_LEN, '0', STR_PAD_LEFT);
            $code   = self::PREFIX . '-' . $year . '-' . $suffix;

            if ($this->repository->isCodeUnique($code)) {
                return $code;
            }
        }

        // Fallback: cryptographically random suffix (collision extremely unlikely)
        do {
            $randomSuffix = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
            $code         = self::PREFIX . '-' . $year . '-' . $randomSuffix;
        } while (!$this->repository->isCodeUnique($code));

        return $code;
    }
}
