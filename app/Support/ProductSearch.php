<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Storefront product/pack search: normalization, Catalan/Spanish synonyms, optional fuzzy fallback.
 */
final class ProductSearch
{
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if ($value === '') {
            return '';
        }

        if (class_exists(\Transliterator::class)) {
            $tr = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($tr !== null) {
                $value = $tr->transliterate($value) ?? $value;
            }
        } else {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false && $converted !== '') {
                $value = mb_strtolower($converted, 'UTF-8');
            }
        }

        return $value;
    }

    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * @return list<string>
     */
    public static function searchVariants(string $raw): array
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }

        $normalized = self::normalize($trimmed);
        $synonyms = self::synonymExpandedTerms($normalized);

        $variants = array_values(array_unique(array_filter(
            array_merge([$trimmed, $normalized], $synonyms),
            static fn (string $v): bool => $v !== ''
        )));

        $max = max(4, (int) config('product_search.max_variants', 32));

        return array_slice($variants, 0, $max);
    }

    /**
     * @return list<string>
     */
    private static function synonymExpandedTerms(string $normalizedQuery): array
    {
        $words = preg_split('/\s+/u', $normalizedQuery, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $groups = config('product_search.synonym_groups', []);
        if (! is_array($groups) || $words === []) {
            return [];
        }

        $extra = [];
        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }
            $normalizedGroup = [];
            foreach ($group as $term) {
                $n = self::normalize((string) $term);
                if ($n !== '') {
                    $normalizedGroup[] = $n;
                }
            }
            $normalizedGroup = array_values(array_unique($normalizedGroup));
            if ($normalizedGroup === []) {
                continue;
            }

            $hit = false;
            foreach ($words as $w) {
                if (mb_strlen($w) < 3) {
                    continue;
                }
                if (in_array($w, $normalizedGroup, true)) {
                    $hit = true;
                    break;
                }
            }
            if ($hit) {
                foreach ($normalizedGroup as $g) {
                    $extra[] = $g;
                }
            }
        }

        return array_values(array_unique($extra));
    }

    public static function shouldAttemptFuzzy(string $raw): bool
    {
        if (! config('product_search.fuzzy_fallback_enabled', true)) {
            return false;
        }

        return mb_strlen(self::normalize($raw)) >= 4;
    }

    public static function applyStrictProductSearch(Builder $query, string $rawTerm): void
    {
        $variants = self::searchVariants($rawTerm);
        if ($variants === []) {
            return;
        }

        $query->where(function (Builder $outer) use ($variants): void {
            foreach ($variants as $variant) {
                $like = '%'.self::escapeLike($variant).'%';
                $outer->orWhere(function (Builder $q) use ($like): void {
                    $q->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhereHas('features', function (Builder $f) use ($like): void {
                            $f->where('value', 'like', $like)
                                ->orWhereHas('featureName', fn (Builder $n) => $n->where('name', 'like', $like));
                        });
                });
            }
        });
    }

    public static function applyStrictPackSearch(Builder $query, string $rawTerm): void
    {
        $variants = self::searchVariants($rawTerm);
        if ($variants === []) {
            return;
        }

        $query->where(function (Builder $outer) use ($variants): void {
            foreach ($variants as $variant) {
                $like = '%'.self::escapeLike($variant).'%';
                $outer->orWhere(function (Builder $q) use ($like): void {
                    $q->where('packs.name', 'like', $like)
                        ->orWhere('packs.description', 'like', $like)
                        ->orWhereHas('items.product', function (Builder $p) use ($like): void {
                            $p->where('name', 'like', $like)
                                ->orWhere('description', 'like', $like)
                                ->orWhere('code', 'like', $like)
                                ->orWhereHas('features', function (Builder $f) use ($like): void {
                                    $f->where('value', 'like', $like)
                                        ->orWhereHas('featureName', fn (Builder $n) => $n->where('name', 'like', $like));
                                });
                        });
                });
            }
        });
    }

    public static function applyFuzzyProductSearch(Builder $base, string $rawTerm): void
    {
        $normalized = self::normalize($rawTerm);
        if (mb_strlen($normalized) < 3) {
            $base->whereRaw('1 = 0');

            return;
        }

        $prefix = mb_substr($normalized, 0, 3);
        $like = '%'.self::escapeLike($prefix).'%';
        $limit = max(50, (int) config('product_search.fuzzy_candidate_limit', 400));

        $candidates = (clone $base)
            ->where(function (Builder $q) use ($like): void {
                $q->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('code', 'like', $like);
            })
            ->limit($limit)
            ->get(['id', 'name', 'description', 'code']);

        $ids = $candidates
            ->filter(fn ($p) => self::isFuzzyMatch($normalized, $p))
            ->pluck('id')
            ->all();

        if ($ids === []) {
            $base->whereRaw('1 = 0');

            return;
        }

        $base->whereIn('id', $ids);
    }

    public static function applyFuzzyPackSearch(Builder $base, string $rawTerm): void
    {
        $normalized = self::normalize($rawTerm);
        if (mb_strlen($normalized) < 3) {
            $base->whereRaw('1 = 0');

            return;
        }

        $prefix = mb_substr($normalized, 0, 3);
        $like = '%'.self::escapeLike($prefix).'%';
        $limit = max(50, (int) config('product_search.fuzzy_candidate_limit', 400));

        $candidates = (clone $base)
            ->with(['items.product'])
            ->where(function (Builder $q) use ($like): void {
                $q->where('packs.name', 'like', $like)
                    ->orWhere('packs.description', 'like', $like)
                    ->orWhereHas('items.product', function (Builder $p) use ($like): void {
                        $p->where('name', 'like', $like)
                            ->orWhere('description', 'like', $like)
                            ->orWhere('code', 'like', $like);
                    });
            })
            ->limit($limit)
            ->get(['id', 'name', 'description']);

        $ids = $candidates
            ->filter(fn ($p) => self::isFuzzyPackMatch($normalized, $p))
            ->pluck('id')
            ->all();

        if ($ids === []) {
            $base->whereRaw('1 = 0');

            return;
        }

        $base->whereIn('id', $ids);
    }

    /**
     * @param  \App\Models\Product  $p
     */
    private static function isFuzzyMatch(string $normalizedQuery, object $p): bool
    {
        $name = self::normalize((string) ($p->name ?? ''));
        $desc = self::normalize((string) ($p->description ?? ''));
        $code = self::normalize((string) ($p->code ?? ''));

        if (str_contains($name, $normalizedQuery)
            || str_contains($desc, $normalizedQuery)
            || str_contains($code, $normalizedQuery)) {
            return true;
        }

        return self::editDistanceAcceptable($normalizedQuery, $name)
            || self::editDistanceAcceptable($normalizedQuery, $desc)
            || self::editDistanceAcceptable($normalizedQuery, $code);
    }

    /**
     * @param  \App\Models\Pack  $p
     */
    private static function isFuzzyPackMatch(string $normalizedQuery, object $p): bool
    {
        $name = self::normalize((string) ($p->name ?? ''));
        $desc = self::normalize((string) ($p->description ?? ''));

        if (str_contains($name, $normalizedQuery) || str_contains($desc, $normalizedQuery)) {
            return true;
        }

        if (self::editDistanceAcceptable($normalizedQuery, $name)
            || self::editDistanceAcceptable($normalizedQuery, $desc)) {
            return true;
        }

        foreach ($p->items as $item) {
            $prod = $item->product;
            if ($prod === null) {
                continue;
            }
            $pn = self::normalize((string) $prod->name);
            $pd = self::normalize((string) ($prod->description ?? ''));
            $pc = self::normalize((string) ($prod->code ?? ''));
            if (str_contains($pn, $normalizedQuery)
                || str_contains($pd, $normalizedQuery)
                || str_contains($pc, $normalizedQuery)) {
                return true;
            }
            if (self::editDistanceAcceptable($normalizedQuery, $pn)) {
                return true;
            }
        }

        return false;
    }

    private static function editDistanceAcceptable(string $normalizedQuery, string $haystackNorm): bool
    {
        $q = self::toAsciiKey($normalizedQuery);
        $h = self::toAsciiKey($haystackNorm);
        if ($q === '' || $h === '') {
            return false;
        }

        $len = strlen($q);
        if ($len < 4) {
            return false;
        }

        $maxDist = $len <= 8 ? 2 : 3;
        if (strlen($h) <= 255 && strlen($q) <= 255 && levenshtein($q, $h) <= $maxDist) {
            return true;
        }

        foreach (explode(' ', $h) as $word) {
            if (strlen($word) < 4) {
                continue;
            }
            if (strlen($word) <= 255 && strlen($q) <= 255 && levenshtein($q, $word) <= $maxDist) {
                return true;
            }
        }

        return false;
    }

    private static function toAsciiKey(string $value): string
    {
        $value = self::normalize($value);

        return preg_replace('/[^a-z0-9]+/i', '', $value) ?? '';
    }
}
