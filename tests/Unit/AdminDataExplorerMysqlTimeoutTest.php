<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AdminDataExplorerService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdminDataExplorerMysqlTimeoutTest extends TestCase
{
    #[DataProvider('mysqlFamilyTimeoutProvider')]
    public function test_mysql_family_timeout_statement_resolves_expected_set_session_sql(
        string $version,
        int $seconds,
        ?string $expectedSql,
    ): void {
        $svc = new class extends AdminDataExplorerService
        {
            public function expose(string $versionString, int $seconds): ?string
            {
                return $this->mysqlFamilyTimeoutStatement($versionString, $seconds);
            }
        };

        $this->assertSame($expectedSql, $svc->expose($version, $seconds));
    }

    /**
     * @return iterable<string, array{0: string, 1: int, 2: ?string}>
     */
    public static function mysqlFamilyTimeoutProvider(): iterable
    {
        yield 'mariadb uses max_statement_time seconds' => [
            '10.11.8-MariaDB-deb12',
            25,
            'SET SESSION max_statement_time = 25',
        ];

        yield 'mysql 8 uses max_execution_time milliseconds' => [
            '8.0.35',
            10,
            'SET SESSION max_execution_time = 10000',
        ];

        yield 'mysql 8.0.3 first release with max_execution_time' => [
            '8.0.3',
            25,
            'SET SESSION max_execution_time = 25000',
        ];

        yield 'mysql 8.0.2 has no session max_execution_time' => [
            '8.0.2',
            25,
            null,
        ];

        yield 'mysql 5.7 has no session guard' => [
            '5.7.44-log',
            25,
            null,
        ];

        yield 'mysql 9.x still uses max_execution_time' => [
            '9.1.0',
            25,
            'SET SESSION max_execution_time = 25000',
        ];
    }
}
