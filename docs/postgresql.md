# PostgreSQL

PostgreSQL is the recommended database for production and for local development when you want parity with production. The default in `.env.example` remains **SQLite** so tests and quick setup work without installing a server.

## PHP driver (`pdo_pgsql`)

Laravel uses PDO for database connections. Before setting `DB_CONNECTION=pgsql`, install the **PostgreSQL PDO** extension for your PHP build (package names vary by OS: e.g. Debian/Ubuntu `php8.2-pgsql` or `php-pgsql`, Homebrew PHP often includes it via `pecl` or formula options).

Verify:

```bash
php -m | grep -i pdo_pgsql
```

If this prints nothing, Artisan commands that connect to Postgres (including `php artisan db:show`, `migrate`, `db:monitor`) fail with **`could not find driver`** at `Illuminate\Database\Connectors\Connector`. Install `pdo_pgsql`, restart PHP-FPM or your CLI SAPI if needed, then retry.

## Configuration

- Set `DB_CONNECTION=pgsql` and the usual `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- **`DB_SSLMODE`:** Laravel defaults to `prefer` (try TLS, then non-TLS). Local instances without SSL (common in Docker) need `DB_SSLMODE=disable` or connections will fail with an SSL error.
- **`DB_SCHEMA`:** Optional; maps to the `search_path` (default `public`).

## Extensions (search and login email)

On PostgreSQL, the first migration enables (idempotent):

- **`pg_trgm`** — trigram similarity; GIN index on `products.search_text` for fuzzy/partial matching.
- **`citext`** — case-insensitive text; `clients.login_email` and `password_reset_tokens.email` use this type on PostgreSQL only.
- **`unaccent`** — available for SQL that needs accent folding (product `search_text` is normalized in PHP for cross-database parity).

SQLite and MySQL migrations skip this DDL; PHPUnit stays on SQLite.

## Verify

From a clean database:

```bash
php artisan migrate:fresh --seed
```

Use `php artisan test` with the default SQLite testing configuration for CI-style checks.

After bulk raw SQL imports into `products`, run:

```bash
php artisan products:rebuild-search-text
```

## Catalog search (`ProductSearchService`)

`App\Services\Search\ProductSearchService` searches active products using normalized `products.search_text`. **Query normalization** matches how `search_text` is built: trim, collapse whitespace, optional **intl** diacritic folding, then lowercase (`Product::normalizeSearchText`). **PostgreSQL** combines `ILIKE` (per token) with **`pg_trgm`** `word_similarity` / `similarity` so minor typos still match; the GIN index on `search_text` supports these operators. **SQLite / MySQL** use substring (`instr`) per token with deterministic `LIKE … ESCAPE` ordering only—no typo tolerance.

Configuration: `config/product_search.php` (`PRODUCT_SEARCH_LIMIT`, `PRODUCT_SEARCH_WORD_SIMILARITY`, `PRODUCT_SEARCH_SIMILARITY`).
