<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Search\SearchSynonymDictionary;
use Tests\TestCase;

class ScoutElasticsearchMappingTest extends TestCase
{
    public function test_products_index_defines_completion_suggest_and_text_fields(): void
    {
        $def = config('scout.elasticsearch.index_definitions.products');

        $this->assertIsArray($def);
        $this->assertSame('completion', $def['mappings']['properties']['suggest']['type']);
        $this->assertSame('text', $def['mappings']['properties']['name_ca']['type']);
        $this->assertSame('standard', $def['mappings']['properties']['name_ca']['analyzer']);
        $this->assertSame('text', $def['mappings']['properties']['search_text_es']['type']);
    }

    public function test_synonym_overlay_switches_text_fields_to_product_synonym_analyzer(): void
    {
        $base = config('scout.elasticsearch.index_definitions.products');
        $dict = new SearchSynonymDictionary([
            'enabled' => true,
            'max_expansions_per_token' => 10,
            'groups' => [['notebook', 'laptop']],
        ]);
        $merged = array_replace_recursive($base, $dict->elasticsearchIndexOverlay());

        $this->assertSame('product_synonym', $merged['mappings']['properties']['search_text_ca']['analyzer']);
        $this->assertSame('completion', $merged['mappings']['properties']['suggest']['type']);
    }
}
