<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Ensures an on-disk SQLite database file exists before Laravel opens the PDO connection.
 *
 * Laravel's SQLite driver errors when DB_DATABASE points at a missing path (common with
 * DB_DATABASE=/tmp/….sqlite overrides). Creating an empty file matches `touch path.sqlite`
 * before migrate / artisan commands.
 */
final class SqliteDatabaseBootstrap
{
    /**
     * @param  array<string, mixed>  $connection
     */
    public static function touchDatabaseFileIfMissing(string $environment, array $connection): void
    {
        if ($environment === 'production') {
            return;
        }

        if (($connection['driver'] ?? null) !== 'sqlite') {
            return;
        }

        $database = $connection['database'] ?? null;
        if (! is_string($database) || $database === '' || $database === ':memory:') {
            return;
        }

        if (file_exists($database)) {
            return;
        }

        $directory = dirname($database);
        if (! is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        if (! is_dir($directory) || ! is_writable($directory)) {
            return;
        }

        @touch($database);
    }
}
