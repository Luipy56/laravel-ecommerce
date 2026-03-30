# Search platform: Artisan tooling, seed fixtures, PHPUnit matrix, setup docs

## Epic / tracking

- **Program:** PostgreSQL + Elasticsearch search platform — **closing slice** for operability and proof.

## Dependencies

- **Requires:** Prior epic tasks implemented or ready to validate together (minimum: PostgreSQL path + ES integration + API fallback).

## Goal

1. **Artisan commands**
   - **Reindex Elasticsearch** (full + optional chunk/batch).
   - **Rebuild `search_text`** for all or stale rows (PostgreSQL).
   - Idempotent, queue-aware where appropriate.

2. **Seeders**
   - Deterministic products/codes for **search demos** and tests (typo, partial SKU, mixed query) — align with PHPUnit examples from earlier tasks.

3. **Tests (mandatory consolidation)**
   - **Database:** extensions (pgsql), indexes, `search_text` correctness.
   - **Search:** typo, partial, mixed queries (duplicate coverage acceptable if it locks regressions).
   - **Elasticsearch:** indexing happy path (mocked or optional env); **fallback** when ES down.
   - Ensure CI default (`php artisan test` on SQLite) **passes**; pgsql/es integration tests **skip** unless env present.

4. **Documentation**
   - **`README.md`** or **`docs/`** (only if needed): PostgreSQL extensions (`CREATE EXTENSION` privileges), Elasticsearch version, env vars, `docker compose` reference, local workflow (`migrate:fresh --seed`, reindex command).
   - **`CHANGELOG.md`** under `[Unreleased]` for operator-visible changes.

## Constraints

- **Reversible / incremental:** document how to disable ES and run PostgreSQL-only.
- Follow **`.cursor/rules/testing-verification.mdc`**: after schema/seeder changes, `php artisan migrate:fresh --seed` must exit **0** on dev PostgreSQL.

---

## Implementation summary (coder)

- **`products:reindex-elasticsearch`:** `--fresh`, `--recreate`, `--chunk=`, `--queued`; exits **0** with a skip message when `SCOUT_DRIVER` is not `elasticsearch` or hosts are empty. See **`docs/elasticsearch.md`**.
- **`products:rebuild-search-text`:** `--stale` (only null / mismatched `search_text`), `--chunk=` (default 100). **`RebuildsProductSearchText`** + **`ProductSearchTextRebuildService`** extended accordingly.
- **`SearchDemoProductSeeder`:** deterministic `SEARCH-DEMO-*` products (typo / partial K1 / mixed-query copy); registered in **`DatabaseSeeder`** after **`ProductSeeder`**.
- **Tests:** **`ReindexElasticsearchProductsCommandTest`**; **`ProductSearchTextTest`** — stale rebuild + PostgreSQL GIN index check (skipped off pgsql); fallback remains in **`ProductCatalogSearchApiTest`**.
- **Docs / changelog:** **`docs/elasticsearch.md`**, **`docs/postgresql.md`**, **`README.md`** (optional search pointer), **`CHANGELOG.md`** `[Unreleased]`.

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh`.
2. **PostgreSQL dev DB:** `php artisan migrate:fresh --seed` → exit code **0**; confirm three rows with codes `SEARCH-DEMO-TYPO`, `SEARCH-DEMO-K1-PARTIAL`, `SEARCH-DEMO-MIX-3030-K1`.
3. `php artisan test` — full suite green; optional **`ES_TEST_HOST`** for live ES integration test.
4. `php artisan routes:smoke` — no HTTP **500**.
5. **Artisan (SQLite or any DB):** `php artisan products:reindex-elasticsearch` — should print skip line and exit **0** when Scout is not on Elasticsearch.
6. **Artisan:** `php artisan products:rebuild-search-text --stale` — completes; compare with full rebuild without `--stale` on a large DB if available.
7. **With Elasticsearch (optional):** `SCOUT_DRIVER=elasticsearch`, `ELASTICSEARCH_HOSTS` set; run `php artisan products:reindex-elasticsearch --recreate` (or `--fresh`), then confirm index document count vs active products.
8. `npm run build` — **not required** for this task (no `resources/js` / Vite changes).
9. **Manual read:** **`docs/elasticsearch.md`** (disable ES, version, env, reindex table) and **`docs/postgresql.md`** (extensions, rebuild commands).

## Test report

_(Tester fills after verification.)_
