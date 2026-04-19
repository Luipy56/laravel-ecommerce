# Advanced admin query builder / universal search engine

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/16

## Problem / goal
Admins need a **new `/admin` section** (dashboard-level importance) for exploring data without SQL: free text plus structured filters, optional table scope, date ranges, postal codes, aggregations (sum/count/avg, group by month/weekday/postal code, etc.), and results that stay usable for non-technical users. The engine should be **schema-aware** and avoid a fixed library of hardcoded SQL strings for every report.

## High-level instructions for coder
- Break the work into phases: discovery (schema introspection strategy, safety/permissions), MVP (global search + table pick + export-friendly results), then aggregations and advanced filters.
- Define **authorization and safety** first: which admin roles may run broad queries, rate limits, row/column allowlists or read-only replicas, and query timeouts to protect production.
- Design API + Admin UI consistent with **AdminLayout** patterns (toolbar, lists, i18n under `admin.*`).
- Plan performance for large tables (indexes, optional async jobs for heavy aggregates) and document operational limits for the team.

---

## Implementation summary (coder, 2026-04-19 UTC)

**MVP delivered**

1. **Allowlisted schema exploration** — `config/admin_data_explorer.php` defines per-table allowed columns, searchable columns, and date columns. The service intersects this list with `Schema::getColumnListing()` so responses stay aligned with the database.
2. **API** (`auth:admin`):  
   - `GET /api/v1/admin/data-explorer/schema` — tables, columns, limits.  
   - `POST .../query` — paginated read-only rows; global `q` search across configured text columns; optional date range on an allowed date column; optional sort. Throttle: 30/min.  
   - `POST .../export` — UTF-8 CSV with BOM, row cap from config. Throttle: 10/min.  
   - `POST .../aggregate` — `count` / `sum` / `avg` with `group_by` on an allowlisted column; same filters as query. Result groups capped by `max_aggregate_groups`. Throttle: 20/min.
3. **Safety** — No client-provided SQL; identifiers restricted to allowlists; `LIKE` pattern escaping; optional `max_execution_time` (MySQL) / `statement_timeout` (PostgreSQL); read-only `select` usage.
4. **Admin UI** — `/admin/data-explorer` (“Consulta de dades” / “Consulta de datos”): table picker, search, optional date filters, results table, pagination, CSV export, aggregation panel.
5. **Tests** — `tests/Feature/AdminDataExplorerTest.php`; `AdminUserJourneyTest` extended with schema GET.
6. **i18n** — React: `resources/js/locales/ca.json`, `es.json` under `admin.data_explorer.*` and `admin.nav.data_explorer`. PHP errors: `lang/ca.json`, `lang/es.json` keys `admin.data_explorer.errors.*`.

**Not in this iteration (follow-up)** — Dedicated “group by month/weekday/postal code” presets (requires joins or calendar logic); structured filter builder in the UI (filters are supported server-side via API for future UI); separate admin roles for explorer (currently all authenticated admins).

---

## Testing instructions

1. **Sync** (multi-agent): from repo root, `./scripts/git-sync-agent-branch.sh`.
2. **PHPUnit:** `php artisan test` (includes `AdminDataExplorerTest`, `AdminUserJourneyTest`).
3. **Front-end build:** `npm run build`.
4. **Manual (admin UI):** Log in at `/admin/login` (e.g. seeded `manager` / `admin`). Open **Consulta de dades** in the sidebar (`/admin/data-explorer`). Select table **Comandes / Pedidos**, click **Consultar**, confirm rows and pagination work. Use **Exportar CSV** and open the file in a spreadsheet. Under aggregation, choose metric **Recompte / Recuento**, group by **`kind`**, click **Calcular**, confirm grouped counts.
5. **Manual (API):** With admin session cookie, `GET /api/v1/admin/data-explorer/schema` returns `success` and `data.tables`. Without auth, same URL returns **401** JSON.

---

## Tester
- Hand off to **testing** agent: rename this file from **`UNTESTED-…`** to **`TESTING-…`** when verification starts.
