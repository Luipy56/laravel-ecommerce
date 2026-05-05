# GDPR / LOPDGDD Compliance

> Version: 2026-05-05 · Applicable law: GDPR (EU 2016/679) + LOPDGDD (Spain)

## 1. Data Controller

**Serralleria Solidària**  
Contact for data protection matters: empresa@serralleriasolidaria.cat

---

## 2. Data Inventory

| Table | Fields | Sensitivity | Legal basis |
|---|---|---|---|
| `clients` | `identification` (DNI/CIF/NIE, **encrypted**), `login_email`, `password` (hashed) | High | Contract (6.1.b) |
| `client_contacts` | `name`, `surname`, `phone` (**encrypted**), `phone2` (**encrypted**), `email` | Medium | Contract (6.1.b) |
| `client_addresses` | `street`, `city`, `province`, `postal_code` | Medium | Contract (6.1.b) |
| `order_addresses` | same address fields + `note` | Medium (order snapshot) | Contract + Legal obligation |
| `personalized_solutions` | `email`, `phone`, address, `problem_description` (**encrypted**), `resolution` (**encrypted**), `improvement_feedback` (**encrypted**), file uploads | High | Contract / Legitimate interest (6.1.b/f) |
| `payments` | `gateway_reference`, `metadata` (Stripe/PayPal IDs) | High (financial) | Contract + Legal obligation |
| `product_reviews` | `comment`, `rating` | Low | Legitimate interest (6.1.f) |
| `return_requests` | `reason` | Low–Medium | Contract + Legal obligation |
| `sessions` | `ip_address`, `user_agent`, session payload | Medium | Legitimate interest (6.1.f) |
| `password_reset_tokens` | `email`, `token` | Medium | Contract (6.1.b) |
| `client_consents` | `type`, `version`, `accepted`, `ip_address`, `user_agent` | Low | Legal record (6.1.c) |

> **Encrypted at rest** = Laravel `encrypted` cast (AES-256-CBC via `APP_KEY`). Decrypted automatically by the ORM; raw DB access shows ciphertext only.

---

## 3. Purposes and Legal Basis

| Purpose | Data used | Legal basis (GDPR Art. 6) |
|---|---|---|
| Account management | Account, identity, contact | Contract (6.1.b) |
| Order fulfilment and delivery | Address, order, payment refs | Contract (6.1.b) |
| Invoicing and tax compliance | DNI/CIF/NIE, order data | Legal obligation (6.1.c) |
| Fraud prevention / session security | IP, session, user agent | Legitimate interest (6.1.f) |
| Product reviews and platform trust | Review comment, rating | Legitimate interest (6.1.f) |
| Marketing communications | Email, name | Consent (6.1.a) — opt-in only |
| Custom solution service | Contact, address, description, files | Contract / Legitimate interest (6.1.b/f) |

---

## 4. Data Retention Matrix

| Category | Purpose | Legal basis | Retention | Deletion rule |
|---|---|---|---|---|
| Account (email, password) | Auth + order history | Contract | Until deletion + 30 days | Anonymise; keep order refs |
| Contact info (name, phone) | Order comms, invoicing | Contract | Duration of account | Delete with account |
| Official ID (DNI/CIF/NIE) | Invoicing | Legal obligation | 5 years from last invoice | Hard delete via `gdpr:purge` |
| Address (saved) | Delivery | Contract | Until user removes / account closes | Delete with account |
| Order address snapshot | Proof of delivery | Contract + Legal obligation | 5 years (civil) / 7 years (tax) | Anonymise after 7 y via `gdpr:purge` |
| Payment gateway refs | Dispute resolution | Contract + Legal obligation | 7 years | Anonymise after 7 y via `gdpr:purge` |
| Personalized solutions | Service delivery | Contract | 3 years after service closes | Anonymise personal fields via `gdpr:purge` |
| Product reviews | Platform trust | Legitimate interest | Until account deletion or 3 y inactive | Anonymise to "[deleted]" |
| Sessions / IP | Security / fraud | Legitimate interest | 30 days | Auto-delete via `gdpr:purge` |
| Password reset tokens | Security | Contract | 1 day | Auto-delete (Laravel default) |
| Marketing consent | Marketing comms | Consent | Until withdrawn | Delete consent record |

