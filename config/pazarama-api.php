<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pazarama API Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu konfigürasyon dosyası Pazarama API entegrasyonu için gerekli ayarları içerir.
    | Aşağıdaki değerleri kendi hesap bilgilerinizle değiştirin.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Erişim Bilgileri
    |--------------------------------------------------------------------------
    */
    'client_id' => env('PAZARAMA_CLIENT_ID', ''),
    'client_secret' => env('PAZARAMA_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | API URL Yapılandırması
    |--------------------------------------------------------------------------
    */
    'base_url' => env('PAZARAMA_API_URL', 'https://isortagimapi.pazarama.com'),
    'auth_url' => env('PAZARAMA_AUTH_URL', 'https://isortagimgiris.pazarama.com/connect/token'),

    /*
    |--------------------------------------------------------------------------
    | API İstek Ayarları
    |--------------------------------------------------------------------------
    */
    'timeout' => env('PAZARAMA_TIMEOUT', 30), // Saniye cinsinden
    'retry_attempts' => env('PAZARAMA_RETRY_ATTEMPTS', 3), // Başarısız isteklerin yeniden deneme sayısı
    'retry_delay' => env('PAZARAMA_RETRY_DELAY', 1000), // Milisaniye cinsinden

    /*
    |--------------------------------------------------------------------------
    | Debug & Loglama Ayarları
    |--------------------------------------------------------------------------
    */
    'debug' => env('PAZARAMA_DEBUG', false), // Debug modunu aktifleştir
    'log' => [
        'requests' => env('PAZARAMA_LOG_REQUESTS', true), // API isteklerini logla
        'responses' => env('PAZARAMA_LOG_RESPONSES', true), // API yanıtlarını logla
        'errors' => env('PAZARAMA_LOG_ERRORS', true), // API hatalarını logla
        'channel' => env('PAZARAMA_LOG_CHANNEL', 'daily'), // Loglama kanalı
    ],

    /*
    |--------------------------------------------------------------------------
    | Önbellek Ayarları
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('PAZARAMA_CACHE_ENABLED', true), // Önbellek kullanımını aktifleştir
        'ttl' => env('PAZARAMA_CACHE_TTL', 3600), // Saniye cinsinden önbellek süresi (1 saat)
    ],
]; 