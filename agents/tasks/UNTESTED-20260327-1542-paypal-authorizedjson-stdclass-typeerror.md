# PayPal client: `authorizedJson` TypeError (stdClass vs array)

## Source

- **Log:** `storage/logs/laravel.log`
- **UTC window:** incident at **2026-03-26** (log timestamp `19:05:02`, channel `testing`)
- **Representative lines:**
  - `App\Services\Payments\PayPal\PayPalClient::authorizedJson(): Argument #3 ($json) must be of type array, stdClass given, called in ... PayPalClient.php on line 120`

## Problem / goal

During PayPal API usage, a value passed as JSON body is a **`stdClass`** where the method expects an **`array`**, causing a **TypeError** and breaking the checkout/payment path that reaches `PayPalClient::authorizedJson`.

## High-level instructions for coder

- Trace **`PayPalClient`** (around the `authorizedJson` call sites, including line ~120): ensure payloads are **arrays** before passing them in, or widen/normalize types consistently (e.g. decode JSON to array, or cast safely).
- Reproduce with the same flow that hit **`testing`** (likely automated test or local run with PayPal fakes); add or adjust a test so **`stdClass` responses** from HTTP fakes do not regress.
- Keep behavior aligned with existing PayPal order/create flows (`PayPalCheckoutStarter`, `PaymentCheckoutService`, `OrderController::checkout`).
- Run **`php artisan test`** and payment-related checks per project rules.

**Note:** Log also shows **PayPal OAuth “Client Authentication failed”** for the same date; that is often **credential / mode** misconfiguration and is already in scope for **`agents/tasks/FEAT-20260327-1401-paypal-checkout-sandbox-e2e.md`**. This **NEW** task targets the **TypeError** specifically.

## Coder notes (2026-03-27)

- **`PayPalClient::authorizedJson`** already accepts **`array|\stdClass`** for the JSON body; **`captureOrder`** intentionally passes **`new \stdClass`** so PayPal capture posts **`{}`** (empty object), not a PHP empty array encoding.
- **Regression guard:** extended **`tests/Feature/PayPalPaymentTest.php`** (`test_paypal_capture_marks_payment_succeeded`) with **`Http::assertSent`** so the capture POST body stays **`{}`**. Reverting the parameter type to **`array` only** breaks that path at runtime and should be caught by this test.

---

## Testing instructions

### What to verify

- PayPal order capture still succeeds end-to-end with HTTP fakes.
- The capture request sends an empty JSON object body (`{}`), consistent with using **`stdClass`** for the internal client payload.

### How to test

- From repo root: **`php artisan test`**
- Narrow: **`php artisan test --filter=PayPal`**
- Optional manual: with sandbox credentials, complete a PayPal checkout and capture (same as existing payment QA).

### Pass/fail criteria

- **Pass:** All tests green; **`test_paypal_capture_marks_payment_succeeded`** passes including the new **`Http::assertSent`** assertion.
- **Fail:** Any PayPal test failure, or capture POST body not exactly **`{}`** when using the current client implementation.
