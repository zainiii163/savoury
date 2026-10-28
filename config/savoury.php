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

];
