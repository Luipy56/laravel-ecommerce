<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Tag Manager / GA4
    |--------------------------------------------------------------------------
    |
    | Prefer GTM + dataLayer. Measurement ID is for GTM tag config / docs —
    | the storefront does not load gtag.js in parallel with GTM.
    |
    */

    'enabled' => filter_var(env('ANALYTICS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'gtm_container_id' => env('GTM_CONTAINER_ID', ''),

    'ga4_measurement_id' => env('GA4_MEASUREMENT_ID', ''),

];
