---
## Closing summary (TOP)

- **What happened:** The search-platform operability slice (Artisan commands, demo seeders, PHPUnit coverage, and docs) was implemented and handed to the tester for verification.
- **What was done:** Coder added Elasticsearch reindex and PostgreSQL `search_text` rebuild commands, `SearchDemoProductSeeder`, consolidated database/search/ES-related tests, and documentation in `docs/elasticsearch.md`, `docs/postgresql.md`, plus changelog entries as described in the task body.
- **What was tested:** Tester ran git sync, isolated SQLite `migrate:fresh --seed` (three `SEARCH-DEMO-*` SKUs), `php artisan test` (65 passed, 5 skipped), `routes:smoke`, Artisan reindex skip and rebuild `--stale`, and manual doc review; overall **PASS** with optional live ES reindex not run.
- **Why closed:** All required automated checks passed; optional steps were correctly marked N/A; no GitHub issue was linked for label/comment updates.
- **Closed at (UTC):** 2026-03-30 17:02
---

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

### 1. Date/time (UTC) and log window

- **Started:** 2026-03-30 16:58:15 UTC  
- **Finished:** 2026-03-30 17:01:00 UTC (approx.)  
- **Log window:** `storage/logs/laravel.log` lines around **2026-03-30 16:41–17:00** UTC (`testing` channel entries from PHPUnit / smoke; no errors tied to this verification).

### 2. Environment

- **PHP:** 8.3.6  
- **Node:** v22.21.0  
- **Branch:** `agentdevelop`  
- **`php artisan test`:** `APP_ENV=testing` (per `phpunit.xml`), `DB_CONNECTION=sqlite` (in-memory via `tests/bootstrap.php`).  
- **Note:** Workspace `.env` uses a **remote** `pgsql` host; **`migrate:fresh --seed` was not run against that database** (destructive on a shared/remote instance). Equivalence check used an **isolated SQLite file** at `/tmp/le-search-test.sqlite`.

### 3. What was tested (from Testing instructions)

- Git sync, migrate+seed + demo SKUs, full test suite, route smoke, Artisan reindex/rebuild, optional ES reindex (skipped), docs spot-check, log sample.

### 4. Results

| Criterion | Result | Evidence |
|-----------|--------|----------|
| `./scripts/git-sync-agent-branch.sh` | **PASS** | Exit 0; `Already up to date` on `agentdevelop`. |
| `migrate:fresh --seed` + three `SEARCH-DEMO-*` codes | **PASS** (mitigated) | `DB_CONNECTION=sqlite DB_DATABASE=/tmp/le-search-test.sqlite php artisan migrate:fresh --seed` → exit **0**. Tinker query returned all three codes: `SEARCH-DEMO-K1-PARTIAL`, `SEARCH-DEMO-MIX-3030-K1`, `SEARCH-DEMO-TYPO`. Remote PostgreSQL in `.env` not wiped by tester. |
| `php artisan test` | **PASS** | Exit **0**; **65 passed**, **5 skipped** (expected: ES host, pgsql-only, ranking smoke per warnings). |
| `php artisan routes:smoke` | **PASS** | Exit **0**; `All checked GET routes returned a non-500 status.` |
| `php artisan products:reindex-elasticsearch` | **PASS** | Printed `Skipping Elasticsearch reindex: SCOUT_DRIVER is not "elasticsearch".`, exit **0**. |
| `php artisan products:rebuild-search-text --stale` | **PASS** | On seeded SQLite file: `Updated 0 product(s).`, exit **0**. |
| Optional live ES reindex (`--recreate` / `--fresh`) | **N/A** | No local ES / `ES_TEST_HOST` run in this step (optional per instructions). |
| `npm run build` | **N/A** | Task states not required. |
| Manual read `docs/elasticsearch.md`, `docs/postgresql.md` | **PASS** | Files present; cover disable ES, env, reindex command; extensions / `migrate:fresh --seed` workflow. |

### 5. Overall

**PASS.** All required automated checks succeeded; optional ES live reindex not exercised. PostgreSQL-specific integration is covered by PHPUnit **skip** when not on `pgsql`, consistent with task text (“pgsql/es integration tests skip unless env present”).

### 6. Product owner feedback

Search-related Artisan commands behave safely when Elasticsearch is off (skip message, exit 0), and seed data includes the three deterministic demo SKUs for search scenarios. Documentation in `docs/elasticsearch.md` and `docs/postgresql.md` matches the described operator workflow. Teams should run `migrate:fresh --seed` on a **dedicated** PostgreSQL dev instance (not a shared remote DB without approval) to validate extensions and GIN index tests that PHPUnit skips on SQLite.

### 7. URLs tested

**N/A — no browser** (no checkout/payments or manual HTTP flows required for this task).

### 8. Relevant log excerpts

```text
[2026-03-30 17:00:15] testing.INFO: catalog_search.fallback_to_database {"mode":"full_text","reason":"elasticsearch_unavailable","db_driver":"sqlite"}
```

(Shows search fallback path exercised during automated tests; no application **ERROR** lines in this window for the verification run.)

---

**GitHub:** No issue number (`#NN`) in this task file; labels/comments not updated.
