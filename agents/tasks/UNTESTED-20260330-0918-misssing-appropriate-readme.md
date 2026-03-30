# Missing appropriate README

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/3

## Problem / goal
The repository lacks a proper README: it should explain what the project does, include screenshots where helpful, and orient new contributors and operators.

## High-level instructions for coder
- Audit existing `README.md` and `docs/` against the issue; decide what belongs in the root README versus deeper docs (setup, stack, payments, agent workflow).
- Add clear sections: project purpose, main stack (Laravel API + React/Vite), prerequisites, install/run steps, testing smoke commands, and pointers to payment/email docs if relevant.
- Include or link to screenshots or UI captures only if the team can host them without bloating the repo (e.g. `docs/` assets or documented external links); avoid secrets in examples.
- Align wording with `AGENTS.md` / `docs/agent-loop.md` only where it helps human readers; keep the README focused on product and development, not internal agent-only detail.
- After substantive doc changes, follow project verification rules if anything references commands that must stay accurate (e.g. `composer install`, `npm ci`, `php artisan migrate`).

## Implementation summary
- Replaced the default Laravel skeleton **`README.md`** with a project-specific document: purpose, stack (Laravel API + session auth, React/Vite, Tailwind 4, daisyUI 5), storefront/admin/API overview, prerequisites, full quick start (`composer install`, `.env`, migrate/seed, `npm ci`, `php artisan serve` + `npm run dev`, `npm run build`).
- Added a **verification** table (`php artisan test`, `routes:smoke`, `npm run build`) and a pointer to **`.cursor/rules/testing-verification.mdc`** for checkout/payments checks.
- Added a **Documentation** table linking **`AGENTS.md`**, payments (`docs/CONFIGURACION_PAGOS_CORREO.md`), email, mobile notes, and optional agent docs without duplicating orchestrator detail.
- **Screenshots:** documented policy (no binaries in repo by default; optional `docs/screenshots/` or external links) per task constraints.

## Testing instructions
1. Open **`README.md`** at repo root and confirm Markdown renders (headings, tables, code fences).
2. Click or resolve every **relative link** in the README (`AGENTS.md`, `docs/*.md`) and confirm targets exist. License is stated as MIT with reference to `composer.json` (no root `LICENSE` file required).
3. From a clean shell in the repo root, spot-check that documented commands match the project: `composer validate` (optional), `npm run build` if Node deps are installed (confirms front-end build still works; not strictly required for doc-only change).
4. Skim against **`AGENTS.md`**: no contradiction on PHP/Node versions, migrate/seed, or smoke commands.
5. **GitHub:** Issue **#3** should reflect that the root README now describes the ecommerce app and points to deeper docs.
