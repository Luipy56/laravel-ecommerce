<?php

namespace Tests\Unit;

use App\Exceptions\GoogleOAuthException;
use App\Support\Auth\GoogleOAuthErrorMapper;
use Illuminate\Database\QueryException;
use Laravel\Socialite\Two\InvalidStateException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GoogleOAuthErrorMapperTest extends TestCase
{
    #[DataProvider('exceptionMappingProvider')]
    public function test_maps_throwables_to_oauth_error_codes(object $exception, string $expectedCode): void
    {
        $this->assertSame($expectedCode, GoogleOAuthErrorMapper::errorCodeFrom($exception));
    }

    public static function exceptionMappingProvider(): array
    {
        return [
            'google oauth exception' => [new GoogleOAuthException('email_not_verified'), 'email_not_verified'],
            'invalid state' => [new InvalidStateException, 'session_expired'],
            'missing google_sub column' => [
                new QueryException(
                    'mysql',
                    'select * from `clients` where `google_sub` = ? limit 1',
                    [],
                    new \PDOException("SQLSTATE[42S22]: Column not found: 1054 Unknown column 'google_sub'")
                ),
                'schema_outdated',
            ],
            'generic failure' => [new \RuntimeException('boom'), 'provider_error'],
        ];
    }
}
