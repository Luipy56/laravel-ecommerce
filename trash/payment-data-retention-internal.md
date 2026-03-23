# Internal note: payment data retention and traces

This document is a **working baseline** for engineering and operations. **Legal, tax, and PCI obligations depend on your entity, volume, and contracts.** Have retention periods, invoices, and any PSP agreement reviewed by qualified counsel and your tax advisor.

## Principles

- **Minimize storage**: keep only what you need to prove a charge, reconcile with the PSP, handle disputes, and meet accounting rules.
- **Never store** full card numbers, CVV/CVC, magnetic stripe data, PINs, or raw 3DS challenge secrets in application databases or logs.
- **Prefer PSP references**: gateway transaction IDs, order IDs, amount, currency, payment method label, status, and timestamps are usually enough for reconciliation.

## Suggested retention (indicative)

| Data | Suggested minimum retention | Notes |
|------|----------------------------|--------|
| Order + invoice-related totals, shipping, customer identifiers needed for the sale | Align with **commercial / tax record** rules applicable to you (often **several years** in Spain for many traders) | Not card data; standard business records. |
| PSP transaction IDs, payment status history, chargeback-related references | **Long enough for card dispute windows** (often cited up to roughly **12–18 months** for card disputes; your PSP may specify) plus a small operational buffer | Adjust per PSP contract and chargeback experience. |
| Webhook or API **debug logs** | **Short** (e.g. days to weeks in production), with **redaction** of secrets and personal data | Use structured logging without PAN/CVV; rotate and restrict access. |
| Optional `payments.metadata` JSON | **Avoid PII**; if used for support, align with the shortest retention that still allows incident handling | Review regularly and purge where safe. |

## GDPR

- Retain personal data only with a **lawful basis** and for **no longer than necessary** for the purpose.
- Document **purposes**, **retention**, and **deletion or anonymization** procedures in your records of processing.

## Operational checklist

- Secrets only in `.env` / secure stores; rotate on staff or vendor change.
- Monitor failed webhooks and reconciliation gaps between orders and PSP dashboard.
- Production: disable simulated payments (`PAYMENTS_ALLOW_SIMULATED=false`, `APP_DEBUG=false`).

**External review**: schedule periodic review with legal/fiscal counsel and, if applicable, PCI or PSP compliance contacts.
