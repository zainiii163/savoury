<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency and money storage
    |--------------------------------------------------------------------------
    |
    | Every financial column in the database is stored as an unsigned integer
    | in PKR minor units (paisa). One PKR equals 100 minor units, so a price
    | of PKR 375.00 is stored as 37500. This keeps all server-side arithmetic
    | exact and free of floating-point errors.
    |
    */

    'currency' => 'PKR',

    'money_scale' => 100,

    /*
    |--------------------------------------------------------------------------
    | Timezones
    |--------------------------------------------------------------------------
    |
    | Database timestamps are persisted in UTC. This timezone is used only
    | when formatting dates for customers and staff.
    |
    */

    'database_timezone' => 'UTC',

    'display_timezone' => 'Asia/Karachi',

    /*
    |--------------------------------------------------------------------------
    | Order statuses
    |--------------------------------------------------------------------------
    |
    | Stored values are snake_case. "ready" is shown to customers as
    | "Ready for Pickup" when fulfilment is pickup.
    |
    */

    'order_statuses' => [
        'pending',
        'accepted',
        'preparing',
        'ready',
        'out_for_delivery',
        'delivered',
        'collected',
        'cancelled',
        'rejected',
    ],

    'fulfillment_types' => [
        'delivery',
        'pickup',
    ],

    'payment_methods' => [
        'cash_on_delivery',
        'cash_on_pickup',
    ],

    /*
    |--------------------------------------------------------------------------
    | Brand tokens (frontend Phase 3+)
    |--------------------------------------------------------------------------
    */

    'brand' => [
        'primary' => '#075B76',
        'white' => '#FFFFFF',
        'cream' => '#FFF2E7',
        'accent' => '#D68B45',
        'phone' => '0316-6100080',
        'tagline' => 'Special Aroma, Homemade Trust',
    ],

];
