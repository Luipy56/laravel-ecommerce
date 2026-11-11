# GA4 + Google Tag Manager (Serralleria Solidària)

## Status

| Area | State |
|------|--------|
| Shop SPA code (consent, dataLayer, ecommerce events) | Implemented on production |
| Marketing `/web` consent + GTM loader | Implemented on production |
| Production site env (`ANALYTICS_*` / `STATIC_*`) | **Enabled** 2026-11-11 |
| GTM import (17 elements, Merge) | **Done** (JSON `list` → `LIST` fix before import) |
| GTM Preview + GA4 DebugView (manual) | **Passed** 2026-11-11 (see Validation log) |
| GTM container publish | **Not published** (Luipy publishes manually when ready) |
| Purchase event validation | **Pending** safe test transaction |

## Architecture

- **GTM only** (no parallel `gtag.js` config tag in the page). Measurement ID is for GTM configuration.
- **Consent Mode v2** defaults to denied; GTM script loads **only after** analytics consent.
- Shared consent key: `localStorage.cookie_consent_v2` (`{ essential, analytics, updated_at }`).
- Shop SPA pushes ecommerce objects to `dataLayer`; GTM maps them to GA4.
- GA4 Configuration tag uses `send_page_view=false`. Shop page views come from custom event `serra_page_view`.
- GA4 Enhanced Measurement **history-based page views are disabled** (avoids duplicate SPA page views).

## Identifiers

- GA4 Measurement ID: `G-TS9QP8HY4H`
- GTM container: `GTM-KGM5ZJLJ` (do **not** use the old placeholder `GTM-P3JR3DMB`)

## Environment (production)

Shop (Laravel / Docker app) — `/srv/serra/prod/.env`:

```env
ANALYTICS_ENABLED=true
GTM_CONTAINER_ID=GTM-KGM5ZJLJ
GA4_MEASUREMENT_ID=G-TS9QP8HY4H
```

Marketing static PHP-FPM — `/srv/serra/prod/.env.static`:

```env
STATIC_ANALYTICS_ENABLED=1
STATIC_GTM_CONTAINER_ID=GTM-KGM5ZJLJ
STATIC_GA4_MEASUREMENT_ID=G-TS9QP8HY4H
```

## Consent UX (AEPD)

Banner offers, with equal visibility for reject vs accept-all:

- **Reject optional**
- **Accept selected**
- **Accept all**

Footer: **Cookie settings** reopens the banner.

All GA4 tags in the container require `analytics_storage` (Consent Mode).

## dataLayer events (shop)

| Event | When |
|-------|------|
| `serra_page_view` | SPA route change (non-admin) |
| `view_item_list` / `select_item` / `view_item` | Catalog |
| `add_to_cart` / `remove_from_cart` / `view_cart` | Cart |
| `begin_checkout` / `add_shipping_info` / `add_payment_info` | Checkout |
| `purchase` | Order detail when `has_payment` (once per order / session) |
| `generate_lead` | Successful custom-solution submit |
| `view_search_results` | Product search |
| `serra_consent_analytics_granted` / `_denied` | Consent changes |

Ecommerce payloads clear prior `ecommerce` before each push (GA4 recommended pattern).

### Known SPA note (non-blocking)

`serra_page_view` is gated on analytics consent. Accepting cookies on the landing URL loads GTM but does **not** re-fire `trackPageView` for that same URL until the next SPA navigation. Preview/DebugView confirmed page views on subsequent navigations (including back to home = +1). Optional later fix: call `trackPageView` for the current path inside `applyAnalyticsConsent(true)`.

### Marketing `/web` note

Classic `/web` pages do not push `serra_page_view`. With `send_page_view=false` and history Enhanced Measurement off, marketing page views are out of scope of the SPA page_view tag unless a separate All Pages tag is added later.

## GTM configuration (current)

### Done

- Container `GTM-KGM5ZJLJ`
- Imported **all 17 elements** from `docs/gtm/gtm-kgm5zjlj-remaining-ecommerce.json` via **Merge** (not Overwrite). Import required correcting invalid enum `list` → `LIST` before upload.
- GA4 Configuration tag: `send_page_view=false`, Consent Mode / `analytics_storage`
- SPA `page_view` tag + trigger **CE - serra_page_view** + **DLV - page_path** / **DLV - page_title**
- Ecommerce tag + trigger; `generate_lead` and `view_search_results` tags verified present
- Required `analytics_storage` consent on tags
- Enhanced Measurement history-based page views **disabled** in GA4

### Not done (manual)

1. **Publish** the GTM container (Luipy; after optional purchase test).
2. **Purchase** tracking validation with a safe test transaction.
3. Keep **user-provided data / ads** settings off unless legal review says otherwise.
4. Do **not** add a parallel site `gtag.js` snippet for `G-TS9QP8HY4H` (site loads GTM only).

Reference import file (historical; import already completed): `docs/gtm/gtm-kgm5zjlj-remaining-ecommerce.json`

### GTM API automation

Official Tag Manager API can create the same entities, but needs an OAuth client + refresh token from a Google user with **Edit** on `GTM-KGM5ZJLJ`. JSON import was the path used. No further import is required.

## Validation log (2026-11-11)

Production testing with **Tag Assistant** and **GA4 DebugView** (container still unpublished; Preview connected to production site):

| Check | Result |
|-------|--------|
| GTM connects to production site | OK |
| GA4 Config + SPA `page_view` tags fire | OK |
| `page_path` / `page_title` resolve | OK |
| `view_item_list`, `select_item`, `add_to_cart` in GA4 | OK |
| `add_to_cart` product info, EUR, value | OK |
| No customer PII in inspected `add_to_cart` | OK |
| SPA nav back to homepage → `page_view` +1 | OK |
| Obvious duplicate page views | None observed |
| Purchase / safe test transaction | Pending |
| GTM container published | No (manual next step) |

Until the container is **published**, live visitors outside Preview do not receive the full tag set as a released version. After publish, confirm Realtime / standard reports.

## Legal copy

Update privacy / cookies pages to mention Google Analytics / Tag Manager and optional analytics cookies (shop locales already describe optional analytics). Marketing `cookies.php` / `privacitat.php` still mention Analytics historically — review with legal if needed.

## Files (main)

- `config/analytics.php`, `resources/views/welcome.blade.php`
- `resources/js/analytics/*`, `CookieConsentBanner.jsx`, cart/checkout/product/order hooks
- `static-site/www/php/analytics.php`, `js/serra-analytics.js`, `css/serra-consent.css`
- `docs/gtm/gtm-kgm5zjlj-remaining-ecommerce.json` (merge-import reference; already applied in GTM)
