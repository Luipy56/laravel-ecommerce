<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SyncPostgresSequencesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_postgres_sequences_command_succeeds(): void
    {
        $command = $this->artisan('db:sync-postgres-sequences');

        if (DB::connection()->getDriverName() === 'pgsql') {
            $command->expectsOutputToContain('Synced');
        } else {
            $command->expectsOutputToContain('Skipped: not a PostgreSQL connection.');
        }

        $command->assertSuccessful();
    }
}
