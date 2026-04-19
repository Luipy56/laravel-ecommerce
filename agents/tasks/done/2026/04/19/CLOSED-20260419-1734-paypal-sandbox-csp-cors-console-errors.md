---
## Closing summary (TOP)

- **What happened:** GitHub issue #19 addressed PayPal sandbox browser console CSP/CORS noise and the need to keep sandbox vs live PayPal mode and endpoints consistent in this app’s checkout integration.
- **What was done:** The implementation wired `paypal_mode` through checkout payloads and PayPal inline SDK usage, updated payment docs and changelog, and the tester confirmed automated PHPUnit (including PayPal/config coverage) plus the Vite production build.
- **What was tested:** `php artisan test` passed (98 tests); `npm run build` passed; CLI `routes:smoke` failed locally due to missing PDO PostgreSQL driver while `RouteSmokeTest` passed in the suite; manual PayPal-hosted sandbox browser verification was not run here and remains for staging.
- **Why closed:** Required automated verification gates passed per the test report; documented environment limitation for CLI smoke; residual hosted-page CSP/CORS warnings are acknowledged as upstream.
- **Closed at (UTC):** 2026-04-19 17:51
---

# PayPal sandbox: CSP unsafe-eval and CORS console errors

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/19

## Problem / goal
Browser console on PayPal-hosted sandbox checkout reports two separate classes of problems: (1) Content-Security-Policy blocking script evaluation (`unsafe-eval` not allowed), affecting PayPal/third-party scripts such as banner/fraud tooling; (2) CORS failures on `https://c.paypal.com/...` because responses allow `https://www.paypal.com` while the page origin is `https://www.sandbox.paypal.com`. The reporter asks to avoid mixing sandbox with production endpoints and to validate CSP and SDK usage. See also payment configuration docs (`docs/CONFIGURACION_PAGOS_CORREO.md`) and prior closure notes for related PayPal CSP/CORS work on issue #14.

## High-level instructions for coder
- Reproduce in sandbox with the same checkout path; distinguish telemetry noise on PayPal’s domain from failures that block capture or user return to the merchant site.
- Confirm this application loads the PayPal JS SDK and API base URLs consistent with sandbox vs live (`payments` config / env); ensure no mixed-mode scripts or redirects from this app.
- Review whether any CSP or security headers emitted by **this** Laravel app affect embedded or return flows (vs headers on `paypal.com`, which the merchant cannot control).
- Align documentation and operator guidance: what is upstream/browser-only vs what is configurable in the integration; link or consolidate with existing PayPal troubleshooting text to avoid contradictory advice.

## Implementation summary (coder)

- **`PayPalCheckoutStarter::checkoutPayload()`** now includes **`paypal_mode`** (`sandbox`|`live`), aligned with **`PaymentCheckoutService::paypalModeLabelForStorefront()`** and REST hosts (**`PayPalClient::baseUrl()`** already switches `api-m.sandbox.paypal.com` vs `api-m.paypal.com`).
- **`PayPalInlineButtons`:** Documents PayPal’s single **`www.paypal.com/sdk/js`** loader for sandbox and live (client ID selects environment); **`paypalMode`** prop (`checkout`/`pay` JSON + fallback from **`payments/config`**) separates script element ids (`paypal-sdk-inline-storefront-sandbox|live`) so switching mode does not reuse the wrong cached script tag.
- **`CheckoutPage` / `OrderDetailPage`:** Pass **`paypal_mode`** from **`payment_checkout`** into inline PayPal UI; **`payments/config`** stores **`paypal_mode`** in meta for fallback on checkout.
- **`docs/CONFIGURACION_PAGOS_CORREO.md`:** CSP/CORS subsection extended — no CSP in **`bootstrap/app.php`** for merchant HTML; REST vs SDK split; links to GitHub **#14** and **#19**.
- **`CHANGELOG.md`:** **[Unreleased]** note for the above.

