<?php

namespace App\Support\Auth;

use App\Exceptions\GoogleOAuthException;
use Illuminate\Database\QueryException;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

final class GoogleOAuthErrorMapper
{
    public static function errorCodeFrom(Throwable $e): string
    {
        if ($e instanceof GoogleOAuthException) {
            return $e->errorCode;
        }

        if ($e instanceof InvalidStateException) {
            return 'session_expired';
        }

        if ($e instanceof QueryException && self::isMissingGoogleOAuthSchema($e)) {
            return 'schema_outdated';
        }

        return 'provider_error';
    }

    public static function isMissingGoogleOAuthSchema(QueryException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'google_sub')
            || (str_contains($message, 'Unknown column') && str_contains($message, 'clients'));
    }
}
