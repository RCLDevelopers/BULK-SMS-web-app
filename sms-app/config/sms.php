<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Service
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS service that will be used when
    | sending SMS messages. Supported services: 'twilio', 'textsms'
    |
    */
    'default' => env('SMS_DEFAULT_SERVICE', 'twilio'),

    /*
    |--------------------------------------------------------------------------
    | SMS Service Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each SMS service that is used by
    | your application. You should create a separate configuration for each
    | service that your application uses.
    |
    */
    'services' => [
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
        'textsms' => [
            'api_key' => env('TEXTSMS_API_KEY'),
            'sender_id' => env('TEXTSMS_SENDER_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Character Limits
    |--------------------------------------------------------------------------
    |
    | These values define the maximum number of characters allowed in a single
    | SMS message. This is used for validation and character counting.
    |
    */
    'character_limits' => [
        'gsm' => 160,      // Standard GSM character set
        'unicode' => 70,   // Unicode characters (emojis, non-Latin scripts, etc.)
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | These options configure how SMS messages are queued for sending.
    |
    */
    'queue' => [
        'enabled' => env('SMS_QUEUE_ENABLED', true),
        'queue' => env('SMS_QUEUE_NAME', 'default'),
    ],
];
