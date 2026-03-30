# Elasticsearch (product catalog indexing)

This app uses **Laravel Scout** with a **custom Elasticsearch engine** (`App\Scout\ElasticsearchEngine`) backed by the official [`elasticsearch/elasticsearch`](https://github.com/elastic/elasticsearch-php) PHP client. Packaged Scout drivers checked at implementation time did not yet support **Scout 11**, so the engine stays small and owned by the repo.

## When Elasticsearch is off

- Set **`SCOUT_DRIVER=null`** (default in `config/scout.php` and PHPUnit).
- The application boots normally; indexing calls are no-ops on the **null** engine.

## Enabling Elasticsearch

1. Run an Elasticsearch **8.x** node (Docker example: `docker run -p 9200:9200 -e "discovery.type=single-node" -e "xpack.security.enabled=false" docker.elastic.co/elasticsearch/elasticsearch:8.12.2`).
2. In `.env`:
   - `SCOUT_DRIVER=elasticsearch`
   - `ELASTICSEARCH_HOSTS=http://127.0.0.1:9200` (comma-separated for multiple nodes)
   - Optional auth: `ELASTICSEARCH_API_KEY` or `ELASTICSEARCH_USERNAME` / `ELASTICSEARCH_PASSWORD`
3. Create the product index with mappings (includes a **`completion`** field for future autocomplete):

   ```bash
   php artisan scout:index "App\Models\Product"
   ```

4. Initial or full reindex:

   ```bash
   php artisan scout:import "App\Models\Product"
   ```

5. For async indexing in production, set **`SCOUT_QUEUE=true`** and run a queue worker. Failed jobs surface in the `failed_jobs` table like any other queued job.

## Index mapping

Mappings for the `products` table index are defined under **`config/scout.php`** → `scout.elasticsearch.index_definitions.products`. Adjust there if you add searchable fields (keep in sync with `Product::toSearchableArray()`).

## Optional integration test

With a reachable node, set **`ES_TEST_HOST`** (e.g. same as `ELASTICSEARCH_HOSTS`) so `ProductElasticsearchScoutIntegrationTest` runs; without it, that test is skipped.
