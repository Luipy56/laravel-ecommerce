# SQLite `:memory:`: `no such table: sessions` during requests

## Source

- **Log:** `storage/logs/laravel.log`
- **UTC window:** burst at **2026-03-26** (log timestamps around `19:05:57`, channels `local` / `testing`)
- **Representative lines:**
  - `SQLSTATE[HY000]: General error: 1 no such table: sessions (Connection: sqlite, Database: :memory:, SQL: select * from "sessions" where "id" = ... limit 1)`

## Problem / goal

The app is using the **database session driver** against **SQLite in-memory**, but the **`sessions`** table is not present in that connection, producing repeated **QueryException** noise and broken session reads during tests or local runs that use `:memory:`.

## High-level instructions for coder

- Identify which **environment** writes these entries (`APP_ENV=testing` / PHPUnit, Dusk, or local with in-memory SQLite).
- Align **session driver** with test setup: e.g. use **`array`** sessions in `phpunit.xml` / test bootstrap, or ensure migrations / schema setup for `sessions` run on the same in-memory connection before HTTP/kernel tests.
- Confirm **`config/session.php`** and `.env.testing` (or CI) do not combine **`SESSION_DRIVER=database`** with an unmigrated **`:memory:`** database.
- Add a short regression test or doc note if the fix is configuration-only; run **`php artisan test`** after changes.

## Coder notes (2026-03-27 UTC)

- **`tests/bootstrap.php`:** If the PHPUnit test DB is SQLite `:memory:`, set `SESSION_DRIVER=array` via `putenv` / `$_ENV` / `$_SERVER` so it wins over `.env` and matches `phpunit.xml`.
- **`AppServiceProvider::register()`:** If `session.driver` is `database` and the connection used for sessions (default or `SESSION_CONNECTION`) is SQLite with `database === ':memory:'`, override config to `array` (covers cached `config` and local `APP_ENV=local` with `:memory:`).
- **`tests/Feature/SqliteMemorySessionDriverTest.php`:** Regression assert when that setup applies.
- **`.env.example`:** Comment documenting `:memory:` + database sessions.

---

## Testing instructions

### What to verify

- No `QueryException` / `no such table: sessions` when the default DB is SQLite `:memory:` and `.env` would use `SESSION_DRIVER=database` or cached config baked with that driver.
- PHPUnit default (`sqlite` + `:memory:`) keeps `config('session.driver') === 'array'` after boot.

### How to test

- `php artisan test` (includes `SqliteMemorySessionDriverTest` and full suite).
- `php artisan routes:smoke` (sanity; no route changes in this task).
- Optional manual: temporarily set `DB_DATABASE=:memory:` and `SESSION_DRIVER=database` in `.env`, `php artisan config:clear`, hit any web route that touches the session; expect no session table error (driver falls back to `array` at runtime).

### Pass/fail criteria

- **Pass:** `php artisan test` exit code 0; `routes:smoke` reports no HTTP 500; on SQLite `:memory:` the effective session driver after boot is `array`.
- **Fail:** Any test failure, smoke 500s, or session queries still hitting SQLite `:memory:` without a `sessions` migration.
