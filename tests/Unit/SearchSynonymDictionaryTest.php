<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Search\SearchSynonymDictionary;
use Tests\TestCase;

class SearchSynonymDictionaryTest extends TestCase
{
    public function test_expand_token_slots_merges_group_members(): void
    {
        $dict = new SearchSynonymDictionary([
            'enabled' => true,
            'max_expansions_per_token' => 10,
            'groups' => [['notebook', 'laptop']],
        ]);

        $slots = $dict->expandTokenSlots(['notebook']);

        $this->assertSame([['laptop', 'notebook']], $slots);
    }

    public function test_disabled_or_empty_groups_is_identity_expansion(): void
    {
        $dict = new SearchSynonymDictionary([
            'enabled' => false,
            'max_expansions_per_token' => 10,
            'groups' => [['a', 'b']],
        ]);

        $this->assertSame([['only']], $dict->expandTokenSlots(['only']));

        $empty = new SearchSynonymDictionary([
            'enabled' => true,
            'groups' => [],
        ]);
        $this->assertSame([['x']], $empty->expandTokenSlots(['x']));
    }

    public function test_elasticsearch_synonym_lines_and_overlay(): void
    {
        $dict = new SearchSynonymDictionary([
            'enabled' => true,
            'max_expansions_per_token' => 10,
            'groups' => [['notebook', 'laptop']],
        ]);

        $this->assertSame(['laptop, notebook'], $dict->elasticsearchSynonymLines());

        $overlay = $dict->elasticsearchIndexOverlay();
        $this->assertArrayHasKey('settings', $overlay);
        $this->assertSame(
            'synonym_graph',
            $overlay['settings']['analysis']['filter']['product_synonyms']['type']
        );
        $this->assertSame(
            'product_synonym',
            $overlay['mappings']['properties']['search_text_ca']['analyzer']
        );
    }

    public function test_max_expansions_per_token_truncates_group(): void
    {
        $dict = new SearchSynonymDictionary([
            'enabled' => true,
            'max_expansions_per_token' => 2,
            'groups' => [['a', 'b', 'c', 'd']],
        ]);

        $variants = $dict->expandTokenSlots(['a'])[0];
        $this->assertCount(2, $variants);
    }
}
