# Audit Cursor rules and keep docs/agent-cursor-rules.md accurate

## Problem / goal

The multi-agent workflow added **`docs/agent-cursor-rules.md`** as a catalog of **`.cursor/rules/*.mdc`**. Over time, new rules may be added without updating the index, or overlapping rules may drift. This task brings the catalog and the filesystem back in sync and tightens guidance where gaps exist (payments, auth edge cases, admin vs storefront).

## High-level instructions for coder

- List every **`.cursor/rules/*.mdc`** file and ensure **`docs/agent-cursor-rules.md`** has one row per file (or an explicit decision to exclude with reason).
- Remove redundant cross-duplication between rules where safe; prefer **one source of truth** and links to **`AGENTS.md`** / **`docs/agent-loop.md`**.
- Confirm **`git-agent-branch-workflow.mdc`** and **`commit-changelog-version.mdc`** align with **`AGENTS.md`** and **`docs/agent-loop.md`** (integration branch naming, `AGENT_GIT_BRANCH`).
- Do not change application behavior unless a rule clearly contradicts the codebase (then fix rule or code in the smallest scope).

## Acceptance criteria

- **`docs/agent-cursor-rules.md`** matches the current set of `.mdc` files.
- No broken internal references between agent docs and rules.
- **`php artisan test`** passes after edits (if only markdown changed, still run a quick test pass when feasible).

## Testing instructions

1. Confirm **`docs/agent-cursor-rules.md`** — **Inventory** lists 20 names and matches `ls -1 .cursor/rules/*.mdc` (same count and filenames).
2. Spot-check internal links: **`AGENTS.md`**, **`docs/agent-loop.md`**, **`docs/CONFIGURACION_PAGOS_CORREO.md`**, and referenced **`.mdc`** paths exist.
3. Run **`php artisan test`** from the repo root (expect all tests passing; no application code was changed).
4. Optionally open **`.cursor/rules/auth.mdc`** and confirm the **API guests** bullet points only to **testing-verification** (no contradictory duplicate policy).

---

## Test report

1. **Date/time (UTC) and log window:** 2026-03-27T17:08:21Z start; verification completed same window. No Laravel log review required (documentation-only scope).

2. **Environment:** PHP 8.3.6, Node v22.21.0; branch `agentdevelop` (synced via `./scripts/git-sync-agent-branch.sh`). `APP_ENV` not required for this verification.

3. **What was tested:** Per **Testing instructions** — inventory sync vs `ls -1 .cursor/rules/*.mdc`, spot-check of referenced paths, `php artisan test`, optional `auth.mdc` API guests cross-reference.

4. **Results:**
   - Inventory (20 files, alphabetical) matches filesystem — **PASS** — `diff` of sorted basenames vs **Inventory** bullets: empty diff (`inventory identical`).
   - Referenced paths exist — **PASS** — `test -f` on `AGENTS.md`, `docs/agent-loop.md`, `docs/CONFIGURACION_PAGOS_CORREO.md`, `.cursor/rules/testing-verification.mdc`, `.cursor/rules/git-agent-branch-workflow.mdc`.
   - `php artisan test` — **PASS** — 30 tests, 165 assertions, exit code 0.
   - `auth.mdc` API guests defers to `testing-verification.mdc` — **PASS** — line 13: “See **`.cursor/rules/testing-verification.mdc`** (section *Auth / API note*)”.

5. **Overall:** **PASS** (all criteria met).

6. **Product owner feedback:** El índice de reglas en `docs/agent-cursor-rules.md` está alineado con los 20 ficheros `.mdc` reales y los enlaces comprobados existen. La suite de tests automatizados sigue en verde, así que el cambio documental no ha roto el comportamiento de la aplicación.

7. **URLs tested:** N/A — no browser.

8. **Relevant log excerpts:** PHPUnit summary (stdout): `Tests: 30 passed (165 assertions)`; `RouteSmokeTest` included: `all distinct get routes do not return 500`.

**GitHub:** No issue number (`#NN`) on this task — labels/comments not updated.

**Tester:** Renamed `UNTESTED-` → `TESTING-` → `CLOSED-` (same slug `20260327-1400-cursor-rules-audit-and-index`).
