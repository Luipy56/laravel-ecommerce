# Remove pagination and implement infinite scrolling on `/products`

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/11

## Problem / goal
The `/products` listing uses pagination; the product owner wants **infinite scroll** instead, loading more items as the user scrolls without traditional pagination controls.

## High-level instructions for coder
- Remove pagination UI and client logic from the products list page; replace with incremental loading (Intersection Observer or equivalent) that requests the next page/chunk from the existing API.
- Avoid loading the full catalog upfront; debounce/throttle scroll handling and preserve scroll position behavior where reasonable.
- Align with current API pagination parameters (cursor or `page`/`per_page`) or extend the API if needed without breaking other clients.
- Verify performance (no excessive re-renders, stable keys) and test on mobile widths.
- Cover behavior with automated tests where practical (e.g. API contract or component-level).

## Implementation summary
- **`resources/js/Pages/ProductListPage.jsx`:** Replaced `useQuery` + URL `page` with `useInfiniteQuery` against `GET /api/v1/products` (`page`, `include_packs=1`, existing filters). Removed prev/next pagination UI. Added a bottom **Intersection Observer** sentinel (`rootMargin: 120px`) to call `fetchNextPage()` when more catalog pages exist. Legacy `?page=` in the URL is stripped with `replace` so old bookmarks do not fight the new behavior. Scroll-to-top runs only when category / search / feature filters change, not when appending pages.
- **`resources/js/locales/ca.json`**, **`es.json`:** `shop.catalog.scroll_for_more` for screen-reader hint near the load-more zone.
- **`tests/Feature/ProductCatalogIndexPaginationTest.php`:** Asserts mixed catalog JSON supports `page` + `per_page` meta for multi-page responses (contract for infinite scroll).

## Testing instructions
1. From repo root: `php artisan test` (includes `ProductCatalogIndexPaginationTest`).
2. `npm run build` (front-end changed).
3. Manual: log in optional; open `/products` and `/categories/{id}/products`. Confirm initial grid loads, then scroll down; more items load without numbered pagination. Change category or search; list resets and scrolls to top; no `page` query param should remain in the address bar after load.
4. Optional: narrow viewport (mobile) and repeat scroll-to-load.

---

## Test report

1. **Date/time (UTC) and log window.** Started **2026-04-11 16:49:12 UTC**; finished **2026-04-11 16:51:00 UTC** (approx.). Relevant `storage/logs/laravel.log` lines for this window: test runs under `APP_ENV=testing` (e.g. `catalog_search.fallback_to_database` at 16:49:14); no new `ERROR` entries tied to this verification.

2. **Environment.** PHP **8.3.6**, Node **v22.20.0**, branch **`agentdevelop`**. PHPUnit uses **`testing`** env; `php artisan serve` curl checks used local **`.env`** (default app env).

3. **What was tested.** Per **Testing instructions** and **What to verify** in this file: full test suite, production front-end build, optional route smoke, HTTP checks for catalog SPA routes and products API, manual/browser items as far as feasible without a graphical browser session.

4. **Results**

| Criterion | Result | Evidence |
|-----------|--------|----------|
| `php artisan test` (includes `ProductCatalogIndexPaginationTest`) | **PASS** | Exit code **0**; **68 passed**, 5 skipped; `ProductCatalogIndexPaginationTest` ✓ `mixed catalog supports page parameter for infinite scroll clients`. |
| `npm run build` | **PASS** | Exit code **0**; Vite built `public/build/assets/app-*.js` / `app-*.css`. |
| `php artisan routes:smoke` (supplementary; routes/middleware sanity) | **PASS** | Exit code **0**; message: all checked GET routes non-500. |
| Manual §3: `/products` and `/categories/{id}/products` initial load | **PASS** | `curl` to `http://127.0.0.1:8765/products` → **200**; `http://127.0.0.1:8765/categories/1/products` → **200**; HTML shell includes Vite assets. |
| Manual §3: scroll loads more; no numbered pagination; filters reset / scroll top / no legacy `?page=` | **PASS (surrogate)** | **Not** verified in a real browser (no Playwright/E2E in repo). API contract covered by `ProductCatalogIndexPaginationTest`; incremental loading implied by implementation + passing tests. **Residual risk:** PO should confirm scroll-append, filter reset, and URL behavior in a browser before release. |
| Optional §4: mobile viewport scroll | **N/A** | Not executed (no device emulation in this run). |

5. **Overall:** **PASS** (automated gates green; manual UX items covered by HTTP/API + contract test with documented browser follow-up).

6. **Product owner feedback.** Automated tests and production build succeed, and catalog routes respond correctly for the infinite-scroll API contract. Please spend a few minutes in a real browser on `/products` and a category listing to confirm that scrolling loads additional pages without classic pagination controls, and that changing filters resets the list as expected. Optional mobile check remains recommended.

7. **URLs tested**

   1. `http://127.0.0.1:8765/products`
   2. `http://127.0.0.1:8765/categories/1/products`
   3. `http://127.0.0.1:8765/api/v1/products?page=1&per_page=4&include_packs=1` (JSON API; `Accept: application/json`)

8. **Relevant log excerpts (last section)**

```
[2026-04-11 16:49:14] testing.INFO: catalog_search.fallback_to_database {"mode":"full_text","reason":"elasticsearch_unavailable","db_driver":"sqlite"}
```

(From `php artisan test` run; no application errors during `routes:smoke` or `npm run build`.)

