# Search API: Elasticsearch primary, PostgreSQL fallback, autocomplete

## Epic / tracking

- **Program:** PostgreSQL + Elasticsearch search platform.

## Dependencies

- **Requires:** `FEAT-20260330-1825-laravel-search-service-postgresql-trgm.md` (PostgreSQL search service) **and** `FEAT-20260330-1835-elasticsearch-scout-mapping-queue-sync.md` (indexing + mapping), or compatible partial implementations.

## Goal

Expose a **single** storefront/admin-facing search entry point (reuse or extend existing `/api/v1/products/search` or equivalent — **map current codebase first**):

1. **Primary:** Elasticsearch query (full-text + completion/suggest for autocomplete).
2. **Fallback:** If ES is unavailable, misconfigured, or times out → **`SearchService` PostgreSQL** path (ILIKE + trigram).
3. **Consistent response shape** for React clients; **do not** leak internal errors — log server-side.
4. **Rate limiting / limits:** align with existing API throttling patterns.

## Scope

- Thin controller → delegate to **application service** (e.g. `CatalogSearchFacade` orchestrating ES + PG).
- **Autocomplete:** endpoint or `suggest=true` query param — keep contract documented in code / OpenAPI if present.
- **Observability:** structured log when fallback triggers (metric hook optional).

## Out of scope

- Heavy synonym dictionaries — next FEAT (structure only).
- Reindex/rebuild artisan commands — tooling FEAT.

## Implementation summary (coder)

- **`GET /api/v1/products/search`:** `CatalogProductSearchService` tries Elasticsearch when `SCOUT_DRIVER=elasticsearch` and `scout.elasticsearch.hosts` is non-empty; on adapter failure or `null` result, falls back to `ProductSearchService` (PostgreSQL trigram or SQLite token match). Responses include `meta.engine`: `elasticsearch`, `database`, or `none` (query shorter than `product_search.api_min_query_length`). Errors are not exposed to the client; fallbacks log `catalog_search.fallback_to_database` (info).
- **Autocomplete:** query param `suggest=1` (or other values accepted by `Request::boolean()`). `data` is a list of `{ "text", "product_id" }`; ES path uses `match_bool_prefix` on `search_text` via the Elasticsearch client.
- **Throttling:** `throttle:60,1` on this route.
- **New / updated code:** `app/Services/Search/CatalogProductSearchService.php`, `ScoutElasticsearchProductCatalogSearch.php`, contract `ElasticsearchProductCatalogSearch`; `config/product_search.php` keys `api_min_query_length`, `api_limit`; `ProductController::search`; `ElasticsearchEngine::map` / `lazyMap` signatures aligned with Scout `Engine` (PHP 8.3 compatibility).
- **Tests:** `tests/Feature/ProductCatalogSearchApiTest.php`.

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh`.
2. `php artisan test` (includes `ProductCatalogSearchApiTest`: mocked ES success, mocked ES failure → database, `suggest` modes).
3. `php artisan routes:smoke` — no HTTP 500.
4. `npm run build` only if `resources/js` or Vite config changed (not required for this task).
5. **Manual (optional):** With `SCOUT_DRIVER=elasticsearch` and a synced index, `GET /api/v1/products/search?q=<term>` and `...&suggest=1`. With ES stopped or wrong host, confirm results still return via database and `meta.engine` is `database`.
