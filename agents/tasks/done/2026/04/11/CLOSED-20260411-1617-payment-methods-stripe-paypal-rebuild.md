---
## Closing summary (TOP)

- **What happened:** The payment stack was refactored so Stripe Checkout is primary, PayPal remains as fallback, and Stripe webhooks drive paid order state with verification and idempotency.
- **What was done:** Stripe Checkout sessions, secure webhook handling (`checkout.session.completed` and related events) with `stripe_webhook_events`, PayPal redirect/approval flow, removal of Redsys/Revolut from storefront and config, and documentation/env updates per the task.
- **What was tested:** `migrate:fresh --seed`, `php artisan test` (68 passed), `routes:smoke`, `npm run build`, and payments config API checks all passed; live Stripe/PayPal browser sandbox flows were not run in the automated tester run (recommended for staging).
- **Why closed:** All automatable acceptance criteria from the test report passed; tester overall outcome **PASS** with explicit note that manual PSP E2E remains for operators with keys.
- **Closed at (UTC):** 2026-04-11 17:18
---

# Rebuild payment methods: Stripe primary, PayPal fallback, webhooks

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/12

## Problem / goal
Payment implementation needs a **controlled refactor**: strip non-PayPal providers except keeping PayPal as temporary fallback; fix PayPal so users see embedded UI or (preferred) redirect checkout in sandbox; make **Stripe Checkout** the main path with cards, Apple Pay, Google Pay, and Bizum where supported for Spain. **Webhooks** must drive paid order state with signature verification, idempotency, and logging—never trust the frontend alone.

## High-level instructions for coder
- Map current payment/checkout flow (services, config, checkout UI) and plan a minimal invasive migration: backend as source of truth, secrets only in `.env` / `config()`.
- Implement Stripe Checkout per official docs; surface publishable key to SPA safely; keep PayPal sandbox working with visible approval (redirect or Smart Buttons as required).
- Add a secure Stripe webhook route: verify `STRIPE_WEBHOOK_SECRET`, handle `checkout.session.completed` (primary), optional `payment_intent.*` / `charge.refunded`, idempotent processing by Stripe event id, structured logging.
- Ensure orders transition to paid **only** after verified webhook processing; document required env vars (without committing secrets)—see issue body for variable names.
- End-to-end test in sandbox: checkout redirect return, webhook simulation or Stripe CLI where applicable; update `docs/` references if payment setup changes.

## Testing instructions

1. **`php artisan migrate:fresh --seed`** (development DB only) — must exit 0; includes new `stripe_webhook_events` table.
2. **`php artisan test`**
3. **`php artisan routes:smoke`**
4. **`npm run build`**
5. **Manual (sandbox):** With real `STRIPE_*` and `STRIPE_WEBHOOK_SECRET`, place a test order with **card**, confirm redirect to Stripe Checkout, return URL `?payment=ok`, then confirm order shows paid only after webhook (use Stripe CLI `stripe listen --forward-to …/api/v1/payments/webhooks/stripe` or Dashboard webhook deliveries). Test **PayPal** with sandbox buyer: expect redirect to `approval_url` when returned by API, or Smart Buttons fallback; capture still via `POST /api/v1/payments/paypal/capture`.
6. **`GET /api/v1/payments/config`** — response `data.methods` has only `card` and `paypal`; no `revolut_missing_credentials` (removed).

## Implementation summary (for reviewer)

- **Stripe:** `StripeCheckoutStarter` creates **Checkout Sessions** (hosted); stores `cs_*` on payment; success/cancel URLs to `/orders/{id}`. Webhook handles **`checkout.session.completed`** (primary), keeps **`payment_intent.*`**, adds **`charge.refunded`**; idempotency via **`stripe_webhook_events`**; structured `Log::info` / `Log::warning`.
- **PayPal:** `approval_url` from order `links` when present; storefront redirects before falling back to embedded buttons.
- **Removed** storefront **Redsys / Revolut** (routes `redsys/notify`, `revolut` webhook removed). **`PAYMENTS_CHECKOUT_METHODS`** valid values: **`card`**, **`paypal`** only. Bizum via Stripe when `STRIPE_CHECKOUT_PAYMENT_METHOD_TYPES` includes `bizum` (default `card,bizum`).
- **Docs:** `docs/CONFIGURACION_PAGOS_CORREO.md`, `.env.example` comment updated.

