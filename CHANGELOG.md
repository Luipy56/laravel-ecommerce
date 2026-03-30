# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.3] - 2026-03-30

### Added

- Product catalog **`search_text`** (normalized name, code, description) with PostgreSQL **`pg_trgm` GIN** index on `products.search_text`; on PostgreSQL the baseline migration enables **`pg_trgm`**, **`citext`**, and **`unaccent`**, and uses **`citext`** for `clients.login_email` and `password_reset_tokens.email`.
- **`App\Services\ProductSearchTextRebuildService`**, contract **`RebuildsProductSearchText`**, and Artisan **`products:rebuild-search-text`** to rebuild `search_text` in bulk.
- **Catalog search** over HTTP: **`GET /api/v1/products/search`** (validated query and limit; **Elasticsearch** via Scout when configured, **PostgreSQL** `ILIKE` plus `pg_trgm` word similarity / similarity when on `pgsql`, token-wise `LIKE` on other drivers) with **`throttle:60,1`**; tuning via **`config/product_search.php`**.
- **Laravel Scout** with optional **Elasticsearch** for `Product`: custom **`App\Scout\ElasticsearchEngine`** using **`elasticsearch/elasticsearch`** (Scout 11–compatible), completion-oriented mapping for future autocomplete, queued **`MakeSearchable`** / **`RemoveFromSearch`** when **`SCOUT_QUEUE=true`**, default **`SCOUT_DRIVER=null`** for CI and environments without a cluster. See **`docs/elasticsearch.md`**.
- **Documentation:** **`docs/postgresql.md`** (PostgreSQL setup, `pdo_pgsql`, SSL, extensions, search); Elasticsearch notes in **`docs/elasticsearch.md`**.
- **Tests:** product search text, catalog search API, PostgreSQL search service, Scout indexing, optional live Elasticsearch integration (**`ES_TEST_HOST`**), and Scout mapping unit coverage.
- Agent task files under **`agents/tasks/`** for search/PostgreSQL/Elasticsearch work items and a closed **`db:show`** connectivity task archive.

### Changed

- **`.env.example`:** commented PostgreSQL and MySQL connectivity guidance (**`DB_SSLMODE`**, **`127.0.0.1`** vs **`localhost`**, Docker hostnames), plus Scout and Elasticsearch variables (**`ES_TEST_HOST`** for integration tests).
- **`README.md`:** PDO extension requirements per database driver, PostgreSQL recommendation, connectivity troubleshooting, link to **`docs/postgresql.md`**.
- **`config/database.php`:** PostgreSQL schema search path and SSL mode from environment.
- **`AppServiceProvider`:** bindings for catalog search and product search-text rebuild abstractions.
- **`Product` model:** `search_text` in **`$fillable`**, Scout **`Searchable`**, and search-text normalization helpers.
- **`ProductController`:** catalog search action wired to the search service contract.
- **`ProductSeeder`:** populates **`search_text`** for seeded products.
- **`phpunit.xml`:** default **`ES_TEST_HOST`** for optional Elasticsearch feature tests.
- **`routes/api.php`:** **`throttle:60,1`** on **`products/search`**.
- **`trash/diagramZero.dbml`:** schema notes aligned with search-related columns and PostgreSQL indexes.
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-03-30T10:12Z).

### Fixed

- **`CustomerTransactionalEmailTest`:** set **`payments.checkout_method_keys`** in simulated Bizum scenarios so checkout payment config matches test expectations.

## [0.1.2] - 2026-03-29

### Added

- `scripts/gh-bootstrap-agent-labels.sh`: idempotent `gh` helper to create GitHub labels used by the multi-agent workflow (`agent:planned`, `agent:wip`, `agent:testing`, `production-urgent`).

### Changed

- PayPal storefront: SDK script URL uses `intent=capture` and `commit=true`; checkout copy (ca/es) and `docs/CONFIGURACION_PAGOS_CORREO.md` clarify popup/overlay vs full-page redirect and that server capture implies PayPal-side approval.
- Agent pipeline (GitHub #2 smoke): removed queue pickup `agents/tasks/UNTESTED-20260327-1614-test.md`; updated archived `agents/tasks/done/2026/03/27/CLOSED-20260327-1614-test.md` with tester report; added `agents/tasks/done/2026/03/27/CLOSED-20260327-1614-test-hello-world-coder-artifact.md` for verification traceability.
- Agent pipeline: closed PayPal sandbox E2E verification task (`agents/tasks/UNTESTED-20260327-1401-paypal-checkout-sandbox-e2e.md` → `agents/tasks/done/2026/03/27/CLOSED-20260327-1401-paypal-checkout-sandbox-e2e.md` with tester report); `agents/001-log-reviewer/time-of-last-review.txt` updated.
- Agent pipeline: archived PayPal buyer-approval UI task (`agents/tasks/CLOSED-20260327-1745-paypal-approval-ui-popup-vs-redirect.md` → `agents/tasks/done/2026/03/27/CLOSED-20260327-1745-paypal-approval-ui-popup-vs-redirect.md`).
- Agent log reviewer: latest pass appended to `agents/001-log-reviewer/time-of-last-review.txt` (2026-03-27T18:14Z).
- Agent pipeline: Stripe order-pay task tracking moved to `agents/tasks/WIP-20260329-2114-stripe-not-configured-order-pay.md` (coder + tester notes).

### Fixed

- Order pay / Stripe: when card checkout is started without valid Stripe keys, respond with **422** and `code: payment_method_not_configured` (translated message) instead of treating it as an application failure; `PaymentProviderNotConfiguredException` is not reported to the log; `GET /api/v1/payments/config` availability aligns with the same credential rules as checkout start (`dontReport` in `bootstrap/app.php`).

## [0.1.1] - 2026-03-27

### Added

- Multi-agent workflow documentation and tooling (`AGENTS.md`, `agents/`, `docs/agent-loop.md`, `docs/agent-cursor-rules.md`, `scripts/git-sync-agent-branch.sh`).
- Cursor rules for commit/changelog/version workflow and git integration branch policy (`.cursor/rules/commit-changelog-version.mdc`, `.cursor/rules/git-agent-branch-workflow.mdc`).
- PayPal sandbox E2E operator checklist in `docs/CONFIGURACION_PAGOS_CORREO.md`.
- Feature test for PayPal-only checkout payments config (`CheckoutPaymentConfigTest`).

### Changed

- Default multi-agent integration branch is **`agentdevelop`** (`AGENT_GIT_BRANCH` overrides). Sync script and docs updated accordingly.
- **`laravel-ecommerce-agent-loop.sh`:** invoke **`cursor-agent`** with **`--print`** and the prompt file contents (current CLI; `-p` is `--print`, not a path).
- **`.cursor/rules/auth.mdc`:** cross-link to testing verification for unauthenticated `api/*` behaviour.

### Removed

- Obsolete agent task file `agents/tasks/UNTESTED-20260327-1542-paypal-authorizedjson-stdclass-typeerror.md`.
