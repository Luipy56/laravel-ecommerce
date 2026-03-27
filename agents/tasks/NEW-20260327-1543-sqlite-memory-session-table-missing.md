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
