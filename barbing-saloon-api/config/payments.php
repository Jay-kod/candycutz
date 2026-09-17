<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Supported options: "paystack", "manual_transfer", "stripe"
    |
    */
    'default' => env('PAYMENT_GATEWAY', 'paystack'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways configuration
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'paystack' => [
            'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
            'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
            'base_url' => 'https://api.paystack.co',
            'callback_url' => env('PAYSTACK_CALLBACK_URL', env('APP_URL') . '/api/v1/payments/webhook'),
        ],
        'manual_transfer' => [
            'default_bank_name' => env('MANUAL_TRANSFER_BANK', 'Guaranty Trust Bank (GTB)'),
            'default_account_name' => env('MANUAL_TRANSFER_ACCOUNT_NAME', 'Candy Cutz Saloon'),
            'default_account_number' => env('MANUAL_TRANSFER_ACCOUNT_NUMBER', '0123456789'),
        ],
    ],
];
