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
- SPA: official Google Identity Services button on `/login` and `/register` when `GOOGLE_CLIENT_ID` is set.
- Same email + Google `email_verified` → auto-link to existing `Client`.
- Google-only accounts have nullable `password`; users may set a password later in profile.
- Admin auth (`admins` guard) is not affected.

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
