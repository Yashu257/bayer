<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Database;

class Event extends BaseModel
{
    protected static string $table = 'events';

    // --- Domain helpers ------------------------------------------------------

    public function isLive(): bool
    {
        return ($this->status ?? '') === 'live';
    }

    public function hasStarted(): bool
    {
        return strtotime($this->starts_at ?? '0') <= time();
    }

    public function hasEnded(): bool
    {
        return strtotime($this->ends_at ?? '0') < time();
    }

    public function isOpen(): bool
    {
        return in_array($this->status ?? '', ['published', 'live'], true);
    }

    public function registrationOpen(): bool
    {
        $now = time();

        $opens  = $this->registration_opens  ? strtotime($this->registration_opens)  : null;
        $closes = $this->registration_closes ? strtotime($this->registration_closes) : null;

        if ($opens  !== null && $now < $opens)  return false;
        if ($closes !== null && $now > $closes) return false;

        return $this->isOpen();
    }

    /** Seconds until event starts (0 if already started). */
    public function secondsUntilStart(): int
    {
        return max(0, strtotime($this->starts_at ?? 'now') - time());
    }

    public function formattedDate(): string
    {
        return date('l, F j, Y', strtotime($this->starts_at ?? 'now'));
    }

    public function formattedTime(): string
    {
        return date('g:i A', strtotime($this->starts_at ?? 'now')) . ' ' . ($this->timezone ?? 'UTC');
    }

    // --- Scoped finders ------------------------------------------------------

    public static function findBySlug(string $slug): ?self
    {
        $row = Database::queryOne(
            "SELECT * FROM events WHERE slug = ? AND deleted_at IS NULL LIMIT 1",
            [$slug]
        );
        return $row ? new self($row) : null;
    }

    public static function published(): array
    {
        $rows = Database::query(
            "SELECT * FROM events WHERE status IN ('published','live') AND deleted_at IS NULL ORDER BY starts_at ASC"
        );
        return array_map(fn($r) => new self($r), $rows);
    }
}
