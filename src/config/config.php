<?php

return [

    'api' => [
        'delivery' => [
            'space' => env('CONTENTFUL_SPACE_ID'),
            'token' => env('CONTENTFUL_TOKEN_LIVE'),
            'preview' => env('CONTENTFUL_TOKEN_PREVIEW'),
            'use_preview' => env('CONTENTFUL_USE_PREVIEW'),
            'defaultLocale' => null,
        ],
        'management' => [
            'token' => env('CONTENTFUL_TOKEN_MANAGEMENT')
        ]
    ],

    'media' => [
        'quality' => env('MEDIA_QUALITY', 90),
        'progressive' => env('MEDIA_PROGRESSIVE', 'progressive'),
        'replace_host' => env('IMAGE_SOURCE_REPLACE', 'images.contentful.com'),
        'dest_host' => env('IMAGE_DEST_REPLACE', 'asset.contentful.com'),
        'webp_enabled' => env('IMAGE_WEBP_ENABLED', false)
    ]
];
