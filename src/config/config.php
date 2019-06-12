<?php

return [

    // Must be changed programmatically (in Command via option OR via Middleware OR via WebhookController)
    'use_preview' => 0,

    'space_id' => env('CONTENTFUL_SPACE_ID'),

    'environment' => env('CONTENTFUL_ENVIRONMENT', 'master'),
    'use_environment' => env('CONTENTFUL_USE_ENVIRONMENT', false),

    'default_locale' => env('CONTENTFUL_DEFAULT_LOCALE', 'fr'),
    'default_country' => env('CONTENTFUL_DEFAULT_COUNTRY', 'www'),
    'locales_not_flatten' => env('CONTENTFUL_LOCALES_NOT_FLATTEN', 'www_default'),
    'locales_not_flatten_preview' => env('CONTENTFUL_LOCALES_NOT_FLATTEN_PREVIEW', 'www_default'),

    'namespace' => [
        'model' => [env('MODEL_BASE_NAMESPACE', 'App\Models')],
        'mapper' => [env('MAPPER_BASE_NAMESPACE', 'App\Models\Mappers')],
        'transformer' => [env('TRANSFORMER_BASE_NAMESPACE', 'App\Models\Transformers')],
    ],

    'generator' => [
        'model' => app_path('Models'),
        'mapper' => app_path('Models/Mappers'),
    ],

    'tokens' => [
        'delivery' => [
            'live' => env('CONTENTFUL_TOKEN_LIVE'),
            'preview' => env('CONTENTFUL_TOKEN_PREVIEW'),
        ],
        'management' => env('CONTENTFUL_TOKEN_MANAGEMENT'),
    ],

    'image' => [
        'use_webp' => env('CONTENTFUL_IMAGE_USE_WEBP', 0),
        'replace_host' => 'images.ctfassets.net',
        'search_hosts' => 'images.contentful.com,images.ctfassets.net',
        'use_progressive' => env('CONTENTFUL_IMAGE_USE_PROGRESSIVE', 1),
        'default_quality' => env('CONTENTFUL_IMAGE_DEFAULT_QUALITY', 80),
    ],

    'payload_fields_not_fallback' => explode(',', env('CONTENTFUL_PAYLOAD_FIELD_NOT_FALLBACK', '')),

];
