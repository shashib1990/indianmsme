<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        'vendor' => [
            'driver' => 'session',
            'provider' => 'vendors',
        ],

        'pickup_store' => [
            'driver' => 'session',
            'provider' => 'pickup_stores',
        ],

        'ddc' => [
            'driver' => 'session',
            'provider' => 'ddcs',
        ],

        'adc' => [
            'driver' => 'session',
            'provider' => 'adcs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        'vendors' => [
            'driver' => 'eloquent',
            'model' => App\Models\Vendor::class,
        ],

        'pickup_stores' => [
            'driver' => 'eloquent',
            'model' => App\Models\PickupStore::class,
        ],

        'ddcs' => [
            'driver' => 'eloquent',
            'model' => App\Models\DDC::class,
        ],

        'adcs' => [
            'driver' => 'eloquent',
            'model' => App\Models\ADC::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'vendors' => [
            'provider' => 'vendors',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'pickup_stores' => [
            'provider' => 'pickup_stores',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'ddcs' => [
            'provider' => 'ddcs',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'adcs' => [
            'provider' => 'adcs',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */
    'password_timeout' => 10800,

];
