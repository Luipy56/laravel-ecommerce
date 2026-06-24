<?php

namespace Tests\Feature;

use App\Support\KeyColorSchemaHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReconcileKeyColorSchemaCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconcile_command_is_no_op_on_fresh_database(): void
    {
        $this->artisan('db:reconcile-key-color-schema')
            ->expectsOutputToContain('Key color schema already up to date.')
            ->assertSuccessful();
    }

    public function test_reconcile_records_migration_when_key_colors_table_exists_without_row(): void
    {
        $this->assertTrue(Schema::hasTable('key_colors'));
        $this->assertTrue(KeyColorSchemaHelper::migrationRan(KeyColorSchemaHelper::KEY_COLORS_MIGRATION));

        DB::table('migrations')
            ->where('migration', KeyColorSchemaHelper::KEY_COLORS_MIGRATION)
            ->delete();

        $this->assertFalse(KeyColorSchemaHelper::migrationRan(KeyColorSchemaHelper::KEY_COLORS_MIGRATION));

        $this->artisan('db:reconcile-key-color-schema')
            ->expectsOutputToContain('Recorded migration for existing key_colors table')
            ->assertSuccessful();

        $this->assertTrue(KeyColorSchemaHelper::migrationRan(KeyColorSchemaHelper::KEY_COLORS_MIGRATION));
    }

    public function test_reconcile_creates_key_color_translations_when_missing(): void
    {
        $this->assertTrue(Schema::hasTable('key_color_translations'));

        Schema::dropIfExists('key_color_translations');

        $this->assertFalse(Schema::hasTable('key_color_translations'));

        $this->artisan('db:reconcile-key-color-schema')
            ->expectsOutputToContain('Created key_color_translations table')
            ->assertSuccessful();

        $this->assertTrue(Schema::hasTable('key_color_translations'));
    }

    public function test_reconcile_adds_order_line_key_color_columns_when_missing(): void
    {
        $this->assertTrue(Schema::hasColumn('order_lines', 'key_color_id'));

        Schema::table('order_lines', function ($table) {
            $table->dropForeign(['key_color_id']);
            $table->dropColumn(['key_color_id', 'key_color_rgb', 'key_color_name']);
        });

        $this->assertFalse(Schema::hasColumn('order_lines', 'key_color_id'));

        $this->artisan('db:reconcile-key-color-schema')
            ->expectsOutputToContain('Added key color columns to order_lines')
            ->assertSuccessful();

        $this->assertTrue(Schema::hasColumn('order_lines', 'key_color_id'));
        $this->assertTrue(Schema::hasColumn('order_lines', 'key_color_rgb'));
        $this->assertTrue(Schema::hasColumn('order_lines', 'key_color_name'));
    }

    public function test_reconcile_skips_order_line_columns_when_key_colors_table_missing(): void
    {
        $this->assertTrue(Schema::hasTable('order_lines'));
        $this->assertTrue(Schema::hasColumn('order_lines', 'key_color_id'));

        Schema::table('order_lines', function ($table) {
            $table->dropForeign(['key_color_id']);
            $table->dropColumn(['key_color_id', 'key_color_rgb', 'key_color_name']);
        });

        Schema::dropIfExists('key_color_translations');
        Schema::dropIfExists('key_colors');

        DB::table('migrations')
            ->where('migration', KeyColorSchemaHelper::KEY_COLORS_MIGRATION)
            ->delete();

        $this->assertFalse(Schema::hasTable('key_colors'));
        $this->assertFalse(Schema::hasColumn('order_lines', 'key_color_id'));

        $this->artisan('db:reconcile-key-color-schema')
            ->expectsOutputToContain('Key color schema already up to date.')
            ->assertSuccessful();

        $this->assertFalse(Schema::hasColumn('order_lines', 'key_color_id'));
    }
}
