<?php

return [
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
    'secure' => env('CLOUDINARY_SECURE', true),

    'folders' => [
        'image' => env('CLOUDINARY_IMAGE_FOLDER', 'images'),
        'video' => env('CLOUDINARY_VIDEO_FOLDER', 'videos'),
        'raw' => env('CLOUDINARY_RAW_FOLDER', 'files'),
    ],

    'validation' => [
        'image' => [
            'max_size' => 2048, // KB
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        ],
        'video' => [
            'max_size' => 20000, // KB
            'mime_types' => ['video/mp4', 'video/quicktime', 'video/x-msvideo'],
        ],
    ],

    'transformations' => [
        'thumbnail' => [
            'width' => 150,
            'height' => 150,
            'crop' => 'fill',
        ],
        'medium' => [
            'width' => 500,
            'height' => 500,
            'crop' => 'limit',
        ],
    ],
];
