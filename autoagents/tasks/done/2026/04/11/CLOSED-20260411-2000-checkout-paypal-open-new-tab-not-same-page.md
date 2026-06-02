---
## Closing summary (TOP)

- **What happened:** Redirect-based PayPal approval used same-tab navigation to `approval_url`, which left the SPA and made returning to checkout confusing.
- **What was done:** A shared helper opens hosted PayPal approval in a new tab with `noopener`/`noreferrer`; if `window.open` is blocked, a warning and user-triggered link appear with i18n on checkout and order pay; the inline Smart Buttons path is unchanged.
- **What was tested:** `npm run build`, `php artisan test` (69 passed, 5 skipped), and `php artisan routes:smoke` succeeded; grep and file review confirmed no same-tab `location.href` to `approval_url`; interactive browser and PayPal sandbox steps were not run (N/A in test report).
- **Why closed:** Tester outcome **PASS** — automated checks and static verification satisfied the stated pass criteria; manual spot-check deferred to staging per test report.
- **Closed at (UTC):** 2026-04-11 19:50
---

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

---

## Test report

### Date/time (UTC) and log window

- **Started:** 2026-04-11 19:47:51 UTC  
- **Finished:** 2026-04-11 19:49:00 UTC (approx.)  
- **Log window reviewed:** `storage/logs/laravel.log` lines around 2026-04-11 19:46–19:48 UTC (test run output; no PayPal redirect UI errors expected in logs).

### Environment

- **Branch:** `agentdevelop`  
- **PHP:** 8.3.6  
- **Node:** v22.20.0  
- **APP_ENV:** default for `php artisan test` / `npm run build` (local tooling; PHPUnit uses `testing` per log lines).

### What was tested

Per **What to verify** and **How to test**: automated build and test commands; static code review for `approval_url` handling, fallback UI, and i18n; PHPUnit PayPal feature coverage for regression signal. Interactive manual steps (live PayPal redirect tab, order pay, popup-blocker UX, inline sandbox completion) were **not** executed in this session (no browser / no PayPal sandbox session).

### Results

| Criterion | Result | Evidence |
|-----------|--------|----------|
| No same-tab navigation to PayPal for `approval_url` | **PASS** | `CheckoutPage.jsx` / `OrderDetailPage.jsx` call `openPayPalApprovalInNewTab(c.approval_url)`; no `location.href` to `approval_url` in those paths (grep). |
| Popup blocked: warning + “Open PayPal” link (checkout + order pay) | **PASS** | `setPaypalApprovalFallbackUrl` when `!opened`; alert uses `t('shop.payment.paypal_popup_blocked')` and link with `target="_blank"` / `rel="noopener noreferrer"` (`CheckoutPage.jsx`; same pattern on order detail). |
| Inline PayPal path unchanged / no capture regression | **PASS** | `php artisan test` — `Tests\Feature\PayPalPaymentTest` and related payment tests passed; implementation summary states inline path unchanged. |
| i18n `ca` / `es` for new keys | **PASS** | `shop.payment.paypal_popup_blocked` and `shop.payment.paypal_open_link` present in `resources/js/locales/ca.json` and `es.json`. |
| `npm run build` | **PASS** | Exit code 0 (Vite production build). |
| `php artisan test` | **PASS** | Exit code 0; 69 passed, 5 skipped. |
| `php artisan routes:smoke` | **PASS** | Exit code 0; no HTTP 500 on checked GET routes. |
| Manual: `/checkout` redirect PayPal in new tab | **N/A — no browser** | Not executed; recommend product spot-check with `approval_url` flow. |
| Manual: `/orders/:id` Pay order + new tab | **N/A — no browser** | Not executed. |
| Manual (optional): strict popup blocking | **N/A — no browser** | Not executed. |
| Manual: inline PayPal end-to-end | **N/A — no browser** | Automated coverage only. |

### Overall

**PASS** — Automated checks and static verification satisfy the stated **PASS** criteria (no same-tab `location.href` for `approval_url`, translations present, PayPal tests green). Manual browser and PayPal sandbox checks were out of scope for this automated run; **loop protection** does not apply (first verification).

### Product owner feedback

The implementation opens hosted PayPal approval in a new tab with a documented fallback when popups are blocked, and translations exist in both storefront locales. Please run a quick manual pass in staging with real `approval_url` redirect and, if possible, a popup-blocked profile, so tab behaviour and the fallback link are confirmed in real browsers before release.

### URLs tested

**N/A — no browser** (no interactive session in this verification).

### Relevant log excerpts

```
[2026-04-11 19:48:04] testing.INFO: stripe.webhook.payment_intent_succeeded {"event_id":"evt_test_webhook_1","payment_id":1} 
[2026-04-11 19:48:04] testing.INFO: stripe.webhook.checkout_session_completed {"event_id":"evt_cs_completed_1","payment_id":1,"order_id":1} 
```

(General test-run noise; no errors tied to PayPal redirect UI.)
