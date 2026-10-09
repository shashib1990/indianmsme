<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TheekHai API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for TheekHai Register and Message APIs used for ADC
    | Registration mobile verification and OTP delivery.
    |
    */

    'register_url' => env('THEEKHAI_REGISTER_URL', 'https://theekhai.world/api.theekhai.world/register.php'),
    'message_url' => env('THEEKHAI_MESSAGE_URL', 'https://api.theekhai.world/send-message.php'),
    'sender_number' => env('THEEKHAI_SENDER_NUMBER', '8800001259'),
    'playstore_url' => env('THEEKHAI_PLAYSTORE_URL', 'https://play.google.com/store/apps/details?id=com.chat.theekhai'),
];
