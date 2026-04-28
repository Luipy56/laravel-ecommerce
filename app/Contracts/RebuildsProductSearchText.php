<?php

namespace App\Contracts;

/**
 * Recomputes {@see \App\Models\Product::$search_text} for rows not going through Eloquent saves (e.g. raw inserts, imports).
 */
interface RebuildsProductSearchText
{
    /**
     * @return int Number of rows updated
     */
    public function rebuildAll(int $chunkSize = 100): int;

    /**
     * Rows where {@see \App\Models\Product::$search_text} is null or differs from normalized name, code, and description.
     *
     * @return int Number of rows updated
     */
    public function rebuildStale(int $chunkSize = 100): int;
}
