# Troubleshooting HTTP 419 (CSRF / session) in production

Intermittent **419 Page Expired** on the SPA usually means the browser sent a stale CSRF token or session cookie. A full page reload fixes it because Laravel serves a fresh shell and cookies.

This document covers **operational checks** (infra) and **what the app does automatically** after the #25 fix.

## What the SPA does automatically

| Layer | Behavior |
|-------|----------|
| **Axios (`api.js`)** | On **419**, `GET /csrf-cookie` refreshes cookies, then **retries the request once**. |
| **Auth pages** | If retry still fails on `/login`, `/register`, `/forgot-password`, `/reset-password`: **one automatic reload** per tab (`sessionStorage` guard). |
| **Other pages** | Redirect to `/session-expired`; page **auto-reloads after 2 s** once per tab if reload was not tried yet. |
| **Tab focus** | `SessionKeepAlive` calls `/csrf-cookie` + `GET /api/v1/csrf-ping` when the tab becomes visible (throttled). |
| **Google Sign-In** | Refreshes CSRF before form submit and when the tab regains focus. |

## Production checklist (cookies & session)

Verify these before blaming application code:

- [ ] **`SESSION_DRIVER=database`** (or Redis) **shared by all PHP replicas** — not `file` on multiple containers.
- [ ] **Sticky sessions** on the load balancer **or** centralized session store (DB/Redis).
- [ ] **`SESSION_SECURE_COOKIE=true`** when the site is served over HTTPS.
- [ ] **`SESSION_DOMAIN`** — empty or the apex domain; avoid a wrong subdomain that drops cookies.
- [ ] **`APP_URL`** matches the public URL (scheme + host).
- [ ] **`TrustProxies` / `TRUSTED_PROXIES`** — set when behind nginx, Cloudflare, or Docker reverse proxy so Laravel sees HTTPS and correct host.
- [ ] **Do not cache** the HTML shell (`welcome.blade.php`) at CDN with long TTL; CSRF meta would go stale.
- [ ] **`SESSION_LIFETIME`** (default 120 min in `.env.example`) — increasing alone does not replace client recovery.

## Backend observability

When CSRF validation fails, Laravel logs a structured warning (no token values):

```
CSRF token mismatch — path, method, IP, user-agent, has_session_cookie
```

Search production logs for `CSRF token mismatch` after deploy to confirm volume drops.

## Manual verification

1. Open `/login`, wait past session lifetime (or delete session cookie keeping the tab open) → login or Google should recover via refresh/reload.
2. Repeat with Google Sign-In after inactive tab.
3. Add to cart after long inactivity.
4. Two tabs: log in on one, POST on the other.
5. After prod deploy, monitor 419 log lines.

## Related routes

| Route | Purpose |
|-------|---------|
| `GET /csrf-cookie` | Returns `{ "token": "…" }` and refreshes `XSRF-TOKEN` cookie |
| `GET /api/v1/csrf-ping` | Lightweight session ping (204) |
| `POST /auth/google/redirect` | Web route — **requires** valid `_token` (not API-exempt) |

## References

- [Laravel CSRF](https://laravel.com/docs/12.x/csrf)
- [Laravel Sanctum SPA CSRF pattern](https://laravel.com/docs/12.x/sanctum#csrf-protection) (same cookie refresh idea)