## Testing instructions

1. **`php artisan test`** — full suite must pass (includes **`PayPalPaymentTest`**, **`CheckoutPaymentConfigTest`** assertions on **`paypal_mode`**).
2. **`npm run build`** — storefront bundle builds after JSX changes.
3. **Manual (sandbox):** With **`PAYPAL_MODE=sandbox`** and sandbox REST credentials, **`GET /api/v1/payments/config`** shows **`data.paypal_mode":"sandbox"`**. Complete checkout with PayPal Smart Buttons (no **`approval_url`** path): **`POST /api/v1/orders/checkout`** JSON **`data.payment_checkout`** must include **`paypal_mode":"sandbox"`**. In DevTools Network, SDK request stays **`https://www.paypal.com/sdk/js?...`** (expected); hosted PayPal tab may still show upstream CSP/CORS noise — confirm **`POST /api/v1/payments/paypal/capture`** succeeds when the buyer completes approval.

---

## Test report

1. **Date/time (UTC) and log window:** Started **2026-04-19 17:50:01 UTC**; finished **~2026-04-19 17:52 UTC**. Log window reviewed: **`storage/logs/laravel.log`** entries immediately after **`php artisan routes:smoke`** (same UTC window).

2. **Environment:** PHP **8.4.16**, Node **v22.22.2**, branch **`agentdevelop`**, **`APP_ENV=local`**. **`php artisan routes:smoke`** failed because **`.env`** uses **`DB_CONNECTION=pgsql`** while PHP CLI has **no PDO PostgreSQL driver** (`PDOException: could not find driver`).

3. **What was tested (from Testing instructions):** Full PHPUnit suite; Vite production build; optional **`routes:smoke`**; review of Laravel log for smoke failures; **`CheckoutPaymentConfigTest`** coverage for **`paypal_mode`** assertions (per instruction 1). Manual PayPal-hosted sandbox browser flow not executed in this environment.

4. **Results:**
   - **`php artisan test`:** **PASS** — Exit code **0**; **98 passed**, 5 skipped; **`PayPalPaymentTest`** and **`CheckoutPaymentConfigTest`** (includes **`data.paypal_mode`** sandbox/live/normalization and checkout payload **`payment_checkout.paypal_mode`**) all green.
   - **`npm run build`:** **PASS** — Vite **built in ~5.2s**, exit code **0**.
   - **`php artisan routes:smoke`:** **FAIL (environment)** — Many routes returned **500**; log shows **`could not find driver`** for Postgres session/DB on CLI. Mitigation: **`Tests\Feature\RouteSmokeTest`** (**all distinct get routes do not return 500**) **PASS** in the same **`php artisan test`** run (test harness DB/session differs from broken CLI `.env`).
   - **Manual (sandbox):** **NOT RUN** — No PayPal sandbox browser session or live **`www.sandbox.paypal.com`** approval in this tester run; capture/SDK DevTools checks are operator/staging verification per issue #19.

5. **Overall:** **PASS** — Required automated gates (**`php artisan test`** including PayPal/config tests, **`npm run build`**) succeeded. **`routes:smoke`** CLI outcome is recorded as failed due to missing PDO driver for the configured DB, not as a regression signal; **`RouteSmokeTest`** passed. Manual sandbox E2E remains for staging with credentials.

6. **Product owner feedback:** Automated tests now assert **`paypal_mode`** through **`payments/config`** and checkout payloads, which reduces risk of mixing REST hosts with the wrong labelled mode. Hosted PayPal pages may still print CSP/CORS warnings that the merchant cannot fix; confirming capture in sandbox with real buttons remains the definitive check before relying on console cleanliness.

7. **URLs tested:** **N/A — no browser**

8. **Relevant log excerpts:**
   ```
   PDOException(code: 0): could not find driver
   ...
   PostgresConnector.php ... Session/DatabaseSessionHandler.php ... RouteSmokeCommand.php
   ```
