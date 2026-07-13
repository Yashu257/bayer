<?php

declare(strict_types=1);

namespace App\StreamingProviders;

use App\Contracts\StreamingProviderInterface;

/**
 * PlaceholderProvider — used when no streaming source has been configured yet.
 * The frontend renders a styled "Live stream will begin shortly" card.
 */
class PlaceholderProvider implements StreamingProviderInterface
{
    public function getEmbedConfig(): array
    {
        return [
            'type'      => 'placeholder',
            'videoId'   => '',
            'streamUrl' => '',
            'autoplay'  => false,
            'params'    => [],
        ];
    }
}
