<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted reverse proxies
    |--------------------------------------------------------------------------
    |
    | Used by Illuminate\Http\Middleware\TrustProxies. Comma-separated IPs, or
    | "*" / "**" only behind a controlled load balancer or Docker network.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
