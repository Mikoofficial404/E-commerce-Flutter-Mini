<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
     * Semua route yang diawali dengan path ini akan otomatis masuk ke dokumentasi.
     */
    'api_path' => 'api',

    /*
     * Domain API kamu. Biarkan null biar pakai default (APP_URL).
     */
    'api_domain' => null,

    /*
     * Path untuk export OpenAPI spec (biasanya tidak perlu diubah).
     */
    'export_path' => 'api.json',

    /*
     * Informasi dasar dokumentasi API kamu.
     */
    'info' => [
        'version' => env('API_VERSION', '1.0.0'),
        'description' => '📘 Dokumentasi API E-Commerce Project (Products, Orders, Midtrans, Auth)',
    ],

    /*
     * Pengaturan tampilan UI dokumentasi.
     */
    'ui' => [
        'title' => env('APP_NAME', 'Laravel API Docs'),
        'theme' => 'dark',
        'hide_try_it' => false,
        'hide_schemas' => false,
        'logo' => '', // kamu bisa isi URL logo kalau mau
        'try_it_credentials_policy' => 'include',
        'layout' => 'responsive',
    ],

    /*
     * Tambahkan daftar server API yang bisa kamu pilih di UI (multi server support)
     */
    'servers' => [
        'Ngrok Public URL' => 'https://miss-idoneous-gerard.ngrok-free.dev/api',
        'Localhost Development' => 'http://127.0.0.1:8000/api',
        'Midtrans Sanbox with snaptoken' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/{snap_token}'
    ],

    /*
     * Strategy untuk enum description (biarin default aja)
     */
    'enum_cases_description_strategy' => 'description',

    /*
     * Middleware yang mengatur siapa yang bisa akses dokumentasi
     */
    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    /*
     * Security Schemes (biar Scramble tahu kamu pakai JWT Bearer Token)
     */
    'security_schemes' => [
        'bearerAuth' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ],
    ],

    /*
     * Default Security (semua route butuh Bearer Token)
     */
    'default_security' => [
        ['bearerAuth' => []],
    ],

    'extensions' => [],
];
