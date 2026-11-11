# GA4 + Google Tag Manager (Serralleria Solidària)

## Status

| Area | State |
|------|--------|
| Shop SPA code (consent, dataLayer, ecommerce events) | Implemented |
| Marketing `/web` consent + GTM loader | Implemented |
| Production deploy | **Enabled** 2026-11-11 (Luipy authorized env + rebuild) |
| GTM UI tags / triggers / publish | Tags imported; **container not published** (Luipy publishes after verify) |
| GA4 DebugView / Realtime verification | After GTM publish |

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
- Tag **GA4 - Serralleria Solidària - Configuración** (`send_page_view=false`) + Consent Mode / `analytics_storage`
- Tag **GA4 - page_view - SPA** + trigger **CE - serra_page_view** + **DLV - page_path** / **DLV - page_title**
- Container **not published** yet (correct until the site loads GTM)

### Remaining GTM work (import JSON — preferred)

Importable merge file (validated, no publish): `docs/gtm/gtm-kgm5zjlj-remaining-ecommerce.json`

1. Admin → **Import Container** → that JSON → **Existing workspace** → **Merge** → **Rename** conflicts.
2. Preview after site deploy; **Publish only** when Luipy authorizes.
3. Do **not** use Overwrite (would wipe the manual page_view / config tags).

The import adds: one GA4 ecommerce tag (`{{Event}}` + ecommerce from dataLayer), `generate_lead`, `view_search_results`, matching triggers/variables, all with `analytics_storage` consent.

### Still required in GTM (before/at publish)

1. Confirm Consent Mode remains enabled and existing GA4 tags still require `analytics_storage`.
2. Import the remaining ecommerce JSON above (or recreate those tags manually).
3. **Avoid duplicates:** Do not also install the GA4 snippet (`gtag.js` with `G-TS9QP8HY4H`) in the website HTML. Code loads **only** GTM.
4. **User-provided data / ads settings:** Prefer leave “datos proporcionados por el usuario” **off** unless legal review says otherwise (PII risk). Enhanced measurement / auto events are OK if they do not duplicate custom tags.
5. **Publish** only after Luipy authorizes **and** the site is deployed with `ANALYTICS_ENABLED=true` + `GTM_CONTAINER_ID=GTM-KGM5ZJLJ`.
6. Validate: GTM Preview → site → accept analytics cookies → GA4 DebugView / Realtime.

### GTM API automation

Official Tag Manager API can create the same entities, but needs an OAuth client + refresh token from a Google user with **Edit** on `GTM-KGM5ZJLJ` (scopes `tagmanager.edit.containers` / `tagmanager.readonly`). No Google password. Not available on this host today; JSON import is the safe path.

Until the site loads GTM **and** the container is published, GA4 will show “no data yet”.

## Legal copy

Update privacy / cookies pages to mention Google Analytics / Tag Manager and optional analytics cookies (shop locales already describe optional analytics). Marketing `cookies.php` / `privacitat.php` still mention Analytics historically — review with legal if needed.

## Files (main)

- `config/analytics.php`, `resources/views/welcome.blade.php`
- `resources/js/analytics/*`, `CookieConsentBanner.jsx`, cart/checkout/product/order hooks
- `static-site/www/php/analytics.php`, `js/serra-analytics.js`, `css/serra-consent.css`
