# Serra analytics (GA4 + GTM)

**Last reviewed:** 2026-11-11  
**Audience:** engineers who maintain Serralleria Solidària tracking  
**Redmine:** parent `#7469`, documentation subticket `#8465`

## Status (2026-11-11)

| Item | State |
|------|--------|
| Website integration | Deployed on production |
| Domain | `https://serralleriasolidaria.cat` (shop) + `/web/` (marketing) |
| GA4 Measurement ID | `G-TS9QP8HY4H` |
| GTM Container ID | `GTM-KGM5ZJLJ` |
| Published GTM version | `2` — *Initial GA4 Ecommerce Integration* (operator-confirmed) |
| Obsolete container | `GTM-P3JR3DMB` (do not use) |
| Purchase end-to-end validation | **Pending** (see [pending-work.md](./pending-work.md)) |

## Table of contents

1. [architecture.md](./architecture.md) — components, lifecycle, Mermaid diagram  
2. [configuration-reference.md](./configuration-reference.md) — IDs, env vars, GTM tags/triggers/variables  
3. [events-reference.md](./events-reference.md) — every dataLayer / GA4 event  
4. [consent-and-privacy.md](./consent-and-privacy.md) — Consent Mode v2, AEPD, storage  
5. [testing-and-validation.md](./testing-and-validation.md) — evidence matrix  
6. [operations-and-troubleshooting.md](./operations-and-troubleshooting.md) — import, publish, rollback, debug  
7. [pending-work.md](./pending-work.md) — purchase validation and optional follow-ups  

Legacy overview (kept, reconciled): [`../analytics-ga4-gtm.md`](../analytics-ga4-gtm.md)

## One-paragraph overview

The shop is a Laravel + React (Vite) SPA. The marketing site is a separate static PHP stack under `/web/`. Both set Google Consent Mode v2 defaults to **denied**, show an equal-choice cookie banner, and load **only** Google Tag Manager (`GTM-KGM5ZJLJ`) after analytics consent. The SPA pushes ecommerce and `serra_page_view` objects to `dataLayer`. GTM maps those events to GA4 (`G-TS9QP8HY4H`) with `send_page_view=false` so SPA navigations do not double-count against Enhanced Measurement history changes.

## Official references

- [GA4 ecommerce](https://developers.google.com/analytics/devguides/collection/ga4/ecommerce)  
- [GA4 recommended events](https://developers.google.com/analytics/devguides/collection/ga4/reference/events)  
- [Google Tag Manager](https://developers.google.com/tag-platform/tag-manager)  
- [Consent Mode](https://developers.google.com/tag-platform/security/guides/consent)  
- [GA4 DebugView](https://support.google.com/analytics/answer/7201382)  
- [AEPD](https://www.aepd.es/)
