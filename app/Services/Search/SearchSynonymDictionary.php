<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Models\Product;

/**
 * Loads synonym groups from config and expands query tokens for PostgreSQL.
 * The same groups produce Elasticsearch synonym_graph lines (single source of truth).
 */
final class SearchSynonymDictionary
{
    /** @var list<list<string>> */
    private array $normalizedGroups;

    /** @var array<string, list<string>> token => sorted unique variants (bounded) */
    private array $tokenToVariants;

    private bool $enabled;

    /** @param  array<string, mixed>  $config  search_synonyms config array */
    public function __construct(array $config)
    {
        $max = max(1, (int) ($config['max_expansions_per_token'] ?? 10));
        $this->enabled = (bool) ($config['enabled'] ?? true);
        $this->normalizedGroups = $this->normalizeGroups(
            is_array($config['groups'] ?? null) ? $config['groups'] : [],
            $max,
        );
        $this->tokenToVariants = $this->buildTokenMap($this->normalizedGroups, $max);
    }

    public function enabled(): bool
    {
        return $this->enabled && $this->normalizedGroups !== [];
    }

    /**
     * @param  list<string>  $normalizedTokens
     * @return list<list<string>> one variant list per original token (OR within slot, AND across slots)
     */
    public function expandTokenSlots(array $normalizedTokens): array
    {
        if (! $this->enabled()) {
            return array_map(fn (string $t): array => [$t], $normalizedTokens);
        }

        $out = [];
        foreach ($normalizedTokens as $token) {
            $out[] = $this->tokenToVariants[$token] ?? [$token];
        }

        return $out;
    }

    /**
     * Elasticsearch synonym_graph lines (Solr format): "a, b, c".
     *
     * @return list<string>
     */
    public function elasticsearchSynonymLines(): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $lines = [];
        foreach ($this->normalizedGroups as $group) {
            if (count($group) >= 2) {
                $lines[] = implode(', ', $group);
            }
        }

        return $lines;
    }

    /**
     * Index settings + mapping overrides when synonyms are active; empty otherwise.
     *
     * @return array<string, mixed>
     */
    public function elasticsearchIndexOverlay(): array
    {
        $synonyms = $this->elasticsearchSynonymLines();
        if ($synonyms === []) {
            return [];
        }

        return [
            'settings' => [
                'analysis' => [
                    'filter' => [
                        'product_synonyms' => [
                            'type' => 'synonym_graph',
                            'synonyms' => $synonyms,
                        ],
                    ],
                    'analyzer' => [
                        'product_synonym' => [
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase', 'product_synonyms'],
                        ],
                    ],
                ],
            ],
            'mappings' => [
                'properties' => [
                    'name' => ['type' => 'text', 'analyzer' => 'product_synonym'],
                    'code' => ['type' => 'text', 'analyzer' => 'product_synonym'],
                    'description' => ['type' => 'text', 'analyzer' => 'product_synonym'],
                    'search_text' => ['type' => 'text', 'analyzer' => 'product_synonym'],
                ],
            ],
        ];
    }

    /**
     * @param  list<list<string|int|float>>  $groups
     * @return list<list<string>>
     */
    private function normalizeGroups(array $groups, int $max): array
    {
        $out = [];
        foreach ($groups as $group) {
            if (! is_array($group) || $group === []) {
                continue;
            }
            $normalized = [];
            foreach ($group as $raw) {
                if (! is_string($raw) && ! is_int($raw) && ! is_float($raw)) {
                    continue;
                }
                $term = Product::normalizeSearchText((string) $raw, '', '');
                if ($term === '' || str_contains($term, ',')) {
                    continue;
                }
                $normalized[$term] = $term;
            }
            $members = array_values($normalized);
            if (count($members) < 2) {
                continue;
            }
            sort($members, SORT_STRING);
            $out[] = array_slice($members, 0, $max);
        }

        return $out;
    }

    /**
     * @param  list<list<string>>  $normalizedGroups
     * @return array<string, list<string>>
     */
    private function buildTokenMap(array $normalizedGroups, int $max): array
    {
        /** @var array<string, array<string, true>> $acc */
        $acc = [];

        foreach ($normalizedGroups as $group) {
            foreach ($group as $term) {
                if (! isset($acc[$term])) {
                    $acc[$term] = [];
                }
                foreach ($group as $other) {
                    $acc[$term][$other] = true;
                }
            }
        }

        /** @var array<string, list<string>> $map */
        $map = [];
        foreach ($acc as $term => $set) {
            $list = array_keys($set);
            sort($list, SORT_STRING);
            $map[$term] = array_slice($list, 0, $max);
        }

        return $map;
    }
}
