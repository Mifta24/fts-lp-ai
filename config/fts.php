<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contact details and pricing display
    |--------------------------------------------------------------------------
    |
    | Shown on the landing page. Change them in .env, not in the views.
    |
    */

    'whatsapp' => env('FTS_WHATSAPP', '6289529336179'),
    'email' => env('FTS_EMAIL', 'info@fts-tech.co.id'),
    'phone' => env('FTS_PHONE', '+62 895 2933 6179'),
    'usd_rate' => (int) env('FTS_USD_RATE', 16500),

    'languages' => ['id', 'en', 'ja'],

];
