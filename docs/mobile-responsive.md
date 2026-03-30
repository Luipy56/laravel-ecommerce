# Mobile and responsive layout (storefront + admin)

Short reference for this repo. Full utility rules live in `.cursor/rules/tailwind.mdc` and `.cursor/rules/daisyui.mdc`.

## Breakpoints (Tailwind 4 defaults)

| Prefix | Min width | Typical use |
|--------|-----------|-------------|
| *(none)* | 0 | Base = mobile-first |
| `sm:` | 40rem (640px) | Larger phones, small tablets |
| `md:` | 48rem (768px) | Tablets |
| `lg:` | 64rem (1024px) | Desktop; admin sidebar always visible (`lg:drawer-open`) |
| `xl:` | 80rem (1280px) | Wide desktop (e.g. navbar search width) |

## Conventions used here

- **Storefront:** `Layout.jsx` wraps the app in a **daisyUI drawer** (`id="drawer-nav"`). The hamburger in `Navbar.jsx` toggles it on viewports below `lg`. Primary links mirror the desktop bar: home, catalog, custom solution, cart.
- **Admin:** `AdminLayout.jsx` uses the same drawer pattern with `lg:drawer-open` so the sidebar is fixed on large screens. The sidebar uses `max-w-[min(100vw,16rem)]` so it never exceeds the viewport width on very narrow devices. List pages use **toolbar** on one row (`flex flex-wrap … gap-2 sm:gap-4`) and **horizontal scroll** on wide tables (`overflow-x-auto` on a wrapper).
- **Overflow:** Prefer `min-w-0` (and `max-w-full` on flex children) so nested flex/grid layouts do not force horizontal page scroll. Long **breadcrumbs** scroll inside a horizontal strip instead of squashing the header.
- **Max-width shells:** Pages that use `max-w-*` plus `mx-auto` should also set **`w-full min-w-0`** on that wrapper so content stays inside the global `container` and does not widen the page on small screens.
- **Cards with side-by-side actions:** Use a **column-first** layout and switch to row on `sm:` (example: order list in `OrdersPage.jsx` — summary stacked above price and actions; full-width buttons on the narrowest widths).
- **Admin list “mobile cards”:** Keep `min-w-0` on horizontal `card-body` rows (image + text) so long titles truncate instead of overflowing.
- **Viewport:** `welcome.blade.php` sets `width=device-width, initial-scale=1` and `viewport-fit=cover` for notched devices.

When adding pages, default to a single-column stack, then add `sm:` / `lg:` columns or side-by-side only where it helps.
