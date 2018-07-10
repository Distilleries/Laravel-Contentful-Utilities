<?php

return [

    'space_id' => env('CONTENTFUL_SPACE_ID'),
    'use_environment' => env('CONTENTFUL_USE_ENVIRONMENT', false),
    'environment' => env('CONTENTFUL_ENVIRONMENT', 'master'),
    'default_locale' => env('CONTENTFUL_DEFAULT_LOCALE', 'fr'),
    'default_country' => env('CONTENTFUL_DEFAULT_COUNTRY', 'www'),

    // Must be changed programmatically (in Command via option OR via Middleware OR via WebhookController)
    'use_preview' => 0,

    'tokens' => [
        'delivery' => [
            'live' => env('CONTENTFUL_TOKEN_LIVE'),
            'preview' => env('CONTENTFUL_TOKEN_PREVIEW'),
        ],

        'management' => env('CONTENTFUL_TOKEN_MANAGEMENT'),
    ],

    'image' => [
        'use_webp' => env('CONTENTFUL_IMAGE_USE_WEBP', 0),
        'use_progressive' => env('CONTENTFUL_IMAGE_USE_PROGRESSIVE', 1),
        'default_quality' => env('CONTENTFUL_IMAGE_DEFAULT_QUALITY', 80),
        'search_hosts' => 'images.contentful.com,images.ctfassets.net',
        'replace_host' => 'images.ctfassets.net',
    ],

];
