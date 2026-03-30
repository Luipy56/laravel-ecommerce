# Customer email notifications

Transactional emails use Laravel Mail with copy in **Catalan** and **Spanish** (`lang/ca/mail.php`, `lang/es/mail.php`). Locale is chosen from `Accept-Language` when the request provides `ca` or `es`; otherwise it defaults to **Catalan**.

## When messages are sent

| Trigger | Recipient |
|--------|-----------|
| Payment succeeds (checkout simulation, webhooks, PayPal capture) | Client `login_email` |
| Checkout with installation quote requested | Client `login_email` |
| Admin assigns installation price (existing) | Client `login_email` |
| Personalized solution form submitted | Address given in the form |
| Admin sets order status to **in transit** or **sent** (first time entering those states) | Client `login_email` |

## Operations checklist

1. Set **`MAIL_*`** in `.env` (`MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`). For local debugging, `MAIL_MAILER=log` writes to the log instead of sending.
2. Messages are sent **synchronously** in the same request as the triggering action (no queue worker required for delivery to be attempted). If you switch mailables to the queue later, run **`php artisan queue:work`** and ensure `QUEUE_CONNECTION` is configured.
3. Do not commit real SMTP passwords; use the hosting provider’s recommended TLS/port settings in production.

## Troubleshooting: customers see no email

1. **`MAIL_MAILER=log`** — Laravel’s default in `.env.example` never delivers to real inboxes. Switch to **`smtp`** (or your provider’s mailer) on every environment where buyers should receive mail.
2. **`MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME`** — Many providers reject unverified domains or placeholder addresses. Align with SPF/DKIM records for the sending domain.
3. **Payment completed only via webhook** — Confirmation is sent when `PaymentCompletionService::markSucceeded()` runs (Stripe/Redsys/Revolut notifications, PayPal capture, or simulated checkout). If webhooks are missing or return errors, the payment row may stay pending and no confirmation email is sent.
4. **Spam folder** — Transactional copy is plain HTML from Blade; ask the recipient to check junk/spam when testing.
5. **Logs** — Failed sends are logged from mail listeners (`SendOrderPaymentConfirmationEmail`, etc.). Search logs for `email failed` or `email skipped`.

## Code entry points

- Payment success: `App\Services\Payments\PaymentCompletionService::markSucceeded()`
- Installation quote at checkout: `OrderController::checkout()`
- Installation price: `AdminOrderController::update()` → `InstallationPriceWasAssigned`
- Personalized solution: `PersonalizedSolutionController::store()`
- Shipped: `AdminOrderController::update()` when status enters `in_transit` or `sent`
