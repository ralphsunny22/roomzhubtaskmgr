<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    'clicksend' => [
        'username' => env('CLICKSEND_USERNAME'),
        'api_key' => env('CLICKSEND_API_KEY'),
    ],
    'stripe' => [
        'currency' => env('STRIPE_CURRENCY', 'AUD'),
        'public' => env('STRIPE_PUBLIC_KEY', 'pk_test_51PWVRZRo7F6YokKUn8fu3QcnwgvWxXH5QJzgpXyEBXKKvJqdYgNJUI5dFAjK0H3dbQZreiMJ6BPrSIcG1qSdlph2005zd453A0'),
        'secret' => env('STRIPE_SECRET_KEY', 'sk_test_51PWVRZRo7F6YokKUC2wVvQKxOQMwv5ZZRlJHzcLtMN7m3fE1Tn2wMXdmIqmmVtJELvjiBTTY1zX0WMW38YtISpih00E1J0WyIR'),
    ],
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],
    'mailchimp' => [
        'key' => env('MAILCHIMP_API_KEY'),
        'server' => env('MAILCHIMP_SERVER_PREFIX'),
        'list_id' => env('MAILCHIMP_LIST_ID'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
