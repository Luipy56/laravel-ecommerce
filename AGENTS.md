# Agent operating instructions

These instructions apply to all work in this repository:

- **Commits:** Do not add `Co-authored-by:`, `Signed-off-by:`, or any Cursor/agent/IDE attribution to commit messages. To enforce stripping locally, run `./scripts/install-git-hooks.sh` once. **Commit completed work** when you finish a change the user asked for; do not leave the tree dirty without reason. Use **SSH** for `git remote` when configured (`git@github.com:Luipy56/laravel-ecommerce.git`).
- **Branches and integration (`agentdevelop` vs `master`) — essential:** Do routine work on the **integration branch** (**`agentdevelop`** by default). Override with **`AGENT_GIT_BRANCH`** only if the team uses another shared branch. **Before changing files** (including task markdown under **`agents/tasks/`**), sync: run **`./scripts/git-sync-agent-branch.sh`** — see **`.cursor/rules/git-agent-branch-workflow.mdc`**. After committing, pull/rebase again, then **`git push origin <integration-branch>`**. Merge **`agentdevelop` → `master`** (or promote via **`prod`** per your release process) **only** when: (1) a **~2-hour** batch promotion window, (2) a **production-impacting** change (security, payments, critical bugs, blocking migrations), or (3) the **GitHub issue** or **user** explicitly requests **urgent / hotfix / production**. Otherwise **do not** merge to **`master`** routinely. When the user says to push without asking for production: push the **integration branch**. Details: **`docs/agent-loop.md`**.
- **Stack:** Laravel (API + session auth), React SPA (Vite) under **`resources/js/`**, Tailwind CSS 4 + daisyUI 5. See **`.cursor/rules/project-standards.mdc`** and **`docs/agent-cursor-rules.md`**.
- **Verification:** Default policy is **`.cursor/rules/agent-verification-opt-in.mdc`**: unless the user explicitly asks for checks, **do not run** test / smoke / build / migrate-for-QA as automatic gates; **always** bump root **`package.json`** patch before **`git push`** when tracked files changed (**`.cursor/rules/agent-task-version-bump.mdc`**). When the user **does** request full verification, follow **`.cursor/rules/testing-verification.mdc`** (and **`app-version-cadence.mdc`** for releases).
- **Cursor rules:** Short constraints live in **`.cursor/rules/*.mdc`**. A categorized index is in **`docs/agent-cursor-rules.md`**.

## Project overview

Laravel ecommerce: REST API + React storefront and admin UI.

- **Backend:** Laravel (`app/`, `routes/`, `database/`).
- **Frontend:** React + Vite (`resources/js/`).
- **Assets:** Vite (`npm run dev` without HMR by default, `npm run dev:hmr` for hot reload, `npm run build`).

## Setup and development

**Prerequisites:** PHP 8.2+, Composer, Node.js 18+, database (MySQL/PostgreSQL/SQLite per `.env`).

1. **Install PHP deps:** `composer install`
2. **Environment:** copy `.env.example` → `.env`, generate key: `php artisan key:generate`
3. **Database:** `php artisan migrate --seed` (or `migrate:fresh --seed` in development when schema changes)
4. **Frontend deps:** `npm ci` (or `npm install` per team habit)
5. **Run:** `php artisan serve` and `npm run dev` (or your local orchestration script)

## Smoke checks

When the user **explicitly** asks for verification before calling a fix complete, run:

1. **`php artisan test`**
2. **`php artisan routes:smoke`** (no HTTP **500** on GET routes)
3. When **`resources/js/`** or Vite config changed: **`npm run build`**
4. When checkout or payments changed: open **`/checkout`** with a logged-in user and non-empty cart; confirm **`GET /api/v1/payments/config`** per **`.cursor/rules/testing-verification.mdc`**

Otherwise see **`.cursor/rules/agent-verification-opt-in.mdc`** (skip the list above by default).

## Multi-agent task workflow (optional)

Task pipeline and roles: **`docs/agent-loop.md`** (target **`agents/tasks/`**, coder/tester/closing/committer handoffs).

**Orchestrator:** **`./agents/laravel-ecommerce-agent-loop.sh`** (full cycle: **001** log reviewer first, then FEAT/coder/tester/closer/committer; requires **`cursor-agent`** on `PATH`).

**Git sync:** Each step runs **`./scripts/git-sync-agent-branch.sh`** unless **`AGENT_GIT_SYNC=0`**. Branch: **`AGENT_GIT_BRANCH`** (default **`agentdevelop`**).

**Prompt index:** **`agents/README.md`**.

## Related documentation

- **`docs/agent-loop.md`** — roles, task filenames, GitHub labels, orchestrator.
- **`docs/CONFIGURACION_PAGOS_CORREO.md`** — payment env vars (Stripe, PayPal, etc.).
