---
## Closing summary (TOP)

- **What happened:** GitHub #24 asked for richer link previews when sharing storefront URLs and a Google Merchant Center product feed.
- **What was done:** Server-side OG/Twitter meta via `SpaShellController`, `GET /feeds/google-merchant.xml` with cache invalidation, client `useDocumentMeta` on key pages, and `docs/google-merchant-center.md`.
- **What was tested:** `ShareMetaSeoTest` and `GoogleMerchantFeedTest` passed; full suite (193 tests), `routes:smoke`, and `npm run build` passed; curl verified product/pack/category meta and feed XML.
- **Why closed:** Tester report overall **PASS**; all automated criteria met.
- **Closed at (UTC):** 2026-06-02 13:04
---

# SEO: Open Graph / Twitter Card (WhatsApp) + feed Google Merchant Center

## GitHub Issue
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/24
- **Number:** #24
- **Labels:** none
- **Created:** 2026-06-02T12:46:29Z

## Problem / goal
# SEO: Open Graph / Twitter Card (WhatsApp) + feed Google Merchant Center  ## Resumen  Mejorar cómo se ve la tienda al **compartir enlaces** (WhatsApp, Telegram, X/Twitter, Facebook, LinkedIn) y ofrecer un **feed de productos para Google Merchant Cen...

## High-level instructions for coder
- Read the full issue at https://github.com/Luipy56/laravel-ecommerce/issues/24
- Identify affected paths under app/, routes/, resources/js/, database/, tests/
- Implement minimal, on-scope changes for laravel-ecommerce
- Follow .cursor/rules/project-standards.mdc and i18n (ca/es/en)
- Add **Testing instructions** before renaming to UNTESTED-

## Implementation summary
- **Server-side OG/Twitter:** `SpaShellController` serves `welcome.blade.php` with route-specific meta for `/`, `/products`, `/products/{id}`, `/packs/{id}`, `/categories/{id}/products` (Approach A). Locale from `Accept-Language` / `?locale=`.
- **Google Merchant feed:** `GET /feeds/google-merchant.xml` — RSS 2.0 + Google namespace; active products only; price after discount; availability from stock; 6 h cache; invalidated on `Product` save/delete.
- **Client complement:** `useDocumentMeta` on Home, ProductList, ProductDetail, PackDetail pages.
- **Docs:** `docs/google-merchant-center.md`.

## References
- Repo: https://github.com/Luipy56/laravel-ecommerce
- Agent loop: docs/agent-loop.md

## Testing instructions

1. **Automated (required):**
   ```bash
   docker compose exec app php artisan test --filter='ShareMetaSeoTest|GoogleMerchantFeedTest'
   docker compose exec app php artisan test
   docker compose exec node npm run build
   ```

2. **Product share meta (WhatsApp / Facebook Debugger):**
   - Seed or pick an active product with image at `/products/{id}`.
   - `curl -s -H 'Accept-Language: ca' http://localhost:8080/products/{id} | grep 'og:title'`
   - Expect `og:title` = product name, `og:image` absolute https URL, `canonical` = same product URL.
   - Repeat with `Accept-Language: es` on a product with translated names.

3. **Inactive product:** `GET /products/{inactive_id}` → HTTP 404 (no generic home meta).

4. **Google Merchant feed:**
   - Open `http://localhost:8080/feeds/google-merchant.xml` — valid RSS/XML, only `is_active` products.
   - Confirm `<g:price>` reflects `discount_percent` (e.g. 100 € − 10% → `90.00 EUR`).
   - Set `stock = 0` on a product → `<g:availability>out of stock</g:availability>`.

5. **Pack / category / catalog:**
   - `/packs/{id}` and `/categories/{id}/products` return page-specific `og:title` and canonical in HTML source.

6. **GMC registration (ops, one-time):** follow `docs/google-merchant-center.md` — register feed URL in Merchant Center; no recurring CSV export.

7. **Product owner feedback:** Paste a product URL in WhatsApp (or use Facebook Sharing Debugger) to confirm preview shows product name, description snippet, and image (or brand fallback when no product image).

---

## Test report

**Date/time (UTC):** 2026-06-02 13:01:28 – 13:04:10  
**Log window:** same window (`storage/logs/laravel.log` — no errors related to SEO/feed)

### Environment

| Item | Value |
|------|-------|
| Branch | `autoagents` |
| PHP | 8.2.30 (Docker `app`) |
| Node | v22.22.2 (Docker `node`) |
| Stack | Docker Compose (`localhost:8080`) |
| APP_ENV | local (dev stack) |

### What was tested

1. Filtered PHPUnit: `ShareMetaSeoTest`, `GoogleMerchantFeedTest`
2. Full suite: `php artisan test`
3. Route smoke: `php artisan routes:smoke`
4. Front-end build: `npm run build`
5. curl smoke: product/pack/category OG meta + Google Merchant feed XML

### Results

| Criterion | Result | Evidence |
|-----------|--------|----------|
| ShareMetaSeoTest (5 tests) | **PASS** | 5 passed, 20 assertions |
| GoogleMerchantFeedTest (3 tests) | **PASS** | 3 passed; discount price + out-of-stock + cache invalidation covered |
| Full PHPUnit suite | **PASS** | 193 passed, 2 skipped (ES integration, sqlite session), 800 assertions, exit 0 |
| routes:smoke | **PASS** | "All checked GET routes returned a non-500 status." |
| npm run build | **PASS** | Vite build exit 0 (~5 s) |
| Product OG meta (ca) | **PASS** | `/products/30`: `og:title` = "Segon pestell SAG amb M&C", `og:image` absolute URL, `canonical` = product URL |
| Product OG meta (es) | **PASS** | Same product: `og:title` = "Segundo cerrojo SAG con M&C" |
| Inactive product 404 | **PASS** | `ShareMetaSeoTest::test_inactive_product_shell_returns_not_found` |
| Google Merchant feed XML | **PASS** | Valid RSS 2.0 + `xmlns:g`; active products with `<g:price>`, `<g:availability>` |
| Pack / category meta | **PASS** | `/packs/1` and `/categories/1/products` return page-specific `og:title` + canonical |
| GMC registration (ops) | **N/A** | One-time Merchant Center setup; doc present at `docs/google-merchant-center.md` |
| WhatsApp / Facebook preview (manual) | **N/A** | Requires external debugger or mobile share; server-side meta verified via curl + tests |

### Overall

**PASS**

### URLs tested

- `http://localhost:8080/products/30` (Accept-Language: ca, es)
- `http://localhost:8080/packs/1`
- `http://localhost:8080/categories/1/products`
- `http://localhost:8080/feeds/google-merchant.xml`

### Log excerpts

No SEO/feed-related errors in `laravel.log` during the test window.
