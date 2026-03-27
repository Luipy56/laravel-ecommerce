<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SqliteMemorySessionDriverTest extends TestCase
{
    public function test_session_driver_is_array_when_default_sqlite_database_is_memory(): void
    {
        $sessionConnection = config('session.connection') ?: config('database.default');
        $conn = config("database.connections.{$sessionConnection}");

        if (($conn['driver'] ?? null) !== 'sqlite' || ($conn['database'] ?? null) !== ':memory:') {
            $this->markTestSkipped('Requires SQLite :memory: as session DB connection (default PHPUnit setup).');
        }

        $this->assertSame(
            'array',
            config('session.driver'),
            'Database sessions require a migrated sessions table; :memory: must use array driver.'
        );
    }
}
