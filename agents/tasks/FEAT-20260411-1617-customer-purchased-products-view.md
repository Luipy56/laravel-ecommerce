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
