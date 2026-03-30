<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ReindexElasticsearchProductsCommandTest extends TestCase
{
    public function test_skips_when_scout_driver_is_not_elasticsearch(): void
    {
        config(['scout.driver' => 'null']);

        $this->artisan('products:reindex-elasticsearch')
            ->expectsOutputToContain('Skipping Elasticsearch reindex')
            ->assertExitCode(0);
    }

    public function test_skips_when_elasticsearch_hosts_empty(): void
    {
        config([
            'scout.driver' => 'elasticsearch',
            'scout.elasticsearch.hosts' => [],
        ]);

        $this->artisan('products:reindex-elasticsearch')
            ->expectsOutputToContain('Skipping Elasticsearch reindex')
            ->assertExitCode(0);
    }
}
