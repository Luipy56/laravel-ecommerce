<?php

namespace App\Support;

/**
 * Resolves ca / es / en for transactional emails when client preference is not stored.
 */
final class MailLocale
{
    public static function resolve(?string $preferred = null): string
    {
        $allowed = config('app.available_locales', ['ca', 'es', 'en']);
        foreach ([$preferred, app()->getLocale()] as $locale) {
            if (is_string($locale) && in_array($locale, $allowed, true)) {
                return $locale;
            }
        }

        return 'ca';
    }
}
