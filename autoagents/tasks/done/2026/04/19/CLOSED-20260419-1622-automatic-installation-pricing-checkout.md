---
## Closing summary (TOP)

- **What happened:** Checkout installation pricing moved from blocking payment until an admin quote to tiered automatic fees from merchandise subtotal (≤1000 €), keeping the manual quote path above that threshold per the issue.
- **What was done:** Backend tier helper on `Order`, cart/checkout API fields, React cart and checkout totals with ca/es strings, and PHPUnit coverage for tiers, mail behaviour, and payment flows.
- **What was tested:** Tester ran full `php artisan test`, `routes:smoke`, and `npm run build` — all passed; recommended manual browser checkout was not run but overall outcome was PASS.
- **Why closed:** Required automated verification completed successfully with documented PASS from the tester.
- **Closed at (UTC):** 2026-04-19 16:37
---

# Automatic installation pricing in checkout

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/15

## Problem / goal
Installation is optional via a checkout checkbox; today it forces **Pending Installation Quote** and blocks payment until an admin sets a price manually. The goal is to apply **tiered installation fees** from cart subtotal (excluding installation) so most orders can pay immediately, keeping manual quotes only above **1000 €** cart total per the issue’s table.

## High-level instructions for coder
- Map current data model and flow: order/cart totals, installation flag, **Pending Installation Quote**, and payment gating rules.
- Implement tiered fees (0–250 € → 90 €, 251–500 → 120 €, 501–1000 → 180 €, &gt;1000 → manual quote path unchanged) without circular total calculation (installation must not inflate the bracket used for the bracket itself).
- Update checkout UI for real-time total when toggling installation, clear messaging for “custom quote” when &gt;1000 €, and keep user-facing copy translatable (ca/es).
- Add or extend automated tests around totals, status transitions, and payment eligibility; run the project’s verification steps after behavior changes.

## Implementation summary (coder)
- **`Order`:** `INSTALLATION_MERCHANDISE_AUTOMATIC_MAX_EUR` (1000) and `automaticInstallationFeeFromMerchandiseSubtotal()` tier table; bracket uses **merchandise lines subtotal only** (`lines_subtotal`), never shipping or installation.
- **`OrderController::checkout`:** If installation is requested and merchandise total **&gt; 1000 €**, unchanged manual-quote flow (`awaiting_installation_price`, quote email). Otherwise assigns `installation_price` + `installation_status = priced`, creates installation address, and proceeds with payment like a normal checkout.
- **`CartController`:** Responses include `installation_quote_required` and `installation_fee_eur` so the SPA can preview totals; `addLineDb` aligned with full cart payload.
- **React:** Checkout and cart show installation fee row and **estimated grand total** when tier applies; payment method is required only when payment is allowed (tier or no installation). Quote-only path keeps pay-after-quote messaging (`ca`/`es` keys added).
- **Tests:** Unit test for tier helper; feature tests adjusted (merchandise **&gt; 1000 €** uses 41×25 € line for quote path); new test for automatic tier + simulated payment without quote mail.

---

## Testing instructions

1. **PHPUnit (requires `pdo_sqlite` for Feature tests):** From repo root, `php artisan test` — pay attention to `CustomerTransactionalEmailTest` (installation quote vs tier) and `OrderInstallationPricingTest`.
2. **Smoke:** `php artisan routes:smoke` — no HTTP 500 on GET routes.
3. **Frontend:** `npm run build` (passed after changes).
4. **Manual checkout (recommended):** After `migrate:fresh --seed`, log in, add cart lines so **merchandise total ≤ 1000 €**, enable installation on the cart — confirm tier fee on cart/checkout and payment completes. Repeat with merchandise **&gt; 1000 €** — confirm quote-only flow (no immediate payment), then admin quote path unchanged.

---

## Test report

1. **Date/time (UTC) and log window.** Started **2026-04-19 16:35:00 UTC**; finished **2026-04-19 16:37 UTC**. Log window reviewed: **`storage/logs/laravel.log`** approximately **2026-04-19 16:28 UTC** through **2026-04-19 16:36 UTC** (testing env).

2. **Environment.** PHP **8.4.16**, Node **v22.22.2**, Git branch **`agentdevelop`** @ **`7fabb68`**. No project **`.env`** in this workspace; PHPUnit used **`APP_ENV=testing`** via **`phpunit.xml`**. **`routes:smoke`** and **`migrate:fresh --seed`** for smoke used **`APP_KEY`** from **`phpunit.xml`**, **`DB_CONNECTION=sqlite`**, file DB **`database/smoke_tester.sqlite`** (created for the run, then removed).

3. **What was tested** (from Testing instructions). Full **`php artisan test`**; **`php artisan routes:smoke`** after DB/key bootstrap; **`npm run build`**; manual browser checkout **not** executed (recommended step only).

4. **Results.**

   | Criterion | Result | Evidence |
   |-----------|--------|----------|
   | PHPUnit (`CustomerTransactionalEmailTest`, `OrderInstallationPricingTest`, suite) | **PASS** | `php artisan test` exit code **0**; **`OrderInstallationPricingTest`** bracket cases ✓; **`CustomerTransactionalEmailTest`** includes **`checkout with installation automatic tier skips quote mail and confirms`** and **`checkout with installation request sends quote request mail`** (! style output but non-failing). PHPUnit prints **WARN** metadata deprecation on **`OrderInstallationPricingTest`** docblock (PHPUnit 12 prep); not a test failure. |
   | **`php artisan routes:smoke`** | **PASS** | After **`APP_KEY`** + SQLite **`migrate:fresh --seed`**, stdout: **`All checked GET routes returned a non-500 status.`**, exit **0**. |
   | **`npm run build`** | **PASS** | Vite **`✓ built in 5.11s`**, exit **0** (chunk size / CSS `@property` notices only). |
   | Manual checkout (recommended) | **NOT RUN** | No interactive browser session in this runner; reliance on automated installation mail/tier tests above. |

5. **Overall:** **PASS** (required automated checks; recommended manual checkout **not** executed).

6. **Product owner feedback.** Tier logic and checkout email behaviour are covered by automated tests, including confirmation that automatic tiers skip the quote-request mail while the manual quote path still sends it when installation is requested. A quick hands-on pass in staging—toggle installation under and over **1000 €** merchandise and confirm UI copy and totals—would still add confidence before release.

7. **URLs tested.** **N/A — no browser**

8. **Relevant log excerpts**

```
[2026-04-19 16:35:52] testing.INFO: stripe.webhook.payment_intent_succeeded {"event_id":"evt_test_webhook_1","payment_id":1}
[2026-04-19 16:35:52] testing.INFO: stripe.webhook.checkout_session_completed {"event_id":"evt_cs_completed_1","payment_id":1,"order_id":1}
```

(First `routes:smoke` without `.env` logged **`MissingAppKeyException`**; reran smoke with **`APP_KEY`** from **`phpunit.xml`** and migrated SQLite — **no** new errors on successful smoke.)
