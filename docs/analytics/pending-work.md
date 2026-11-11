# Pending work

**Last reviewed:** 2026-11-11  
**Redmine documentation ticket:** `#8465` (parent `#7469`)

## High priority — Validate GA4 `purchase` end-to-end

**Status:** Pending  
**Priority:** High  
**Do not** run real paid charges without explicit Luipy authorization. Prefer sandbox / safe test order.

### Objective

Prove completed paid orders reach GA4 accurately once, with correct ecommerce fields.

### Acceptance criteria

- Safe test order completes.  
- `purchase` fires only when `order.has_payment` is true (code gate).  
- `transaction_id` = order id string.  
- `currency` = `EUR`.  
- `value` matches grand total / total used by the helper.  
- `items` match purchased lines (id, name, price, qty).  
- Shipping present when the order object supplies shipping fields.  
- Failed / cancelled payments do not purchase.  
- Reload confirmation in the **same** tab does not duplicate (`sessionStorage` dedupe).  
- Revisit / new session / multi-tab / payment callback retry behavior documented (client dedupe is **not** exactly-once).  
- Consent required; no customer PII in the hit.  
- Event visible in DebugView / ecommerce reports after processing.

### Deduplication note (code)

`trackPurchaseOnce` uses `sessionStorage` key `serra_ga4_purchase_{order.id}`. This does **not** cover new browsers, devices, or cleared session storage. Do not assume exactly-once delivery.

## Optional follow-ups (not confirmed defects)

- Production reject / withdraw consent re-check after publish.  
- Review `gtm_debug` (and similar) in `page_path`.  
- Improve `item_name` when API returns placeholders.  
- Validate remaining ecommerce events one-by-one in DebugView.  
- Align marketing/privacy copy with GTM/GA4 wording (legal).  
- Review GA4 retention / admin settings.  
- Consider `refund` if finance workflows need it.  
- Optional code: fire `trackPageView` on consent grant for the current URL.
