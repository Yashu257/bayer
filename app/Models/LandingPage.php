<?php

declare(strict_types=1);

namespace App\Models;

class LandingPage extends BaseModel
{
    protected static string $table = 'landing_pages';

    public function isPublished(): bool
    {
        return ($this->status ?? '') === 'published';
    }

    public function hasHero(): bool
    {
        return !empty($this->hero_headline);
    }

    public function hasHeroImage(): bool
    {
        return !empty($this->hero_image_path);
    }

    public static function findByEventId(int $eventId): ?self
    {
        return self::where('event_id', $eventId);
    }
}
