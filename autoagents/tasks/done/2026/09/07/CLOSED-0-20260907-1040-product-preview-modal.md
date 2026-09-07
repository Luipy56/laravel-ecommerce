---
## Closing summary (TOP)

- **What happened:** Storefront product/pack card clicks navigated straight to the detail page; the intermediate preview modal had been removed and needed restoring.
- **What was done:** Restored `ProductPreviewModal` and wired `ProductCard` so click/Enter/Space opens the modal (detail CTA + add-to-cart; favorite/cart keep stopPropagation); version bumped to 0.1.417.
- **What was tested:** Tester PASS — `npm run build` OK, `php artisan test` 216 passed / 5 skipped, static review of modal/card/i18n; live storefront on this branch not reachable from chrome MCP.
- **Why closed:** All acceptance criteria met and test report overall PASS.
- **Closed at (UTC):** 2026-09-07 10:49
---

# Restore intermediate product preview modal on card click

## Origin
- **Operator:** Luipy (Discord / Maestro)
- **Env:** serra-prod (`/srv/serra/prod`)
- **No GitHub issue** (operator request)

## Problem / goal
On the storefront home and `/products/`, clicking a product (or pack) card navigates straight to the full detail page (`/products/:id` or `/packs/:id`). We want an **intermediate preview modal** again: card click opens the modal; a “view more info” action goes to the full detail page. Add-to-cart should remain available from the modal.

This modal existed in prod and was later removed (see `CHANGELOG.md`: “intermediate preview modal removed”). It is **not** present as commented-out code in current prod `ProductCard`. Reference implementation that still has it: **serraalt** = serra-style at `/srv/serra/style` (vhost `serraalt.ldeluipy.es` → port 18082).

## High-level instructions for coder
- Compare prod vs style:
  - Reference: `/srv/serra/style/source/resources/js/components/ProductPreviewModal.jsx` and how `ProductCard.jsx` / `HomePage.jsx` / `ProductListPage.jsx` open it.
  - Prod today: `resources/js/components/ProductCard.jsx` calls `navigate(detailUrl)` on card click.
- Prefer restoring/adapting from **serra-style** (and/or git history of this repo’s former `ProductPreviewModal`) into **prod** React + daisyUI patterns — do **not** copy style-only design tokens / rubenserra BEM wholesale; match current serra-prod card/detail look (existing SCSS, icons, i18n helpers like `catalogFeatureTypeLabel`).
- Wire card activation on home, product list, favorites, and any other catalog grids that use `ProductCard` so click opens the preview modal instead of immediate navigation. Keep favorite toggle and cart button `stopPropagation` behavior.
- Modal should show image, name, price, low-stock warning (when settings enable it), description, features / pack contents; primary CTA to full detail; add-to-cart; close via backdrop / Esc / close control.
- Reuse existing i18n keys where possible; add ca/es/en only if new strings are required.
- Do not change admin product flows; do not alter unrelated Docker services.
- Follow `.cursor/rules/` / `AGENTS.md` (git agent branch, tests, build).

## Acceptance criteria
- [x] Clicking a product/pack card on home and `/products/` opens an intermediate preview modal (not full-page navigation).
- [x] Modal offers navigation to the full detail page and add-to-cart.
- [x] Visual language matches serra-prod (not a raw style/serraalt skin dump).
- [x] i18n ca/es/en updated if new UI strings were added. (reused existing keys; no new strings)
- [x] No secrets committed; usual project verification (`npm run build` / tests as required by Testing instructions).

## Coder notes
- Restored `resources/js/components/ProductPreviewModal.jsx` from this repo’s history (commit `9cdbc71`), adapted to use `catalogFeatureTypeLabel` for feature rows.
- `ProductCard` opens the modal on click / Enter / Space; favorite + cart keep `stopPropagation`. Home / products / favorites already use `ProductCard`, so they pick up the behaviour with no page changes.
- App version bumped to **0.1.417**.

## Testing instructions

### What to verify
- Product/pack card click opens the intermediate preview modal (does not navigate immediately).
- Modal shows image, name, price, optional low-stock warning, description, features or pack contents.
- “View more info” navigates to `/products/:id` or `/packs/:id`.
- Add-to-cart from the modal and from the card still works.
- Close via ×, backdrop, and Esc.
- Favorite toggle on the card does not open the modal.
- Build succeeds with the new component.

### How to test
```bash
# From repo root (or docker compose exec node / app as appropriate)
npm run build
php artisan test
# No route/middleware changes expected; optional:
# php artisan routes:smoke

# Manual (storefront):
# 1. Open `/` and `/products/` (and `/favorites` if logged in with favorites).
# 2. Click a product card → preview modal appears.
# 3. Click pack card (if any) → pack preview modal.
# 4. Use “View more info” → full detail page.
# 5. Add to cart from modal; confirm cart updates.
# 6. Close modal with × / backdrop / Esc.
# 7. Click favorite heart → no navigation / no modal.
```

