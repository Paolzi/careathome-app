<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nursing Platform Configurations
    |--------------------------------------------------------------------------
    */

    // Persentase komisi platform dari total pembayaran
    'platform_fee_percent' => 15.0,

    // Radius maksimum pencarian perawat aktif dalam kilometer
    'max_radius_km' => 10.0,

    // Batas waktu toleransi update lokasi terakhir dalam menit (heartbeat online)
    'location_timeout_minutes' => 15,

    // Interval throttle pengiriman update lokasi dari frontend (detik)
    'location_throttle_seconds' => 30,
];
