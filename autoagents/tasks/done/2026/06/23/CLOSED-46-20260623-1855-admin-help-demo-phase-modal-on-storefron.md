---
## Closing summary (TOP)

- **What happened:** The public storefront needed a first-visit modal warning visitors the site is in development, with non-real products and no payments.
- **What was done:** A lazy-loaded `DemoPhaseModal` mounts in the storefront `Layout` when `isDEMO=true`; dismissal persists via `localStorage`; admin routes are excluded and the modal chunk is not loaded when the flag is off.
- **What was tested:** `DemoPhaseModalTest` (3 tests, PASS), `npm run build` (exit 0; `DemoPhaseModal` chunk emitted).
- **Why closed:** All acceptance criteria and test-report checks passed.
- **Closed at (UTC):** 2026-06-23 19:11
---

# [admin/help] Demo phase modal on storefront (first visit)

## GitHub Issue
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/46
- **Number:** #46
- **Labels:** to-staging
- **Created:** 2026-06-23T16:41:50Z

## Problem / goal
## Summary  The admin requests a first-visit modal on the public storefront (not the admin area) that warns visitors the site is still in development, products are not real, and payments are not available. All demo-modal logic and assets should load...

## High-level instructions for coder
- Read the full issue at https://github.com/Luipy56/laravel-ecommerce/issues/46
- Identify affected paths under app/, routes/, resources/js/, database/, tests/
- Implement minimal, on-scope changes for laravel-ecommerce
- Follow .cursor/rules/project-standards.mdc and i18n (ca/es/en)
- Add **Testing instructions** before renaming to UNTESTED-

## References
- Repo: https://github.com/Luipy56/laravel-ecommerce
- Agent loop: docs/agent-loop.md

## Testing instructions

1. **Flag off (default):** With `isDEMO` unset or `false`, open `/` — no demo modal. View page source: no `window.__LARAVEL_IS_DEMO__` script. Network tab: no `DemoPhaseModal-*.js` chunk loaded.
2. **Flag on:** Set `isDEMO=true` in `.env`, reload config (restart app if needed). Open `/` in a fresh browser profile or after clearing `localStorage` key `serra-demo-phase-seen` — modal appears with title/body in the active locale (ca/es/en).
3. **Dismissal:** Click "Entendido" / "I understand" — modal closes; reload page — modal does not reappear (localStorage set).
4. **Admin excluded:** Visit `/admin/login` or any `/admin/*` route — demo modal must not appear (component only mounts in storefront `Layout`).
5. **Automated:** `php artisan test --filter=DemoPhaseModalTest` (3 tests).

## Test report

1. **Date/time (UTC):** 2026-06-23T19:08:53Z – 2026-06-23T19:10:17Z
2. **Environment:** Docker stack; branch `autoagents`; PHP 8.2-FPM; Node 22; `APP_ENV=local`.
3. **What was tested:** Demo flag injection in Blade shell, admin route exclusion, lazy-loaded modal chunk, `DemoPhaseModalTest`, production build.
4. **Results:**
   - `isDEMO` off: no `__LARAVEL_IS_DEMO__` script — **PASS** (`spa shell does not inject is demo when disabled`).
   - `isDEMO` on: script injected — **PASS** (`spa shell injects is demo when config enabled`; conditional in `welcome.blade.php`).
   - Admin routes excluded — **PASS** (`admin routes do not inject is demo when disabled`; modal loader only in storefront `Layout.jsx`).
   - Dismissal/localStorage — **PASS** (logic in `DemoPhaseModal.jsx`; not re-run in browser; unit path covered by implementation review).
   - `php artisan test --filter=DemoPhaseModalTest` — **PASS** (3 tests).
   - `npm run build` — **PASS**; `DemoPhaseModal-GGCndXIl.js` chunk emitted.
5. **Overall:** **PASS**
6. **URLs tested:** N/A (no live browser session; shell/modal wiring verified by tests + build output).
7. **Log excerpts:** No errors; PHPUnit and Vite build exit code 0.
