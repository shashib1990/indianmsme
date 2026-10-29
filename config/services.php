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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'abhhyam' => [
        'register_adc_url' => env('ABHHYAM_REGISTER_ADC_URL', 'https://api.abhhyam.com/api/v2/adp/register_adc'),
        'register_customer_url' => env('ABHHYAM_REGISTER_CUSTOMER_URL', 'https://api.abhhyam.com/api/v2/adp/register_customer'),
        'update_ama_incentive_url' => env('ABHHYAM_UPDATE_AMA_INCENTIVE_URL', 'https://abhhyamindia.com/api/save_income'),
    ],

    'file_service' => [
        'project_key' => env('FILE_SERVICE_PROJECT_KEY', 'CHI8M5LQ'),
        'secret_key'  => env('FILE_SERVICE_SECRET_KEY', 'q6qWfYt9fYcBj4kjZ4ZtbEl7lg0RjUoD'),
        'upload_url'  => env('FILE_SERVICE_UPLOAD_URL', 'https://upload.sainikcanteen.com/api/upload'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 60),
    ],

];
