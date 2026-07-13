<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * StreamingProviderInterface — every streaming provider must implement this.
 *
 * The single method returns a plain config array that is JSON-encoded into
 * window.WEBCAST.stream on the page. The frontend JS reads that object and
 * builds the player — no template changes required when swapping providers.
 *
 * Config shape:
 * {
 *   "type":      "placeholder" | "vimeo" | "youtube" | "wowza",
 *   "videoId":   "string",   // Vimeo or YouTube video/stream ID
 *   "streamUrl": "string",   // Wowza HLS/RTMP URL (or empty)
 *   "autoplay":  bool,
 *   "params":    {}          // provider-specific query params
 * }
 *
 * To add a new provider (e.g. Brightcove):
 *   1. Create app/StreamingProviders/BrightcoveProvider.php
 *   2. Add a case to StreamingProviderFactory::make()
 *   3. Handle 'brightcove' type in public/assets/js/webcast.js → loadPlayer()
 *   Zero other files change.
 */
interface StreamingProviderInterface
{
    public function getEmbedConfig(): array;
}
