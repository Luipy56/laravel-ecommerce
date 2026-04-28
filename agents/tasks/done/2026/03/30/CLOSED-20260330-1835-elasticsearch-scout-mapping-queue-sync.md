---
## Closing summary (TOP)

- **What happened:** Production-oriented Elasticsearch indexing for products was added using Laravel Scout 11, a custom Elasticsearch engine, and queue-driven sync.
- **What was done:** `elasticsearch/elasticsearch` v8, `App\Scout\ElasticsearchEngine`, Scout config with product mappings (text + completion suggest), `Product` as `Searchable` with `shouldBeSearchable()` / `toSearchableArray()`, Scout `MakeSearchable` / `RemoveFromSearch` when queued, plus docs as noted in the implementation section.
- **What was tested:** Tester ran `php artisan test` (`ProductScoutIndexingTest`, `ScoutElasticsearchMappingTest` green; `ProductElasticsearchScoutIntegrationTest` skipped without `ES_TEST_HOST`), full suite exit 0, and `php artisan routes:smoke` — no HTTP 500; optional live ES / failed-jobs steps not run — **overall PASS.**
- **Why closed:** All required automated checks passed; tester marked overall **PASS**.
- **Closed at (UTC):** 2026-03-31 10:31
---

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

---

## Test report

1. **Date/time (UTC) and log window:** Session started **2026-03-31T10:29:57Z**; verification completed within the same minute. Log window for evidence: **2026-03-31 10:29–10:30 UTC** (approx.).

2. **Environment:** PHP **8.3.6**, Node **v22.20.0**, branch **`agentdevelop`**. PHPUnit uses **`APP_ENV=testing`** (default). No browser or live Elasticsearch cluster in this run.

3. **What was tested (from “What to verify” / Testing instructions):** Automated suite for Scout indexing and Elasticsearch mapping; route smoke; optional live ES index/import and failed-jobs scenarios per instructions §4–5.

4. **Results:**
   - **`./scripts/git-sync-agent-branch.sh`:** **PASS** — completed successfully (`Already up to date.`).
   - **`php artisan test` — `ProductScoutIndexingTest`, `ScoutElasticsearchMappingTest` green; `ProductElasticsearchScoutIntegrationTest` skipped unless `ES_TEST_HOST`:** **PASS** — `ScoutElasticsearchMappingTest` (2 tests) and `ProductScoutIndexingTest` (3 tests) passed; `ProductElasticsearchScoutIntegrationTest` skipped with expected message (no `ES_TEST_HOST`). Full suite: **65 passed**, **5 skipped** (includes other skipped tests), exit code **0**.
   - **`php artisan routes:smoke` — no HTTP 500:** **PASS** — output: `All checked GET routes returned a non-500 status.`, exit code **0**.
   - **Optional — live ES (`scout:index` / `scout:import`, document count):** **N/A** — not executed; no Elasticsearch instance configured for this verification.
   - **Optional — failed jobs with misconfigured ES and worker:** **N/A** — not executed; relies on optional live stack.

5. **Overall:** **PASS.** All required automated checks passed; optional manual Elasticsearch steps were not exercised in this environment.

6. **Product owner feedback:** Scout mapping and queue-driven indexing are covered by unit and feature tests without a live cluster, which matches the stated constraint for environments without Elasticsearch. To validate production-like indexing and failure paths end-to-end, run the optional steps against a real ES instance and a queue worker when convenient.

7. **URLs tested:** **N/A — no browser** (API-only / CLI verification).

8. **Relevant log excerpts (last section):**

```
[2026-03-31 10:29:43] testing.INFO: catalog_search.fallback_to_database {"mode":"full_text","reason":"elasticsearch_unavailable","db_driver":"sqlite"}
```

(From `storage/logs/laravel.log` during the test window; reflects catalog search tests, not a failure of this task’s automated checks.)
