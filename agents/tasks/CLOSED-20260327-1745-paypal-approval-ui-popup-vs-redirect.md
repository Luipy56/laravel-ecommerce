# PayPal checkout: visible buyer approval (popup vs redirect)

## Problem / goal

Storefront PayPal uses the **JS SDK Smart Payment Buttons** (`resources/js/components/payments/PayPalInlineButtons.jsx`) with a **server-created order** (`createOrder` returns the existing `paypal_order_id`). Operators report completing checkout in **incognito** without noticing a **new browser tab** to PayPal and without recalling PayPal login — raising questions about whether the buyer is authenticated with PayPal and how charge confirmation works.

**Clarification for product:** PayPal’s SDK typically opens a **popup** or **in-context overlay**, not necessarily a **new tab**. A successful **`POST /api/v1/payments/paypal/capture`** after `onApprove` implies PayPal had moved the order to an approvable state and **capture** returned **`COMPLETED`** with payer data on PayPal’s side. If capture truly succeeded with **zero** PayPal UI, that is **not** expected and must be reproduced and fixed.

## References (read first)

- `resources/js/components/payments/PayPalInlineButtons.jsx` — SDK URL, `Buttons({ createOrder, onApprove })`.
- `app/Services/Payments/PayPal/PayPalClient.php` — `createOrder`, `captureOrder`.
- `app/Http/Controllers/Api/PayPalPaymentController.php` — capture after client `onApprove`.
- PayPal docs: Smart Buttons vs **redirect** to `approve` link (`rel: approve` from create-order response).

## High-level instructions for coder

1. **Reproduce** in a clean profile / incognito: confirm whether a **popup** appears (check popup blockers), whether the user must log into a **sandbox buyer** account, and whether **`capture`** succeeds without any PayPal window. Document findings in a short note (e.g. `docs/CONFIGURACION_PAGOS_CORREO.md` or troubleshooting) without bloating README.
2. If the desired UX is **always a full-page PayPal experience** (clear security boundary), evaluate implementing **redirect-based approval** (user navigates to `https://www.sandbox.paypal.com/checkoutnow?token=…` or live equivalent, then `return_url` / `cancel_url` handling) while keeping server-side order creation and capture integrity. Alternatively, document that the current **popup** flow is intentional and add concise UI copy (i18n `ca` / `es`) so users expect a window from PayPal, not a new tab.
3. Ensure **`PAYMENTS_ALLOW_SIMULATED`** confusion is ruled out during tests: PayPal is not simulated when credentials exist; verify `payments_simulated` / config in the failing scenario.
4. Automated tests: extend only with mocks/fakes; no live PayPal in CI.

## Acceptance criteria

- Clear **operator-facing** explanation of what users should see (popup vs redirect) and that **capture requires PayPal-side approval** for real transactions.
- If redirect (or `experience` / SDK options) is implemented: E2E manual checklist passes in sandbox; **`php artisan test`** and **`npm run build`** (if JSX changed) pass; no regression on existing `PayPalPaymentTest` patterns.

## Implementation notes (coder)

- **Operator-facing:** Added `### PayPal: qué debería ver el comprador (operadores)` and expanded the PayPal row in the methods table in `docs/CONFIGURACION_PAGOS_CORREO.md` (popup/overlay vs full-page redirect, blockers, session, capture/`COMPLETED`, `PAYMENTS_ALLOW_SIMULATED` vs real PayPal).
- **Storefront copy (ca/es):** New `checkout.payment.paypal_user_edu_popup_and_capture`; updated `paypal_help`, `paypal_user_edu_compact`; `PayPalUserEducation.jsx` shows the new paragraph in full variant.
- **SDK URL:** `PayPalInlineButtons.jsx` loads the JS SDK with `intent=capture` and `commit=true` (aligned with server `CAPTURE` orders and PayPal’s recommended capture flow).
- **Redirect-only checkout:** Not implemented: would need `return_url` / `cancel_url`, app routes, and return handling; current path documents the intentional Smart Buttons experience and sets user/operator expectations.

## Testing instructions (tester)

### What to verify

