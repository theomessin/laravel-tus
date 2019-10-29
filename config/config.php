<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tus Endpoint
    |--------------------------------------------------------------------------
    |
    | This value is the route that the tus server should be listening to.
    |
    */
    'endpoint' => 'tus',

    /*
    |--------------------------------------------------------------------------
    | Tus Controller
    |--------------------------------------------------------------------------
    |
    | This value is the controller that Tus requests should be routed to.
    |
    */
    'controller' => \Theomessin\Tus\Http\Controllers\TusController::class,

    /*
    |--------------------------------------------------------------------------
    | Tus Storage
    |--------------------------------------------------------------------------
    |
    | These values are where the tus server should be saving uploaded files.
    |
    */
    'storage' => [
        'disk' => 'local',
        'prefix' => 'tus',
    ],
];
