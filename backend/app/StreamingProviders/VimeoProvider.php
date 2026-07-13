<?php

declare(strict_types=1);

namespace App\StreamingProviders;

use App\Contracts\StreamingProviderInterface;
use App\Models\Event;

/**
 * VimeoProvider — Vimeo Livestream or recorded Vimeo video.
 *
 * Requires event.stream_id to be the numeric Vimeo video/event ID.
 * Optional: event.stream_token for private/unlisted video hash.
 *
 * Embed URL built by webcast.js:
 *   https://player.vimeo.com/video/{videoId}?{params}
 */
class VimeoProvider implements StreamingProviderInterface
{
    public function __construct(private readonly Event $event) {}

    public function getEmbedConfig(): array
    {
        return [
            'type'      => 'vimeo',
            'videoId'   => $this->event->stream_id ?? '',
            'streamUrl' => '',
            'autoplay'  => true,
            'params'    => [
                'autoplay'   => 1,
                'autopause'  => 0,
                'badge'      => 0,
                'byline'     => 0,
                'portrait'   => 0,
                'title'      => 0,
                'loop'       => 0,
                'h'          => $this->event->stream_token ?? '',  // private video hash
            ],
        ];
    }
}
