# Elasticsearch: Scout (or minimal client), mapping, async sync

## Epic / tracking

- **Program:** PostgreSQL + Elasticsearch search platform.

## Dependencies

- **Requires:** Stable `products` (and related) schema and searchable fields defined in prior tasks; **SearchService** for PostgreSQL may exist in parallel but ES integration should **not** hard-depend on it for indexing.

## Goal

Add **production-ready** Elasticsearch indexing for products:

- **Package choice:** Prefer **Laravel Scout** with an **Elasticsearch** driver **if** it fits Laravel version and maintenance expectations; otherwise use a **thin, justified** official Elasticsearch PHP client wrapper — document the decision in **`CHANGELOG.md`** / task notes.
- **Index mapping:**
  - Text fields with appropriate **analyzer** (standard or language-aware baseline).
  - **`completion`** (or **search-as-you-type**) field for **autocomplete** suggestions.
- **Sync:**
  - On create/update/delete → **queue job** async indexing (use existing Laravel queue stack).
  - Idempotent upserts; handle delete tombstones.

## Scope

1. **Configuration:** `.env.example` keys (hosts, index prefix, optional API keys); `config/scout.php` or dedicated `config/elasticsearch.php`.
2. **Model:** `Product` (and variants if needed) — `Searchable` implementation or repository that pushes documents.
3. **Jobs:** `MakeSearchable` / custom jobs; failed-job handling notes.
4. **Local/dev:** Docker Compose snippet or **docs** pointer only — do not commit secrets.

## Constraints

- **Do not** break environments **without** Elasticsearch: app boot and tests must allow **disabling** ES (null driver / `SCOUT_DRIVER=log` / feature flag).
- **Avoid unnecessary packages:** justify each dependency.

## Out of scope

- HTTP search endpoint and PostgreSQL fallback → next FEAT.

## Verification (before **UNTESTED-**)

- Tests that run **without** a live ES cluster: mock driver, or `log`/`null` driver, asserting job dispatch / mapping builder where feasible.
- Optional **marked** integration test (skipped by default) if the team uses `ES_TEST_HOST` env.

## Implementation notes (coder)

- **Scout 11** with **`elasticsearch/elasticsearch` v8** and **`App\Scout\ElasticsearchEngine`** (no third-party Scout ES driver: `matchish/laravel-scout-elasticsearch` does not support Scout 11).
- **`config/scout.php`**: `SCOUT_DRIVER` defaults to **`null`**; Elasticsearch hosts/auth under `scout.elasticsearch`; product index mappings in `scout.elasticsearch.index_definitions.products` (`standard` text + **`completion`** `suggest`).
- **`Product`**: `Searchable`, `shouldBeSearchable()` = `is_active`, `toSearchableArray()` for catalog fields + suggest payload.
- **Queue:** Laravel Scout’s **`MakeSearchable`** / **`RemoveFromSearch`** when **`SCOUT_QUEUE=true`** (same stack as other jobs; failures → `failed_jobs`).
- **Docs:** `docs/elasticsearch.md` (Docker one-liner, env vars, `scout:index` / `scout:import`).

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh`.
2. `php artisan test` — expect `ProductScoutIndexingTest`, `ScoutElasticsearchMappingTest` green; `ProductElasticsearchScoutIntegrationTest` skipped unless `ES_TEST_HOST` is set.
3. `php artisan routes:smoke` — no HTTP 500.
4. **With Elasticsearch (optional):** set `SCOUT_DRIVER=elasticsearch`, `ELASTICSEARCH_HOSTS`, run `php artisan scout:index "App\Models\Product"` then `php artisan scout:import "App\Models\Product"`; verify document count in ES (e.g. `_cat/indices` or Kibana).
5. **Failed jobs:** misconfigure host or stop ES, save a product with `SCOUT_QUEUE=true` and a worker running; confirm job fails and appears in `failed_jobs` (then fix env and retry).
