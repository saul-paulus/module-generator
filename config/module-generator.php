<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default API Specification Driver
    |--------------------------------------------------------------------------
    |
    | Supported Drivers out of the box:
    |   - 'rest'            : Standard REST Envelope (default, backward-compatible)
    |   - 'jsonapi'         : Official JSON:API 1.1 Specification (jsonapi.org)
    |   - 'problem-details' : RFC 7807 Problem Details Specification
    |   - Custom Class Name : Any class implementing ApiSpecificationInterface
    |
    */
    'api_specification' => env('API_SPECIFICATION', 'rest'),

    /*
    |--------------------------------------------------------------------------
    | JSON:API 1.1 Specification Options
    |--------------------------------------------------------------------------
    */
    'jsonapi' => [
        'version'  => '1.1',
        'base_url' => env('APP_URL', 'http://localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | RFC 7807 Problem Details Options
    |--------------------------------------------------------------------------
    */
    'problem_details' => [
        'type_base_url' => env('APP_URL', 'http://localhost') . '/errors',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Prefix
    |--------------------------------------------------------------------------
    */
    'table_prefix' => '',

    /*
    |--------------------------------------------------------------------------
    | Database Table Naming Convention
    |--------------------------------------------------------------------------
    */
    'table_naming' => 'snake_plural',

    /*
    |--------------------------------------------------------------------------
    | Controller Action Naming Style
    |--------------------------------------------------------------------------
    |
    | Options: 'restful' (index, show, store, update, destroy), 'handler'
    |
    */
    'controller_style' => 'restful',

    /*
    |--------------------------------------------------------------------------
    | Automatic Registration Options
    |--------------------------------------------------------------------------
    */
    'auto_register_provider' => true,
    'auto_register_route'    => true,
];
