<?php

declare(strict_types=1);

namespace App\StreamingProviders;

use App\Contracts\StreamingProviderInterface;
use App\Models\Event;

/**
 * StreamingProviderFactory — resolves the correct provider from the event record.
 *
 * The event row has a `streaming_provider` column (enum: placeholder/vimeo/youtube/wowza).
 * Changing that column value is the ONLY step required to switch providers — no
 * controller, view, or JS template changes are needed.
 *
 * To add a new provider (e.g. Brightcove):
 *   1. Create app/StreamingProviders/BrightcoveProvider.php
 *   2. Add   'brightcove' => new BrightcoveProvider($event)   here
 *   3. Add a 'brightcove' case in public/assets/js/webcast.js → loadPlayer()
 */
final class StreamingProviderFactory
{
    private function __construct() {}

    public static function make(Event $event): StreamingProviderInterface
    {
        return match ($event->streaming_provider ?? 'placeholder') {
            'vimeo'   => new VimeoProvider($event),
            'youtube' => new YouTubeProvider($event),
            'wowza'   => new WowzaProvider($event),
            default   => new PlaceholderProvider(),
        };
    }
}
