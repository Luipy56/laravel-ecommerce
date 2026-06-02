---
## Closing summary (TOP)

- **What happened:** Artisan database introspection (`db:show` path) failed with MySQL `SQLSTATE[2002] Network is unreachable` and PostgreSQL `could not find driver`, as logged in `storage/logs/laravel.log`.
- **What was done:** `.env.example`, `README.md`, and `docs/postgresql.md` were updated to document required PDO extensions, `DB_HOST` guidance (127.0.0.1 vs localhost, Docker service names), and when SQLite-only workflows apply vs needing a live server for `db:show`-style commands.
- **What was tested:** Review of README, `.env.example`, and `docs/postgresql.md` plus `php artisan test` — 56 passed, 4 skipped, exit code 0.
- **Why closed:** Tester overall **PASS**; all pass/fail criteria in the task were satisfied.
- **Closed at (UTC):** 2026-03-30 16:44
---

# Local `artisan` DB commands failing: MySQL network error and missing PostgreSQL PDO driver

## Source

- **Log:** `storage/logs/laravel.log`
- **UTC window:** `2026-03-30` (entries at **16:12:31** and **16:14:06** local channel `local.ERROR`)
- **Representative lines:**
  - `[2026-03-30 16:12:31] local.ERROR: SQLSTATE[HY000] [2002] Network is unreachable` — `PDOException` at `Illuminate\Database\Connectors\Connector.php:66`, stack via `MySqlConnector` → `ShowCommand` (`db:show` path).
  - `[2026-03-30 16:14:06] local.ERROR: could not find driver` — `PDOException` at same `Connector.php:66`, stack via `PostgresConnector` → `ShowCommand`.

## High-level instructions for coder

- Treat as **environment / developer experience**, not necessarily an application bug: two separate failure modes while running database introspection (`ShowCommand`) from Artisan.
- For **MySQL `2002` Network is unreachable:** confirm default `.env.example` / docs describe reachable `DB_HOST` (and when to use `127.0.0.1` vs `localhost`, Docker hostnames, firewall/VPN). Optionally improve error surfacing or troubleshooting notes if this confuses new contributors.
- For **PostgreSQL `could not find driver`:** document required PHP extensions (`pdo_pgsql`) for Postgres as default connection, and align README or setup docs with the PostgreSQL migration epic so developers install the right PDO driver before switching `DB_CONNECTION=pgsql`.
- No code change is mandatory if the outcome is **clear documentation and setup checks**; if the project intentionally supports SQLite for CI, call out which Artisan DB commands need a real server and which work on SQLite.

## Coder status

- **Completed:** 2026-03-30 (UTC)

## Implementation notes

- **`.env.example`:** Comments for PostgreSQL (`pdo_pgsql` before `DB_CONNECTION=pgsql`, `could not find driver` / `db:show`); MySQL/MariaDB (`pdo_mysql`, `127.0.0.1` vs `localhost`, Docker hostnames, `SQLSTATE[2002] Network is unreachable`).
- **`README.md`:** Requirements list PDO extensions per driver; Quick start “Database” step notes SQLite vs server-backed DB, `db:show`-style commands needing a live connection + correct extension, `127.0.0.1` vs `localhost`, and 2002 / Docker service name as `DB_HOST`.
- **`docs/postgresql.md`:** New “PHP driver (`pdo_pgsql`)” section with verify command and link to Artisan failures.

---

## Testing instructions

### What to verify

- Documentation matches the two log failure modes (MySQL network / missing Postgres PDO) and states when SQLite-only workflows (e.g. default PHPUnit) avoid a database server.
- `.env.example` comments are accurate and not misleading for new contributors.

### How to test

- Read **`README.md`** (Requirements + Database step) and **`docs/postgresql.md`** (PHP driver section); skim **`.env.example`** DB block.
- **`php artisan test`** — should still pass (no application code changed).

### Pass/fail criteria

- **Pass:** Docs clearly explain `pdo_pgsql` for Postgres, `127.0.0.1` vs `localhost` / Docker `DB_HOST`, and causes of `2002 Network is unreachable`; SQLite default for CI/tests is mentioned where relevant.
- **Fail:** Missing extension guidance, or incorrect statements about when Artisan needs a reachable server.

---

## Test report

1. **Date/time (UTC) and log window:** Verification started **2026-03-30 16:43:02 UTC**. No `storage/logs/laravel.log` review required for this documentation-only change; no new application errors were introduced by the test run.

2. **Environment:** PHP **8.3.6** (CLI), branch **`agentdevelop`** (synced via `./scripts/git-sync-agent-branch.sh`). Node version not required for this task. PHPUnit used default SQLite testing configuration per project.

3. **What was tested:** Per **What to verify** / **How to test**: reviewed **README.md** (Requirements + Database), **docs/postgresql.md** (PHP driver section), **.env.example** DB block; ran **`php artisan test`** from repository root.

4. **Results:**
   - **Docs cover MySQL `2002` / `DB_HOST` / `127.0.0.1` vs `localhost` / Docker service name:** **PASS** — README “Database” connectivity bullets (lines 55–58) and `.env.example` MySQL block (lines 44–47).
   - **Docs cover missing Postgres PDO (`could not find driver`) and `pdo_pgsql`:** **PASS** — README Requirements (lines 14–17), **docs/postgresql.md** § PHP driver (`pdo_pgsql`) with `php -m` verify and explicit `could not find driver` / `db:show` text, `.env.example` lines 28–31.
   - **SQLite default for CI/tests and when a server is needed:** **PASS** — `.env.example` states SQLite default for PHPUnit/CI; README states SQLite needs no server for migrations/tests and server-backed DB needs live connection for `db:show`-style commands.
   - **`.env.example` comments accurate and not misleading:** **PASS** — PostgreSQL and MySQL blocks match stated failure modes and setup order (extension before `DB_CONNECTION=pgsql`).
   - **`php artisan test`:** **PASS** — Exit code **0**; **56 passed**, 4 skipped (Elasticsearch/Postgres-specific optional tests), **254 assertions**, duration ~1.79s.

5. **Overall:** **PASS** (all criteria above satisfied).

6. **Product owner feedback:** Contributors now have a single place in the README and `.env.example` that ties the two original log errors to concrete fixes (PDO extension vs host/network). The PostgreSQL doc section gives a copy-paste verification command, which should shorten time-to-green when switching to `pgsql`. No product behaviour change; risk is limited to documentation drift if future DB defaults change.

7. **URLs tested:** **N/A — no browser** (documentation and automated tests only).

8. **Relevant log excerpts (last section):**

```text
2026-03-30 16:43:02 UTC  (verification timestamp)

php artisan test (excerpt):
Tests:    4 skipped, 56 passed (254 assertions)
Duration: 1.79s
Exit code: 0
```

**GitHub:** No issue number (`#NN`) was referenced in this task file; labels/comments were not applied from this agent step.
