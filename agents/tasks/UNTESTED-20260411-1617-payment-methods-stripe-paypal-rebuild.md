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