---

## Test report

**Tester:** automated verification run (UTC).

1. **Date/time (UTC) and log window**
   - Started: **2026-04-11 17:11:13 UTC**
   - Finished: **2026-04-11 17:17:00 UTC** (approx.)
   - Log window reviewed: **`storage/logs/laravel.log`** entries for **2026-04-11** during/after `php artisan test` (Stripe webhook test lines).

2. **Environment**
   - **PHP:** 8.3.6
   - **Node:** v22.20.0
   - **Branch:** `agentdevelop`
   - **`APP_ENV`:** `local` (from `.env`)

3. **What was tested (from Testing instructions / What to verify)**
   - Fresh migrate + seed including `stripe_webhook_events`
   - Full PHP test suite
   - Route smoke (GET routes, no HTTP 500)
   - Vite production build
   - Payment config API shape (`data.methods` only `card` / `paypal`; no `revolut_missing_credentials`)
   - Manual Stripe Checkout + PayPal sandbox browser flows (instruction 5)

4. **Results**

   | Criterion | Result | Evidence |
   |-----------|--------|----------|
   | `migrate:fresh --seed` exit 0; `stripe_webhook_events` present | **PASS** | Migration `2026_04_11_120000_create_stripe_webhook_events_table` ran; seed completed including `HistoricalSalesSeeder`; terminal `exit_code: 0`. |
   | `php artisan test` | **PASS** | `Tests: 5 skipped, 68 passed (291 assertions)`; exit code 0. Includes `PaymentWebhookTest`, `CheckoutPaymentConfigTest`, `PayPalPaymentTest`, `StripeCheckoutStarterTest`. |
   | `php artisan routes:smoke` | **PASS** | Output: `All checked GET routes returned a non-500 status.` |
   | `npm run build` | **PASS** | `vite build` completed with `✓ built in 3.77s`; exit code 0 (CSS `@property` warning only). |
   | Manual sandbox (card redirect, webhook, PayPal buyer) | **NOT RUN** | No browser session with live Stripe/PayPal sandbox credentials in this run; Stripe/PayPal E2E left to operator with keys + Stripe CLI as in task §5. |
   | `GET /api/v1/payments/config` — `data.methods` only `card` & `paypal`; no `revolut_missing_credentials` | **PASS** | `PaymentConfigController` exposes only `card` and `paypal` under `data.methods`; no `revolut_missing_credentials` key. `CheckoutPaymentConfigTest` + `PayPalPaymentTest` (`assertArrayNotHasKey('revolut', ...)`) passed. |

5. **Overall:** **PASS** — All automatable checks (1–4, 6) passed. Instruction **5** (live sandbox browser + PSP) was **not executed** here; recommend product owner or deployer run that once with real sandbox keys before production.

6. **Product owner feedback**
   - Automated coverage is strong: webhooks (signature, idempotency, `checkout.session.completed`), PayPal capture, and payments config behaviour are exercised by the test suite after a clean migrate/seed.
   - Please still run a **real** Stripe Checkout and PayPal sandbox checkout in a staging environment to confirm redirects, return URLs, and paid-state timing with your PSP accounts.
   - After that smoke, this refactor is ready from a QA automation standpoint for promotion per your release process.

7. **URLs tested**
   - **N/A — no browser** (manual sandbox flows not run).

8. **Relevant log excerpts (minimal)**

```
[2026-04-11 17:15:43] testing.INFO: stripe.webhook.payment_intent_succeeded {"event_id":"evt_test_webhook_1","payment_id":1}
[2026-04-11 17:15:43] testing.INFO: stripe.webhook.checkout_session_completed {"event_id":"evt_cs_completed_1","payment_id":1,"order_id":1}
```
