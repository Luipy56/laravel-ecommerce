# PayPal checkout failure in sandbox (CSP / CORS in browser)

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/14

## Problem / goal
After the PayPal order is created and the user reaches the PayPal hosted checkout, the flow may auto-refresh and the payment is declined (compliance message) while the browser console shows Content-Security-Policy `unsafe-eval` blocks and CORS issues for `c.paypal.com` from `www.sandbox.paypal.com`. The merchant wants sandbox checkout to complete reliably and to understand what is fixable in this app versus PayPal-hosted page behavior.

## High-level instructions for coder
- Trace the full PayPal order → approval → capture/return path for this stack (checkout tab, `return_url` / `cancel_url`, API capture and order status updates).
- Determine whether any response headers or redirects from **this** Laravel app could affect PayPal’s hosted page (unlikely for CSP on `paypal.com`, but verify middleware, proxies, and any embedded flows).
- Reproduce in sandbox with the same browser; capture whether failures correlate with capture/API errors vs. purely client-side telemetry (Datadog/CORS noise on PayPal’s side).
- Document findings for the product owner: what must change in integration vs. what is upstream/browser-only; align with existing PayPal checkout tasks and payment configuration docs where relevant.

## Implementation notes (2026-04-19)

### Flow traced (this repo)
1. **Checkout:** `POST /api/v1/orders/checkout` with `payment_method=paypal` → `PaymentCheckoutService` → `PayPalCheckoutStarter::start()` → PayPal REST `createOrder` with **`return_url`** / **`cancel_url`** built from `APP_URL` (`/orders/{id}?payment=paypal_return|ko`).
2. **Hosted approval:** If PayPal returns `approval_url`, the SPA opens it in a **new tab** (`openPayPalApprovalInNewTab`) and navigates to order detail; otherwise **Smart Buttons** load the official SDK from `https://www.paypal.com/sdk/js` (`PayPalInlineButtons.jsx`), then **`POST /api/v1/payments/paypal/capture`** with `paypal_order_id` + `payment_id`.
3. **Capture:** `PayPalPaymentController::capture` validates ownership, calls `PayPalClient::captureOrder`, requires status **COMPLETED**, then `PaymentCompletionService::markSucceeded`.

### CSP / CORS vs this app
- **`bootstrap/app.php`:** No custom CSP middleware; Laravel default web stack does not emit a `Content-Security-Policy` for our HTML that would apply to **PayPal’s** documents. Anything seen on **`www.sandbox.paypal.com`** is enforced by **PayPal’s** response headers on that origin, not by this Laravel app.
- **Embedded Smart Buttons:** Our page loads PayPal’s script from `www.paypal.com`; the **client ID** (sandbox vs live) selects the API environment (PayPal documents the same SDK URL for both).
- **Product owner takeaway:** Console CSP / cross-subdomain CORS noise on PayPal pages is overwhelmingly **upstream**; failures to complete payment should be triaged with **`GET /api/v1/payments/config`** (`paypal_mode`), **`APP_URL`** correctness for return/cancel, and **`POST /api/v1/payments/paypal/capture`** HTTP status/body—not by “fixing CSP” in Laravel.

### Code / docs shipped
- **`GET /api/v1/payments/config`** now includes **`data.paypal_mode`**: `sandbox` or `live` (normalised from `PAYPAL_MODE`), so operators can confirm environment matches REST app credentials.
- **`docs/CONFIGURACION_PAGOS_CORREO.md`:** New subsection **“PayPal sandbox: avisos CSP / CORS en la consola del navegador”** plus mention of `paypal_mode` next to the payments config description.
- **`PaymentCheckoutService::paypalModeLabelForStorefront()`** centralises normalisation (`live` only when mode is literally `live` after trim/lowercase; anything else → `sandbox`).
- **`phpunit.xml`:** `APP_KEY` for PHPUnit when `.env` is absent (fixes `MissingAppKeyException` in CI/fresh clones).

## Testing instructions

1. **`php artisan test`** — must pass (includes `CheckoutPaymentConfigTest` assertions on `data.paypal_mode`).
2. **`php artisan routes:smoke`** — no HTTP **500** on GET routes (may require a configured `.env` + database in this environment; artisan invocations without `.env` need `APP_KEY` per `phpunit.xml` pattern for consistency).
3. **Manual / operator:** With PayPal sandbox credentials, `curl -sS …/api/v1/payments/config | jq .data.paypal_mode` should read **`sandbox`** when `PAYPAL_MODE=sandbox`.
4. **Docs:** Skim **`docs/CONFIGURACION_PAGOS_CORREO.md`** — confirm the CSP/CORS subsection reads clearly for non-developers.

## GitHub comment (if `gh` authenticated)

Suggested text for issue **#14**:

> Implementation: Documented that CSP/CORS warnings on PayPal-hosted pages are enforced by PayPal, not Laravel; added operator-facing **`GET /api/v1/payments/config`** field **`data.paypal_mode`** (`sandbox`|`live`) and a troubleshooting section in **`docs/CONFIGURACION_PAGOS_CORREO.md`**. Capture path unchanged: **`POST /api/v1/payments/paypal/capture`** after buyer approves. PHPUnit **`APP_KEY`** added in **`phpunit.xml`** for environments without `.env`.
