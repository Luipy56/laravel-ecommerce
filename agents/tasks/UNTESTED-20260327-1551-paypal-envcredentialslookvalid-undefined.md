# Payment config: undefined `PayPalClient::envCredentialsLookValid()`

## Source

- **Log:** `storage/logs/laravel.log`
- **UTC window:** **2026-03-26T20:35Z** (log lines `[2026-03-26 20:35:40]` … `[2026-03-26 20:35:50]`; channel `local` — align with server `APP_TIMEZONE` if not UTC)
- **Representative lines:**
  - `local.ERROR: Call to undefined method App\Services\Payments\PayPal\PayPalClient::envCredentialsLookValid() ... at ... PaymentCheckoutService.php:100`
  - Trigger path includes **`PaymentConfigController::show`** via **`PaymentCheckoutService::paymentMethodsAvailability()`** (stack traces in log)

## Problem / goal

**`PaymentCheckoutService`** calls **`PayPalClient::envCredentialsLookValid()`**, but that method **does not exist** on **`PayPalClient`**, causing a fatal **Error** and breaking **`GET /api/v1/payments/config`** (and any UI that depends on payment method availability) for affected runs.

## High-level instructions for coder

- Open **`app/Services/Payments/PaymentCheckoutService.php`** (around line 100) and **`PayPalClient`**: either **implement** `envCredentialsLookValid()` (consistent with how Stripe/simulated PayPal checks work) or **replace** the call with an existing helper / inline checks already used elsewhere.
- Ensure **`paymentMethodsBaseAvailability()`** and **`PaymentConfigController`** never throw on missing optional PayPal config when simulated mode or docs allow partial setup (match project rules for `PAYMENTS_ALLOW_SIMULATED` and PayPal env vars).
- Add or extend a **feature test** that hits **`GET /api/v1/payments/config`** (authenticated session as needed) so a missing method cannot regress silently.
- Run **`php artisan test`** and payment-related verification per **`.cursor/rules/testing-verification.mdc`**.

**Note:** This is separate from **`NEW-20260327-1542-…`** (`authorizedJson` **TypeError**) and **`NEW-20260327-1543-…`** (SQLite **sessions** table); keep fixes scoped per incident.

---

## Coder notes (2026-03-27 UTC)

- **`PaymentCheckoutService::paymentMethodsBaseAvailability()`** already used **`PayPalClient::envCredentialsPresent()`** on the integration branch; the production error referred to the old name **`envCredentialsLookValid()`**.
- Implemented **`PayPalClient::envCredentialsLookValid()`** as a thin alias of **`envCredentialsPresent()`** so any remaining or future call to that name cannot fatal-error.
- Extended **`tests/Feature/CheckoutPaymentConfigTest.php`** with **`test_paypal_client_env_credentials_look_valid_matches_present`** (asserts parity with `envCredentialsPresent()` and behaviour with/without credentials). Existing tests already cover **`GET /api/v1/payments/config`**.

## Testing instructions

### What to verify

- PayPal credential checks expose **`envCredentialsLookValid()`** and it matches **`envCredentialsPresent()`**.
- **`GET /api/v1/payments/config`** returns **200** JSON (no PHP fatal) with expected `data.methods.*` flags under simulated and non-simulated configs.

### How to test

- From repo root: **`php artisan test`**
- **`php artisan routes:smoke`** (no material route edits; optional sanity check)
- Optional manual: **`curl -sS http://127.0.0.1:8000/api/v1/payments/config`** (or your base URL) and confirm JSON `success` / `data.methods`.

### Pass / fail criteria

- **Pass:** All tests green; **`routes:smoke`** reports no HTTP 500 on checked GET routes; payments config endpoint returns valid JSON when exercised manually.
- **Fail:** Any test failure, smoke 500, or fatal error when loading payment config.
