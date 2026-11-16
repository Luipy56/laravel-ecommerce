# Events reference

**Last reviewed:** 2026-11-11  
**Currency:** all monetary helpers use `EUR` (`ecommerce.js` `CURRENCY`).  
**Consent:** every push is wrapped in `guarded()` → requires `hasAnalyticsConsent()` except where noted.  
**PII:** `stripPii` drops emails / long phone-like strings; names truncated to 120 chars. Fallback name `item-{id}`.

## SPA page views

### `serra_page_view` → GA4 `page_view`

| Field | Detail |
|-------|--------|
| When | React Router location change (`AnalyticsRouteTracker`) |
| Source | `trackPageView` in `ecommerce.js` |
| Payload | `{ event, page_path, page_title? }` |
| Path | `pathname + search` (query string included) |
| Exclusions | Paths starting with `/admin` |
| Limitation | Not re-fired on consent accept for the **current** URL until next navigation |
| Verification | Preview/DebugView: SPA nav +1; back to home +1 (operator) |

## Product discovery

| Event | When | Source |
|-------|------|--------|
| `view_item_list` | Product list loads | `ProductListPage` → `trackViewItemList` |
| `select_item` | Card / list select | `ProductCard` / list → `trackSelectItem` |
| `view_item` | Product or pack detail | `ProductDetailPage` / `PackDetailPage` → `trackViewItem` |

Ecommerce shape (after `ecommerce: null` clear):

```json
{
  "event": "view_item",
  "ecommerce": {
    "currency": "EUR",
    "value": 9,
    "items": [{ "item_id": "1", "item_name": "Example lock", "price": 9, "quantity": 1 }]
  }
}
```

`item_list_id` / `item_list_name` set on list/select when provided by the page.

## Cart

| Event | Source |
|-------|--------|
| `add_to_cart` | `CartContext` |
| `remove_from_cart` | `CartContext` |
| `view_cart` | `CartPage` |

**Operator-observed test payload** (anonymized; not a universal template):

```json
{
  "event": "add_to_cart",
  "ecommerce": {
    "currency": "EUR",
    "value": 9,
    "items": [
      {
        "item_id": "1",
        "item_name": "item-1",
        "price": 9,
        "quantity": 1
      }
    ]
  }
}
```

Prefer human-readable `item_name` when the API supplies product/pack names (`toGa4Item`).

## Checkout

| Event | Source |
|-------|--------|
| `begin_checkout` | `CheckoutPage` mount / entry |
| `add_shipping_info` | Checkout submit path |
| `add_payment_info` | Same path with `payment_method` → `payment_type` (max 40 chars) |

## Purchase

| Field | Detail |
|-------|--------|
| Event | `purchase` |
| Source | `OrderDetailPage` when `order.has_payment` → `trackPurchaseOnce` |
| `transaction_id` | `String(order.id)` |
| `value` | `grand_total` or `total` |
| `shipping` | `shipping_cost` or `shipping_flat_eur` when finite |
| Dedupe | `sessionStorage` key `serra_ga4_purchase_{id}` |
| Status | **Not end-to-end validated** in production test matrix — see [pending-work.md](./pending-work.md) |

Failed payments (`has_payment` false) do not call purchase. Reload of a paid order in the **same tab session** is suppressed by sessionStorage; **new sessions / devices are not**.

## Other

| Event | Source | Params |
|-------|--------|--------|
| `generate_lead` | `CustomSolutionPage` success | `form_id: custom_solution` |
| `view_search_results` | `ProductListPage` with search | `search_term` (PII-stripped) |
| `serra_consent_analytics_granted` / `_denied` | Consent update helpers | Signal for debugging |

## Marketing `/web`

No ecommerce / `serra_page_view` emitters. After consent, GTM loads; Config tag may run, but SPA page_view and ecommerce tags stay idle without those dataLayer events.
