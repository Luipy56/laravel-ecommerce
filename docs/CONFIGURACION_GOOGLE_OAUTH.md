# Google OAuth (storefront client sign-in)

Optional **Sign in with Google** for storefront clients (`Client` model). Email/password login, registration, and password reset are unchanged.

## Google Cloud Console

1. Create an OAuth 2.0 **Web application** client.
2. **Authorized JavaScript origins** (examples):
   - `http://localhost:8080`
   - `http://127.0.0.1:8080`
   - `https://serra.ldeluipy.es`
3. **Authorized redirect URIs** (must match Laravel callback exactly):
   - `http://localhost:8080/auth/google/callback`
   - `http://127.0.0.1:8080/auth/google/callback`
   - `https://serra.ldeluipy.es/auth/google/callback`

## Environment variables

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
# Optional override (default: APP_URL + /auth/google/callback)
# GOOGLE_REDIRECT_URI=
```

Legacy names `GOOGLE_OAUTH_CLIENT_ID` / `GOOGLE_OAUTH_CLIENT_SECRET` are also read.

**Production:** set `APP_URL` to the public site origin (e.g. `https://serra.ldeluipy.es`). Do not commit secrets.

## Application behaviour

- Web routes: `POST /auth/google/redirect` (implicit privacy consent on use), `GET /auth/google/callback`.
- SPA: branded **Continue with Google** button on `/login` and `/register` — **full-page** Socialite redirect (no GIS popup; avoids OAuth state races and COOP errors).
- Same email + Google `email_verified` → auto-link to existing `Client`.
- Google-only accounts have nullable `password`; users may set a password later in profile.
- Admin auth (`admins` guard) is not affected.

## Production database upgrade

Google OAuth columns (`clients.google_sub`, nullable `password`) live in the **initial** `clients` migration (`0001_01_01_000000_create_users_table.php`). Per project migration rules, column changes are **not** shipped as separate `_add_` migrations during development — edit that file and run:

```bash
php artisan migrate:fresh --seed
```

**Databases that already ran the old `clients` schema** (e.g. production before Google OIDC) will not pick up the change from `migrate --force`. Apply a **one-time manual upgrade** on MySQL:

```sql
ALTER TABLE clients ADD COLUMN google_sub VARCHAR(255) NULL UNIQUE AFTER password;
ALTER TABLE clients MODIFY password VARCHAR(255) NULL;
```

Then confirm:

```bash
php artisan tinker --execute="echo Schema::hasColumn('clients','google_sub') ? 'ok' : 'missing';"
```

## Manual testing checklist

1. With credentials in `.env`, open `/login` — Google button appears at the top (no checkbox required).
2. Complete Google sign-in — redirected to `/login?oauth=success`, session active (`GET /api/v1/user` returns data).
3. Sign out; sign in again with Google — same account.
4. Register with email/password, verify email, then sign in with Google (same email) — linked, same `client.id`.
5. Without `GOOGLE_CLIENT_ID`, Google UI is hidden; email/password login and register still work.
6. Cart merge after Google login matches password login (add items as guest, then sign in with Google).

## Automated tests

```bash
php artisan test --filter=GoogleOAuth
```
