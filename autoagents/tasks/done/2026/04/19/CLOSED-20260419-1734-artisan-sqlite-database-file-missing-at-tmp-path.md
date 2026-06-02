---
## Closing summary (TOP)

- **What happened:** Laravel raised “database file does not exist” when `DB_DATABASE` pointed at an on-disk SQLite path under `/tmp` that had not been created yet (operator `.env` override).
- **What was done:** **`SqliteDatabaseBootstrap`** runs on non-production boot from **`AppServiceProvider`** to **`touch`** a missing SQLite file (`:memory:` and production unchanged); **`README.md`** and **`CHANGELOG.md`** were updated and **`tests/Unit/SqliteDatabaseBootstrapTest.php`** was added.
- **What was tested:** **`php artisan test`** passed (**92 passed**, 5 skipped); manual **`db:show`** with a fresh **`/tmp/manual-sqlite-bootstrap-test.sqlite`** passed; **`routes:smoke`** failed only due to missing **`pdo_pgsql`** in the tester environment, which the task treats as unrelated.
- **Why closed:** Tester marked overall **PASS** and task pass/fail criteria were satisfied.
- **Closed at (UTC):** 2026-04-19 17:40
---

# Artisan migrate: configured SQLite database file missing

## Source
- **Log:** `storage/logs/laravel.log`
- **UTC window:** new lines after prior 001 review; representative at **`[2026-04-19 17:25:39]`** (and related context **`[2026-04-19 17:25:37]`**–**`17:28:52]`** same session).
- **Representative lines:**
  - `local.ERROR: Database file at path [/tmp/laravel-ecommerce-feat.sqlite] does not exist. Ensure this is an absolute path to the database. (Connection: sqlite, Database: /tmp/laravel-ecommerce-feat.sqlite, SQL: select exists … 'migrations' …)`
  - Nearby: `could not find driver (Connection: pgsql, …)` when `.env` points at PostgreSQL without `pdo_pgsql` — separate environment class.

## High-level instructions for coder
- Identify which workflow or documentation sets `DB_DATABASE=/tmp/laravel-ecommerce-feat.sqlite` without ensuring the file exists before `migrate:fresh` / migration commands (shell scripts, CI, or README).
- Decide whether to document a required `touch`/`mkdir` step, align the path with other smoke-test SQLite paths used in this repo, or fail fast with a clearer message when the SQLite path is configured but missing.
- Keep destructive migration guidance scoped to development; do not assume production database drops.

## Status (coder)

- **Done:** No single repo script referenced `laravel-ecommerce-feat.sqlite` (operator `.env` override). Implemented **`App\Support\SqliteDatabaseBootstrap`** called from **`AppServiceProvider::register()`** so non-`production` boots **`touch`** an empty SQLite file when the configured path is missing (`:memory:` unchanged; production unchanged). **`README.md`** Database section notes the behaviour; **`CHANGELOG.md`** `[Unreleased]` updated.
- **Tests:** **`tests/Unit/SqliteDatabaseBootstrapTest.php`**.

---

## Testing instructions

### What to verify

- Missing on-disk SQLite path used as `DB_DATABASE` no longer triggers Laravel’s “database file does not exist” error on first Artisan/web boot in non-production.
- `:memory:` and `production` are unaffected.
- PHPUnit suite remains green.

### How to test

1. **`php artisan test`** — full suite (includes **`SqliteDatabaseBootstrapTest`**).
2. **Manual — missing file:**  
   `rm -f /tmp/manual-sqlite-bootstrap-test.sqlite`  
   then from repo root (set **`APP_KEY`** if no `.env`):  
   `APP_ENV=local DB_CONNECTION=sqlite DB_DATABASE=/tmp/manual-sqlite-bootstrap-test.sqlite php artisan db:show`  
   Expect command succeeds and shows database path **`/tmp/manual-sqlite-bootstrap-test.sqlite`** (file created).
3. **Optional:** **`php artisan routes:smoke`** after **`migrate`** on your dev DB — unchanged behaviour expected.

### Pass/fail criteria

- **PASS:** Step 1 exit code **0**; Step 2 **`db:show`** exit code **0** and file exists under **`/tmp/manual-sqlite-bootstrap-test.sqlite`**.
- **FAIL:** Any test failure; **`db:show`** still errors with missing database file in **`APP_ENV=local`**.

---

## Test report

1. **Date/time (UTC) and log window:** Started **2026-04-19 17:39:20 UTC**, finished **2026-04-19 17:39:39 UTC**. Log window for optional smoke check: **`[2026-04-19 17:39:xx]`** (entries appended during `php artisan routes:smoke`).

2. **Environment:** PHP **8.4.16**, branch **`agentdevelop`**, repo root **`/root/Repos/laravel-ecommerce`**. Default `.env` session/DB targets PostgreSQL; **`pdo_pgsql`** not available in this container (see log excerpt).

3. **What was tested (from “What to verify”):** Missing SQLite file bootstrap for non-production; `:memory:` / production behaviour via unit tests; full PHPUnit suite; manual **`db:show`** with fresh path under **`/tmp`**; optional **`php artisan routes:smoke`**.

4. **Results:**
   - Full suite **`php artisan test`:** **PASS** — exit code **0**; **`SqliteDatabaseBootstrapTest`** and **`RouteSmokeTest`** passed (**92 passed**, 5 skipped).
   - Manual **`APP_ENV=local DB_CONNECTION=sqlite DB_DATABASE=/tmp/manual-sqlite-bootstrap-test.sqlite php artisan db:show`:** **PASS** — exit code **0**; output shows **`Database … /tmp/manual-sqlite-bootstrap-test.sqlite`**; file present (`-rw-r--r--`, 0 bytes after touch).
   - **`php artisan routes:smoke`:** **FAIL (environment)** — many routes return **500** because session store uses PostgreSQL and **`could not find driver`** for **`pdo_pgsql`**; same class of issue as “separate environment” in Source. Not treated as regression for this task; Feature **`RouteSmokeTest`** in PHPUnit **PASS**.

5. **Overall:** **PASS** — task pass/fail criteria (full test run + manual missing-file **`db:show`**) satisfied.

6. **Product owner feedback:** Local and CI flows that point **`DB_DATABASE`** at an on-disk SQLite path that does not exist yet should no longer hit Laravel’s “database file does not exist” on first boot; developers still need a working default stack for **`routes:smoke`** when **`.env`** expects PostgreSQL (install **`pdo_pgsql`** or align session/database to SQLite), which is independent of this fix.

7. **URLs tested:** **N/A — no browser**

8. **Relevant log excerpts (last section)**

```text
PDOException(code: 0): could not find driver
... PostgresConnector.php ... DatabaseSessionHandler.php ...
```

*(From `storage/logs/laravel.log` during CLI `routes:smoke`; reflects missing PostgreSQL driver for default app config, not missing SQLite file.)*
