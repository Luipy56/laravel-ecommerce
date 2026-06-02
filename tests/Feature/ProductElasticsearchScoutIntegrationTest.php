<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Scout\ElasticsearchClientFactory;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Hits a real Elasticsearch node when ES_TEST_HOST is set (e.g. http://127.0.0.1:9200).
 */
class ProductElasticsearchScoutIntegrationTest extends TestCase
{
    public function test_index_search_and_delete_document(): void
    {
        $host = env('ES_TEST_HOST');
        if (! is_string($host) || trim($host) === '') {
            $this->markTestSkipped('Set ES_TEST_HOST to run Elasticsearch integration (see docs/elasticsearch.md).');
        }

        $client = ElasticsearchClientFactory::make(array_merge(
            config('scout.elasticsearch', []),
            ['hosts' => [rtrim(trim($host), '/')]],
        ));

        $index = 'phpunit_products_'.Str::lower(Str::random(10));
        $definition = config('scout.elasticsearch.index_definitions.products');
        $this->assertIsArray($definition);

        $client->indices()->create([
            'index' => $index,
            'body' => $definition,
        ]);

        try {
            $client->bulk([
                'body' => [
                    ['index' => ['_index' => $index, '_id' => '42']],
                    [
                        'id' => 42,
                        'code' => 'INT-42',
                        'is_active' => true,
                        'name_ca' => 'Integration cylinder',
                        'name_es' => 'Integration cylinder',
                        'name_en' => 'Integration cylinder',
                        'description_ca' => '',
                        'description_es' => '',
                        'description_en' => '',
                        'search_text_ca' => 'integration cylinder int-42',
                        'search_text_es' => 'integration cylinder int-42',
                        'search_text_en' => 'integration cylinder int-42',
                        'suggest' => [
                            'input' => ['Integration cylinder', 'INT-42'],
                            'weight' => 1,
                        ],
                    ],
                ],
                'refresh' => true,
            ]);

            $response = $client->search([
                'index' => $index,
                'body' => [
                    'query' => [
                        'multi_match' => [
                            'query' => 'cylinder',
                            'fields' => [
                                'name_ca^2',
                                'name_es^2',
                                'name_en^2',
                                'code^2',
                                'search_text_ca',
                                'search_text_es',
                                'search_text_en',
                                'description_ca',
                                'description_es',
                                'description_en',
                            ],
                        ],
                    ],
                    'size' => 5,
                ],
            ]);

            $hits = $response->asArray()['hits']['hits'] ?? [];
            $this->assertNotEmpty($hits);
            $this->assertSame('42', (string) ($hits[0]['_id'] ?? ''));
        } finally {
            try {
                $client->indices()->delete(['index' => $index]);
            } catch (ClientResponseException $e) {
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }
    }
}
