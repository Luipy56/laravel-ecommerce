# Consent and privacy

**Last reviewed:** 2026-11-11

## Requirements (summary)

- **GDPR / AEPD cookies:** optional analytics must be opt-in; reject must be as easy as accept (shop banner has Reject / Accept selected / Accept all).
- **Consent Mode v2:** defaults denied; tags wait on `analytics_storage`.
- **Data minimization:** no parallel ads consent grant; `stripPii` on names/terms; no emails/phones in items.

This document describes **implementation and observed behavior**. It is not a legal opinion.

## Storage

| Key | Location | Shape |
|-----|----------|--------|
| `cookie_consent_v2` | `localStorage` (shared shop + `/web` same apex) | `{ essential: true, analytics: boolean, updated_at?: ISO string }` |

Shop: `consentStorage.js`. Marketing: `serra-analytics.js`.

## Consent Mode values (from code)

Defaults (`welcome.blade.php`, `analytics.php`, and SPA/marketing updates):

| Signal | Default | On analytics accept | On reject |
|--------|---------|---------------------|----------|
| `analytics_storage` | `denied` | `granted` | `denied` |
| `ad_storage` | `denied` | `denied` | `denied` |
| `ad_user_data` | `denied` | `denied` | `denied` |
| `ad_personalization` | `denied` | `denied` | `denied` |
| `wait_for_update` | `500` (default only) | — | — |

Returning visitors with `analytics: true` get a head `consent update` to granted before GTM may load.

## GTM load gate (verified)

| Surface | Before analytics consent | After accept |
|---------|--------------------------|--------------|
| Shop `/` | 0 googletagmanager / gtag scripts | 1× `gtm.js?id=GTM-KGM5ZJLJ` |
| Marketing `/web/` | 0 | 1× same |

**Limits of observation:** session-based CDP/browser checks; not a continuous crawler audit; unpublished vs published container affects *tag* execution for normal visitors, not necessarily the script URL load after accept.

## Withdrawal

- Shop: footer cookie settings dispatches `serra:open-cookie-settings`.
- Marketing: `[data-serra-cookie-settings]`.
- Reject after accept: consent update denied. GTM `<script id="serra-gtm-script">` is **not** removed from DOM; published tags require `analytics_storage` (operator-confirmed) so hits should stop.

## Engineer verification checklist

1. Clear site data for the apex domain.  
2. Load `/` → confirm no `googletagmanager.com` script.  
3. Accept all → exactly one `gtm.js?id=GTM-KGM5ZJLJ`.  
4. DevTools Network: filter `google` / `collect` with reject path — no analytics hits after reject.  
5. Application → Local Storage → `cookie_consent_v2`.  
6. Repeat on `/web/`.  
7. Do not paste real customer payloads into tickets or docs.

## Related product GDPR

Account/privacy consents (`client_consents`, DSAR) are separate from cookie analytics — see `docs/gdpr-compliance.md`.
