# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-04-11T17:31Z).

## [0.1.5] - 2026-04-11

### Added

- **Storefront:** Customer purchased products (**`/purchases`**): authenticated **`GET /api/v1/purchases`** with pagination and optional **`date_from`** / **`date_to`** filters; rows link to product or pack detail and to the originating order; user menu entry alongside Orders (GitHub **#9**).
- **Tests:** `PurchasedProductsTest`; `ProductCatalogIndexPaginationTest` asserts paginated mixed catalog responses for infinite-scroll clients.

### Fixed

- **Storefront:** Rapid catalog search no longer shows a misleading “could not connect” toast when React Query aborts a superseded `GET /api/v1/products` request (axios cancellation is no longer treated as a network failure in `resources/js/api.js`).

### Changed

- **Payments:** Checkout uses **Stripe Checkout** (hosted redirect) for card/wallets/Bizum (configurable `STRIPE_CHECKOUT_PAYMENT_METHOD_TYPES`); PayPal exposes **`approval_url`** when the REST order includes an approve link (redirect before Smart Buttons). Stripe webhooks handle **`checkout.session.completed`** with idempotency (`stripe_webhook_events` table); Redsys/Revolut removed from storefront and public webhook routes. `PAYMENTS_CHECKOUT_METHODS` accepts only `card` and `paypal`.
- **Storefront:** Product catalog (`/products`, `/categories/:id/products`) uses **infinite scroll** (incremental `page` loads via Intersection Observer) instead of prev/next pagination; legacy `?page=` query params are ignored/stripped.
- Agent pipeline: archived closed Laravel search service PostgreSQL `pg_trgm` task (`agents/tasks/done/2026/03/30/CLOSED-20260330-1825-laravel-search-service-postgresql-trgm.md`).
- Agent pipeline: archived closed Elasticsearch Scout mapping / queue sync task and catalog search API task (Elasticsearch primary, PostgreSQL fallback) (`agents/tasks/done/2026/03/30/CLOSED-20260330-1835-elasticsearch-scout-mapping-queue-sync.md`, `agents/tasks/done/2026/03/30/CLOSED-20260330-1845-search-api-elasticsearch-primary-postgresql-fallback.md`).
- Agent pipeline: archived closed customer purchased products view task (`agents/tasks/done/2026/04/11/CLOSED-20260411-1617-customer-purchased-products-view.md`).
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-04-11T16:57Z).

## [0.1.4] - 2026-03-31

### Added

- **Search operator tooling:** Artisan **`products:reindex-elasticsearch`** (flush / recreate index / import with optional `--chunk` and `--queued`; skips safely when Scout is not on Elasticsearch). **`products:rebuild-search-text`** gains **`--stale`** and **`--chunk`**. **`SearchDemoProductSeeder`** adds deterministic `SEARCH-DEMO-*` products for manual search checks after **`migrate:fresh --seed`**.
- **Tests:** `ReindexElasticsearchProductsCommandTest`, PostgreSQL **`idx_products_search_text_trgm`** presence check, and stale rebuild coverage in **`ProductSearchTextTest`** (catalog search fallback remains covered in **`ProductCatalogSearchApiTest`**).
- **Search synonyms:** `config/search_synonyms.php` drives token expansion in `ProductSearchService` (PostgreSQL / SQLite paths) and merges an Elasticsearch `synonym_graph` + `product_synonym` analyzer into the products index definition when groups are non-empty. **`config/search_locales.php`** documents future multilingual index strategies without schema changes.

### Changed

- **Storefront:** Navbar catalog search updates the URL after **300 ms** debounce while typing; preserves **`category_id`** and **`feature_id`** filters on **`/products`** and **`/categories/:slug/products`**. Submit runs navigation immediately (clears pending debounce).
- Agent pipeline: PostgreSQL extensions / search-text GIN task tracking renamed from **`UNTESTED`** to **`WIP`** (`agents/tasks/WIP-20260330-1815-postgresql-extensions-search-text-gin.md`).
- Agent pipeline: archived closed Scout **`EngineManager`** boot binding task (**`CLOSED-20260331-0943-scout-enginemanager-binding-resolutionexception.md`**) under **`agents/tasks/done/2026/03/31/`**.
- Agent pipeline: closed storefront navbar debounced search task (**`agents/tasks/done/2026/03/31/CLOSED-20260331-1200-storefront-searchbar-debounced-live-update.md`**).
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-03-31T10:03Z).

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
