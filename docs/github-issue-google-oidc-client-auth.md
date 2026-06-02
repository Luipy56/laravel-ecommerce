# Google OIDC sign-in (optional alternative to email/password)

> **Paste this into a new GitHub Issue.** Title suggestion: `feat: Google OIDC sign-in as optional client auth (login + register)`

## Summary

Add **Sign in with Google** (OpenID Connect) as an **optional, additional** authentication path for **storefront clients** (`Client` model / `clients` table). **Email + password login, registration, password reset, and email verification must remain unchanged** — Google is an extra, not a replacement.

**Out of scope:** Admin panel users (`admins` table / `auth:admin` guard).

---

## Problem / goal

Customers should be able to **log in or register** with their Google account while keeping the existing local account flow. One logical account per email: linking must work in **both directions** (local account first, or Google account first).

---

## Google Cloud Console (configured)

Human setup is **done** for dev + production. Credentials must be stored in `.env` (never committed).

### Authorized JavaScript origins

- `http://localhost:8080`
- `http://127.0.0.1:8080`
- `https://serra.ldeluipy.es`

### Authorized redirect URIs

- `http://localhost:8080/auth/google/callback`
- `http://127.0.0.1:8080/auth/google/callback`
- `https://serra.ldeluipy.es/auth/google/callback`

### Environment variables

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
# Optional override; default derives from APP_URL + /auth/google/callback
# GOOGLE_REDIRECT_URI=
```

**Production:** `APP_URL` must match the public origin (`https://serra.ldeluipy.es` in prod; `http://localhost:8080` in Docker dev).

**OAuth consent screen:** Validate in production today — ensure test users are listed while in Testing mode, or publish the app when ready.

**Email restriction:** Accept any email Google returns (`email` + `email_verified` claims). No `@gmail.com`-only filter.

---

## Recommended technical approach

### OAuth UX: official Google button + server redirect (recommended)

After reviewing options for this stack (**React SPA + Laravel session cookies**, same-origin API at `/api/v1`, no Sanctum tokens):

| Approach | Verdict |
|----------|---------|
| Custom button + **Laravel Socialite redirect** | Secure and simple; button styling is custom |
| **Google Identity Services (GIS) Sign-In button** + **redirect to Laravel** | **Recommended** — official polished button + server-side code exchange (secret stays on server) |
| GIS popup + ID token only on frontend | More SPA-native but adds token-validation complexity; less aligned with session-first architecture |

**Decision:** Use the **official GIS “Sign in with Google” button** (`data-ux_mode="redirect"`) pointing at a Laravel web route (e.g. `GET /auth/google/redirect`). Laravel completes the OAuth 2.0 / OIDC **authorization code flow** (Socialite or equivalent), creates the session, and redirects back to the SPA (e.g. `/login?oauth=success&next=…`).

**One Tap:** Optional enhancement on **`/login` only** (not sitewide). Disable if it competes with the email/password form or feels intrusive.

### References (implementers must read)

