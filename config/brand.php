<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active brand
    |--------------------------------------------------------------------------
    |
    | Which company this deployment presents. Every brand lives in its own file
    | under config/brands/<key>.php (name, logo, contact, AI context and the
    | knowledge items that replace the FTS company content). FTS is the
    | default, so a deployment without APP_BRAND keeps behaving as before.
    |
    */

    'active' => env('APP_BRAND', 'fts'),

];
