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

---

## Test report

1. **Date/time (UTC) and log window**
   - Started: **2026-03-27T16:17:30Z** (tester pickup / rename to `TESTING-`).
   - Finished: **2026-03-27T16:18:49Z**.
   - Log window reviewed: historical `laravel.log` entries for `:memory:` + `sessions` (2026-03-26 and earlier); **no new** `:memory:` session `QueryException` produced by this verification run (evidence: CLI test/smoke output below).

2. **Environment**
   - **PHP:** 8.3.6 (CLI).
   - **Node:** v22.21.0.
   - **Branch:** `agentdevelop` (synced via `./scripts/git-sync-agent-branch.sh` before edits).
   - **APP_ENV:** not overridden; PHPUnit uses project test configuration.

3. **What was tested** (from “What to verify”)
   - Avoid `QueryException` / `no such table: sessions` when SQLite `:memory:` is used with a database session driver expectation.
   - PHPUnit default (`sqlite` + `:memory:`) yields `config('session.driver') === 'array'` after boot.

4. **Results**
   - **`php artisan test` exit code 0:** **PASS** — `Tests: 29 passed`; `SqliteMemorySessionDriverTest` ✓ `session driver is array when default sqlite database is memory`.
   - **`php artisan routes:smoke` no HTTP 500:** **PASS** — `All checked GET routes returned a non-500 status.`
   - **Effective session driver `array` on `:memory:`:** **PASS** — same regression test assertion (`assertSame('array', config('session.driver'))` when connection is sqlite `:memory:`).
   - **Optional manual `.env` web hit:** **N/A** — not executed (marked optional in task).

5. **Overall:** **PASS** (all required criteria met).

6. **Product owner feedback**
   - The regression test and full suite confirm that SQLite `:memory:` no longer leaves the app on a database session driver that would query a missing `sessions` table.
   - Route smoke stayed clean, so the change did not introduce server errors on GET routes. Optional browser verification against a tweaked `.env` was skipped but is low risk given the dedicated feature test.

7. **URLs tested**
   - **N/A — no browser** (CLI only per instructions; optional manual step not run).

8. **Relevant log excerpts**
   - **CLI (primary evidence):**
     - `Tests\Feature\SqliteMemorySessionDriverTest … ✓ session driver is array when default sqlite database is memory`
     - `php artisan routes:smoke` → `All checked GET routes returned a non-500 status.`
   - **Historical `storage/logs/laravel.log` (context only — pre-fix noise):** e.g. `[2026-03-26 19:05:57] local.ERROR: … no such table: sessions (Connection: sqlite, Database: :memory: …` — documents the original failure mode; not repeated during this test run.

**GitHub:** No issue number in task body; labels/comments **not** updated.

**Loop protection:** N/A (first verification cycle for this change).
