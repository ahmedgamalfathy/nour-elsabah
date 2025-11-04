<?php
return[
        'stripe' => [
            'base_url' => env('STRIPE_BASE_URL', 'https://api.stripe.com'),
            'secret' => env('STRIPE_SECRET_KEY'),
        ],

        'paypal' => [
            'base_url' => env('PAYPAL_BASE_URL','https://api-m.sandbox.paypal.com'),
            'client_id' => env('PAYBAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        ],
];