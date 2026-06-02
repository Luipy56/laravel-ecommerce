<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalog translatable strings: one row per parent entity and locale (ca|es|en).
     */
    public function up(): void
    {
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('search_text')->nullable()->comment('Normalized name+code+description for this locale only (catalog search)');
            $table->timestamps();
            $table->unique(['product_id', 'locale']);
        });
        $this->addLocaleCheck('product_translations');
        $this->addProductTranslationSearchIndex('product_translations');

        Schema::create('product_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 255)->nullable();
            $table->timestamps();
            $table->unique(['product_category_id', 'locale']);
        });
        $this->addLocaleCheck('product_category_translations');

        Schema::create('pack_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->constrained('packs')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('search_text')->nullable()->comment('Normalized name+description for this locale only (catalog search)');
            $table->timestamps();
            $table->unique(['pack_id', 'locale']);
        });
        $this->addLocaleCheck('pack_translations');
        $this->addPackTranslationSearchIndex('pack_translations');

        Schema::create('feature_name_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_name_id')->constrained('feature_names')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 255)->nullable();
            $table->timestamps();
            $table->unique(['feature_name_id', 'locale']);
        });
        $this->addLocaleCheck('feature_name_translations');

        Schema::create('feature_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('value', 255)->nullable();
            $table->timestamps();
            $table->unique(['feature_id', 'locale']);
        });
        $this->addLocaleCheck('feature_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_translations');
        Schema::dropIfExists('feature_name_translations');
        Schema::dropIfExists('pack_translations');
        Schema::dropIfExists('product_category_translations');
        Schema::dropIfExists('product_translations');
    }

    private function addLocaleCheck(string $table): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_locale_check CHECK (locale in ('ca','es','en'))");
        }
        if ($driver === 'sqlite') {
            // SQLite tests: enforce allowed locales (MySQL would ignore CHECK historically; project targets PostgreSQL).
            DB::statement("CREATE TRIGGER {$table}_locale_insert BEFORE INSERT ON {$table} FOR EACH ROW WHEN NEW.locale NOT IN ('ca','es','en') BEGIN SELECT RAISE(ABORT, 'invalid locale'); END");
            DB::statement("CREATE TRIGGER {$table}_locale_update BEFORE UPDATE ON {$table} FOR EACH ROW WHEN NEW.locale NOT IN ('ca','es','en') BEGIN SELECT RAISE(ABORT, 'invalid locale'); END");
        }
    }

    private function addProductTranslationSearchIndex(string $table): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("CREATE INDEX idx_{$table}_search_text_trgm ON {$table} USING gin (search_text gin_trgm_ops)");
        }
    }

    private function addPackTranslationSearchIndex(string $table): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("CREATE INDEX idx_{$table}_search_text_trgm ON {$table} USING gin (search_text gin_trgm_ops)");
        }
    }
};
