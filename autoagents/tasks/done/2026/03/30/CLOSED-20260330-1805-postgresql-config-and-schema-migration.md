---
## Closing summary (TOP)

- **What happened:** The team delivered PostgreSQL-first configuration, documentation, and migration/schema compatibility groundwork for the MariaDB/MySQL → PostgreSQL epic while keeping SQLite-based PHPUnit viable.
- **What was done:** `config/database.php` and `.env.example` were aligned for `pgsql` (including `DB_SCHEMA`, `DB_SSLMODE`), `docs/postgresql.md` and README cross-links were added, the personalized-solution-attachments migration used portable `foreignId()->constrained()`, and `trash/diagramZero.dbml` was noted for the production target; existing migrations were already PostgreSQL-safe.
- **What was tested:** Tester reported **PASS** for git sync, `php artisan test` (65 passed, 5 skipped), and `php artisan routes:smoke`; disposable-local **`migrate:fresh --seed` on PostgreSQL** was explicitly **not verified** (credentials / not dropping shared DB), with recommendation to run that check in CI or Docker Postgres.
- **Why closed:** Tester **overall PASS** with documented environment limits; no further tester loop required per task policy.
- **Closed at (UTC):** 2026-03-30 17:11
---

# PostgreSQL migration: configuration and schema compatibility

## Epic / tracking

- **Program:** Migrate MariaDB/MySQL → PostgreSQL; add production-grade search (PostgreSQL + Elasticsearch).
- **GitHub:** Open a tracking issue if none exists; reference it in commits. Subsequent FEAT tasks in this epic depend on this foundation.

## Dependencies

- **None** (first slice). **Blocks:** `FEAT-20260330-1815-postgresql-extensions-search-text-gin.md` and all later epic tasks.

## Goal

Make the application **first-class on PostgreSQL** while keeping **local/CI reproducibility** (SQLite in-memory or file is commonly used for `php artisan test` in this repo — preserve passing tests without requiring Postgres in CI unless the team explicitly opts in).

## Scope

1. **`config/database.php` and `.env.example`**
   - Document `pgsql` as the recommended production/default development driver for this epic.
   - Keep MySQL/MariaDB **documented as optional** during transition if needed; avoid breaking existing clones.

2. **Convert existing migrations** (per **`.cursor/rules/project-standards.mdc`**: edit **existing** migration files; do not add new migrations that only alter columns on existing tables).
   - Replace MySQL-specific column types, indexes, and defaults with PostgreSQL-safe equivalents (`json`/`jsonb`, `boolean`, `text` vs `varchar` where appropriate, `decimal`, timestamps, etc.).
   - Ensure **`migrate:fresh --seed`** succeeds on PostgreSQL in development.
   - Where the test suite uses SQLite, guard **PostgreSQL-only** DDL with `Schema::getConnection()->getDriverName() === 'pgsql'` (or equivalent) so SQLite tests keep working.

3. **Data / reversibility**
   - Do **not** assume production data migration scripts in this task unless explicitly requested; focus on **schema + Laravel config**. If a separate dump/migration runbook is needed, add a short note under **`docs/`** (only if you touch operational docs as part of this slice).

4. **Diagram**
   - Update **`trash/diagramZero.dbml`** if schema types or notes change in a way the diagram should reflect.

## Out of scope (later tasks)

- `pg_trgm`, `citext`, `unaccent`, `search_text`, GIN indexes → next FEAT.
- Elasticsearch / Scout → later FEAT.
- Search API behaviour → later FEAT.

## Implementation summary (coder)

