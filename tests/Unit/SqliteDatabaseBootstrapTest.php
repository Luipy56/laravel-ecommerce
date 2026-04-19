<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\SqliteDatabaseBootstrap;
use PHPUnit\Framework\TestCase;

final class SqliteDatabaseBootstrapTest extends TestCase
{
    private string $scratchDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scratchDir = sys_get_temp_dir().'/laravel-ecommerce-sqlite-bootstrap-test-'.uniqid('', true);
        mkdir($this->scratchDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->scratchDir)) {
            $files = glob($this->scratchDir.'/*');
            foreach ($files ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->scratchDir);
        }
        parent::tearDown();
    }

    public function test_creates_sqlite_file_when_missing_and_not_production(): void
    {
        $path = $this->scratchDir.'/new.sqlite';

        SqliteDatabaseBootstrap::touchDatabaseFileIfMissing('local', [
            'driver' => 'sqlite',
            'database' => $path,
        ]);

        $this->assertFileExists($path);
    }

    public function test_skips_creation_in_production(): void
    {
        $path = $this->scratchDir.'/prod.sqlite';

        SqliteDatabaseBootstrap::touchDatabaseFileIfMissing('production', [
            'driver' => 'sqlite',
            'database' => $path,
        ]);

        $this->assertFileDoesNotExist($path);
    }

    public function test_skips_memory_database(): void
    {
        SqliteDatabaseBootstrap::touchDatabaseFileIfMissing('local', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->assertSame([], glob($this->scratchDir.'/*') ?: []);
    }

    public function test_skips_non_sqlite_connections(): void
    {
        $path = $this->scratchDir.'/pgsql.sqlite';

        SqliteDatabaseBootstrap::touchDatabaseFileIfMissing('local', [
            'driver' => 'pgsql',
            'database' => $path,
        ]);

        $this->assertFileDoesNotExist($path);
    }
}