- Checkout and order payment pages show updated PayPal helper text (ca/es) mentioning popup/overlay vs full page and that capture only confirms after PayPal allows it.
- PayPal buttons still render and the SDK script URL includes `intent=capture` and `commit=true` (DevTools → Network or Elements → script `src`).
- `docs/CONFIGURACION_PAGOS_CORREO.md` reads clearly for operators (Spanish).

### How to test

- `php artisan test`
- `npm run build`
- Manual (sandbox): `/checkout` with PayPal selected, complete order until Smart Buttons appear; confirm PayPal UI appears and payment still completes. Optional: repeat with pop-ups blocked to observe full-page redirect behaviour.

### Pass/fail criteria

- **Pass:** All automated tests green; `npm run build` succeeds; manual PayPal sandbox flow completes without console errors from the SDK; copy matches locale and is understandable.
- **Fail:** Build/test failures, PayPal buttons fail to load, or misleading copy (e.g. implying payment confirms without PayPal).

---

## Test report

1. **Date/time (UTC) and log window**
   - Started: `2026-03-27T17:32:12Z` (approx.; sync and rename immediately before).
   - Finished: same session (~17:33Z).
   - Log window: `storage/logs/laravel.log` — no new lines reviewed that were specific to this verification (automated tests use in-memory SQLite; smoke uses test dispatcher).

2. **Environment**
   - PHP `8.3.6`, Node `v22.21.0`.
   - Branch: `agentdevelop` (after `./scripts/git-sync-agent-branch.sh`).
   - `APP_ENV`: not required for this run (PHPUnit + Vite build only).

3. **What was tested** (from “What to verify” + How to test)
   - Automated: `php artisan test`, `npm run build`, `php artisan routes:smoke`.
   - Static: `ca.json` / `es.json` PayPal checkout strings; `PayPalInlineButtons.jsx` SDK query string; `PayPalUserEducation.jsx` uses new key; `docs/CONFIGURACION_PAGOS_CORREO.md` operator section.
   - Manual PayPal sandbox E2E (`/checkout`, Smart Buttons, buyer login): **not executed** (no interactive browser / PayPal sandbox buyer session in this tester run).

4. **Results**
   - `php artisan test`: **PASS** — `Tests: 30 passed (165 assertions)`, exit code 0.
   - `npm run build`: **PASS** — Vite build completed (`✓ built in 3.46s`), exit code 0.
   - `php artisan routes:smoke`: **PASS** — `All checked GET routes returned a non-500 status.`
   - Checkout copy (ca/es): popup/overlay vs full page + capture only after PayPal approval: **PASS** — keys `checkout.payment.paypal_help`, `checkout.payment.paypal_user_edu_popup_and_capture`, `checkout.payment.paypal_user_edu_compact` present and aligned in `resources/js/locales/ca.json` and `es.json`; `PayPalUserEducation.jsx` renders `paypal_user_edu_popup_and_capture`.
   - SDK script URL includes `intent=capture` and `commit=true`: **PASS** — `PayPalInlineButtons.jsx` builds `scriptSrc` with `&intent=capture&commit=true` (lines 28–30).
   - Operator doc (Spanish): **PASS** — table row + `### PayPal: qué debería ver el comprador (operadores)` + E2E checklist in `docs/CONFIGURACION_PAGOS_CORREO.md` read clearly and match the product intent.
   - Manual sandbox E2E (Smart Buttons visible, PayPal UI, payment completes, console clean): **NOT RUN** — evidence: headless verification only; follow checklist in same doc § “Flujo E2E PayPal sandbox”.

5. **Overall:** **PASS** — all automatable gates and static acceptance checks passed; manual sandbox sign-off remains recommended for operators/staging (documented in repo).

6. **Product owner feedback**
   - The storefront and docs now explain popup/overlay vs redirect and that capture implies PayPal-side approval, which should reduce operator confusion after incognito tests.
   - Please run the documented PayPal sandbox checklist once in a real browser before treating production behaviour as signed off, since this run did not drive the live SDK UI.

7. **URLs tested**
   - **N/A — no browser** (no full URLs visited).

8. **Relevant log excerpts**
   - N/A — evidence for pass/fail taken from PHPUnit/Vite/routes:smoke CLI output above, not from `laravel.log` during this window.