---

## 5. Consent Recording

Consent is recorded in `client_consents` at registration.

- `type` values: `privacy_policy`, `marketing`, `cookies_analytics`
- `version`: ISO date string matching the policy version (e.g. `2026-05-05`)
- `accepted`: boolean
- `ip_address` + `user_agent`: stored at time of consent for audit trail

**Config key:** `app.privacy_policy_version` (default `2026-05-05`).  
Update this key whenever the Privacy Policy content changes materially.

---

## 6. Security Measures

| Measure | Implementation |
|---|---|
| TLS in transit | HTTPS enforced via web server / load balancer |
| Column-level encryption | `encrypted` cast on `identification`, `phone`, `phone2`, `problem_description`, `resolution`, `improvement_feedback` |
| Password hashing | `hashed` cast on `Client.password` (bcrypt / Argon2) |
| DB access control | Dedicated app DB user with DML only; see `docs/database-security.md` |
| Stripe webhook verification | `PaymentWebhookController` validates `Stripe-Signature` header |
| Session expiry | Configured via `SESSION_LIFETIME`; sessions older than 30 d purged by `gdpr:purge` |
| PII logging | `LOG_LEVEL` must not be `debug` in production; request body logging disabled |

---

## 7. Subject Rights API

| Right | Endpoint | Auth required |
|---|---|---|
| Access / portability (DSAR) | `GET /api/v1/profile/export` | Yes (client session) |
| Erasure / right to be forgotten | `DELETE /api/v1/profile` | Yes (client session) |
| Consent history | `GET /api/v1/profile/consents` | Yes (client session) |
| Rectification | `PUT /api/v1/profile` | Yes + email verified |

---

## 8. Automated Purge Command

```bash
php artisan gdpr:purge            # live run
php artisan gdpr:purge --dry-run  # preview without changes
```

Scheduled weekly on Sunday at 02:00 (see `routes/console.php`).

---

## 9. Sub-processors

| Processor | Purpose | Privacy URL |
|---|---|---|
| Hosting provider | Server infrastructure | [provider URL] |
| Stripe, Inc. | Payment processing | https://stripe.com/privacy |
| PayPal Holdings, Inc. | Payment processing | https://www.paypal.com/privacy |
| Email delivery provider | Transactional email | [provider URL] |
| Backup / DR provider | Disaster recovery | [provider URL] |

> Update this table whenever a new integration is added.

---

## 10. International Transfers

Stripe and PayPal process payment data in the US under Standard Contractual Clauses (SCCs) or other GDPR Chapter V-approved mechanisms. No other international transfers currently take place.

---

## 11. Dev-Team Checklist

### Before adding a new form field

- [ ] Is this field strictly necessary? (data minimisation)
- [ ] Is there a legal basis? (listed in §3)
- [ ] Is it declared in the Privacy Policy (§2)?
- [ ] Is a retention period defined in §4?
- [ ] Is there a deletion / anonymisation rule in `GdprPurgeCommand`?
- [ ] If sensitive (official ID, health, financial): added `encrypted` cast?
- [ ] Does the form notice or field tooltip explain why it is collected?
- [ ] If based on consent: is there a non-pre-ticked opt-in checkbox?

### Before deploying a new feature that processes PII

- [ ] TLS enforced for the route
- [ ] Auth middleware on the endpoint
- [ ] No PII in Laravel log files (`LOG_LEVEL ≠ debug` in production)
- [ ] New sub-processor added to §9 (and Privacy Policy §5)
- [ ] `client_consents` updated if new consent type introduced
- [ ] DSAR export (`GET /profile/export`) includes the new field

---

## 12. Privacy Policy Version History

| Version | Date | Summary of changes |
|---|---|---|
| 1.0 | 2026-05-05 | Initial GDPR-compliant policy, consent recording, encrypted fields, retention jobs |
