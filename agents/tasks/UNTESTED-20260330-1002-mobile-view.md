# Mobile view

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/4

## Problem / goal
Several pages do not behave well on small viewports, and there is no clear, project-specific guidance on how to implement consistent responsive layouts (storefront and admin).

## High-level instructions for coder
- Review main user-facing and admin routes at typical mobile widths; fix layout issues (overflow, navigation, tables, forms, spacing) using Tailwind responsive utilities and daisyUI components per project rules.
- Prefer reusing shared layout and list patterns so fixes stay consistent across pages.
- Add brief, actionable documentation for contributors (for example in the root README or an existing `docs/` page) describing breakpoints and conventions for new UI work.
- After changes, smoke-check critical flows on a narrow viewport; keep all user-visible copy on translation keys (`ca` / `es`).

## Implementation summary (coder)
- **Orders list:** `OrdersPage.jsx` — cards stack on narrow viewports; actions full-width below `sm`.
- **Overflow:** `w-full min-w-0` on major `max-w-*` page shells (orders detail, product/pack detail, profile, auth pages, custom solution, not found, session expired); `min-w-0` on checkout form card.
- **Admin:** drawer sidebar `max-w-[min(100vw,16rem)]`; mobile pack/order/variant-group cards use explicit `flex` + `min-w-0` on `card-body`.
- **Docs:** `docs/mobile-responsive.md` extended with shells, action cards, admin card rows.

## Testing instructions
1. **`npm run build`** — must succeed.
2. **`php artisan test`** — all tests pass.
3. **`php artisan routes:smoke`** — no HTTP 500 on GET routes.
4. **Manual (narrow viewport, e.g. 375px width):**
   - Logged-in user: **`/orders`** — each order card shows stacked layout; “Detall” / invoice buttons usable without horizontal page scroll.
   - Spot-check **`/orders/:id`**, **`/checkout`**, **`/profile`**, **`/products/:id`** — no unexpected horizontal scroll on body.
   - **Admin** (if session available): open mobile drawer; ficha pack / pedido with mobile card lists — text truncates, no overflow.
5. **GitHub #4:** comment posted with change summary (if `gh` authenticated).

## GitHub
- Comment added on issue **#4** with implementation summary.
