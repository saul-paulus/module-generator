<?php

use App\Http\Middleware\ForceJsonResponse;

return [

    /*
    |--------------------------------------------------------------------------
    | API Middleware Aliases
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'force.json' => ForceJsonResponse::class,
    ],

];
