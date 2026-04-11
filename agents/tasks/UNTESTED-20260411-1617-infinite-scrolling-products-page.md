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

