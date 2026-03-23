<?php

return [

    /*
    | When true with APP_DEBUG, checkout/pay may complete without a real PSP (tests / local only).
    | Must be false in production.
    */
    'allow_simulated' => env('PAYMENTS_ALLOW_SIMULATED', false),

];
