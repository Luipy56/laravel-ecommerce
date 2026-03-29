<?php

namespace App\Support;

/**
 * Resolves ca/es for transactional emails when client preference is not stored.
 */
final class MailLocale
{
    public static function resolve(?string $preferred = null): string
    {
        foreach ([$preferred, app()->getLocale()] as $locale) {
            if (is_string($locale) && in_array($locale, ['ca', 'es'], true)) {
                return $locale;
            }
        }

        return 'ca';
    }
}
