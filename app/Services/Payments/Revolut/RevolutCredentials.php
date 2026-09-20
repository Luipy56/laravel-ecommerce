<?php

namespace App\Services\Payments\Revolut;

/**
 * Same rules as other PSPs: non-empty string Merchant API secret only.
 * Public key is optional for hosted Checkout redirect (required later for widgets).
 */
class RevolutCredentials
{
    public static function areConfigured(): bool
    {
        $apiKey = config('services.revolut.api_key');

        return is_string($apiKey) && $apiKey !== '';
    }

    public static function publicKey(): ?string
    {
        $key = config('services.revolut.public_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    public static function sandbox(): bool
    {
        return filter_var(config('services.revolut.sandbox', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function apiVersion(): string
    {
        $version = config('services.revolut.api_version', '2024-09-01');

        return is_string($version) && $version !== '' ? $version : '2024-09-01';
    }

    public static function merchantBaseUrl(): string
    {
        return self::sandbox()
            ? 'https://sandbox-merchant.revolut.com'
            : 'https://merchant.revolut.com';
    }
}
