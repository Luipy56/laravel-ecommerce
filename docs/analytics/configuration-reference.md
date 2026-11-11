# Configuration reference

**Last reviewed:** 2026-11-11  
**GTM truth source:** operator inspection of published container version **2** (Maestro has no live GTM API). Repo JSON `docs/gtm/gtm-kgm5zjlj-remaining-ecommerce.json` is the merge-import artifact (historical).

## Identifiers

| Component | Value | Where engineers use it |
|-----------|--------|-------------------------|
| Project / host path | `serra-prod` → `/srv/serra/prod` | Deploy, env |
| Production domain | `https://serralleriasolidaria.cat` | Shop apex |
| Marketing | `/web/` | Static stack |
| Google Analytics account | Serralleria Solidària | GA admin |
| GA4 property | Serralleria Solidària - Web | GA admin |
| GA4 web stream | Serralleria Solidària - E-commerce | Stream settings / Measurement ID |
| Measurement ID | `G-TS9QP8HY4H` | GTM tag measurement override / docs |
| GTM container name | serralleriasolidaria.cat | Tag Manager UI |
| GTM Container ID | `GTM-KGM5ZJLJ` | Site loader + GTM UI |
| Published version | `2` — Initial GA4 Ecommerce Integration (2026-11-11) | Versions list |
| Obsolete | `GTM-P3JR3DMB` | Never use |

## GTM tags (5) — operator-confirmed

### 1. `GA4 - Serralleria Solidària - Configuración`

| Field | Value |
|-------|--------|
| Purpose | Initialize GA4 destination |
| Type | GA4 Configuration |
| Measurement ID | `G-TS9QP8HY4H` |
| `send_page_view` | `false` |
| Trigger | `Initialization - All Pages` (built-in) |
| Consent | Requires `analytics_storage` |
| Expected | Fires when GTM runs after consent; does **not** send automatic page_view |
| Verify | Tag Assistant → Config tag; DebugView shows stream activity |
| Failure | Wrong Measurement ID; consent denied; container not loaded |

### 2. `GA4 - page_view - SPA`

| Field | Value |
|-------|--------|
| Purpose | SPA navigations without double-counting |
| Type | GA4 Event |
| Event name | `page_view` |
| Params | `page_path` ← `{{DLV - page_path}}`; `page_title` ← `{{DLV - page_title}}` |
| Trigger | `CE - serra_page_view` |
| Consent | `analytics_storage` |
| Depends on | Shop `trackPageView` / `AnalyticsRouteTracker` |
| Verify | Navigate shop routes; +1 page_view each |
| Failure | No consent; `/admin` path; accept-on-landing without later navigation (see events doc) |

### 3. `GA4 - ecommerce - Event`

| Field | Value |
|-------|--------|
| Purpose | All standard ecommerce events from one tag |
| Type | GA4 Event |
| Event name | `{{Event}}` (built-in Event variable) |
| Ecommerce | From `dataLayer` |
| Trigger | `CE - GA4 ecommerce` (regex of event names) |
| Consent | `analytics_storage` |
| Verify | Preview + DebugView for `view_item_list` / `add_to_cart` / … |
| Failure | Missing `ecommerce` object; consent; event name outside regex |

### 4. `GA4 - generate_lead`

| Field | Value |
|-------|--------|
| Event | `generate_lead` |
| Param | `form_id` ← `{{DLV - form_id}}` |
| Trigger | `CE - generate_lead` |
| Consent | `analytics_storage` |
| Source code | `trackGenerateLead('custom_solution')` on successful custom-solution submit |

### 5. `GA4 - view_search_results`

| Field | Value |
|-------|--------|
| Event | `view_search_results` |
| Param | `search_term` ← `{{DLV - search_term}}` |
| Trigger | `CE - view_search_results` |
| Consent | `analytics_storage` |
| Source code | `ProductListPage` when search query present |

## GTM triggers

| Name | Type | Match | Tags |
|------|------|-------|------|
| `Initialization - All Pages` | Built-in | Container init | Config tag |
| `CE - serra_page_view` | Custom Event | `serra_page_view` | SPA page_view |
| `CE - GA4 ecommerce` | Custom Event | regex `^(view_item_list\|select_item\|view_item\|add_to_cart\|remove_from_cart\|view_cart\|begin_checkout\|add_shipping_info\|add_payment_info\|purchase)$` | Ecommerce tag |
| `CE - generate_lead` | Custom Event | `generate_lead` | Lead tag |
| `CE - view_search_results` | Custom Event | `view_search_results` | Search tag |

One ecommerce tag + regex trigger avoids nine nearly identical tags.

## GTM variables

### User-defined (Data Layer v2)

| Name | Key | Used by |
|------|-----|---------|
| `DLV - page_path` | `page_path` | SPA page_view tag |
| `DLV - page_title` | `page_title` | SPA page_view tag |
| `DLV - form_id` | `form_id` | generate_lead |
| `DLV - search_term` | `search_term` | view_search_results |
| `DLV - ecommerce.items` | `ecommerce.items` | Optional / debug / future (ecommerce primarily from dataLayer ecommerce object) |
| `DLV - ecommerce.value` | `ecommerce.value` | Same |
| `DLV - ecommerce.currency` | `ecommerce.currency` | Same |
| `DLV - ecommerce.transaction_id` | `ecommerce.transaction_id` | Same |
| `DLV - ecommerce.payment_type` | `ecommerce.payment_type` | Same |
| `DLV - ecommerce.shipping` | `ecommerce.shipping` | Same |
| `DLV - ecommerce.item_list_id` | `ecommerce.item_list_id` | Same |
| `DLV - ecommerce.item_list_name` | `ecommerce.item_list_name` | Same |

### Built-in (operator-listed)

`Event`, `Page Hostname`, `Page Path`, `Page URL`, `Referrer` — `Event` drives ecommerce tag event name.

## Site config (code)

```php
// config/analytics.php
'enabled' => env('ANALYTICS_ENABLED'),
'gtm_container_id' => env('GTM_CONTAINER_ID'),
'ga4_measurement_id' => env('GA4_MEASUREMENT_ID'),
```

Loader requires `enabled` **and** non-empty GTM id (`gtmLoader.js` / marketing `analytics.php`).
