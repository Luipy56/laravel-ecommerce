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
