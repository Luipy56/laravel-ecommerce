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

## Verification (before **UNTESTED-**)

- `php artisan migrate:fresh --seed` (PostgreSQL dev DB).
- `php artisan test`.
- `php artisan routes:smoke`.
- `npm run build` if front-end or Vite touched.
- Run new Artisan commands locally and paste brief evidence into task **Test report** (tester step).

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh`.
2. **PostgreSQL dev DB:** `php artisan migrate:fresh --seed` → **0**.
3. `php artisan test` — full suite green.
4. `php artisan routes:smoke` — no HTTP 500.
5. **Commands:** run reindex and `search_text` rebuild; verify row counts / ES document counts (if ES available).
6. `npm run build` if applicable.
7. **Manual read:** setup doc is sufficient for a new developer to enable PG extensions and ES.
