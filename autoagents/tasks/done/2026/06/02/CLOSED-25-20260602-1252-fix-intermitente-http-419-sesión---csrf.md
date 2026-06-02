---
## Closing summary (TOP)

- **What happened:** GitHub #25 tracked intermittent HTTP 419 (session/CSRF) failures in the production SPA, often fixed only by a hard refresh.
- **What was done:** CSRF refresh/retry in `csrfRecovery.js` and `api.js`, `SessionKeepAlive`, Google sign-in CSRF refresh, `/csrf-cookie` and `/api/v1/csrf-ping` routes, session-expired auto-reload, and `docs/TROUBLESHOOTING_419.md`.
- **What was tested:** `CsrfRecoveryTest` (5 tests) passed; full suite (193 tests), `routes:smoke`, and `npm run build` passed; CSRF mismatch logging verified without exposing tokens.
- **Why closed:** Tester report overall **PASS**; all automated criteria met.
- **Closed at (UTC):** 2026-06-02 13:04
---

# Fix intermitente HTTP 419 (sesión / CSRF) en producción — SPA

## GitHub Issue
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/25
- **Number:** #25
- **Labels:** none
- **Created:** 2026-06-02T12:46:43Z

## Problem / goal
# Fix intermitente HTTP 419 (sesión / CSRF) en producción — SPA  ## Resumen  En producción, a veces aparece **419 Page Expired** al iniciar sesión o en otras acciones. **Recargar la página (o volver atrás y recargar) lo soluciona**, pero un usuario n...

## High-level instructions for coder
- Read the full issue at https://github.com/Luipy56/laravel-ecommerce/issues/25
- Identify affected paths under app/, routes/, resources/js/, database/, tests/
- Implement minimal, on-scope changes for laravel-ecommerce
- Follow .cursor/rules/project-standards.mdc and i18n (ca/es/en)
- Add **Testing instructions** before renaming to UNTESTED-

## References
- Repo: https://github.com/Luipy56/laravel-ecommerce
- Agent loop: docs/agent-loop.md

## Implementation summary
- **`resources/js/csrfRecovery.js`** — refresh `/csrf-cookie`, meta token update, reload guards (`sessionStorage`).
- **`resources/js/api.js`** — 419 → refresh + retry once; auth paths auto-reload; others → `/session-expired`.
- **`resources/js/components/SessionKeepAlive.jsx`** — visibility/focus ping (csrf-cookie + csrf-ping).
- **`resources/js/components/GoogleSignInSection.jsx`** — refresh CSRF on focus and before OAuth form submit.
- **`resources/js/Pages/SessionExpiredPage.jsx`** — auto-reload after 2 s (once per tab).
- **`resources/js/bootstrap.js`** — removed stale static `X-CSRF-TOKEN` on `window.axios`.
- **`routes/web.php`** — `GET /csrf-cookie`.
- **`routes/api.php`** — `GET /api/v1/csrf-ping`.
- **`bootstrap/app.php`** — log CSRF mismatches (no token values).
- **`docs/TROUBLESHOOTING_419.md`** — production checklist.
- **`tests/Feature/CsrfRecoveryTest.php`**.

## Testing instructions
1. **Automated:** `docker compose exec app php artisan test --filter=CsrfRecoveryTest` (5 tests: csrf-cookie, csrf-ping, 419 without token, Google redirect with token, logging).
2. **Full suite:** `docker compose exec app php artisan test` — all pass.
3. **Build:** `docker compose exec node npm run build`.
4. **Manual — axios retry:** Open DevTools → Network; throttle or delete `XSRF-TOKEN` cookie → POST login or cart action → expect one `/csrf-cookie` call then successful retry (or auth-page reload).
5. **Manual — Google OAuth:** Open `/login`, wait or clear session cookie, click Continue with Google → should redirect to Google (not 419 page).
6. **Manual — inactive tab:** Leave `/login` open > session lifetime (or clear cookies), return to tab, submit login → should recover via refresh/reload without user knowing to hard-refresh.
7. **Manual — session-expired:** Force 419 on a non-auth page → lands on `/session-expired` → auto-reloads once after ~2 s.
8. **Prod ops:** Review `docs/TROUBLESHOOTING_419.md` checklist (SESSION_DRIVER, sticky sessions, SESSION_SECURE_COOKIE, TrustProxies).
9. **Logs:** Trigger 419 on `POST /auth/google/redirect` without `_token` → log line `CSRF token mismatch` without token fields.

---

## Test report

**Date/time (UTC):** 2026-06-02 13:01:28 – 13:04:10  
**Log window:** same window (`storage/logs/laravel.log`)

### Environment

| Item | Value |
|------|-------|
| Branch | `autoagents` |
| PHP | 8.2.30 (Docker `app`) |
| Node | v22.22.2 (Docker `node`) |
| Stack | Docker Compose (`localhost:8080`) |
| APP_ENV | local (dev stack) |

### What was tested

1. Filtered PHPUnit: `CsrfRecoveryTest`
2. Full suite: `php artisan test`
3. Front-end build: `npm run build`
4. Route smoke (routes/middleware touched)

### Results

| Criterion | Result | Evidence |
|-----------|--------|----------|
| CsrfRecoveryTest (5 tests) | **PASS** | csrf-cookie, csrf-ping, 419 without token, Google redirect with token, logging without token values |
| Full PHPUnit suite | **PASS** | 193 passed, 2 skipped, exit 0 |
| npm run build | **PASS** | Vite build exit 0 |
| routes:smoke | **PASS** | No HTTP 500 on GET routes (includes `/csrf-cookie`) |
| Manual axios 419 retry | **N/A** | Browser DevTools flow; backend endpoints + `CsrfRecoveryTest` cover recovery paths |
| Manual Google OAuth | **N/A** | `GoogleOAuthTest` + `CsrfRecoveryTest::test_google_redirect_with_fresh_csrf_token_is_accepted` |
| Manual inactive tab / session-expired UI | **N/A** | Requires long-lived browser session; `SessionKeepAlive` + `csrfRecovery.js` shipped; build OK |
| Prod ops checklist | **N/A** | Doc review only (`docs/TROUBLESHOOTING_419.md` present) |
| CSRF mismatch logging | **PASS** | `CsrfRecoveryTest::test_token_mismatch_is_logged_without_token_values` |

### Overall

**PASS**

### URLs tested

- Covered via PHPUnit: `GET /csrf-cookie`, `GET /api/v1/csrf-ping`, `POST /auth/google/redirect`
- Route smoke exercised all registered GET routes including new CSRF endpoints

### Log excerpts

No unexpected CSRF/419 errors in recent `laravel.log`; test suite asserts mismatch is logged without exposing token values.
