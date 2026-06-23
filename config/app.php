<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Serralleria Solidària'),

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | Single source of truth is package.json at the repository root (same as
    | the Vite front-end build). Optional APP_VERSION env overrides for deploys.
    |
    */

    'version' => env(
        'APP_VERSION',
        (static function (): string {
            $path = base_path('package.json');
            if (! is_readable($path)) {
                return '0.0.0';
            }
            $decoded = json_decode((string) file_get_contents($path), true);
            if (! is_array($decoded) || empty($decoded['version']) || ! is_string($decoded['version'])) {
                return '0.0.0';
            }

            return $decoded['version'];
        })(),
    ),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Admin login: dev auto-login button (SPA /admin/login)
    |--------------------------------------------------------------------------
    |
    | When true, the admin login page shows a one-click "Auto login" button.
    | Set ADMINAUTOLOGIN=true in .env for local/staging only.
    |
    */

    'admin_auto_login' => filter_var(env('ADMINAUTOLOGIN', false), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Storefront demo phase modal (first visit)
    |--------------------------------------------------------------------------
    |
    | When true, the public storefront shows a one-time modal warning that the
    | site is in development. Set isDEMO=true in .env for staging/demo only.
    |
    */

    'is_demo' => filter_var(env('isDEMO', false), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'ca'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'es'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Supported application locales
    |--------------------------------------------------------------------------
    |
    | API and server-rendered text use these; the React app also offers them in
    | resources/js/locales/*.json (ca, es, en).
    |
    */

    'available_locales' => ['ca', 'es', 'en'],

    /*
    |--------------------------------------------------------------------------
    | SPA redirects (email verification link lands on API then redirects here)
    |--------------------------------------------------------------------------
    */

    'verify_email_redirect_path' => env('FRONTEND_VERIFY_REDIRECT', '/login'),

    'verify_email_failed_redirect_path' => env('FRONTEND_VERIFY_FAILED_REDIRECT', '/verify-email'),

    'frontend_reset_password_path' => env('FRONTEND_RESET_PASSWORD_PATH', '/reset-password'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