### Pass/fail criteria
- **Pass:** Card click opens modal; detail CTA and add-to-cart work; close controls work; `npm run build` exits 0; no regressions on favorite/cart stopPropagation.
- **Fail:** Card still navigates immediately; modal missing CTA/cart; build errors; favorite opens modal or navigates.

## Test report

- **Date/time (UTC):** 2026-09-07 10:45:20 – 10:48:50 UTC
- **Log window:** 2026-09-07 10:45:20 UTC → 10:48:50 UTC
- **Environment:**
  - Branch: `autoagents` @ `c02217e` (+ local uncommitted modal work under test)
  - PHP 8.4.24 (host CLI); PHPUnit 11.5.55 via stage `vendor` symlink (workspace has no local `vendor`)
  - Node v22.22.2
  - `APP_ENV=testing` (PHPUnit / `phpunit.xml`); host `.env` created from example for artisan bootstrap (`local` / sqlite) — gitignored
  - Docker `serra-prod-app-1` is production image **0.1.416** (no bind-mount of this source); chrome MCP cannot reach `127.0.0.1`
- **What was tested:**
  1. `npm run build` (Vite production build with `ProductPreviewModal`)
  2. `php artisan test` (full suite; includes `RouteSmokeTest`)
  3. Static review of `ProductCard.jsx` / `ProductPreviewModal.jsx` / i18n keys / consumers (Home, ProductList, Favorites)
  4. Built bundle string presence (`shop.product.view_more*`, `modal-backdrop`, `common.close`)
  5. Behavioral reference on https://serraalt.ldeluipy.es/ (style preview modal UX)
  6. Baseline: https://serra.ldeluipy.es/ still serves pre-feature build (expected until this branch deploys)

### Results

| Criterion | Result | Evidence |
|-----------|--------|----------|
| Card click opens intermediate preview modal (no immediate navigation) | **PASS** | `ProductCard` `onClick`/`Enter`/`Space` → `openModal()`; no `navigate(detailUrl)` on card. Home / `/products` / favorites all use `ProductCard`. |
| Modal shows image, name, price, optional low-stock, description, features/pack contents | **PASS** | `ProductPreviewModal.jsx` renders image panel, title, price/discount, low-stock alert, description, features or pack item list. |
| “View more info” → `/products/:id` or `/packs/:id` | **PASS** | `<Link to={detailUrl}>` with `t('shop.product.view_more_info')`; `detailUrl` from pack/product id. Keys present in ca/es/en. |
| Add-to-cart from modal and card | **PASS** | Modal `handleAdd` + card cart button with `stopPropagation`; both call `addLine`. |
| Close via ×, backdrop, Esc | **PASS** | Close button → `dialog.close()`; `form.method=dialog.modal-backdrop`; native `<dialog showModal()>` Esc. Bundle contains `modal-backdrop` / `common.close`. |
| Favorite toggle does not open modal | **PASS** | Favorite wrapper `onClick`/`onKeyDown` `stopPropagation`. |
| `npm run build` exit 0 | **PASS** | Vite build OK (~6.9s); `e-commerce@0.1.417`; assets under `public/build/`. |
| No PHPUnit regressions | **PASS** | `php artisan test`: **216 passed**, 5 skipped, 0 failed (17.87s). `RouteSmokeTest` PASS. |
| Visual language matches serra-prod (not raw style dump) | **PASS** | daisyUI `modal` / `btn` / existing card SCSS; `catalogFeatureTypeLabel`; not BEM style skin. |
| i18n | **PASS** | Reused existing keys (`shop.product.view_more_info`, `low_stock_warning`, `common.close`, etc.) in ca/es/en. |

### Overall: **PASS**

### URLs tested
- N/A for this branch’s assets on a public host (changes not deployed; chrome MCP is remote → localhost unreachable).
- Reference UX: https://serraalt.ldeluipy.es/ — card click opened preview modal with “View more info” + cart CTA + × (PASS on reference pattern).
- Baseline: https://serra.ldeluipy.es/ — still without this modal (prod image 0.1.416).

### Relevant log excerpts
- Host: no `storage/logs/laravel.log` for this workspace.
- PHPUnit: clean green run; no test failures.
- Build: daisyUI `@property` CSS optimize warning only (pre-existing / non-blocking).
- `serra-prod` container log tail during window: unrelated prior artisan stack noise only; no errors attributed to this feature.

