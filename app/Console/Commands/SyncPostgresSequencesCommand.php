<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\PostgresSequenceHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPostgresSequencesCommand extends Command
{
    protected $signature = 'db:sync-postgres-sequences';

    protected $description = 'Align PostgreSQL serial sequences with table MAX(id) values (safe before migrate on long-lived databases)';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->comment('Skipped: not a PostgreSQL connection.');

            return self::SUCCESS;
        }

        $count = PostgresSequenceHelper::syncAll();
        $this->info("Synced {$count} PostgreSQL sequence(s).");

        return self::SUCCESS;
    }
}
