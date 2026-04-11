# PayPal abandoned / failed: keep sale in cart, clear messaging, avoid silent unpaid orders

## GitHub
- **Issue:** (optional — link when created)

## Problem / goal

If the customer **does not complete** PayPal (cancel, error, or closes tab):

- There is **no clear notification** that payment did not succeed.
- Under **Pedidos**, nothing **highlights** that the order still needs payment — confusing.

**Product preference:** If payment is **not** completed successfully, prefer that the **order effectively stays associated with the cart** so the user can **retry payment from the same checkout context**, avoiding orphan “unpaid” orders that look like finished purchases.

**Goal (interpret for implementation):**

1. **UX:** On cancel/failure/return without capture, show an explicit **error or warning** (toast or inline alert) that **payment was not completed** — never silent success.
2. **Data flow:** Align backend + frontend so an **abandoned PayPal attempt** does not leave the user with a **misleading order list** (e.g. unpaid order that looks like a normal placed order). Preferred direction: **restore cart / allow retry checkout** for the same intent; avoid stranding users without guidance.
3. **Orders list / detail:** If an order **must** exist in `pending_payment` (or similar), the **orders UI** must **visually distinguish** “awaiting payment” vs “paid” (badges, copy, actions). Prefer not to rely on the user inferring from absence of email alone.
4. Coordinate with **`NEW-20260411-2000-checkout-paypal-open-new-tab-not-same-page.md`** for return URLs and tab handling.

**Constraints:**
- **i18n:** All new strings in `resources/js/locales/ca.json` and `es.json` (or Laravel lang if server-rendered).
- Respect existing **`Order`** / **`Payment`** models and webhooks; do not break captured payments or idempotency.

## Known starting points

- `resources/js/Pages/CheckoutPage.jsx` — post-checkout navigation, `payment_error`, `has_payment`, `payment_checkout`.
- `resources/js/Pages/OrderDetailPage.jsx` — query params `payment=ko`, Stripe return handling; extend patterns for PayPal return/cancel if needed.
- `app/Http/Controllers/Api/` — order checkout, PayPal capture/cancel endpoints.
- `routes/api.php` — payments routes.
- Search: `payment_error`, `awaiting`, `pending`, `paypal/capture`, `fetchCart`.

## High-level instructions for coder

1. Map the **current lifecycle**: when is the order created vs when is payment captured for PayPal (redirect and inline)?
2. Design the smallest change that meets the product preference: e.g. **defer order commit** until payment success, **or** cancel/reopen cart **or** mark order clearly and offer **“Retry payment”** with cart repopulation — pick one coherent approach and document it in the task **Implementation summary**.
3. Implement **return/cancel** handling for PayPal flows so the SPA can show **`payment=ko`**-style feedback (or equivalent) for PayPal.
4. Update **orders list** (`OrdersPage` or equivalent) and **order detail** so unpaid states are obvious.
5. **`php artisan test`:** add or extend feature tests for the chosen behaviour (e.g. cancel path, pending state).
6. Run **`php artisan migrate:fresh --seed`** only if schema/migrations change (avoid unnecessary migrations per project rules).

## Implementation summary

- **Approach:** Keep the existing model (order exists, `awaiting_payment` until capture). Improve **visibility** and **retry context** instead of deferring order creation.
- **PayPal REST `createOrder`:** `application_context` now sends **`return_url`** and **`cancel_url`** to the storefront (`/orders/{id}?payment=paypal_return` and `?payment=ko`) so hosted approval/cancel can land in the SPA with existing query handling for `payment=ko` plus a short info line for `paypal_return`.
- **Inline Smart Buttons:** `PayPalInlineButtons` exposes **`onCancel`**; checkout and order pay flows show **warning toast + inline alert** (`shop.payment.paypal_not_completed`).
- **New-tab approval:** After opening the PayPal approval URL, the client is **navigated to the order detail** page (avoids an empty-cart checkout dead-end) with a one-time toast (`shop.order.paypal_window_hint`).
- **Orders list:** Rows with **`can_pay` and no successful payment** show a **warning badge** (`shop.order.list_payment_due`).
- **Tests:** `PayPalPaymentTest` asserts `cancel_url` / `return_url` in the PayPal create-order JSON.

---

## Testing instructions (handoff)

### What to verify

- PayPal **cancel** (inline buttons) shows **non-silent** warning feedback; **hosted cancel** redirects to order with `payment=ko` messaging where applicable.
- After checkout opens PayPal in a **new tab**, the user lands on **order detail** with guidance, not a confusing empty cart.
- **Orders** list highlights orders that still need payment when the API allows pay.
- Successful PayPal capture flows remain unchanged.

### How to test

- Commands (from repo root): **`php artisan test`**, **`php artisan routes:smoke`**, **`npm run build`**.
- **Manual:** Logged-in user, non-empty cart, **`/checkout`**, choose PayPal, place order; cancel in PayPal inline UI → warning toast/alert. Open approval in new tab → confirm redirect to **`/orders/{id}`** and info toast.
- **Manual:** Complete PayPal successfully (sandbox) → payment succeeds as before.
- **Manual:** **`/orders`** → unpaid payable orders show the **Pendent de pagament / Pendiente de pago** badge next to the order number.

### Pass/fail criteria

- **PASS:** No silent abandonment; clear unpaid signalling on list/detail; automated tests and build/smoke pass.
- **FAIL:** Cancel is still silent; list shows no distinction for unpaid payable orders; regressions in capture or tests.
