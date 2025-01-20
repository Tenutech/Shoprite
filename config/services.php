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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'meta' => [
        'token' => env('META_ACCESS_TOKEN'),
        'phone' => env('META_PHONE_ID'),
        'whatsapp_number' => env('META_WHATSAPP_NUMBER'),
        'account' => env('META_WHATSAPP_BUSINESS_ACCOUNT_ID'),
    ],

    'googlemaps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'jira' => [
        'host' => env('JIRA_HOST', 'https://tenutech.atlassian.net'),
        'user' => env('JIRA_USER', 'admin@tenutech.com'),
        'secret' => env('JIRA_SECRET'),
        'token' => env('JIRA_API_TOKEN', 'ATATT3xFfGF0Hv2D5CyGjDnjGL5j9medBUqpDdy922KtUnA3BBICVROxYboITpzmWeDhIeFvj-noomSdyaPSaigvQ5QZg8EN_mfJqQ-gAI5swknj2HDYa3pNROqqvuzWn3HkpIPjQ0Z8MAMOPv5U0QtygZbxhUG73VMwzmlHEAM4Wiv_lS2pCKo=569C722C'),
    ],

    'sap' => [
        'endpoint' => env('SAP_ENDPOINT'),
        'username' => env('SAP_USERNAME'),
        'password' => env('SAP_PASSWORD'),
        'contract_id' => env('SAP_CONTRACT_ID'),
    ],

    'python' => [
        'path' => env('PYTHON_PATH', 'python'),
    ],
];
