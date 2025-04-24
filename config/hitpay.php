<?php

return [
    'apikey' => [
        'sandbox' => env('HITPAY_SANDBOX_APIKEY'),
        'live' => env('HITPAY_LIVE_APIKEY'),
    ],
    'url' => [
        'sandbox' => env('HITPAY_SANDBOX_URL'),
        'live' => env('HITPAY_LIVE_URL'),
    ],
];
