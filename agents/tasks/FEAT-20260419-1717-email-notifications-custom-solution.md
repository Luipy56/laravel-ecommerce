# Email notifications and custom-solution client experience

## GitHub

- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/17

## Problem / goal

- Set up **transactional email** (Gmail SMTP for low volume) with env vars as in the issue, and **Mailables** for: order completed, payment verified, installation quote response, custom-solution resolved, and other business-critical updates.
- **Email UX:** company logo, responsive layout, short status + **link** to the right page (not full content in the body), branded footer, optional unsubscribe.
- **Client area (`/client/*`):** custom-solution page to see status; actions: **Accept & pay**, **Request improvements** (notify admin), **Delete request**; order status history with navigable updates.
- **Admin:** handle improvement requests (see feedback, update solution, resend email, consider **iterations** tracking if the product model needs it).
- **Privacy / LOPD:** custom solutions can be created without login; **pay without being forced to log in** if data was already collected; client can **edit personal data** or **delete** the request.
- **Priority in issue:** wire **Gmail SMTP** and a test mailable first. See `docs/` (e.g. email / deployment) if the repo already documents mail.

## High-level instructions for coder

- Add or align **Laravel mail config** and **Mailable** classes; use **translatable** user-facing copy (ca/es) per project rules; do not put secrets in the front end.
- Implement the **client** and **admin** flows and notifications as required by the issue, including API and policies for who can read/update/delete custom-solution data.
- After changes, follow **`.cursor/rules/testing-verification.mdc`**: tests, `routes:smoke` as applicable, and `npm run build` if React changes; mention any **mail** or **queue** env the owner must set in non-dev.
