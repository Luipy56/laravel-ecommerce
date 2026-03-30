<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Scout\ElasticsearchEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Laravel\Scout\EngineManager;

/**
 * Operator-friendly full reindex: wraps Scout flush/index/import with optional index recreation.
 */
class ReindexElasticsearchProductsCommand extends Command
{
    protected $signature = 'products:reindex-elasticsearch
                            {--fresh : Remove all documents from the index before importing (keeps index settings)}
                            {--recreate : Delete the Elasticsearch index and recreate mappings before importing}
                            {--chunk= : Import chunk size (default: scout.chunk.searchable)}
                            {--queued : Dispatch scout:queue-import jobs instead of synchronous scout:import}';

    protected $description = 'Flush or recreate the product Elasticsearch index and run Scout import (no-op when Scout driver is not elasticsearch)';

    public function handle(EngineManager $manager): int
    {
        if (config('scout.driver') !== 'elasticsearch') {
            $this->warn('Skipping Elasticsearch reindex: SCOUT_DRIVER is not "elasticsearch".');

            return self::SUCCESS;
        }

        $hosts = config('scout.elasticsearch.hosts', []);
        if (! is_array($hosts) || $hosts === [] || trim((string) ($hosts[0] ?? '')) === '') {
            $this->warn('Skipping Elasticsearch reindex: scout.elasticsearch.hosts is empty.');

            return self::SUCCESS;
        }

        $modelClass = Product::class;
        $chunkOpt = $this->option('chunk');
        $importParams = ['model' => $modelClass];
        if ($chunkOpt !== null && $chunkOpt !== '') {
            $importParams['--chunk'] = max(1, (int) $chunkOpt);
        }

        $product = new Product;
        $indexName = $product->indexableAs();

        if ($this->option('recreate')) {
            $engine = $manager->engine();
            if (! $engine instanceof ElasticsearchEngine) {
                $this->error('The current Scout engine does not support index recreation (expected Elasticsearch).');

                return self::FAILURE;
            }
            $this->info("Deleting index [{$indexName}] if it exists…");
            $engine->deleteIndex($indexName);
            $this->info('Creating index with current mappings…');
            Artisan::call('scout:index', ['name' => $modelClass]);
            $this->output->write(Artisan::output());
        }

        if ($this->option('queued')) {
            if ($this->option('fresh') && ! $this->option('recreate')) {
                $this->info('Flushing existing documents from the index…');
                Artisan::call('scout:flush', ['model' => $modelClass]);
                $this->output->write(Artisan::output());
            }
            $this->info('Queueing import jobs…');
            Artisan::call('scout:queue-import', $importParams);
        } else {
            $importArgs = $importParams;
            if ($this->option('fresh') && ! $this->option('recreate')) {
                $importArgs['--fresh'] = true;
            }
            $this->info('Importing products into Elasticsearch…');
            Artisan::call('scout:import', $importArgs);
        }
        $this->output->write(Artisan::output());

        $this->info('Done.');
        if ($this->option('queued') || (bool) config('scout.queue', false)) {
            $this->comment('If SCOUT_QUEUE is true, run a queue worker until indexing jobs finish.');
        }

        return self::SUCCESS;
    }
}
