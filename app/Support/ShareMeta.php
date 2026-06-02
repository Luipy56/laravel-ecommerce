<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Open Graph / Twitter Card meta for the SPA HTML shell.
 */
final class ShareMeta
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $canonicalUrl,
        public readonly string $ogType = 'website',
        public readonly ?string $imageUrl = null,
        public readonly ?string $imageAlt = null,
    ) {}

    public function twitterCard(): string
    {
        return $this->imageUrl !== null && $this->imageUrl !== '' ? 'summary_large_image' : 'summary';
    }
}
