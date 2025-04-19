<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pazarama SP API Kimlik Bilgileri
    |--------------------------------------------------------------------------
    |
    | Pazarama API'ye erişmek için gerekli olan client_id ve client_secret 
    | bilgilerini buraya girebilirsiniz. Bu bilgileri Pazarama satıcı 
    | hesabınızdan edinebilirsiniz.
    |
    */
    'client_id' => env('PAZARAMA_CLIENT_ID', ''),
    'client_secret' => env('PAZARAMA_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Pazarama API Seçenekleri
    |--------------------------------------------------------------------------
    |
    | API bağlantısı için gerekli yapılandırma seçenekleri.
    |
    */
    'timeout' => env('PAZARAMA_API_TIMEOUT', 30),
    'debug' => env('PAZARAMA_API_DEBUG', false),
]; 