<?php

use Illuminate\Support\Str;

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime (HARDENED)
    |--------------------------------------------------------------------------
    | 30 menit idle → session expired
    */
    'lifetime' => (int) env('SESSION_LIFETIME', 30),

    /*
    |--------------------------------------------------------------------------
    | Expire on Close (HARDENED)
    |--------------------------------------------------------------------------
    | Session hilang saat browser ditutup → cegah akses dari laptop terbuka
    */
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', true),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption (HARDENED)
    |--------------------------------------------------------------------------
    | Encrypt session data di DB → jika DB bocor, session tidak bisa dibaca
    */
    'encrypt' => env('SESSION_ENCRYPT', true),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies (HARDENED)
    |--------------------------------------------------------------------------
    | Session hanya dikirim via HTTPS. Set true di production.
    | Di lokal (http://localhost) → false
    */
    'secure' => env('SESSION_SECURE_COOKIE', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only (SUDAH BENAR)
    |--------------------------------------------------------------------------
    | JS tidak bisa akses cookie → cegah XSS session hijacking
    */
    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies (SUDAH BENAR)
    |--------------------------------------------------------------------------
    | Mitigasi CSRF
    */
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),
];