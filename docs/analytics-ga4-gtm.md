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
- GTM container (from setup notes): `GTM-P3JR3DMB`

Confirm both belong to Serralleria Solidària before enabling.

## Environment

Shop (Laravel / Docker app):

```env
ANALYTICS_ENABLED=true
GTM_CONTAINER_ID=GTM-P3JR3DMB
GA4_MEASUREMENT_ID=G-TS9QP8HY4H
```

Marketing static PHP-FPM (`.env.static`):

```env
STATIC_ANALYTICS_ENABLED=1
STATIC_GTM_CONTAINER_ID=GTM-P3JR3DMB
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

1. Open container `GTM-P3JR3DMB` (or create if the ID was only a placeholder).
2. Add **GA4 Configuration** tag with `G-TS9QP8HY4H`, trigger All Pages (Consent Mode aware).
3. Add GA4 Event tags for each ecommerce / custom event above (or one GA4 Event tag using `{{Event}}` where applicable).
4. Enable **Consent Overview** / built-in Consent Mode support; require `analytics_storage` for analytics tags.
5. **Preview** on stage/prod URL, then **Publish**.
6. Validate in GA4 **DebugView** and Realtime (no production purchase tests without sandbox).

Until tags are published, the site will push `dataLayer` events but GA4 will not show them.

## Legal copy

Update privacy / cookies pages to mention Google Analytics / Tag Manager and optional analytics cookies (shop locales already describe optional analytics). Marketing `cookies.php` / `privacitat.php` still mention Analytics historically — review with legal if needed.

## Files (main)

- `config/analytics.php`, `resources/views/welcome.blade.php`
- `resources/js/analytics/*`, `CookieConsentBanner.jsx`, cart/checkout/product/order hooks
- `static-site/www/php/analytics.php`, `js/serra-analytics.js`, `css/serra-consent.css`
