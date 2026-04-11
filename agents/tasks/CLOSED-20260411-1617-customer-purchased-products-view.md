# Feature: Customer Purchased Products View

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/9

## Problem / goal
Customers can see order history at `/orders` but cannot easily browse **products they have bought** in one place. Add a customer-area experience that lists previously purchased products with optional date filtering, rich rows (image, price, purchase date, metadata), row click to product detail (`/products/{id}`), and a control to open the originating order.

## High-level instructions for coder
- Design API + React page(s) under the authenticated customer area; reuse existing order line / product data models and API patterns.
- Support optional filtering by purchase date (query params or UI controls consistent with the rest of the shop).
- Ensure each row/card is fully clickable to the product detail route; add a separate action linking to the specific order.
- Follow i18n (`ca` / `es`), shared components (e.g. `PageTitle`, `ProductCard` where appropriate), and accessibility for keyboard/screen readers.
- Add or extend tests for the new endpoints and critical UI behavior.

## Implementation notes (coder)
- **API:** `GET /api/v1/purchases` (auth required), query `date_from`, `date_to`, `page`. Returns paginated order lines from `kind = order` with product or pack rows, ordered by order date (newest first). `PurchasedProductsController` in `app/Http/Controllers/Api/`.
- **SPA:** `/purchases` — `PurchasesPage.jsx`; nav link under user menu (with Orders). Rows: image, name, pack badge, date + quantity, line total; main area links to `/products/{id}` or `/packs/{id}`; “View order” links to `/orders/{id}`.
- **Tests:** `tests/Feature/PurchasedProductsTest.php` (auth, payload, date filter, validation).

## Testing instructions
1. `php artisan test` and `php artisan routes:smoke` (already run in dev; re-run after changes).
2. `npm run build` when `resources/js` changed.
3. **Manual:** Log in as a client with at least one completed order line (seed or checkout). Open `/purchases` from the user dropdown. Confirm rows show image, totals, and date; click the row (not “View order”) → product or pack detail; “View order” → order detail. Set **Des de / Fins a** and **Aplicar** — list should filter by order date; **Netejar** resets filters. Guest: `/purchases` should prompt login.

---

## Test report

1. **Date/time (UTC) and log window**
   - Started: **2026-04-11 16:58:55 UTC**
   - Finished: **2026-04-11 17:03:00 UTC** (approx.)
   - Log window reviewed: `storage/logs/laravel.log` tail for the session (no purchases-specific errors; general test-run noise only).

2. **Environment**
   - **Branch:** `agentdevelop`
   - **PHP:** 8.3.6 (CLI)
   - **Node:** v22.20.0
   - **APP_ENV:** `local` (from `.env`; automated tests use Laravel `testing` + in-memory SQLite per suite)

3. **What was tested** (from “What to verify” / testing instructions)
   - `php artisan test` (full suite)
   - `php artisan routes:smoke`
   - `npm run build`
   - **Manual (API + HTTP shell):** session login via `POST /api/v1/login` with CSRF cookies; `GET /api/v1/purchases` and date query params; guest `GET /api/v1/purchases` → 401; `GET /purchases` → 200 SPA shell.
   - **Not executed:** full graphical browser session (user menu → `/purchases`, row click navigation, “View order”, **Des de / Fins a** / **Aplicar** / **Netejar** UI). Rationale: no browser automation in this run; behavior covered by `PurchasedProductsTest` + API checks below.

4. **Results**

   | Criterion | Result | Evidence |
   |-----------|--------|----------|
   | `php artisan test` | **PASS** | Exit code 0; `Tests\Feature\PurchasedProductsTest` all passed (guest 401, authenticated payload, date filters, invalid range 422). Suite: 68 passed, 5 skipped. |
   | `php artisan routes:smoke` | **PASS** | Exit code 0; message: “All checked GET routes returned a non-500 status.” |
   | `npm run build` | **PASS** | Exit code 0; Vite built successfully (daisyUI CSS warning only). |
   | API auth & payload | **PASS** | After session login, `GET http://127.0.0.1:8765/api/v1/purchases?page=1` returned `success: true` with lines including `image_url`, `line_total`, `order_date`, `quantity`, `order_id`, `product_id`. |
   | Date filters (`date_from` / `date_to`) | **PASS** | `GET .../purchases?date_from=2026-12-01&date_to=2026-12-31` returned HTTP 200 and filtered December rows (10 items in sample DB). |
   | Guest API | **PASS** | `GET .../api/v1/purchases` without session → HTTP **401** JSON `{"message":"Unauthenticated."}`. |
   | `/purchases` route | **PASS** | `GET .../purchases` → HTTP **200** (SPA shell; same-origin client handles auth UX). |
   | Full manual UI (dropdown, row/order links, filter buttons) | **PARTIAL** | Not exercised in a real browser this run; recommend quick human QA on `/purchases` for clicks and i18n filter controls. |

5. **Overall:** **PASS** — automated and API-level checks satisfy the task; one **PARTIAL** item (visual/browser UI) documented for follow-up QA.

6. **Product owner feedback**
   The purchased-products API and automated tests behave as specified: authenticated clients get paginated lines with image, totals, and dates; guests get 401 on the API; date filtering works. A short human pass in the browser is still worthwhile to confirm the React page navigation (product vs order links) and the **Des de / Fins a** controls match expectations in Catalan/Spanish.

7. **URLs tested** (numbered)
   1. `http://127.0.0.1:8765/` (GET, session bootstrap)
   2. `http://127.0.0.1:8765/api/v1/login` (POST, session login)
   3. `http://127.0.0.1:8765/api/v1/purchases?page=1`
   4. `http://127.0.0.1:8765/api/v1/purchases?date_from=2026-12-01&date_to=2026-12-31`
   5. `http://127.0.0.1:8765/api/v1/purchases` (GET, no cookies — guest)
   6. `http://127.0.0.1:8765/purchases` (GET, SPA shell)

8. **Relevant log excerpts**
   - No dedicated purchases errors in `laravel.log` for this verification window.
   - PHPUnit run produced no failures; sample tail lines are unrelated search/webhook INFO from the same day’s test runs.

**Loop protection:** N/A (first verification cycle for this task).
