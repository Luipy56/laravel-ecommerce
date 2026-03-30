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

## Synonyms (shared with PostgreSQL)

- **Config:** `config/search_synonyms.php` — `groups` are synonym sets (each inner list is bidirectional). Terms are normalized like `search_text` (lowercase, diacritic folding when intl is available). Optional env: `SEARCH_SYNONYMS_ENABLED`, `SEARCH_SYNONYMS_MAX_EXPANSIONS` (caps variants per token on the SQL side).
- **Elasticsearch:** On boot, non-empty groups merge a `synonym_graph` filter and a `product_synonym` analyzer into the live Scout index definition for text fields (`name`, `code`, `description`, `search_text`). The **`suggest`** completion field stays on the default analyzer.
- **After changing synonyms:** delete/recreate the index (or run `php artisan scout:flush` + `scout:index` / `scout:import` as appropriate for your workflow) so settings apply, then re-import documents. Full reindex automation may be covered by the search tooling Artisan FEAT; until then, use the Scout commands above.

Multilingual index strategy (future per-locale fields) is described in **`config/search_locales.php`** only; product rows still use a single `search_text` blob today.

## Optional integration test

With a reachable node, set **`ES_TEST_HOST`** (e.g. same as `ELASTICSEARCH_HOSTS`) so `ProductElasticsearchScoutIntegrationTest` runs; without it, that test is skipped.
