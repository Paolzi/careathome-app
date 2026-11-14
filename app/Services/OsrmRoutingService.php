<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OsrmRoutingService
{
    /**
     * Hitung jarak rute jalan raya sebenarnya (driving distance) dan geometri rute
     * menggunakan OSRM Public API gratis (OpenStreetMap).
     * Memiliki fallback ke perhitungan Haversine x faktor koreksi jalan raya (1.35) jika offline.
     */
    public static function getDrivingRoute(float $lat1, float $lng1, float $lat2, float $lng2, bool $includeGeometry = false): array
    {
        if (abs($lat1 - $lat2) < 0.00001 && abs($lng1 - $lng2) < 0.00001) {
            return [
                'distance_km' => 0.1,
                'duration_minutes' => 2,
                'source' => 'osrm',
                'geometry' => [
                    [$lat1, $lng1],
                    [$lat2, $lng2],
                ],
            ];
        }

        $cacheKey = sprintf(
            'osrm_route_%s_%s_%s_%s_%s',
            round($lat1, 4),
            round($lng1, 4),
            round($lat2, 4),
            round($lng2, 4),
            $includeGeometry ? 'full' : 'simple'
        );

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($lat1, $lng1, $lat2, $lng2, $includeGeometry) {
            $overview = $includeGeometry ? 'full' : 'false';
            $url = "https://router.project-osrm.org/route/v1/driving/{$lng1},{$lat1};{$lng2},{$lat2}?overview={$overview}&geometries=geojson";

            try {
                $response = Http::timeout(3)
                    ->withHeaders(['User-Agent' => 'CareAtHome-Platform/2.0'])
                    ->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    if (! empty($json['routes'][0])) {
                        $route = $json['routes'][0];
                        $distanceKm = max(0.1, round(((float) $route['distance']) / 1000.0, 2));
                        $durationMin = max(1, (int) ceil(((float) $route['duration']) / 60.0));

                        $leafletCoords = [];
                        if ($includeGeometry && ! empty($route['geometry']['coordinates'])) {
                            foreach ($route['geometry']['coordinates'] as $coord) {
                                // GeoJSON adalah [lng, lat] -> konversi ke [lat, lng] untuk Leaflet
                                $leafletCoords[] = [(float) $coord[1], (float) $coord[0]];
                            }
                        }

                        return [
                            'distance_km' => $distanceKm,
                            'duration_minutes' => $durationMin,
                            'source' => 'osrm',
                            'geometry' => $leafletCoords,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Fallback ke estimasi rute jalan raya bila jaringan OSRM tidak terjangkau
            }

            $straightKm = self::haversineKm($lat1, $lng1, $lat2, $lng2);
            $roadKm = max(0.2, round($straightKm * 1.35, 2));
            $durationMin = max(3, (int) ceil(($roadKm / 30.0) * 60.0));

            return [
                'distance_km' => $roadKm,
                'duration_minutes' => $durationMin,
                'source' => 'road_estimate',
                'geometry' => [
                    [$lat1, $lng1],
                    [$lat2, $lng2],
                ],
            ];
        });
    }

    /**
     * Ambil jarak jalan raya dalam satuan KM (akurat via OSRM).
     */
    public static function getDrivingDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $route = self::getDrivingRoute($lat1, $lng1, $lat2, $lng2, false);
        return (float) ($route['distance_km'] ?? 0.5);
    }

    /**
     * Perhitungan jarak garis lurus (Haversine) dasar dalam KM.
     */
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}

