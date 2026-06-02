<?php

namespace App\Services;

use App\Contracts\RebuildsProductSearchText;
use App\Models\Product;
use App\Models\ProductTranslation;

class ProductSearchTextRebuildService implements RebuildsProductSearchText
{
    public function rebuildAll(int $chunkSize = 100): int
    {
        $chunkSize = max(1, $chunkSize);
        $updated = 0;
        Product::query()->with('translations')->orderBy('id')->chunkById($chunkSize, function ($products) use (&$updated): void {
            foreach ($products as $product) {
                foreach ($product->translations as $t) {
                    $expected = Product::normalizeSearchText(
                        $t->name,
                        $product->code,
                        $t->description
                    );
                    if ((string) ($t->search_text ?? '') !== $expected) {
                        ProductTranslation::query()->whereKey($t->id)->update([
                            'search_text' => $expected,
                            'updated_at' => now(),
                        ]);
                        $updated++;
                    }
                }
                if ($product->shouldBeSearchable()) {
                    $product->searchable();
                }
            }
        });

        return $updated;
    }

    public function rebuildStale(int $chunkSize = 100): int
    {
        $chunkSize = max(1, $chunkSize);
        $updated = 0;
        Product::query()->with('translations')->orderBy('id')->chunkById($chunkSize, function ($products) use (&$updated): void {
            foreach ($products as $product) {
                foreach ($product->translations as $t) {
                    $expected = Product::normalizeSearchText(
                        $t->name,
                        $product->code,
                        $t->description
                    );
                    $current = $t->search_text;
                    if ($current !== null && (string) $current === $expected) {
                        continue;
                    }
                    ProductTranslation::query()->whereKey($t->id)->update([
                        'search_text' => $expected,
                        'updated_at' => now(),
                    ]);
                    $updated++;
                }
                if ($product->shouldBeSearchable()) {
                    $product->searchable();
                }
            }
        });

        return $updated;
    }
}
