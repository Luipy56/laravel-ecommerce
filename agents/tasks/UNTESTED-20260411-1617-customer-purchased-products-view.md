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
