<?php

return [
    'merchant_code' => env('DUITKU_MERCHANT_CODE', ''),
    'api_key' => env('DUITKU_API_KEY', ''),
    'sandbox' => env('DUITKU_SANDBOX', true),
    'payment_method' => env('DUITKU_PAYMENT_METHOD', 'GQ'),
];
