<?php

declare(strict_types=1);

namespace App\StreamingProviders;

use App\Contracts\StreamingProviderInterface;
use App\Models\Event;

/**
 * WowzaProvider — Wowza Streaming Engine / Wowza Streaming Cloud.
 *
 * Delivers an HLS stream via event.stream_url (full .m3u8 URL).
 * The frontend uses HLS.js to play the stream in a <video> element.
 *
 * Required env: event.stream_url = 'https://[wowza-host]/[app]/[stream].m3u8'
 */
class WowzaProvider implements StreamingProviderInterface
{
    public function __construct(private readonly Event $event) {}

    public function getEmbedConfig(): array
    {
        return [
            'type'      => 'wowza',
            'videoId'   => '',
            'streamUrl' => $this->event->stream_url ?? '',
            'autoplay'  => true,
            'params'    => [
                'hlsjs_version' => '1.5.7',    // HLS.js CDN version to load
            ],
        ];
    }
}
