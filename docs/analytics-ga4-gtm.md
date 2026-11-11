# GA4 + Google Tag Manager (Serralleria Solidària)

> **Canonical pack:** [`docs/analytics/README.md`](./analytics/README.md)  
> **Redmine:** `#8465` (parent `#7469`)  
> **Last reviewed:** 2026-11-11

## Status (summary)

| Area | State |
|------|--------|
| Shop + marketing site integration | Deployed on production |
| Env `ANALYTICS_*` / `STATIC_*` | Enabled |
| GTM container | `GTM-KGM5ZJLJ` — published version **2** (*Initial GA4 Ecommerce Integration*, 2026-11-11) |
| GA4 | `G-TS9QP8HY4H` |
| Preview / DebugView (catalog + cart) | Passed (operator) |
| Purchase end-to-end | **Pending** — see [`analytics/pending-work.md`](./analytics/pending-work.md) |
| Obsolete ID | `GTM-P3JR3DMB` — do not use |

## Quick links

- Architecture + diagram → [`analytics/architecture.md`](./analytics/architecture.md)  
- Tags / triggers / variables → [`analytics/configuration-reference.md`](./analytics/configuration-reference.md)  
- Events → [`analytics/events-reference.md`](./analytics/events-reference.md)  
- Consent → [`analytics/consent-and-privacy.md`](./analytics/consent-and-privacy.md)  
- Evidence matrix → [`analytics/testing-and-validation.md`](./analytics/testing-and-validation.md)  
- Ops / import history → [`analytics/operations-and-troubleshooting.md`](./analytics/operations-and-troubleshooting.md)  

## Environment (production)

```env
ANALYTICS_ENABLED=true
GTM_CONTAINER_ID=GTM-KGM5ZJLJ
GA4_MEASUREMENT_ID=G-TS9QP8HY4H
```

```env
STATIC_ANALYTICS_ENABLED=1
STATIC_GTM_CONTAINER_ID=GTM-KGM5ZJLJ
STATIC_GA4_MEASUREMENT_ID=G-TS9QP8HY4H
```

## Import artifact

Historical merge file: `docs/gtm/gtm-kgm5zjlj-remaining-ecommerce.json`  
Import used **Merge** after correcting enum `list` → `LIST`. No further import required for v2.
