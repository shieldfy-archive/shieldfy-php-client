<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shieldfy Configurations
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials and configurations for Shieldfy.
    | You can get your credentials from https://app.shieldfy.io.
    | Please refere to the documentations on https://app.shieldfy.io for further info.
    |
    */

    /*
    
    */
   'endpoint' => 'https://api.shieldfy.io/v1',
    /*
     |--------------------------------------------------------------------------
     | Main App Credentials
     |--------------------------------------------------------------------------
     */
    'keys' => [
        'app_key'    => env('SHIELDFY_APP_KEY'),
        'app_secret' => env('SHIELDFY_APP_SECRET'),
    ],

    /*
     |--------------------------------------------------------------------------
     | Shieldfy debug whether or not expose debug and errors ( True , False )
     |--------------------------------------------------------------------------
     */
    'debug' => env('SHIELDFY_DEBUG', false),

    /*
     |--------------------------------------------------------------------------
     | Shieldfy default action for detecting threat ( block , listen )
     |--------------------------------------------------------------------------
     */
    'action' => env('SHIELDFY_ACTION', 'block'),

    /*
     |--------------------------------------------------------------------------
     | List of headers exposed to shieldfy to overwrite
     | format
     | key => value
     | example
     | ['X-Frame-Options'=>'DENY']
     | you can specify false to disable the header
     | example
     | ['X-Frame-Options'=>false]
     |--------------------------------------------------------------------------
     */
    'headers' => [],

    /*
     |--------------------------------------------------------------------------
     | list of monitors you want to disable
     |--------------------------------------------------------------------------
     */
    'disable' => [],

];
