# Scout EngineManager missing at boot (BindingResolutionException)

## Source

- **Log:** `storage/logs/laravel.log`
- **UTC window:** `2026-03-31` approx. `09:36:24`–`09:37:01` (and repeated pattern)
- **Representative lines:**
  - `local.ERROR: Target class [Laravel\Scout\EngineManager] does not exist.`
  - `Illuminate\Contracts\Container\BindingResolutionException` with `ReflectionException: Class "Laravel\Scout\EngineManager" does not exist`
  - Stack includes `App\Providers\AppServiceProvider` (around line 81) calling `resolve()` during provider `boot`, and `Illuminate\Foundation\Console\Kernel` / `artisan` bootstrap (CLI path observed in log).

## High-level instructions for coder

- Confirm whether Laravel Scout is required at runtime for this project or optional; align `composer.json` / lock and deployment install steps so `vendor/laravel/scout` is present whenever `AppServiceProvider` (or other code) resolves `Laravel\Scout\EngineManager`.
- If Scout-related registration must run only when the package is installed, guard `AppServiceProvider` (and any similar bindings) so the application does not call `resolve('Laravel\Scout\EngineManager')` (or equivalent) when the class is absent — without breaking intended search behaviour when Scout is configured.
- Reproduce via the same entry point implied by the log (e.g. `php artisan …` or web bootstrap), then verify with `php artisan test` and any relevant search/Scout tests after the fix.

## Coder notes (implementation)

- **Scout is required** in this app (`composer.json` / `composer.lock`: `laravel/scout ^11.1`). Deployments should run full `composer install` so `vendor/laravel/scout` exists.
- **`AppServiceProvider::boot`** now wraps `resolve(EngineManager::class)->extend('elasticsearch', …)` in `class_exists(EngineManager::class)`. If the package is missing from disk, bootstrap no longer throws `BindingResolutionException` at that line. When Scout is present, behaviour is unchanged (custom Elasticsearch engine still registers).

---

## Testing instructions

### What to verify

- Application boots (`php artisan …`, HTTP) without `Target class [Laravel\Scout\EngineManager] does not exist` when Scout is installed normally.
- With Scout present, Elasticsearch driver registration still works for environments that use it (existing Scout/ES tests).

### How to test

- From repo root: `php artisan test`
- `php artisan routes:smoke`
- Optional manual repro of the original failure mode: temporarily rename `vendor/laravel/scout` out of the tree, run `php artisan about` — boot should not fail on `AppServiceProvider` line 81 (restore the directory afterward).

### Pass/fail criteria

- **Pass:** No new failures attributable to this change; `php artisan routes:smoke` reports no HTTP 500 on GET routes.
- **Note:** `Tests\Feature\ProductSearchTextTest::product saving sets normalized search text` may fail on this branch independent of this fix (expects `x-ab` substring; observed `search_text` contains `x-áb`). Treat as pre-existing unless that test is green on the integration branch baseline.
