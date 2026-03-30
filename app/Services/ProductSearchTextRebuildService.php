<?php

namespace App\Services;

use App\Contracts\RebuildsProductSearchText;
use App\Models\Product;

class ProductSearchTextRebuildService implements RebuildsProductSearchText
{
    public function rebuildAll(int $chunkSize = 100): int
    {
        $chunkSize = max(1, $chunkSize);
        $updated = 0;
        Product::query()->orderBy('id')->chunkById($chunkSize, function ($products) use (&$updated): void {
            foreach ($products as $product) {
                $product->search_text = Product::normalizeSearchText(
                    $product->name,
                    $product->code,
                    $product->description
                );
                $product->saveQuietly();
                $updated++;
            }
        });

        return $updated;
    }

    public function rebuildStale(int $chunkSize = 100): int
    {
        $chunkSize = max(1, $chunkSize);
        $updated = 0;
        Product::query()->orderBy('id')->chunkById($chunkSize, function ($products) use (&$updated): void {
            foreach ($products as $product) {
                $expected = Product::normalizeSearchText(
                    $product->name,
                    $product->code,
                    $product->description
                );
                $current = $product->search_text;
                if ($current !== null && (string) $current === $expected) {
                    continue;
                }
                $product->search_text = $expected;
                $product->saveQuietly();
                $updated++;
            }
        });

        return $updated;
    }
}
