<?php

namespace App\Services;

use App\Contracts\RebuildsProductSearchText;
use App\Models\Product;

class ProductSearchTextRebuildService implements RebuildsProductSearchText
{
    public function rebuildAll(): int
    {
        $updated = 0;
        Product::query()->orderBy('id')->chunkById(100, function ($products) use (&$updated): void {
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
}
