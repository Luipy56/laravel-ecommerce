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
