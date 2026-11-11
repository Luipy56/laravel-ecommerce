# GA4 + Google Tag Manager (Serralleria Solidària)

## Status

| Area | State |
|------|--------|
| Shop SPA code (consent, dataLayer, ecommerce events) | Implemented on `autoagents` |
| Marketing `/web` consent + GTM loader | Implemented |
| Production deploy | **Not deployed** until Luipy authorizes |
| GTM UI tags / triggers / publish | **Requires Google account work** |
| GA4 DebugView / Realtime verification | After GTM publish + deploy |

## Architecture

- **GTM only** (no parallel `gtag.js` config tag in the page). Measurement ID is for GTM configuration.
- **Consent Mode v2** defaults to denied; GTM script loads **only after** analytics consent.
- Shared consent key: `localStorage.cookie_consent_v2` (`{ essential, analytics, updated_at }`).
- Shop SPA pushes ecommerce objects to `dataLayer`; GTM must map them to GA4.

## Identifiers (verify in Google Admin)

- GA4 Measurement ID: `G-TS9QP8HY4H`
- GTM container: `GTM-KGM5ZJLJ` (do **not** use the old placeholder `GTM-P3JR3DMB`)

Confirm both belong to Serralleria Solidària before enabling.

## Environment

Shop (Laravel / Docker app):

```env
ANALYTICS_ENABLED=true
GTM_CONTAINER_ID=GTM-KGM5ZJLJ
GA4_MEASUREMENT_ID=G-TS9QP8HY4H
```

Marketing static PHP-FPM (`.env.static`):

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

## What Luipy must do in GTM

### Already done in GTM (by Luipy)

- Container `GTM-KGM5ZJLJ`
- Tag **GA4 - Serralleria Solidària - Configuración** + trigger Initialization / All Pages
- Container **not published** yet (correct until the site loads GTM)

### Still required in GTM (before/at publish)

1. **Consent:** In Admin → Container settings / Consent Overview, enable Consent Mode. On the GA4 Configuration tag, require `analytics_storage` = granted (so it does not fire before cookie accept).
2. **SPA page views:** Shop is React. Add a GA4 Event tag `page_view` fired on custom event `serra_page_view` (and/or History Change). Do **not** rely only on the first full page load.
3. **Ecommerce:** GA4 Event tags (or one GA4 Event using the Data Layer event name) for: `view_item_list`, `select_item`, `view_item`, `add_to_cart`, `remove_from_cart`, `view_cart`, `begin_checkout`, `add_shipping_info`, `add_payment_info`, `purchase`, `generate_lead`, `view_search_results`. Ecommerce parameter from Data Layer: `ecommerce`.
4. **Avoid duplicates:** Do not also install the GA4 snippet (`gtag.js` with `G-TS9QP8HY4H`) in the website HTML. Code loads **only** GTM.
5. **User-provided data / ads settings:** Prefer leave “datos proporcionados por el usuario” **off** unless legal review says otherwise (PII risk). Enhanced measurement / auto events are OK if they do not duplicate custom tags.
6. **Publish** only after Luipy authorizes **and** the site is deployed with `ANALYTICS_ENABLED=true` + `GTM_CONTAINER_ID=GTM-KGM5ZJLJ`.
7. Validate: GTM Preview → site → accept analytics cookies → GA4 DebugView / Realtime.

Until the site loads GTM **and** the container is published, GA4 will show “no data yet”.

## Legal copy

Update privacy / cookies pages to mention Google Analytics / Tag Manager and optional analytics cookies (shop locales already describe optional analytics). Marketing `cookies.php` / `privacitat.php` still mention Analytics historically — review with legal if needed.

## Files (main)

- `config/analytics.php`, `resources/views/welcome.blade.php`
- `resources/js/analytics/*`, `CookieConsentBanner.jsx`, cart/checkout/product/order hooks
- `static-site/www/php/analytics.php`, `js/serra-analytics.js`, `css/serra-consent.css`
