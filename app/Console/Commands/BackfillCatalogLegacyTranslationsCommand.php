<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Feature;
use App\Models\FeatureName;
use App\Models\Pack;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\CatalogLocale;
use App\Support\CatalogTranslationSync;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Copies legacy monolingual columns (when still present) into *_translations rows.
 * Use after adding translation tables to an older database that was migrated before parent tables dropped name/description/value.
 */
class BackfillCatalogLegacyTranslationsCommand extends Command
{
    protected $signature = 'catalog:backfill-legacy-translations
                            {--dry-run : Print counts only, do not write}';

    protected $description = 'Backfill product_category_translations, product_translations, pack_translations, feature_name_translations, feature_translations from legacy name/description/value columns when they still exist; also fills empty feature name labels from config/catalog_feature_name_labels.php when codes exist';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $nCat = $this->backfillCategories($dry);
        $nProd = $this->backfillProducts($dry);
        $nPack = $this->backfillPacks($dry);
        $nFn = $this->backfillFeatureNames($dry);
        $nFeat = $this->backfillFeatures($dry);

        $this->info("Categories: {$nCat}, products: {$nProd}, packs: {$nPack}, feature_names: {$nFn}, features: {$nFeat}".($dry ? ' (dry-run)' : ''));

        if (! $dry && ($nProd > 0 || $nCat > 0)) {
            Artisan::call('products:rebuild-search-text');
            $this->info('Ran products:rebuild-search-text.');
        }

