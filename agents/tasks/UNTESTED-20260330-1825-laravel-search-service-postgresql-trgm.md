# Laravel `SearchService`: PostgreSQL ILIKE + pg_trgm similarity

## Epic / tracking

- **Program:** PostgreSQL + Elasticsearch search platform.

## Dependencies

- **Requires:** `FEAT-20260330-1815-postgresql-extensions-search-text-gin.md` (or equivalent: `search_text` + GIN in place).

## Goal

Introduce a **clean architecture** search layer (service class, not fat controllers) that queries PostgreSQL using:

- **Normalized input:** `trim`, lowercase, accent folding (PHP `intl`/transliterator **or** DB `unaccent` — pick one consistent strategy and document it).
- **Combined matching:**
  - `ILIKE` prefix / partial patterns on `search_text` (and/or raw fields where needed).
  - **`pg_trgm` similarity** (`%` operator or `similarity()` / `word_similarity()` with sane thresholds) for typo tolerance.
- **Ranking:** deterministic ordering (e.g. exact > prefix > trigram score); cap result set size.

## Scope

1. **`App\Services\Search` (or agreed namespace)** e.g. `ProductSearchService` / `SearchService` — single entry used by API layer later.
2. **Guardrails:** avoid full-table sequential scans in production-scale paths; rely on `search_text` + GIN where possible.
3. **Configuration:** env or `config/*.php` for thresholds, limits, and feature flags (align with existing `config/product_search.php` if still relevant — **merge or deprecate** thoughtfully to avoid duplicate knobs).
4. **Tests (this slice):**
   - Typo: query resembling **`cilimdro`** matches product **`cilindro`** (adjust to seeded data if needed).
   - Partial code: **`k1`** matches relevant SKU/code-style field.
   - Mixed: **`cilindro 3030 k1`** returns sensible ranked results.

## Out of scope

- Elasticsearch / Scout.
- HTTP route wiring if reserved for the “API + fallback” FEAT — but the service must be **callable from tests** without HTTP.

## Verification (before **UNTESTED-**)

- `php artisan test` including new unit/feature tests for search behaviour.
- `php artisan migrate:fresh --seed` if seed data was adjusted for search fixtures.
- `php artisan routes:smoke` if any route was added in this slice (prefer **not** to add routes here).

---

## Implementation summary

- Added **`App\Services\Search\ProductSearchService`** with `search(string $query): Collection<Product>`. Queries normalize via **`Product::normalizeSearchText($q, '', '')`** (same strategy as `search_text`: intl folding when available, lowercase).
- **PostgreSQL:** per-token `(ILIKE OR word_similarity OR similarity)` with configurable thresholds; score orders **exact → prefix → contains → trigram terms**; respects **`config/product_search.php`** limit (default 50).
- **Non-PostgreSQL:** per-token **`instr(search_text, token)`** plus `LIKE … ESCAPE` tiered ordering (no typo tolerance).
- **Config:** new **`config/product_search.php`** (`PRODUCT_SEARCH_LIMIT`, `PRODUCT_SEARCH_WORD_SIMILARITY`, `PRODUCT_SEARCH_SIMILARITY`).
- **Tests:** `tests/Feature/ProductSearchServiceTest.php` — `k1` partial match on SQLite; typo + mixed cases **run only when `DB_CONNECTION=pgsql`** (skipped under default PHPUnit SQLite).
- **Docs:** `docs/postgresql.md` — short **Catalog search** section.

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh` at repo root.
2. `php artisan test` — entire suite; under default SQLite, confirm `ProductSearchServiceTest` passes (partial `k1` + empty query); note two tests skipped unless Postgres.
3. **PostgreSQL verification (trigram behaviour):** point testing `.env` or `phpunit.xml` override to a migrated Postgres DB with `pg_trgm` enabled, then run  
   `php artisan test tests/Feature/ProductSearchServiceTest.php`  
   and confirm **`test_typo_cilimdro_matches_cilindro`** and **`test_mixed_cilindro_3030_k1_returns_sensible_ranked_results`** are **not** skipped and pass.
4. Seeds were **not** changed for this slice; no `migrate:fresh --seed` required unless you reset the DB for other reasons.
5. `php artisan routes:smoke` — no new routes in this slice; run for regression.
