<?php

return [
    'key_id' => env('RAZORPAY_KEY_ID', ''),
    'key_secret' => env('RAZORPAY_KEY_SECRET', ''),

    // Set in the Razorpay Dashboard (Settings -> Webhooks) and in .env.
    // Used to verify incoming payment.captured webhooks server-side so an order
    // is still created if the customer closes the tab after paying.
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
];
