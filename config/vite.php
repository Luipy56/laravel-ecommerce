<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vite dev server URL
    |--------------------------------------------------------------------------
    | Used when injecting the React Refresh preamble in local env (Laravel
    | serves HTML, so the preamble must be added manually). Set in .env
    | if your dev server runs on a different port (e.g. VITE_DEV_SERVER_URL=http://localhost:5174).
    */
    'dev_server_url' => env('VITE_DEV_SERVER_URL', 'http://localhost:5173'),
];
