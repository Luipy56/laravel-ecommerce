<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCategoryTranslation;
use App\Models\ProductImage;
use App\Support\CatalogTranslationSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Import competitor scrape JSONL (+ local images) into the catalog.
 * Does not invent prices, names, or translations — scraped Spanish text is
 * copied into ca/es/en. Skips rows with missing name/price or duplicate code/URL.
 */
class ImportScrapedProductsCommand extends Command
{
    private const MAX_IMAGE_BYTES = 5 * 1024 * 1024;

    private const MIN_FREE_BYTES = 2 * 1024 * 1024 * 1024;

    private const SITE_CODE_PREFIX = [
        'seguridadbilma' => 'BIL',
        'keylaseguridad' => 'KEY',
    ];

    protected $signature = 'products:import-scrape
                            {--jsonl= : Path to products.jsonl}
                            {--images-root= : Root directory that contains relative image paths from JSONL (e.g. scrape repo root)}
                            {--dry-run : Map and report without writing}
                            {--limit=0 : Max products to import (0 = no limit)}';

    protected $description = 'Import scraped competitor products from JSONL into the catalog';

    /** @var array<string, int> */
    private array $categoryCache = [];

    private int $added = 0;

    private int $skipped = 0;

    private int $imageCopied = 0;

    private int $imageSkipped = 0;

    private int $categoriesCreated = 0;

    /** @var list<string> */
    private array $skipReasons = [];

