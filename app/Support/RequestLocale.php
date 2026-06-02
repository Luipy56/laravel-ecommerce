<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Resolves storefront locale from query string or Accept-Language header.
 */
final class RequestLocale
{
    public static function fromRequest(?Request $request = null): string
    {
        $request ??= request();
        $allowed = config('app.available_locales', CatalogLocale::SUPPORTED);

        $qLocale = $request->query('locale');
        if (is_string($qLocale) && in_array(strtolower($qLocale), $allowed, true)) {
            return CatalogLocale::normalize(strtolower($qLocale));
        }

        $pref = $request->header('Accept-Language', '');
        if (preg_match('/^(ca|es|en)([-_;]|$)/i', $pref, $m)) {
            $loc = strtolower($m[1]);
            if (in_array($loc, $allowed, true)) {
                return CatalogLocale::normalize($loc);
            }
        }

        return CatalogLocale::normalize((string) config('app.locale'));
    }
}
