<?php

return [

    'sms_from' => env('VONAGE_SMS_FROM', env('NEXMO_FROM')),

    'api_key' => env('VONAGE_KEY', env('NEXMO_KEY')),

    'api_secret' => env('VONAGE_SECRET', env('NEXMO_SECRET')),

    'application_id' => env('VONAGE_APPLICATION_ID', env('NEXMO_APPLICATION_ID')),

    'signature_secret' => env('VONAGE_SIGNATURE_SECRET', env('NEXMO_SIGNATURE_SECRET')),

    'private_key' => env('VONAGE_PRIVATE_KEY', env('NEXMO_PRIVATE_KEY')),

    'app' => [
        'name' => env('VONAGE_APP_NAME', env('NEXMO_APP_NAME', 'Laravel')),
        'version' => env('VONAGE_APP_VERSION', env('NEXMO_APP_VERSION', '1.1.2')),
    ],

];
