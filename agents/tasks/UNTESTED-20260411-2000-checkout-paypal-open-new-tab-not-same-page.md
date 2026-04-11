# Checkout: PayPal must not replace the storefront (new tab / window)

## GitHub
- **Issue:** (optional — link when created)

## Problem / goal

When the checkout flow uses the **PayPal redirect** path (`approval_url`), the client sets `window.location.href` to PayPal. The user **leaves the SPA** and the shop context is lost; returning is confusing.

**Goal:** PayPal approval must **not** navigate the current browsing context away from the e-commerce app. **Minimum acceptable:** open PayPal in a **new tab** or **new window** (`window.open` with noopener/noreferrer, or user-triggered link `target="_blank"` with `rel`), so the checkout page remains in the original tab.

**Constraints:**
- Preserve security (no `opener` abuse); follow browser popup policies (prefer opening from a direct user gesture where required).
- If PayPal or the backend requires **return/cancel URLs**, ensure they are configured so the user can land back in the app with clear state (coordinate with task `NEW-20260411-2015-paypal-cancel-failure-cart-retry.md` if behaviour overlaps).
- **i18n:** Any new user-visible copy via `ca` / `es` keys.

## Known starting points

- `resources/js/Pages/CheckoutPage.jsx` — `window.location.href = c.approval_url` when `c?.gateway === 'paypal' && c.approval_url`.
- `resources/js/Pages/OrderDetailPage.jsx` — same pattern for “Pay order” flow.
- `app/Services/Payments/` — PayPal order creation and whether **approval** is redirect-only vs Smart Buttons (inline SDK in `PayPalInlineButtons.jsx` already keeps user on page).
- Prior archive (context): `agents/tasks/done/2026/03/27/CLOSED-20260327-1745-paypal-approval-ui-popup-vs-redirect.md` if still relevant.

## High-level instructions for coder

1. Identify when the app uses **`approval_url` redirect** vs **inline Smart Buttons** (`client_id` + `paypal_order_id` + `payment_id`).
2. Replace same-tab navigation with a **new-tab/window** strategy for the redirect path, or align backend+frontend so **only** the inline/button flow is used in storefront (if product agrees — document trade-offs).
3. Add **manual test notes**: desktop and mobile browsers (popup blockers may affect `window.open` — handle gracefully with message to allow popups or fallback UX).
4. Run **`npm run build`**, **`php artisan test`**, **`php artisan routes:smoke`** after front-end changes.

## Implementation (2026-04-11)

- Added `resources/js/payments/openPayPalApprovalInNewTab.js`: `window.open(url, '_blank', 'noopener,noreferrer')` and `opener = null` when possible.
- **CheckoutPage** / **OrderDetailPage:** redirect path uses the helper instead of `location.href`. If `window.open` returns null (popup blocked), show a **warning alert** with a user-triggered link (`target="_blank"`, `rel="noopener noreferrer"`) using i18n keys `shop.payment.paypal_popup_blocked` and `shop.payment.paypal_open_link` (ca/es).
- Inline Smart Buttons path unchanged.

## Testing instructions

### What to verify

- Redirect-based PayPal (`approval_url`) does not replace the current tab; PayPal opens in a new tab when the browser allows it.
- If the popup is blocked, a warning and a working “Open PayPal” link appear on checkout and on order pay.
- Inline PayPal (`client_id` + `paypal_order_id` + `payment_id`) still completes payment and navigates/refreshes order as before.
- i18n: new strings exist in `ca` and `es`.

### How to test

1. Commands (from repo root): **`npm run build`**, **`php artisan test`**, **`php artisan routes:smoke`** — all must succeed.
2. **Manual:** Configure an environment where checkout returns **`approval_url`** for PayPal (redirect path, not inline buttons). Complete checkout from `/checkout` with PayPal; confirm the **shop tab stays** on the app and PayPal loads in **another** tab.
3. **Manual:** On `/orders/:id` with **Pay order**, same behaviour when `approval_url` is returned.
4. **Manual (optional):** With strict popup blocking, confirm the yellow alert and link open PayPal after click.
5. **Manual:** With inline PayPal buttons, complete a payment and confirm order updates as before.

### Pass/fail criteria

- **PASS:** No same-tab `location.href` to PayPal for the `approval_url` path; automated checks pass; fallback link works when `window.open` is blocked.
- **FAIL:** Same-tab navigation to PayPal remains for that path, or inline PayPal capture/regression; missing translations for new keys.
