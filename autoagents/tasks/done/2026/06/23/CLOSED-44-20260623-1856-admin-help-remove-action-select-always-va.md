---
## Closing summary (TOP)

- **What happened:** The admin Help form at `/admin/help` exposed an Action select that let operators send requests directly to staging instead of always requesting human validation.
- **What was done:** The Action select and related client state were removed from `AdminHelpPage.jsx`; the API now always stores submissions with the human-validation label, ignoring any client-supplied label override.
- **What was tested:** `AdminHelpRequestTest` (6 tests, PASS), full PHPUnit suite (211 passed, 2 skipped), `routes:smoke` (no HTTP 500).
- **Why closed:** All acceptance criteria and manual test-report checks passed.
- **Closed at (UTC):** 2026-06-23 19:11
---

# [admin/help] Remove Action select, always request validation

## GitHub Issue
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/44
- **Number:** #44
- **Labels:** to-staging
- **Created:** 2026-06-23T16:52:14Z

## Problem / goal

The admin Help intake form at `/admin/help` currently exposes an Action select ("Enviar a Stage" vs human validation). Product owners want that select removed entirely. Every submission from this form must go through the human-validation workflow — no direct-to-staging option in the UI.

Per issue triage: do **not** merely hardcode the validation label in the React form; adjust the full flow so submissions always request human validation (frontend + API payload/defaults as needed). Do **not** change the confirmation modal copy unless required by the removal (issue says modal text update is not necessary).

## High-level instructions for coder

- Primary UI: `resources/js/Pages/admin/AdminHelpPage.jsx` — remove the label/Action `<select>` and related state (`label`, `setLabel`, `submittedLabel` branching for to-staging vs validation if no longer needed).
- Ensure POST to `admin/help-requests` always results in the human-validation path (label `waiting for human validation`). Prefer server-side default/fallback in the help-request controller or `config/admin_help.php` if that keeps a single source of truth; align with existing fallback behavior documented in `docs/admin-help-queue-plan.md`.
- Update `tests/Feature/AdminHelpRequestTest.php` — remove or adjust cases that assert to-staging from the form; add/adjust coverage that submissions without a label field always land on human validation.
- Clean up unused i18n keys under `admin.help.*` in `ca.json`, `es.json`, `en.json` only if they become dead after the select removal (keep modal strings if still used).
- Do **not** change autoagents loop rules or GitHub label semantics for issues already labeled `to-staging` by other means.

## Testing instructions

1. Log in to admin (`/admin/login`) as a seeded admin user (e.g. `manager` / `admin`).
2. Open `/admin/help` — confirm there is **no** Action/Acció select; form shows optional title, comment, and Send only.
3. Submit a request with comment text only — success modal shows the human-validation message (Stage link + “you will be notified when finished”).
4. Optional API check (logged-in session): `POST /api/v1/admin/help-requests` with `{ "comment": "test" }` returns `{ "success": true }`; pending JSON under `storage/app/admin-help/pending/` has `"label": "waiting for human validation"`.
5. Optional bypass attempt: same POST with extra `"label": "to-staging"` — stored label must still be `waiting for human validation`.
6. Automated: `php artisan test --filter=AdminHelpRequestTest` (6 tests pass).

## Test report

1. **Date/time (UTC):** 2026-06-23T19:08:53Z – 2026-06-23T19:10:17Z
2. **Environment:** Docker stack (`laravel-ecommerce-app-1`, Postgres 16); branch `autoagents`; PHP 8.2-FPM in container; `APP_ENV=local` (default dev).
3. **What was tested:** Admin help form UI (code review), help-request API human-validation path, `AdminHelpRequestTest`, full suite, `routes:smoke`.
4. **Results:**
   - No Action/Acció select on `/admin/help` form — **PASS** (only title, comment, Send in `AdminHelpPage.jsx`; no label state/select).
   - Submissions always use human validation — **PASS** (`help request ignores to staging label and uses human validation`, `help request falls back to human validation for invalid label`, `help request stores pending json when logged in`).
   - `php artisan test --filter=AdminHelpRequestTest` — **PASS** (6 tests, 59 assertions in filtered run with related suites).
   - Full suite — **PASS** (211 passed, 2 skipped).
   - `php artisan routes:smoke` — **PASS** (no HTTP 500).
5. **Overall:** **PASS**
6. **URLs tested:** N/A (no nginx published on host; SPA criteria covered by feature tests + source inspection).
7. **Log excerpts:** No errors in test output; PHPUnit exit code 0.

## References

- Repo: https://github.com/Luipy56/laravel-ecommerce
- Agent loop: docs/agent-loop.md
- Help queue plan: docs/admin-help-queue-plan.md
