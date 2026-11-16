# Testing and validation

**Last reviewed:** 2026-11-11

Legend:

| Label | Meaning |
|-------|---------|
| Code | Verified in repository source |
| CDP | Verified with production browser / CDP on lu-zero session |
| Preview | Operator: GTM Preview / Tag Assistant |
| DebugView | Operator: GA4 DebugView |
| Prod live | Operator: after GTM publish v2 |
| Pending | Not verified |

## Matrix

| Check | Code | CDP | Preview | DebugView | Prod live | Pending |
|-------|:----:|:---:|:-------:|:---------:|:---------:|:-------:|
| Analytics env enabled on shop + `/web` | ✓ | ✓ | | | | |
| Consent default denied in HTML | ✓ | ✓ | | | | |
| 0 GTM scripts before consent | ✓ | ✓ | | | | |
| 1 GTM script after consent | ✓ | ✓ | | | | |
| No parallel standalone `gtag.js` config | ✓ | ✓ | | | | |
| No duplicate GTM script (tested sessions) | | ✓ | | | | |
| No relevant console errors (tested) | | ✓ | | | | |
| Tag Assistant connects to production | | | ✓ | | | |
| GA4 Config tag fires | | | ✓ | ✓ | | |
| SPA `page_view` tag fires | | | ✓ | ✓ | | |
| `page_path` / `page_title` resolve | ✓ | ✓ | ✓ | ✓ | | |
| `view_item_list` in GA4 | ✓ | | ✓ | ✓ | | |
| `select_item` in GA4 | ✓ | | ✓ | ✓ | | |
| `add_to_cart` product / EUR / value | ✓ | | ✓ | ✓ | | |
| No PII in inspected `add_to_cart` | | | ✓ | ✓ | | * |
| `view_cart` in Realtime | ✓ | | | | ✓ | |
| SPA nav / back-home +1 page_view | ✓ | ✓ | ✓ | ✓ | | |
| Live users/events after publish | | | | | ✓ | |
| `purchase` end-to-end | ✓† | | | | | ✓ |
| Consent reject / withdraw in prod | ✓ | partial | | | | ✓ |
| All ecommerce events individually | ✓ | | partial | partial | | ✓ |

\* Not a full privacy audit of every event.  
† Implementation present (`trackPurchaseOnce`); production payment test not done.

## SPA page view rules under test

- GA4 Config: `send_page_view=false`.  
- Enhanced Measurement: history-based page views **disabled** (operator).  
- Custom `serra_page_view` only.  
- Expect **one** GA4 `page_view` per consented SPA navigation (not per full reload only).  
- Query string is part of `page_path`.  
- `gtm_debug` / Preview params: treat as normal search params in `page_path` unless stripped later (not stripped in code today).  
- `/admin*` excluded in `AnalyticsRouteTracker`.

## How to re-test quickly

1. GTM → Preview → open production URL.  
2. Clear consent storage or use a fresh profile.  
3. Accept analytics → confirm Config + GTM load.  
4. Navigate Products → Pack/Product → Add to cart → Cart.  
5. Watch DebugView / Tag Assistant event list.  
6. Optional: reject optional cookies and confirm tags stop.
