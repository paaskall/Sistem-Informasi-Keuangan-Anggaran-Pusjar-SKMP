<?php

return [
    'api_key' => env('GEMINI_API_KEY', ''),
    'model' => env('GEMINI_MODEL', 'gemini-3.8-flash'),
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    'timeout' => env('GEMINI_TIMEOUT', 30),
    'cache_ttl' => env('GEMINI_CACHE_TTL', 3600), // 1 jam
];