- [Google Identity — Sign in with Google (web)](https://developers.google.com/identity/gsi/web/guides/overview)
- [Display the Sign in with Google button](https://developers.google.com/identity/gsi/web/guides/display-button)
- [OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server)
- [OpenID Connect — ID token validation](https://developers.google.com/identity/openid-connect/openid-connect)
- [Brand guidelines — Sign in with Google buttons](https://developers.google.com/identity/branding-guidelines)
- [Laravel Socialite — Google driver](https://laravel.com/docs/12.x/socialite) (if adopted)
- [Google account linking (general)](https://developers.google.com/identity/account-linking)

---

## Product decisions (locked)

### Account model

- Authenticatable entity: **`App\Models\Client`** (`clients`), not legacy `User`.
- Store Google **`sub`** (OIDC subject) — e.g. `google_sub` column (unique, nullable) or dedicated `client_oauth_providers` table.
- **`password`:** nullable for Google-only accounts; required hashing when set via profile.
- **Registration type:** Google sign-up creates **`type = person`** only. **Company (`company`) accounts remain registration-form only.**

### Account linking (same account per email)

Follow common industry practice (auto-link when the IdP confirms email ownership):

| Scenario | Behaviour |
|----------|-----------|
| Google login, no local row | Create `Client` + primary `client_contact` from Google profile (`given_name`, `family_name`, `email`) |
| Google login, local row exists with same `login_email`, Google `email_verified === true` | **Auto-link:** set `google_sub`, log in — **same account** |
| Google login, email matches but **`email_verified` is false** | Do **not** auto-link; show error asking user to log in with password or verify email first |
| Local account exists, user later uses Google (same email, verified) | Same auto-link on first Google login |
| `google_sub` already linked to another client | Reject; log security event |

**Unlink Google from profile:** **Not required** for v1 (Google does not mandate an unlink UI). If implemented later: only allow unlink when the client has a **password set**.

**Google-only account → set password later:** **Yes** — reuse existing profile update (`password` optional) so the account can also use email/password login.

### GDPR / consents

- **Privacy policy acceptance:** **Before redirect to Google** — same pattern as `RegisterPage` (checkbox required + link to `/privacy-policy`). Do not start OAuth without recorded intent.
- **Marketing opt-in:** Optional **unchecked** checkbox before redirect (mirror register page; explicit opt-in only).
- Record `client_consents` rows using existing **`config('app.privacy_policy_version')`** (default `2026-05-05`). **No new policy version required** unless legal copy is updated to mention Google as identity provider — if copy is updated, bump version in config and document in `docs/gdpr-compliance.md`.
- **DNI/CIF/NIE:** remains **optional** (unchanged).

### Post-login profile completion (soft obligation)

After first Google login/register, if profile is incomplete (e.g. missing postal code / phone — define minimal checklist), **redirect with a modal** explaining what to complete and link to **`/profile`**. Non-blocking for browsing; user-friendly nudge (not a hard gate unless checkout already requires address fields).

### Email verification

If Google returns `email_verified: true`, set `clients.email_verified_at` so **`client.verified`** middleware works immediately (checkout, favorites, etc.). If false, follow existing `/verify-email` flow.

### UI scope

Update **`LoginPage`** and **`RegisterPage`**:

- Keep existing forms **unchanged**.
- Add divider (“or”) + **official Google button** on both pages (register + login — Google creates account automatically when none exists).
- i18n keys under `auth.*` in **ca, es, en** (e.g. “Continue with Google” / “Continuar amb Google” / “Continuar con Google”).
- After OAuth callback: run **`mergeCart()`** like password login; honour `next` query param.

### Feature availability

Google sign-in **must work in production** when credentials are configured. If `GOOGLE_CLIENT_ID` is missing (local dev without secrets), **hide the Google button** gracefully — not a prod kill-switch.

---

## Backend scope

1. **Web routes** (not under `api/v1` CSRF-exempt group):
   - `GET /auth/google/redirect`
   - `GET /auth/google/callback`
2. Controller/service: OAuth exchange, find-or-create/link `Client`, write consents, `Auth::login()`, `session()->regenerate()`, redirect to SPA.
3. **Schema:** add `google_sub` (or oauth table); make `password` nullable where needed — follow project migration rules (new table OK; column changes via existing migration edit + `migrate:fresh --seed` if altering `clients`).
4. Update **`trash/diagramZero.dbml`** if schema changes.
5. **`config/services.php`** + **`.env.example`** entries.
6. **Documentation:** new `docs/CONFIGURACION_GOOGLE_OAUTH.md` covering Console setup, env vars, redirect URIs, testing checklist.

---

## Frontend scope

1. GIS script + official button on login/register (redirect mode).
2. Handle OAuth return query params (success / error toasts).
3. **Soft profile-completion modal** after first Google auth when data missing.
4. Privacy + marketing checkboxes before starting OAuth (both pages).

---

## Testing (required in this issue)

Feature tests (mock HTTP / Socialite fake):

- New Google user → `Client` + contact + consents created, session authenticated.
- Existing local user + verified Google email → linked, same `client.id`.
- Google `email_verified=false` with existing email → no link, appropriate error.
- Google login sets `email_verified_at` when provider verifies email.
- Password login still works for linked accounts.
- Register/login pages/API unchanged for email/password path.

Run `php artisan test` before handoff.

---

## Acceptance criteria

- [ ] Email/password **login and register unchanged** and fully functional.
- [ ] Google button on **`/login`** and **`/register`**; official Google branding.
- [ ] OAuth callback creates session; SPA receives authenticated user via existing `GET /api/v1/user`.
- [ ] Same email → same `Client` when Google verifies email (both link directions).
- [ ] Privacy consent recorded before OAuth; marketing opt-in optional.
- [ ] Google-only users can set password in profile.
- [ ] i18n ca / es / en.
- [ ] Tests pass; docs + `.env.example` updated.
- [ ] Admin auth untouched.

---

## Non-goals

- Replacing or removing local credentials.
- Google sign-in for admin users.
- Company registration via Google.
- Mandatory DNI/CIF for Google users.
- Google account unlink UI (unless added later with password-set guard).

---

## Context links (codebase)

- Client auth: `app/Http/Controllers/Api/AuthController.php`
- Client model: `app/Models/Client.php`
- Auth config: `config/auth.php` (guard `web` → provider `clients`)
- Login UI: `resources/js/Pages/LoginPage.jsx`
- Register UI: `resources/js/Pages/RegisterPage.jsx`
- Consents pattern: `AuthController::register()` + `client_consents`
- Verified middleware: `app/Http/Middleware/EnsureClientEmailVerified.php`
- Profile password update: `app/Http/Controllers/Api/UserController.php`
