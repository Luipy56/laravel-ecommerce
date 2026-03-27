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
