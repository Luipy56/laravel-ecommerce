<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent fixes for key-color schema when long-lived databases ran parent
 * migrations before key_colors / translations / order_lines columns were added.
 */
final class KeyColorSchemaHelper
{
    public const KEY_COLORS_MIGRATION = '2026_02_24_095138_create_key_colors_table';

    /**
     * @return list<string> Human-readable actions taken
     */
    public static function reconcile(): array
    {
        $actions = [];

        if (Schema::hasTable('key_colors') && ! self::migrationRan(self::KEY_COLORS_MIGRATION)) {
            self::recordMigration(self::KEY_COLORS_MIGRATION);
            $actions[] = 'Recorded migration for existing key_colors table';
        }

        if (Schema::hasTable('key_colors') && ! Schema::hasTable('key_color_translations')) {
            self::createKeyColorTranslationsTable();
            $actions[] = 'Created key_color_translations table';
        }

        if (Schema::hasTable('key_colors') && Schema::hasTable('order_lines') && self::ensureOrderLineKeyColorColumns()) {
            $actions[] = 'Added key color columns to order_lines';
        }

        return $actions;
    }

    public static function migrationRan(string $migration): bool
    {
        return DB::table('migrations')->where('migration', $migration)->exists();
    }

    public static function recordMigration(string $migration): void
    {
        if (self::migrationRan($migration)) {
            return;
        }

        $batch = (int) DB::table('migrations')->max('batch');

        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch > 0 ? $batch : 1,
        ]);
    }

    private static function createKeyColorTranslationsTable(): void
    {
        Schema::create('key_color_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_color_id')->constrained('key_colors')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 255)->nullable();
            $table->timestamps();
            $table->unique(['key_color_id', 'locale']);
        });

        self::addLocaleCheck('key_color_translations');
    }

    private static function addLocaleCheck(string $table): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_locale_check CHECK (locale in ('ca','es','en'))");
        }

        if ($driver === 'sqlite') {
            DB::statement("CREATE TRIGGER {$table}_locale_insert BEFORE INSERT ON {$table} FOR EACH ROW WHEN NEW.locale NOT IN ('ca','es','en') BEGIN SELECT RAISE(ABORT, 'invalid locale'); END");
            DB::statement("CREATE TRIGGER {$table}_locale_update BEFORE UPDATE ON {$table} FOR EACH ROW WHEN NEW.locale NOT IN ('ca','es','en') BEGIN SELECT RAISE(ABORT, 'invalid locale'); END");
        }
    }

    private static function ensureOrderLineKeyColorColumns(): bool
    {
        $added = false;

        Schema::table('order_lines', function (Blueprint $table) use (&$added) {
            if (! Schema::hasColumn('order_lines', 'key_color_id')) {
                $table->foreignId('key_color_id')->nullable()->constrained('key_colors')->nullOnDelete();
                $added = true;
            }

            if (! Schema::hasColumn('order_lines', 'key_color_rgb')) {
                $table->string('key_color_rgb', 7)->nullable()->comment('Snapshot at checkout');
                $added = true;
            }

            if (! Schema::hasColumn('order_lines', 'key_color_name')) {
                $table->string('key_color_name', 255)->nullable()->comment('Localized snapshot at checkout');
                $added = true;
            }
        });

        return $added;
    }
}
