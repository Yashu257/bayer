<?php

declare(strict_types=1);

namespace App\StreamingProviders;

use App\Contracts\StreamingProviderInterface;
use App\Models\Event;

/**
 * YouTubeProvider — YouTube Live or YouTube VOD.
 *
 * For YouTube Live: event.stream_id is the live stream video ID (not channel ID).
 * The embed URL built by webcast.js:
 *   https://www.youtube.com/embed/{videoId}?{params}
 */
class YouTubeProvider implements StreamingProviderInterface
{
    public function __construct(private readonly Event $event) {}

    public function getEmbedConfig(): array
    {
        return [
            'type'      => 'youtube',
            'videoId'   => $this->event->stream_id ?? '',
            'streamUrl' => '',
            'autoplay'  => true,
            'params'    => [
                'autoplay'        => 1,
                'rel'             => 0,
                'modestbranding'  => 1,
                'enablejsapi'     => 1,
                'origin'          => $_ENV['APP_URL'] ?? '',
            ],
        ];
    }
}
