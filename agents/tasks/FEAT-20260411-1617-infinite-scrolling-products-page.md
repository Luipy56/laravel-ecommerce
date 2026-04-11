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