        return self::SUCCESS;
    }

    private function backfillCategories(bool $dry): int
    {
        if (! Schema::hasTable('product_categories') || ! Schema::hasColumn('product_categories', 'name')) {
            return 0;
        }

        $n = 0;
        foreach (DB::table('product_categories')->select('id', 'name')->cursor() as $row) {
            $name = is_string($row->name) ? trim($row->name) : '';
            if ($name === '') {
                continue;
            }
            $id = (int) $row->id;
            if ($dry) {
                $n++;

                continue;
            }
            $cat = ProductCategory::query()->find($id);
            if ($cat === null) {
                continue;
            }
            $by = [];
            foreach (CatalogLocale::SUPPORTED as $loc) {
                $by[$loc] = ['name' => $name];
            }
            CatalogTranslationSync::syncCategoryTranslations($cat, $by);
            $n++;
        }

        return $n;
    }

    private function backfillProducts(bool $dry): int
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'name')) {
            return 0;
        }

        $n = 0;
        foreach (DB::table('products')->select('id', 'name', 'description')->cursor() as $row) {
            $name = is_string($row->name) ? trim($row->name) : '';
            $desc = isset($row->description) && is_string($row->description) ? $row->description : null;
            if ($name === '' && ($desc === null || trim($desc) === '')) {
                continue;
            }
            $id = (int) $row->id;
            if ($dry) {
                $n++;

                continue;
            }
            $product = Product::query()->find($id);
            if ($product === null) {
                continue;
            }
            $by = [];
            foreach (CatalogLocale::SUPPORTED as $loc) {
                $by[$loc] = [
                    'name' => $name !== '' ? $name : null,
                    'description' => $desc !== null && trim($desc) !== '' ? $desc : null,
                ];
            }
            CatalogTranslationSync::syncProductTranslations($product, $by);
            $n++;
        }

        return $n;
    }

    private function backfillPacks(bool $dry): int
    {
        if (! Schema::hasTable('packs') || ! Schema::hasColumn('packs', 'name')) {
            return 0;
        }

        $n = 0;
        foreach (DB::table('packs')->select('id', 'name', 'description')->cursor() as $row) {
            $name = is_string($row->name) ? trim($row->name) : '';
            $desc = isset($row->description) && is_string($row->description) ? $row->description : null;
            if ($name === '' && ($desc === null || trim($desc) === '')) {
                continue;
            }
            $id = (int) $row->id;
            if ($dry) {
                $n++;

                continue;
            }
            $pack = Pack::query()->find($id);
            if ($pack === null) {
                continue;
            }
            $by = [];
            foreach (CatalogLocale::SUPPORTED as $loc) {
                $by[$loc] = [
                    'name' => $name !== '' ? $name : null,
                    'description' => $desc !== null && trim($desc) !== '' ? $desc : null,
                ];
            }
            CatalogTranslationSync::syncPackTranslations($pack, $by);
            $n++;
        }

        return $n;
    }

    private function backfillFeatureNames(bool $dry): int
    {
        if (! Schema::hasTable('feature_names')) {
            return 0;
        }

        if (! Schema::hasColumn('feature_names', 'code')) {
            if ($dry) {
                $this->warn('[dry-run] feature_names.code is missing; run without --dry-run to add a nullable code column, then re-run with --dry-run to count rows.');

                return 0;
            }
            Schema::table('feature_names', function (Blueprint $table): void {
                $table->string('code', 64)->nullable();
            });
            $this->info('Added nullable feature_names.code for legacy backfill.');
        }

        $n = 0;
        if (Schema::hasColumn('feature_names', 'name')) {
            $n += $this->backfillFeatureNamesFromLegacyNameColumn($dry);
        }

        $n += $this->backfillFeatureNamesFromKnownCodes($dry);

        return $n;
    }

    /**
     * Legacy DBs may still have a monolingual feature_names.name column.
     */
    private function backfillFeatureNamesFromLegacyNameColumn(bool $dry): int
    {
        $n = 0;
        foreach (DB::table('feature_names')->select('id', 'name', 'code')->cursor() as $row) {
            $label = is_string($row->name) ? trim($row->name) : '';
            if ($label === '') {
                continue;
            }
            $id = (int) $row->id;
            if ($dry) {
                $n++;

                continue;
            }
            $fn = FeatureName::query()->find($id);
            if ($fn === null) {
                continue;
            }
            $code = isset($row->code) && is_string($row->code) ? trim($row->code) : '';
            if ($code === '') {
                $base = Str::slug($label) ?: 'feature';
                $code = substr($base, 0, 60);
                $suffix = 0;
                while (FeatureName::query()->where('code', $code)->where('id', '!=', $id)->exists()) {
                    $suffix++;
                    $code = substr($base, 0, 50).'-'.$suffix;
                }
                $fn->forceFill(['code' => $code])->saveQuietly();
            }
            $by = [];
            foreach (CatalogLocale::SUPPORTED as $loc) {
                $by[$loc] = ['name' => $label];
            }
            CatalogTranslationSync::syncFeatureNameTranslations($fn->fresh(), $by);
            $n++;
        }

        return $n;
    }

    /**
     * When codes exist but feature_name_translations are empty, fill from config.
     *
     * @return int Number of feature_names rows updated (or counted in dry-run)
     */
    private function backfillFeatureNamesFromKnownCodes(bool $dry): int
    {
        /** @var array<string, array<string, string>> $defaults */
        $defaults = config('catalog_feature_name_labels', []);
        if ($defaults === []) {
            return 0;
        }

        $n = 0;
        foreach (FeatureName::query()->with('translations')->cursor() as $fn) {
            if (! $this->featureNameHasNoDisplayableTranslation($fn)) {
                continue;
            }
            $code = trim((string) ($fn->code ?? ''));
            if ($code === '' || ! isset($defaults[$code])) {
                continue;
            }
            if ($dry) {
                $n++;

                continue;
            }
            $labelsByLocale = $defaults[$code];
            $by = [];
            foreach (CatalogLocale::SUPPORTED as $loc) {
                $label = $labelsByLocale[$loc] ?? $labelsByLocale['es'] ?? $labelsByLocale['ca'] ?? null;
                $labelStr = is_string($label) ? trim($label) : '';
                if ($labelStr !== '') {
                    $by[$loc] = ['name' => $labelStr];
                }
            }
            if ($by !== []) {
                CatalogTranslationSync::syncFeatureNameTranslations($fn->fresh(), $by);
                $n++;
            }
        }

        return $n;
    }

    private function featureNameHasNoDisplayableTranslation(FeatureName $fn): bool
    {
        foreach ($fn->translations as $t) {
            $v = $t->name;
            if (is_string($v) && trim($v) !== '') {
                return false;
            }
        }

        return true;
    }

    private function backfillFeatures(bool $dry): int
    {
        if (! Schema::hasTable('features') || ! Schema::hasColumn('features', 'value')) {
            return 0;
        }

        $n = 0;
        foreach (DB::table('features')->select('id', 'value')->cursor() as $row) {
            $value = is_string($row->value) ? trim($row->value) : (is_numeric($row->value) ? (string) $row->value : '');
            if ($value === '') {
                continue;
            }
            $id = (int) $row->id;
            if ($dry) {
                $n++;

                continue;
            }
            $feature = Feature::query()->find($id);
            if ($feature === null) {
                continue;
            }
            $by = [];
            foreach (CatalogLocale::SUPPORTED as $loc) {
                $by[$loc] = ['value' => $value];
            }
            CatalogTranslationSync::syncFeatureTranslations($feature, $by);
            $n++;
        }

        return $n;
    }
}
