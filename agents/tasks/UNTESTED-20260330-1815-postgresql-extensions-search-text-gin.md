# PostgreSQL search foundation: extensions, `search_text`, GIN (pg_trgm)

## Epic / tracking

- **Program:** PostgreSQL + Elasticsearch search platform (see epic intro in `FEAT-20260330-1805-postgresql-config-and-schema-migration.md`).

## Dependencies

- **Requires:** `FEAT-20260330-1805-postgresql-config-and-schema-migration.md` merged or completed (schema on PostgreSQL stable).

## Goal

Enable **pg_trgm**, **citext**, and **unaccent** on PostgreSQL, add a **normalized `search_text`** (lowercased, unaccented concatenation of searchable fields), and create a **GIN (gin_trgm_ops)** index for fuzzy/partial matching — without breaking SQLite-based tests.

## Scope

1. **Extensions (PostgreSQL only)**

   Run only when driver is `pgsql`:

   ```sql
   CREATE EXTENSION IF NOT EXISTS pg_trgm;
   CREATE EXTENSION IF NOT EXISTS citext;
   CREATE EXTENSION IF NOT EXISTS unaccent;
   ```

   - Implement via **edited existing migrations** (preferred per project standards) or, if that is impossible without harming clarity, a **minimal** approach documented in the task handoff — never run these statements on SQLite.

2. **`products` (and any other searchable entities agreed in implementation)**

   - Add **`search_text`** (e.g. `text` or `citext`-backed storage — justify choice in code comments briefly).
   - Populate using **generated/stored** column **or** application/DB trigger **or** explicit rebuild command (rebuild command may land in a later FEAT; if so, stub interface here).
   - Concatenate relevant fields (e.g. name, SKU/code, description snippets — align with current product model and storefront search).

3. **Indexes**

   - GIN index on `search_text` with `gin_trgm_ops` (PostgreSQL only), e.g.:

     ```sql
     CREATE INDEX idx_products_search_text_trgm
     ON products USING gin (search_text gin_trgm_ops);
     ```

   - Evaluate **additional** B-tree or GIN indexes only if justified (avoid over-indexing).

4. **`citext`**

   - Apply to fields where **case-insensitive equality** is required (e.g. email already unique — follow existing schema); do not blindly citext every string.

## Out of scope

- Laravel `SearchService` query composition → next FEAT.
- Elasticsearch → later FEAT.

## Verification (before **UNTESTED-**)

- `php artisan migrate:fresh --seed` on PostgreSQL: **0**.
- `php artisan test`: **pass** (SQLite must not execute PG-only DDL).
- Optional: raw `\dx` / `\d+ products` in `psql` to confirm extensions and index (document in test or manual notes).

---

## Coder implementation summary

- **`database/migrations/0001_01_01_000000_create_users_table.php`:** On `pgsql`, `CREATE EXTENSION IF NOT EXISTS` for `pg_trgm`, `citext`, `unaccent`; then `ALTER` `clients.login_email` and `password_reset_tokens.email` to `citext`.
- **`database/migrations/2026_02_24_095031_create_products_table.php`:** `products.search_text` nullable `text` (maintained in PHP for parity across SQLite/MySQL/PostgreSQL; avoids immutable-generator issues with `unaccent`); on `pgsql`, GIN index `idx_products_search_text_trgm`.
- **`app/Models/Product.php`:** `saving` hook sets `search_text` via `normalizeSearchText()` (whitespace normalize, optional intl diacritic fold, `mb_strtolower`).
- **`database/seeders/ProductSeeder.php`:** Fills `search_text` before raw `insert` (same helper).
- **`App\Contracts\RebuildsProductSearchText`**, **`App\Services\ProductSearchTextRebuildService`**, **`products:rebuild-search-text`** Artisan command; binding in **`AppServiceProvider`**.
- **Tests:** `tests/Feature/ProductSearchTextTest.php` (SQLite + optional pgsql extension check). **Note:** Bizum mail tests now set `payments.checkout_method_keys` explicitly so a restrictive local `.env` does not break CI.
- **Docs / diagram:** `docs/postgresql.md`, `trash/diagramZero.dbml` updated.

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh` at repo root.
2. **SQLite (default CI):** `php artisan test` — expect all passing; `ProductSearchTextTest::test_postgresql_extensions_exist_when_using_pgsql` skips unless `DB_CONNECTION=pgsql`.
3. **PostgreSQL (optional):** Point `.env` (or PHPUnit via `DB_CONNECTION=pgsql` and `DB_TESTING_DATABASE=…`) at a disposable test database; run `php artisan test --filter=ProductSearchTextTest` and confirm the pgsql extension test runs and passes. Run `php artisan migrate:fresh --seed` and confirm exit code **0**; in `psql`, `\dx` lists `pg_trgm`, `citext`, `unaccent`; `\d+ products` shows GIN index on `search_text`.
4. **Smoke:** `php artisan routes:smoke` — no HTTP 500 on GET routes.
5. **Manual:** Create/update a product via admin API; confirm `search_text` updates. After a hypothetical raw SQL product insert, run `php artisan products:rebuild-search-text`.