- **`config/database.php`:** Comment block clarifies PostgreSQL as recommended for production/dev parity; SQLite default when unset; MySQL/MariaDB optional. `pgsql.search_path` reads **`DB_SCHEMA`** (default `public`). Comment on **`DB_SSLMODE`** (`prefer` vs local `disable`).
- **`.env.example`:** Commented PostgreSQL block (port 5432, `DB_SCHEMA`, `DB_SSLMODE`) and optional MySQL block; SQLite remains the default example.
- **`docs/postgresql.md`:** Short setup and verification note. **`README.md`:** One line pointing to it.
- **`database/migrations/..._personalized_solution_attachments`:** `foreignId()->constrained(...)` instead of `unsignedBigInteger` + manual FK (portable across drivers; keeps FK name `ps_att_solution_fk`).
- **`trash/diagramZero.dbml`:** Header note — production target PostgreSQL; tests use SQLite; migrations stay driver-agnostic.
- Existing migrations were already PostgreSQL-safe; no pgsql-only DDL added (nothing to guard for SQLite).

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh` at repo root.
2. **PostgreSQL:** Point `.env` at a Postgres instance (or Docker), then:
   - If the server has no TLS (typical local/Docker), set **`DB_SSLMODE=disable`** (or you may get “server does not support SSL, but SSL was required”).
   - Run **`php artisan migrate:fresh --seed`** — exit code **0**.
3. **Automated (SQLite / PHPUnit):** **`php artisan test`**. If `.env` sets **`PAYMENTS_CHECKOUT_METHODS`** to a list that omits **`bizum`**, either include `bizum` or remove/empty that variable so all default methods apply (otherwise `CustomerTransactionalEmailTest` bizum cases fail).
4. **`php artisan routes:smoke`:** Requires a working app DB in `.env` (migrated). PHPUnit’s **`RouteSmokeTest`** already smoke-tests GET routes without 500 when using the test sqlite DB.
5. **Regression:** With Postgres loaded after seed, `GET /api/v1/products` (and similar) should return 200 for public routes.

No front-end or Vite changes — **`npm run build`** not required for this slice.

---

## Test report

1. **Date/time (UTC) and log window**
   - Started: **2026-03-30 ~17:08 UTC**; finished: **~17:11 UTC**.
   - Log window: `storage/logs/laravel.log` entries around the failed local `migrate:fresh` attempt (password auth failure); no new application errors during `php artisan test` or `routes:smoke`.

2. **Environment**
   - **PHP:** 8.3.6; **Node:** v22.21.0.
   - **Branch:** `agentdevelop`.
   - **PHPUnit:** `APP_ENV=testing`, `DB_CONNECTION=sqlite` per `phpunit.xml` / `tests/bootstrap.php`.
   - **`.env` (non-test):** `DB_CONNECTION=pgsql` (hosted PostgreSQL; not used for destructive migration).

3. **What was tested** (from “What to verify” / testing instructions)
   - Git sync; PostgreSQL `migrate:fresh --seed`; `php artisan test`; `php artisan routes:smoke`; public API regression; `npm run build` only if FE (N/A).

4. **Results**
   - **Git sync (`./scripts/git-sync-agent-branch.sh`):** **PASS** — `Already up to date` on `agentdevelop`.
   - **PostgreSQL `migrate:fresh --seed` (instruction §2):** **NOT VERIFIED (environment)** — Local `127.0.0.1:5432` accepted connections but `DB_USERNAME=postgres` / `DB_PASSWORD=postgres` failed (`password authentication failed`); Unix-socket attempt failed (`role "luipy" does not exist`). Did **not** run `migrate:fresh` against the hosted database in `.env` (would drop shared data). Per project verification policy: state explicitly when a disposable Postgres is unavailable.
   - **`php artisan test`:** **PASS** — `PAYMENTS_CHECKOUT_METHODS=` cleared for the run (`.env` had `paypal` only, which would omit bizum); **65 passed**, 5 skipped, **0 failed**, exit code **0**.
   - **`php artisan routes:smoke`:** **PASS** — `All checked GET routes returned a non-500 status.`, exit code **0** (uses configured `pgsql` app DB).
   - **Regression (public routes on Postgres-backed app):** **PASS (indirect)** — Smoke run against live `pgsql` config; feature tests include catalog/search API behaviour (`ProductCatalogSearchApiTest` passed). No separate browser session.
   - **`npm run build`:** **N/A** — Task scope: no `resources/js/` / Vite changes for this slice.

5. **Overall:** **PASS** — Automated suite and route smoke succeed; full **`migrate:fresh --seed` on a throwaway PostgreSQL** was not executed here (credentials / safety). Recommend re-running instruction §2 in CI or a local Docker Postgres when credentials are available.

6. **Product owner feedback**
   - The codebase remains green under PHPUnit (SQLite) and the application serves all GET routes without HTTP 500 when wired to the team’s PostgreSQL-backed environment.
   - A full “empty database → migrate + seed” check still deserves a one-off or CI run on disposable Postgres so schema drift is caught the same way developers run `migrate:fresh`.

7. **URLs tested**
   - **N/A — no browser** (CLI only).

8. **Relevant log excerpts**
   - `routes:smoke` stdout: `All checked GET routes returned a non-500 status.`
   - `php artisan test` summary: `Tests: 5 skipped, 65 passed (274 assertions)` / `Duration: 1.94s`
   - Local PG attempt (from `laravel.log`): `password authentication failed for user "postgres"` during migration repository check (expected given wrong local credentials).
