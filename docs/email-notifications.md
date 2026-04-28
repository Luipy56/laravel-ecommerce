# Customer email notifications

**Implementation note:** `bootstrap/app.php` uses `->withEvents(discover: false)` so listeners are **not** auto-registered from `app/Listeners` in addition to the explicit `Event::listen` calls in `AppServiceProvider` (double registration would send every mail twice).

Transactional emails use Laravel Mail with copy in **Catalan**, **Spanish**, and **English** (`lang/ca/mail.php`, `lang/es/mail.php`, `lang/en/mail.php`). Locale is chosen from `Accept-Language` when the request provides `ca`, `es`, or `en`; otherwise it defaults to **Catalan** (or the app locale from `config('app.locale')` when resolving via `MailLocale`).

## When messages are sent

| Trigger | Recipient |
|--------|-----------|
| Payment succeeds (checkout simulation, webhooks, PayPal capture) | Client `login_email` |
| Checkout with installation quote requested | Client `login_email` |
| Checkout with installation quote requested (needs manual install budget) | `MAIL_ADMIN_NOTIFICATION_ADDRESS` (if set) |
| Checkout completes but payment is still pending (card redirect, PayPal, etc.) | Client `login_email` |
| Same: order created, no confirmed payment in that request | `MAIL_ADMIN_NOTIFICATION_ADDRESS` (if set) |
| Admin assigns installation price (existing) | Client `login_email` |
| Personalized solution form submitted | Address given in the form |
| Personalized solution marked **completed** by admin | Email on the solution record |
| Client requests improvements on a personalized solution | `MAIL_ADMIN_NOTIFICATION_ADDRESS` (if set) |
| Admin sets order status to **in transit** or **sent** (first time entering those states) | Client `login_email` |

HTML mail uses the shared transactional layout (`resources/views/emails/layouts/transactional.blade.php`): by default the logo is **`public/images/serraller_solidaria_logo_key.png`** (override with **`MAIL_BRAND_LOGO_URL`** or **`MAIL_BRAND_DEFAULT_LOGO`** under `config('mail.brand')`), plus a **visible** brand name from **`MAIL_BRAND_DISPLAY_NAME`**; the footer is the standard automated-message line. Subjects and body copy for personalized-solution emails use the resolved mail locale, not the framework default. Set a public **`APP_URL`** so image URLs resolve in inboxes.

**`POST /api/v1/personalized-solutions`** also applies a short idempotency window (same client + email + start of text) to reduce duplicate messages from a double submit.

## Operations checklist

1. Set **`MAIL_*`** in `.env` (`MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`). For Gmail SMTP see commented examples in `.env.example`. For local debugging, `MAIL_MAILER=log` writes to the log instead of sending.
2. Optional: **`MAIL_ADMIN_NOTIFICATION_ADDRESS`** for personalized-solution improvement requests, **installation quote** orders (admin must set install price), and **payment-pending** orders after checkout; **`MAIL_BRAND_LOGO_URL`** / **`MAIL_FOOTER_CONTACT_LINE`** for branding.
3. Messages are sent **synchronously** in the same request as the triggering action (no queue worker required for delivery to be attempted). If you switch mailables to the queue later, run **`php artisan queue:work`** and ensure `QUEUE_CONNECTION` is configured.
4. Do not commit real SMTP passwords; use the hosting provider’s recommended TLS/port settings in production.

## Troubleshooting: customers see no email

1. **`MAIL_MAILER=log`** — Laravel’s default in `.env.example` never delivers to real inboxes. Switch to **`smtp`** (or your provider’s mailer) on every environment where buyers should receive mail.
2. **`MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME`** — Many providers reject unverified domains or placeholder addresses. Align with SPF/DKIM records for the sending domain.
3. **Payment completed only via webhook** — Confirmation is sent when `PaymentCompletionService::markSucceeded()` runs (Stripe/Redsys/Revolut notifications, PayPal capture, or simulated checkout). If webhooks are missing or return errors, the payment row may stay pending and no confirmation email is sent.
4. **Spam folder** — Transactional copy is plain HTML from Blade; ask the recipient to check junk/spam when testing.
5. **Logs** — Failed sends are logged from mail listeners (`SendOrderPaymentConfirmationEmail`, etc.). Search logs for `email failed` or `email skipped`.

## Code entry points

- Payment success: `App\Services\Payments\PaymentCompletionService::markSucceeded()`
- Installation quote at checkout: `OrderController::checkout()` → `OrderInstallationQuoteRequested` (client + optional admin)
- Payment still pending after checkout: `OrderController::checkout()` → `OrderPlacedPaymentPending` (client + optional admin; skipped when the request already completed payment, e.g. simulated checkout)
- Installation price: `AdminOrderController::update()` → `InstallationPriceWasAssigned`
- Personalized solution: `PersonalizedSolutionController::store()`
- Personalized solution resolved email: `AdminPersonalizedSolutionController::update()` when status becomes **completed**, or **`POST`** `admin/personalized-solutions/{id}/notify-resolution`
- Personalized solution improvements (admin alert): `PublicPersonalizedSolutionController::requestImprovements()`
- Shipped: `AdminOrderController::update()` when status enters `in_transit` or `sent`
