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
    public function rebuildAll(): int;
}
