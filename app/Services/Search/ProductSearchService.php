<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Models\Product;
use App\Support\CatalogLocale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL-first product search over {@see ProductTranslation::search_text} for the active UI locale.
 * Typo tolerance uses pg_trgm; other drivers use case-insensitive token LIKE only.
 */
final class ProductSearchService
{
    /** @var array{limit: int, word_similarity_threshold: float, similarity_threshold: float} */
    private array $config;

    private SearchSynonymDictionary $synonyms;

    public function __construct(?array $config = null, ?SearchSynonymDictionary $synonyms = null)
    {
        $this->config = $config ?? config('product_search', []);
        $this->synonyms = $synonyms ?? new SearchSynonymDictionary(config('search_synonyms', []));
    }

    /**
     * @return Collection<int, Product>
     */
    public function search(string $query, ?string $locale = null): Collection
    {
        $locale = CatalogLocale::normalize($locale ?? app()->getLocale());
        $normalized = $this->normalizeQuery($query);
        if ($normalized === '') {
            return collect();
        }

        $tokens = $this->tokens($normalized);
        if ($tokens === []) {
            return collect();
        }

        $tokenSlots = $this->synonyms->expandTokenSlots($tokens);

        return match (DB::getDriverName()) {
            'pgsql' => $this->searchPostgres($normalized, $tokenSlots, $locale),
            default => $this->searchLikeFallback($normalized, $tokenSlots, $locale),
        };
    }

    public function normalizeQuery(string $input): string
    {
        return Product::normalizeSearchText($input, '', '');
    }

    /**
     * @return list<string>
     */
    private function tokens(string $normalized): array
    {
        $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : array_values($parts);
    }

    /**
     * @param  list<list<string>>  $tokenSlots
     * @return Collection<int, Product>
     */
    private function searchPostgres(string $normalizedFull, array $tokenSlots, string $locale): Collection
    {
        $limit = (int) ($this->config['limit'] ?? 50);
        $wsMin = (float) ($this->config['word_similarity_threshold'] ?? 0.35);
        $simMin = (float) ($this->config['similarity_threshold'] ?? 0.12);

        $prefixPattern = $this->escapeIlikePattern($normalizedFull).'%';
        $containsPattern = '%'.$this->escapeIlikePattern($normalizedFull).'%';

        $tokenWheres = [];
        $bindings = [];

        foreach ($tokenSlots as $variants) {
            $orParts = [];
            foreach ($variants as $token) {
                if ($token === '') {
                    continue;
                }
                $orParts[] = '(pt.search_text ILIKE ? OR word_similarity(?, pt.search_text) >= ? OR similarity(pt.search_text, ?) >= ? OR products.code ILIKE ?)';
                $bindings[] = '%'.$this->escapeIlikePattern($token).'%';
                $bindings[] = $token;
                $bindings[] = $wsMin;
                $bindings[] = $token;
                $bindings[] = $simMin;
                $bindings[] = '%'.$this->escapeIlikePattern($token).'%';
            }
            if ($orParts === []) {
                continue;
            }
            $tokenWheres[] = '('.implode(' OR ', $orParts).')';
        }

        if ($tokenWheres === []) {
            return collect();
        }

        $whereTokens = implode(' AND ', $tokenWheres);

        $sql = <<<SQL
SELECT products.id,
    (CASE WHEN pt.search_text = ? THEN 1000 ELSE 0 END)
    + (CASE WHEN pt.search_text ILIKE ? THEN 800 ELSE 0 END)
    + (CASE WHEN pt.search_text ILIKE ? THEN 400 ELSE 0 END)
    + (100 * COALESCE(word_similarity(?, pt.search_text), 0)::double precision)
    + (50 * COALESCE(similarity(pt.search_text, ?), 0)::double precision)
    + (CASE WHEN products.code ILIKE ? THEN 200 ELSE 0 END)
    AS search_rank
FROM products
INNER JOIN product_translations pt ON pt.product_id = products.id AND pt.locale = ?
WHERE products.is_active = true
AND (
    (pt.search_text IS NOT NULL AND pt.search_text <> '')
    OR (products.code ILIKE ?)
)
AND ({$whereTokens})
ORDER BY search_rank DESC, products.id ASC
LIMIT ?
SQL;

        $headBindings = [
            $normalizedFull,
            $prefixPattern,
            $containsPattern,
            $normalizedFull,
            $normalizedFull,
            $containsPattern,
            $locale,
            $containsPattern,
        ];

        $allBindings = array_merge($headBindings, $bindings, [$limit]);

        /** @var list<object{id: int|string, search_rank: float|string}> $rows */
        $rows = DB::select($sql, $allBindings);

        return $this->hydrateByIdOrder($rows);
    }

    /**
     * @param  list<list<string>>  $tokenSlots
     * @return Collection<int, Product>
     */
    private function searchLikeFallback(string $normalizedFull, array $tokenSlots, string $locale): Collection
    {
        $limit = (int) ($this->config['limit'] ?? 50);

        $q = Product::query()->active()
            ->join('product_translations as pt', function ($join) use ($locale): void {
                $join->on('pt.product_id', '=', 'products.id')->where('pt.locale', '=', $locale);
            })
            ->where(function ($outer) use ($normalizedFull): void {
                $outer->whereNotNull('pt.search_text')->where('pt.search_text', '!=', '')
                    ->orWhere('products.code', 'like', '%'.$this->escapeLike($normalizedFull).'%');
            })
            ->select('products.*');

        foreach ($tokenSlots as $variants) {
            $variants = array_values(array_filter($variants, fn (string $t): bool => $t !== ''));
            if ($variants === []) {
                continue;
            }
            $q->where(function ($sub) use ($variants, $normalizedFull): void {
                foreach ($variants as $token) {
                    $sub->orWhere(function ($x) use ($token, $normalizedFull): void {
                        $x->whereRaw('instr(pt.search_text, ?) > 0', [$token])
                            ->orWhere('products.code', 'like', '%'.$this->escapeLike($token).'%');
                    });
                }
            });
        }

        $esc = fn (string $s): string => $this->escapeLike($s);
        $q->orderByRaw(
            'CASE WHEN pt.search_text = ? THEN 0 WHEN pt.search_text LIKE ? ESCAPE ? THEN 1 WHEN pt.search_text LIKE ? ESCAPE ? THEN 2 ELSE 3 END',
            [
                $normalizedFull,
                $esc($normalizedFull).'%',
                '\\',
                '%'.$esc($normalizedFull).'%',
                '\\',
            ]
        );
        $q->orderBy('products.id');

        return $q->limit($limit)->get();
    }

    /**
     * @param  list<object{id: int|string, search_rank: float|string}>  $rows
     * @return Collection<int, Product>
     */
    private function hydrateByIdOrder(array $rows): Collection
    {
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row->id;
        }

        if ($ids === []) {
            return collect();
        }

        $products = Product::query()->whereIn('id', $ids)->with('translations')->get()->keyBy('id');

        return collect($ids)
            ->map(fn (int $id) => $products->get($id))
            ->filter()
            ->values();
    }

    private function escapeIlikePattern(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
