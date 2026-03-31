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
