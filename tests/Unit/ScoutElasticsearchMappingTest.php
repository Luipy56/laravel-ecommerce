<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

class ScoutElasticsearchMappingTest extends TestCase
{
    public function test_products_index_defines_completion_suggest_and_text_fields(): void
    {
        $def = config('scout.elasticsearch.index_definitions.products');

        $this->assertIsArray($def);
        $this->assertSame('completion', $def['mappings']['properties']['suggest']['type']);
        $this->assertSame('text', $def['mappings']['properties']['name']['type']);
        $this->assertSame('standard', $def['mappings']['properties']['name']['analyzer']);
    }
}
