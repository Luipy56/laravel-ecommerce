# Checkout: expose every configured payment method (not only PayPal)

## GitHub
- **Issue:** (optional — link when created)

## Problem / goal

In checkout, **only PayPal** appears as a payment method. **Stripe / card** (and any other methods the stack supports) should appear when **configured and allowed** by environment and `config/payments.php`.

**Goal:**

1. **`GET /api/v1/payments/config`** and the checkout UI must reflect **all methods that are actually available** (`methods.card`, `methods.paypal`, etc.) given:
   - `PAYMENTS_CHECKOUT_METHODS` / `payments.checkout_method_keys`
   - `PAYMENTS_ALLOW_SIMULATED`
   - Stripe keys (`STRIPE_*` / `config/services.php`)
   - PayPal keys (`services.paypal.*`)
2. Fix any bug where the **front-end hides** card (e.g. wrong default selection, filter, or stale `payMethods` state).
3. Fix any bug where the **back-end** returns `methods.card: false` despite valid Stripe configuration (validation, env caching, or `PaymentCheckoutService::isPaymentMethodAvailable` logic).
4. **Docs:** If the fix is “configure `.env`”, add a short note to the relevant **`docs/`** payment doc and/or **`README`** so operators know how to enable multiple methods.

**Constraints:**
- Do not expose methods without credentials when `PAYMENTS_ALLOW_SIMULATED=false` (respect existing security/simulation rules).
- **i18n** for any new UI labels.

## Known starting points

- `config/payments.php` — `checkout_method_keys`, comments on `PAYMENTS_CHECKOUT_METHODS`.
- `app/Http/Controllers/Api/PaymentConfigController.php` (or equivalent) — `payments/config`.
- `tests/Feature/CheckoutPaymentConfigTest.php` — assertions on `data.methods.card` / `paypal`.
- `app/Services/Payments/PaymentCheckoutService.php` — `isPaymentMethodAvailable`.
- `resources/js/Pages/CheckoutPage.jsx` — loads `payments/config`, builds payment method radio/select.
- `docs/CONFIGURACION_PAGOS_CORREO.md` or project payment docs — env var reference.
- Archive for context: `agents/tasks/done/2026/03/29/CLOSED-20260329-2115-more-ways-to-pay.md`, `done/2026/04/11/CLOSED-20260411-1617-payment-methods-stripe-paypal-rebuild.md`.

## High-level instructions for coder

1. Reproduce locally with **Stripe test keys** + **PayPal sandbox** (or simulated mode): confirm whether the issue is **config**, **API response**, or **React rendering**.
2. Add or extend **automated tests** so `payments/config` returns `methods.card: true` when Stripe credentials are present and card is allowed (mirror production expectations; use config mocking like existing tests).
3. Fix the root cause (single PR or clearly linked commits on integration branch).
4. Verify **`CheckoutPage`** lists **both** when both are available; default selection must be a **visible** method (not a hidden radio pointing at a disabled option).

## Testing instructions

1. **`php artisan test`** (especially `CheckoutPaymentConfigTest` and payment-related tests) — pass.
2. **`php artisan routes:smoke`** — pass.
3. **`npm run build`** — pass if JS changed.
4. **Manual:** With **both** Stripe and PayPal configured, open `/checkout` — **two** methods appear and either can be chosen (smoke per `.cursor/rules/testing-verification.mdc` when possible).
5. **Manual:** With only Stripe configured, card shows; with only PayPal, PayPal shows.

## Pass / fail criteria

- **PASS:** When env + config allow multiple methods, checkout shows them all; tests cover the regression; docs updated if operator setup was unclear.
- **FAIL:** Only PayPal visible while Stripe is configured and allowed, without a documented intentional restriction.

---

## Coder implementation summary (2026-04-11 UTC)

**Root cause (typical “only PayPal”):** `PAYMENTS_CHECKOUT_METHODS=paypal` in `.env` whitelists PayPal only, so `GET /api/v1/payments/config` correctly returns `methods.card: false` even with `STRIPE_*` set. Backend logic was already consistent; docs/README now call this out explicitly.

**Code / tests:**

- **`tests/bootstrap.php`:** After loading `.env`, unset `PAYMENTS_CHECKOUT_METHODS` so PHPUnit does not inherit a developer’s `paypal`-only list when resolving `config/payments.php` (tests that need a whitelist still use `config([...])`).
- **`CheckoutPage.jsx`:** Do not render card/PayPal `<option>`s until `payments/config` has loaded; show `common.loading`; disable submit until config is ready — avoids a brief UI where both methods appeared then one disappeared, and blocks checkout before `methods.*` is known.
- **`CheckoutPaymentConfigTest`:** `test_payments_config_exposes_card_and_paypal_when_both_configured_and_whitelisted` asserts both flags true with Stripe + PayPal credentials and `['card','paypal']` whitelist.
- **`docs/CONFIGURACION_PAGOS_CORREO.md`**, **`README.md`**, **`CHANGELOG.md`**, version **0.1.9** (`package.json` / lock).

### Testing instructions (handoff)

**What to verify**

- Payments config API and checkout UI stay aligned; no premature submit; dual-method regression covered by PHPUnit; operators informed about `PAYMENTS_CHECKOUT_METHODS`.

**How to test**

- `php artisan test` — full suite (includes new `test_payments_config_exposes_card_and_paypal_when_both_configured_and_whitelisted`).
- `php artisan routes:smoke` — no HTTP 500 on GET routes.
- `npm run build` — succeeds after `CheckoutPage.jsx` change.
- **Manual:** Logged-in user, non-empty cart, `/checkout` — payment dropdown shows “Cargando…” then only methods returned by `GET /api/v1/payments/config`; submit stays disabled until loaded. With both Stripe + PayPal configured and whitelist empty or `card,paypal`, both methods appear.
- **Manual:** If `.env` has `PAYMENTS_CHECKOUT_METHODS=paypal` only, only PayPal appears (intentional); remove or set `card,paypal` to show card.

**Pass/fail criteria**

- **PASS:** Above commands exit 0; checkout does not list both card and PayPal before config loads; new test passes; docs explain whitelist vs “only PayPal”.
- **FAIL:** Regression in payments config tests, build failure, or submit enabled before `payments/config` completes.
