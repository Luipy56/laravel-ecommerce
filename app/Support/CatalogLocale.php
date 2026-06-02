<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Supported storefront catalog content locales (database CHECK + API).
 */
final class CatalogLocale
{
    /** @var list<string> */
    public const SUPPORTED = ['ca', 'es', 'en'];

    public static function normalize(?string $locale): string
    {
        $l = strtolower(trim((string) $locale));

        return in_array($l, self::SUPPORTED, true) ? $l : 'ca';
    }

    /**
     * @return list<string> Preferred locale first, then the rest in canonical order.
     */
    public static function fallbackChain(string $preferred): array
    {
        $p = self::normalize($preferred);
        $out = [$p];
        foreach (self::SUPPORTED as $x) {
            if ($x !== $p) {
                $out[] = $x;
            }
        }

        return $out;
    }
}
