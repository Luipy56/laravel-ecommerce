# Storefront search bar: live reload on input (not only Enter)

## Goal

The **client navbar** search (desktop and mobile rows in `resources/js/components/Navbar.jsx`) must **update the product catalog** as the user types, not only when they press **Enter** or click the search button.

Today, search runs via `handleSearch` on **form submit** only. Users expect the list to refresh after they edit the query, without an extra keystroke on Intro.

## Scope

1. **`Navbar.jsx`**
   - Keep local state for the input value (already synced from URL when `location.pathname === '/products'`).
   - After the user **changes** the search text, **debounce** (e.g. **300 ms**, aligned with admin list pages) and then **navigate** the same way as `handleSearch` does today:
     - Non-empty trimmed term → `/products?search=<encoded>` (preserve existing query needs: if the app supports category routes + search, follow the same rules as `ProductListPage` / `buildSearchParams` — document the chosen behaviour in the task’s implementation summary).
     - Empty term → `/products` (clear `search` from the URL).
   - **Submit** (Enter / button) can remain as an immediate navigation (optional optimisation) or delegate to the same logic; avoid duplicate navigations if debounce already fired.
   - Avoid excessive requests: debouncing is **required**; do not `navigate` on every keypress without delay.

2. **Consistency**
   - If there is any other **storefront** `type="search"` that only reacts on submit, align it with this behaviour only if it is part of the same product-search flow (scope narrowly; do not refactor unrelated admin pages — admin lists already use debounced API search).

## Out of scope

- Changing backend search APIs unless a bug is discovered during implementation.
- Elasticsearch / Scout tasks.

## Verification (before **UNTESTED-**)

- `npm run build` (front-end change).
- Manual: open `/` or `/products`, type in the navbar search **without** pressing Enter; after the debounce delay, URL and product list update. Clearing the field updates to show unfiltered list (or empty search) per implemented rules.
- `php artisan test` — regression only (no PHP change expected unless routes/tests need updates).

---

## Testing instructions

1. `./scripts/git-sync-agent-branch.sh` at repo root.
2. `npm run build`.
3. `php artisan test`.
4. Manual checks above on desktop (lg navbar form) and mobile (second row search).
