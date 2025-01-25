<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Keys
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable key and secret key give you access to Stripe's
    | API. The "publishable" key is typically used when interacting with
    | Stripe.js while the "secret" key accesses private API endpoints.
    |
    */

    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Secret
    |--------------------------------------------------------------------------
    |
    | This secret is used to verify that webhooks are actually coming from
    | Stripe. You can find this in your webhook settings in the Stripe
    | dashboard.
    |
    */

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Platform Fee Percentage
    |--------------------------------------------------------------------------
    |
    | This value represents the percentage that the platform takes as a fee
    | from each transaction. This is typically between 5-20%.
    |
    */

    'platform_fee_percentage' => env('STRIPE_PLATFORM_FEE_PERCENTAGE', 10),

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | The default currency that will be used for all Stripe transactions.
    |
    */

    'currency' => env('STRIPE_CURRENCY', 'USD'),
];
