<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL serial/identity sequences drift when seeders reset them while rows
 * already exist (e.g. migrations after migrate:fresh) or when RefreshDatabase rolls
 * back rows but not sequence counters.
 */
final class PostgresSequenceHelper
{
    /**
     * Restart auto-increment sequences to 1 for seeding after migrate:fresh.
     * Skips the migrations table — its rows are created during migrate, before seed.
     */
    public static function restartForSeeding(): void
    {
        $sequences = DB::select(
            "SELECT c.relname FROM pg_class c WHERE c.relkind = 'S' AND c.relname <> 'migrations_id_seq'"
        );

        foreach ($sequences as $seq) {
            DB::statement("ALTER SEQUENCE \"{$seq->relname}\" RESTART WITH 1");
        }
    }

    /**
     * Align every serial sequence with MAX(column) on its owning table.
     *
     * @return int Number of sequences adjusted
     */
    public static function syncAll(): int
    {
        $links = DB::select(
            <<<'SQL'
            SELECT
                seq.relname AS sequence_name,
                tbl.relname AS table_name,
                col.attname AS column_name
            FROM pg_class seq
            JOIN pg_depend dep ON dep.objid = seq.oid AND dep.deptype = 'a'
            JOIN pg_class tbl ON dep.refobjid = tbl.oid
            JOIN pg_attribute col ON col.attrelid = tbl.oid AND col.attnum = dep.refobjsubid
            WHERE seq.relkind = 'S'
            SQL
        );

        $adjusted = 0;

        foreach ($links as $link) {
            $max = DB::table($link->table_name)->max($link->column_name);

            if ($max === null) {
                DB::statement("ALTER SEQUENCE \"{$link->sequence_name}\" RESTART WITH 1");
            } else {
                DB::statement(
                    'SELECT setval(?, ?::bigint)',
                    ["{$link->sequence_name}", (int) $max]
                );
            }

            $adjusted++;
        }

        return $adjusted;
    }
}
