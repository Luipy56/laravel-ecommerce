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