    public function handle(): int
    {
        $jsonl = (string) $this->option('jsonl');
        if ($jsonl === '' || ! is_readable($jsonl)) {
            $this->error('Provide a readable --jsonl path.');

            return self::FAILURE;
        }

        $imagesRoot = (string) ($this->option('images-root') ?: dirname($jsonl, 2));
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));

        $copyImages = true;
        $uploadsRoot = Storage::disk('uploads')->path('');
        $free = @disk_free_space($uploadsRoot !== '' ? $uploadsRoot : sys_get_temp_dir());
        if ($free !== false && $free < self::MIN_FREE_BYTES) {
            $this->warn(sprintf(
                'Free space %.1f GB < 2 GB — skipping image files (competitor_url still stored).',
                $free / (1024 ** 3)
            ));
            $copyImages = false;
        } else {
            $this->info(sprintf(
                'Uploads free space: %s',
                $free === false ? 'unknown' : sprintf('%.1f GB', $free / (1024 ** 3))
            ));
        }

        $this->warmCategoryCache();

        $handle = fopen($jsonl, 'rb');
        if ($handle === false) {
            $this->error("Cannot open {$jsonl}");

            return self::FAILURE;
        }

        $lineNo = 0;
        while (($line = fgets($handle)) !== false) {
            $lineNo++;
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($limit > 0 && $this->added >= $limit) {
                break;
            }

            try {
                /** @var array<string, mixed> $row */
                $row = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->skip('line '.$lineNo.': invalid JSON ('.$e->getMessage().')');
                continue;
            }

            $this->importRow($row, $imagesRoot, $copyImages, $dryRun);
        }
        fclose($handle);

        $this->newLine();
        $this->info(sprintf(
            'Done. added=%d skipped=%d images_copied=%d images_skipped=%d categories_created=%d dry_run=%s',
            $this->added,
            $this->skipped,
            $this->imageCopied,
            $this->imageSkipped,
            $this->categoriesCreated,
            $dryRun ? 'yes' : 'no'
        ));
        if ($this->skipReasons !== []) {
            $counts = array_count_values($this->skipReasons);
            arsort($counts);
            $this->line('Skip reasons:');
            foreach (array_slice($counts, 0, 15, true) as $reason => $n) {
                $this->line("  {$n}× {$reason}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function importRow(array $row, string $imagesRoot, bool $copyImages, bool $dryRun): void
    {
        $name = html_entity_decode(trim((string) ($row['name'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $price = $row['price'] ?? null;
        $sourceUrl = trim((string) ($row['source_url'] ?? ''));
        $siteId = trim((string) ($row['site_id'] ?? ''));
        $sku = trim((string) ($row['sku'] ?? ''));
        $description = html_entity_decode((string) ($row['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        /** @var list<string> $categories */
        $categories = array_values(array_filter(array_map(
            static fn ($c) => trim((string) $c),
            is_array($row['categories'] ?? null) ? $row['categories'] : []
        )));

        if ($name === '') {
            $this->skip('missing name');

            return;
        }
        if (! is_numeric($price)) {
            $this->skip('missing price');

            return;
        }
        $price = round((float) $price, 2);
        if ($price < 0) {
            $this->skip('negative price');

            return;
        }
        if ($sourceUrl === '') {
            $this->skip('missing source_url');

            return;
        }

        $code = $this->buildCode($siteId, $sku, $sourceUrl);
        if (Product::query()->where('code', $code)->exists()) {
            $this->skip('duplicate code');

            return;
        }
        if (Product::query()->where('competitor_url', $sourceUrl)->exists()) {
            $this->skip('duplicate competitor_url');

            return;
        }

        $categoryId = $this->resolveCategoryId($categories, $dryRun, $name);

        if ($dryRun) {
            $this->added++;
            /** @var list<array{local_path?: string}> $images */
            $images = is_array($row['images'] ?? null) ? $row['images'] : [];
            foreach ($images as $img) {
                $local = (string) ($img['local_path'] ?? '');
                if ($local === '') {
                    continue;
                }
                $abs = $this->resolveImagePath($imagesRoot, $local);
                if ($abs === null) {
                    $this->imageSkipped++;
                    continue;
                }
                if ($copyImages) {
                    $this->imageCopied++;
                } else {
                    $this->imageSkipped++;
                }
            }

            return;
        }

        DB::transaction(function () use (
            $name,
            $description,
            $price,
            $sourceUrl,
            $code,
            $categoryId,
            $row,
            $imagesRoot,
            $copyImages
        ): void {
            $product = Product::create([
                'category_id' => $categoryId,
                'variant_group_id' => null,
                'code' => $code,
                'price' => $price,
                'discount_percent' => null,
                'purchase_price' => null,
                'stock' => 0,
                'weight_kg' => null,
                'is_double_clutch' => false,
                'has_card' => false,
                'security_level' => null,
                'competitor_url' => $sourceUrl,
                'is_extra_keys_available' => false,
                'extra_key_unit_price' => null,
                'is_featured' => false,
                'is_trending' => false,
                'is_active' => true,
            ]);

            CatalogTranslationSync::syncProductTranslations($product, [
                'ca' => ['name' => $name, 'description' => $description !== '' ? $description : null],
                'es' => ['name' => $name, 'description' => $description !== '' ? $description : null],
                'en' => ['name' => $name, 'description' => $description !== '' ? $description : null],
            ]);

            if ($copyImages) {
                $this->copyImages($product, $row, $imagesRoot);
            }

            $this->added++;
        });
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function copyImages(Product $product, array $row, string $imagesRoot): void
    {
        /** @var list<array{local_path?: string, url?: string, is_primary?: bool}> $images */
        $images = is_array($row['images'] ?? null) ? $row['images'] : [];
        usort($images, static function (array $a, array $b): int {
            return ((int) empty($a['is_primary'])) <=> ((int) empty($b['is_primary']));
        });

        $sort = 0;
        foreach ($images as $img) {
            $local = (string) ($img['local_path'] ?? '');
            if ($local === '') {
                $this->imageSkipped++;
                continue;
            }
            $abs = $this->resolveImagePath($imagesRoot, $local);
            if ($abs === null) {
                $this->imageSkipped++;
                continue;
            }
            $size = filesize($abs);
            if ($size === false || $size <= 0 || $size > self::MAX_IMAGE_BYTES) {
                $this->imageSkipped++;
                continue;
            }

            $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION) ?: 'jpg');
            $filename = sprintf('%03d.%s', $sort + 1, $ext);
            $storagePath = 'products/'.$product->id.'/'.$filename;
            $contents = file_get_contents($abs);
            if ($contents === false) {
                $this->imageSkipped++;
                continue;
            }
            Storage::disk('uploads')->put($storagePath, $contents);

            $sort++;
            ProductImage::create([
                'product_id' => $product->id,
                'storage_path' => $storagePath,
                'filename' => $filename,
                'size_bytes' => $size,
                'checksum' => hash('sha256', $contents),
                'content_type' => $this->guessMime($ext),
                'sort_order' => $sort,
                'is_active' => true,
            ]);
            $this->imageCopied++;
        }
    }

    private function resolveImagePath(string $imagesRoot, string $localPath): ?string
    {
        $candidates = [
            rtrim($imagesRoot, '/').'/'.ltrim($localPath, '/'),
            rtrim($imagesRoot, '/').'/'.ltrim(preg_replace('#^data/#', '', $localPath) ?? $localPath, '/'),
        ];
        // JSONL paths are often relative to scrape repo root: data/images/...
        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $labels
     */
    private function resolveCategoryId(array $labels, bool $dryRun, string $productName = ''): int
    {
        $haystack = mb_strtolower(trim(implode(' ', $labels).' '.$productName), 'UTF-8');
        $usefulLabels = array_values(array_filter(
            $labels,
            static fn (string $label): bool => ! self::isJunkCategoryLabel($label)
        ));

        $mapped = $this->matchCategoryCode($haystack);
        if ($mapped !== null) {
            return $this->categoryIdByCode($mapped);
        }

        $label = $usefulLabels[0] ?? null;
        if ($label === null || self::isJunkCategoryLabel($label)) {
            // Last resort: existing generic cylinders bucket — still real category, not invented text.
            return $this->categoryIdByCode('cilindros');
        }

        $code = $this->slugCategoryCode($label);
        if (isset($this->categoryCache[$code])) {
            return $this->categoryCache[$code] > 0
                ? $this->categoryCache[$code]
                : $this->categoryIdByCode('cilindros');
        }

        if ($dryRun) {
            $this->categoriesCreated++;
            $this->categoryCache[$code] = -1;

            return $this->categoryIdByCode('cilindros');
        }

        $category = ProductCategory::create([
            'code' => $code,
            'is_active' => true,
        ]);
        CatalogTranslationSync::syncCategoryTranslations($category, [
            'ca' => ['name' => $label],
            'es' => ['name' => $label],
            'en' => ['name' => $label],
        ]);
        $this->categoryCache[$code] = (int) $category->id;
        $this->categoriesCreated++;

        return (int) $category->id;
    }

    private function matchCategoryCode(string $haystack): ?string
    {
        if ($haystack === '') {
            return null;
        }
        $rules = [
            'cilindros' => ['cilindro', 'cylinder', 'bombin', 'bombillo'],
            'escudo' => ['escudo', 'shield', 'protector'],
            'segundo-cerrojo' => ['cerrojo', 'cerradura', 'pestillo', 'cerradero', 'cierre electrico', 'cierre eléctrico'],
        ];
        foreach ($rules as $code => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($haystack, $needle)) {
                    return $code;
                }
            }
        }

        return null;
    }

    private static function isJunkCategoryLabel(string $label): bool
    {
        $normalized = mb_strtolower(trim($label), 'UTF-8');
        if ($normalized === '') {
            return true;
        }

        return in_array($normalized, [
            'inicio',
            'home',
            'novedades',
            'new',
            'news',
            'tienda',
            'shop',
            'catalogo',
            'catálogo',
            'productos',
            'products',
        ], true);
    }

    private function categoryIdByCode(string $code): int
    {
        if (isset($this->categoryCache[$code])) {
            return $this->categoryCache[$code];
        }
        $id = ProductCategory::query()->where('code', $code)->value('id');
        if ($id === null) {
            throw new \RuntimeException("Missing product category code: {$code}");
        }
        $this->categoryCache[$code] = (int) $id;

        return (int) $id;
    }

    private function warmCategoryCache(): void
    {
        foreach (ProductCategory::query()->get(['id', 'code']) as $cat) {
            $this->categoryCache[(string) $cat->code] = (int) $cat->id;
        }
    }

    private function slugCategoryCode(string $label): string
    {
        $slug = Str::slug(Str::limit($label, 40, ''), '-');
        if ($slug === '') {
            $slug = 'importado';
        }
        $base = Str::limit($slug, 50, '');
        $code = $base;
        $i = 2;
        while (
            isset($this->categoryCache[$code])
            || ProductCategory::query()->where('code', $code)->exists()
        ) {
            $suffix = '-'.$i;
            $code = Str::limit($base, 50 - strlen($suffix), '').$suffix;
            $i++;
        }

        return $code;
    }

    private function buildCode(string $siteId, string $sku, string $sourceUrl): string
    {
        $prefix = self::SITE_CODE_PREFIX[$siteId] ?? strtoupper(Str::limit(preg_replace('/[^a-z0-9]/i', '', $siteId) ?: 'IMP', 3, ''));
        $raw = $sku !== '' ? $sku : ('u'.substr(sha1($sourceUrl), 0, 10));
        $sanitized = strtoupper(preg_replace('/[^A-Za-z0-9._-]+/', '-', $raw) ?? $raw);
        $sanitized = trim($sanitized, '-');
        if ($sanitized === '') {
            $sanitized = 'u'.substr(sha1($sourceUrl), 0, 10);
        }
        $code = $prefix.'-'.$sanitized;

        return Str::limit($code, 50, '');
    }

    private function guessMime(string $ext): string
    {
        return match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
    }

    private function skip(string $reason): void
    {
        $this->skipped++;
        $this->skipReasons[] = $reason;
    }
}
