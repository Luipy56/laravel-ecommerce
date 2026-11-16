# Operations and troubleshooting

**Last reviewed:** 2026-11-11

## Deployment history (facts)

| Step | Detail |
|------|--------|
| Code | GA4/GTM shop + marketing on `autoagents`; merged to `prod` |
| Deploy commit (analytics enablement line) | `03e306f` (docs/authorize + prior analytics commits on that tip) |
| Host | `/srv/serra/prod` — `deploy-prod.sh` rebuild; static PHP recreate |
| Env | `ANALYTICS_*` and `STATIC_*` enabled 2026-11-11 |
| GTM JSON | `docs/gtm/gtm-kgm5zjlj-remaining-ecommerce.json` |
| Import failure (historical) | Invalid enum `list` → corrected to `LIST` in the UI before import |
| Repo JSON fix | Same enum fixed in-repo (`0.2.34`) so a future Merge reimport does not hit that error |
| Import | Admin → Import Container → **Existing workspace** → **Merge** (not Overwrite) |
| Import preview | 17 added; no modifications/deletions of existing manual tags (operator) |
| Preview validation | Passed (see testing matrix) |
| Publish | Container version **2** — *Initial GA4 Ecommerce Integration* (2026-11-11, operator) |

## Safe GTM change procedure

1. Work in a workspace (do not edit Live blindly).  
2. Prefer **Merge** import or manual tag edits; **never Overwrite** unless you intend to wipe the container.  
3. **Preview** against production (or stage if tracking is enabled there).  
4. Publish a named version with a clear note.  
5. Keep prior versions for **rollback** (Versions → publish older).  
6. Export JSON after meaningful publishes for repo archival (optional).

## Troubleshooting

| Symptom | Likely cause | Check |
|---------|--------------|-------|
| No GTM script | Consent denied / analytics disabled / empty container id | `localStorage.cookie_consent_v2`, `__SERRA_ANALYTICS__`, env |
| GTM loads, no hits | Consent Mode denied; unpublished workspace only; ad blockers | Tag Assistant consent state |
| Duplicate page_views | History Enhanced Measurement on **and** SPA tag | GA4 Admin → Enhanced Measurement |
| Missing first page_view after accept | `trackPageView` not re-fired on grant | Navigate once more; optional code fix later |
| Ecommerce empty | Event before consent; `/admin`; push failed silently | `guarded()` + consent |
| Wrong container | Old `GTM-P3JR3DMB` | Site boot object + GTM UI |
| Marketing no SPA page_views | By design | Only shop emits `serra_page_view` |

## Host commands (ops)

```bash
# Shop analytics config inside app container
docker compose -f /srv/serra/prod/docker-compose.prod.yml exec -T app php artisan config:show analytics

# Static env presence (values redacted in chat)
docker compose -f /srv/serra/prod/docker-compose.static.yml exec -T php sh -c 'echo $STATIC_ANALYTICS_ENABLED'
```

Do not print secrets or webhook URLs to Discord or Redmine.
