# Cursor rules for agents (laravel-ecommerce)

This repository uses **`.cursor/rules/*.mdc`** to give agents short, verifiable guidance.

- **`AGENTS.md`** — day-to-day operator guide (stack, sync, verification summary).
- **`docs/agent-loop.md`** — multi-agent roles, orchestrator, GitHub label hints, integration branch policy (aligned with **`git-agent-branch-workflow.mdc`** and **`commit-changelog-version.mdc`**).
- **This file** — catalog: **every** `*.mdc` under **`.cursor/rules/`** must appear in the table below. There are **no** excluded rule files right now; if one is ever omitted from the table, document the reason next to it.

Rules focus on **what to do when editing** a given area. Do not duplicate long branching or commit procedures in new docs — link **`AGENTS.md`**, **`docs/agent-loop.md`**, and the relevant **`.mdc`** instead.

## Categories

| Area | Rule file | When it applies |
|------|-----------|-----------------|
| **Git / branches** | `.cursor/rules/git-agent-branch-workflow.mdc` | Always — integration branch (**`agentdevelop`** default, override **`AGENT_GIT_BRANCH`**), sync **`./scripts/git-sync-agent-branch.sh`**, when to promote **`master`** |
| **Commits / changelog** | `.cursor/rules/commit-changelog-version.mdc` | User asks to commit; **`CHANGELOG.md`** entries under **`## [X.Y.Z] - date`** (match **`package.json`**); **`README.md`**, **`docs/`** scan; **patch bump per shippable task**; commits stay on integration branch (promotion per git rule above) |
| **Agent version bump** | `.cursor/rules/agent-task-version-bump.mdc` | Always — **mandatory** root **`package.json`** patch after **each completed prompt/task** that changes tracked files (before hand-off / push), not only when the user says **commit** |
| **App version / footer** | `.cursor/rules/app-version-cadence.mdc` | Always — why **`package.json`** drives **`footer.version`**; **when** to bump semver before push / **`prod`** |
| **Testing / verification** | `.cursor/rules/testing-verification.mdc` | Always — **`php artisan test`**, **`migrate:fresh --seed`** when schema/seeders change, **`routes:smoke`**, **`npm run build`** when front-end changes; **checkout / payments** manual checks and **`GET /api/v1/payments/config`**; env reference **`docs/CONFIGURACION_PAGOS_CORREO.md`** — **running** those commands is **opt-out by default**: see **`.cursor/rules/agent-verification-opt-in.mdc`** |
| **Verification opt-in (speed)** | `.cursor/rules/agent-verification-opt-in.mdc` | Always — **higher precedence** for *execution*: unless the user explicitly asks, **do not run** test / smoke / build / migrate-for-QA / discretionary payment browser checks; **always** bump root **`package.json`** patch before **`git push`** when tracked files changed (see **`agent-task-version-bump.mdc`**) |
| **Project standards** | `.cursor/rules/project-standards.mdc` | Migrations (edit existing, not new columns), i18n ca/es/en, **storefront + admin** (AdminLayout, list/toolbar patterns), shared components |
| **Admin shop settings** | `.cursor/rules/admin-shop-settings.mdc` | `shop_settings`, automatic **`is_trending`**, admin settings page, public **`shop/public-settings`**, featured OR query |
| **API** | `.cursor/rules/api.mdc` | REST API shape and conventions |
| **Auth** | `.cursor/rules/auth.mdc` | Custom session auth, login routes, SPA cookies; **API guest behaviour** — see **testing-verification** (*Auth / API note*) |
| **Security** | `.cursor/rules/security.mdc` | CSRF, validation, mass assignment, secrets, rate limits |
| **Laravel / PHP** | `.cursor/rules/laravel-php.mdc` | PHP backend style |
| **Blade** | `.cursor/rules/blade-views.mdc` | Blade shells / minimal server views |
| **React** | `.cursor/rules/react-use.mdc` | SPA patterns, hooks, a11y |
| **Vite** | `.cursor/rules/vite.mdc` | Build and asset entry |
| **Tailwind** | `.cursor/rules/tailwind.mdc` | Utility styling |
| **daisyUI** | `.cursor/rules/daisyui.mdc` | Component classes and themes |
| **i18n** | `.cursor/rules/i18n.mdc` | Catalan / Spanish / English UI strings |
| **Shared components** | `.cursor/rules/components.mdc` | PageTitle, ProductCard, admin layout details |
| **Ecommerce UX** | `.cursor/rules/ecommerce-ux.mdc` | Shop UX conventions |
| **Ecommerce SEO** | `.cursor/rules/ecommerce-seo.mdc` | SEO-related front patterns |
| **SVG** | `.cursor/rules/svg.mdc` | SVG assets |
| **SCSS** | `.cursor/rules/scss-basics.mdc` | SCSS when used |
| **No co-author branding** | `.cursor/rules/no-coauthor-no-branding.mdc` | Commits and assistant output |

## Inventory (sync check)

Alphabetical — must match **`ls .cursor/rules/*.mdc`** (24 files):

- `admin-shop-settings.mdc`
- `agent-task-version-bump.mdc`
- `agent-verification-opt-in.mdc`
- `api.mdc`
- `app-version-cadence.mdc`
- `auth.mdc`
- `blade-views.mdc`
- `commit-changelog-version.mdc`
- `components.mdc`
- `daisyui.mdc`
- `ecommerce-seo.mdc`
- `ecommerce-ux.mdc`
- `git-agent-branch-workflow.mdc`
- `i18n.mdc`
- `laravel-php.mdc`
- `no-coauthor-no-branding.mdc`
- `project-standards.mdc`
- `react-use.mdc`
- `scss-basics.mdc`
- `security.mdc`
- `svg.mdc`
- `tailwind.mdc`
- `testing-verification.mdc`
- `vite.mdc`

## Adding or changing rules

1. Prefer **new focused `.mdc` files** (one topic) over huge catch-alls.
2. Use YAML frontmatter: **`description`**, and either **`globs`** or **`alwaysApply: true`** — mirror existing files in **`.cursor/rules/`**.
3. Keep bodies **short and imperative**; link **`docs/`** or **`AGENTS.md`** for long procedures.
4. Add the filename to **Inventory** and a row to **Categories** when you add a rule.

## Related

- **`docs/agent-loop.md`** — multi-agent roles and task filenames.
- **`agents/tasks/README.md`** — task status pipeline (`wip`, `untested`, …).
