<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nursing Platform Configurations
    |--------------------------------------------------------------------------
    */

    // Persentase komisi platform dari total pembayaran
    'platform_fee_percent' => (float) env('NURSING_PLATFORM_FEE_PERCENT', 15.0),

    // Radius maksimum pencarian perawat aktif dalam kilometer (default 10 km)
    'max_radius_km' => (float) env('NURSING_MAX_RADIUS_KM', 10.0),

    // Batas waktu toleransi update lokasi terakhir dalam menit (heartbeat online)
    // Di lingkungan lokal, batas dilonggarkan menjadi 120 menit agar pengujian tidak expired cepat
    'location_timeout_minutes' => (int) env(
        'NURSING_LOCATION_TIMEOUT_MINUTES',
        (env('APP_ENV', 'local') === 'local') ? 120 : 15
    ),

    // Interval throttle pengiriman update lokasi dari frontend perawat (detik)
    'location_throttle_seconds' => (int) env('NURSING_LOCATION_THROTTLE_SECONDS', 30),

    // Koordinat pusat kota default untuk fallback testing lokal (Surabaya)
    'default_fallback_lat' => (float) env('NURSING_DEFAULT_LAT', -7.3144),
    'default_fallback_lng' => (float) env('NURSING_DEFAULT_LNG', 112.7304),
    'default_fallback_city' => env('NURSING_DEFAULT_CITY', 'Surabaya'),
];
