<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Toggles
    |--------------------------------------------------------------------------
    |
    | Enable or disable entire GoPos modules. Disabled modules will not
    | register their Filament resources, routes, or navigation items.
    |
    */
    'modules' => [
        'sales' => true,
        'purchases' => true,
        'inventory' => true,
        'accounting' => true,
        'hr' => true,
        'pos' => true,
        'reports' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Overrides
    |--------------------------------------------------------------------------
    |
    | Override any GoPos model with your own. Create a model that extends
    | the GoPos base model, then register it here. GoPos will use your
    | model everywhere instead of the default.
    |
    | Example:
    |   'product' => \App\Models\Product::class,
    |
    */
    'models' => [
        // 'user' => \App\Models\User::class,
        // 'product' => \App\Models\Product::class,
        // 'sale' => \App\Models\Sale::class,
        // 'customer' => \App\Models\Customer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    */
    'name' => 'GoPos',

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    */
    'languages' => ['en', 'ar', 'ckb'],
];
