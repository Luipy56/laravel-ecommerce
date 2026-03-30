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
