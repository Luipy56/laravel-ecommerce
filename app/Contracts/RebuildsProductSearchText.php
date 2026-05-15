<?php

namespace App\Contracts;

/**
 * Recomputes {@see \App\Models\ProductTranslation::search_text} for rows not going through Eloquent saves (e.g. raw inserts, imports).
 */
interface RebuildsProductSearchText
{
    /**
     * @return int Number of translation rows updated
     */
    public function rebuildAll(int $chunkSize = 100): int;

    /**
     * Rows where {@see \App\Models\ProductTranslation::search_text} is null or differs from normalized name, code, and description for that locale.
     *
     * @return int Number of translation rows updated
     */
    public function rebuildStale(int $chunkSize = 100): int;
}
