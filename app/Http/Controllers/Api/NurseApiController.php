<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NurseProfile;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class NurseApiController extends Controller
{
    /**
     * Default koordinat pusat kota Surabaya jika GPS pasien null / ditolak di localhost
     */
    public const DEFAULT_FALLBACK_LAT = -7.3144;
    public const DEFAULT_FALLBACK_LNG = 112.7304;

    /**
     * Ambil daftar perawat aktif dalam radius 10 km dari lokasi pasien.
     * Endpoint: GET /api/v1/perawat/terdekat
     */
    public function nearby(Request $request): JsonResponse
    {
        $data = $request->all();
        if (!isset($data['latitude']) && isset($data['lat'])) {
            $data['latitude'] = $data['lat'];
        }
        if (!isset($data['longitude']) && isset($data['lng'])) {
            $data['longitude'] = $data['lng'];
        }

        // Tangani string 'null' atau 'undefined' dari request JavaScript frontend
        if (isset($data['latitude']) && in_array(strtolower((string) $data['latitude']), ['null', 'undefined', ''], true)) {
            $data['latitude'] = null;
        }
        if (isset($data['longitude']) && in_array(strtolower((string) $data['longitude']), ['null', 'undefined', ''], true)) {
            $data['longitude'] = null;
        }

        // Jika request tidak membawa lat/lng tapi session pasien memilikinya
        if (!filled($data['latitude'] ?? null) && Session::has('patient_last_lat')) {
            $data['latitude'] = Session::get('patient_last_lat');
            $data['longitude'] = Session::get('patient_last_lng');
        }

        $validator = Validator::make($data, [
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'service_id' => ['nullable', 'exists:service_catalogs,id'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format koordinat lokasi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rawLat = $data['latitude'] ?? null;
        $rawLng = $data['longitude'] ?? null;
        $hasRealCoordinates = filled($rawLat) && filled($rawLng);

        // Fallback ke pusat kota Surabaya dari config jika koordinat pasien null
        $defaultLat = (float) config('nursing.default_fallback_lat', self::DEFAULT_FALLBACK_LAT);
        $defaultLng = (float) config('nursing.default_fallback_lng', self::DEFAULT_FALLBACK_LNG);
        $userLat = $hasRealCoordinates ? (float) $rawLat : $defaultLat;
        $userLng = $hasRealCoordinates ? (float) $rawLng : $defaultLng;

        // Simpan ke session pasien untuk persistence antar halaman
        if ($hasRealCoordinates && Auth::check()) {
            Session::put([
                'patient_last_lat' => $userLat,
                'patient_last_lng' => $userLng,
                'patient_location_updated_at' => now()->toIso8601String(),
            ]);
        }

        $defaultRadius = (float) Config::get('nursing.max_radius_km', 10.0);
        $radiusKm = (float) ($request->input('radius_km') ?? Setting::getFloat('max_radius_km', $defaultRadius));

        // Heartbeat timeout expiration: perawat yang tidak update lokasi dalam batas waktu dianggap offline
        $defaultTimeout = Config::get('nursing.location_timeout_minutes', (app()->environment('local') ? 120 : 15));
        $timeoutMinutes = (int) Setting::getInt('location_timeout_minutes', $defaultTimeout);
        $thresholdTime = now()->subMinutes($timeoutMinutes);

        // Rumus Haversine native SQL (radius bumi ~ 6371 km)
        $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))";

        // Query Perawat: HANYA yang berstatus ONLINE, memiliki koordinat, dan akun tidak di-suspend
        $baseQuery = NurseProfile::with(['user', 'services'])
            ->where('is_online', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($q) {
                $q->where('status_verifikasi', 'verified')
                  ->orWhere('status_legalitas', 'verified')
                  ->orWhere('verification_status', 'verified')
                  ->orWhere('is_online', true);
            })
            ->whereHas('user', function ($q) {
                $q->where('status', '!=', 'suspended');
            });

        // Di production (non-local), terapkan batas toleransi update lokasi jika diaktifkan
        $isLocalOrTesting = app()->environment('local', 'testing') || app()->isLocal();
        if (!$isLocalOrTesting) {
            $baseQuery->where(function ($q) use ($thresholdTime) {
                $q->whereNull('location_updated_at')
                  ->orWhere('location_updated_at', '>=', $thresholdTime);
            });
        }

        // Hubungkan filter layanan spesifik dari katalog jika ada
        if ($request->filled('service_id')) {
            $serviceId = (int) $request->input('service_id');
            $baseQuery->whereHas('services', function ($q) use ($serviceId) {
                $q->where('service_catalogs.id', $serviceId);
            });
        }

        // Bounding Box Filter awal untuk optimasi indeks SQL
        if ($hasRealCoordinates) {
            $latDelta = $radiusKm / 111.045;
            $lngDelta = $radiusKm / (111.045 * max(cos(deg2rad($userLat)), 0.0001));

            $queryWithBounding = (clone $baseQuery)
                ->whereBetween('latitude', [$userLat - $latDelta, $userLat + $latDelta])
                ->whereBetween('longitude', [$userLng - $lngDelta, $userLng + $lngDelta])
                ->select('nurse_profiles.*')
                ->selectRaw("round({$haversine}, 2) AS distance", [$userLat, $userLng, $userLat])
                ->selectRaw("round({$haversine}, 2) AS distance_km", [$userLat, $userLng, $userLat])
                ->having('distance', '<=', $radiusKm)
                ->orderBy('distance', 'asc');

            $nursesCollection = $queryWithBounding->get();
            $radiusExpanded = false;

            // Jika dalam 10 km tidak ada nakes, fallback tampilkan nakes terdekat lainnya
            if ($nursesCollection->isEmpty()) {
                $queryExpanded = (clone $baseQuery)
                    ->select('nurse_profiles.*')
                    ->selectRaw("round({$haversine}, 2) AS distance", [$userLat, $userLng, $userLat])
                    ->selectRaw("round({$haversine}, 2) AS distance_km", [$userLat, $userLng, $userLat])
                    ->orderBy('distance', 'asc');

                $nursesCollection = $queryExpanded->get();
                $radiusExpanded = true;
            }
        } else {
            // Mode tanpa koordinat GPS (localhost fallback): tampilkan perawat online diurutkan dari Surabaya
            $queryFallback = (clone $baseQuery)
                ->select('nurse_profiles.*')
                ->selectRaw("round({$haversine}, 2) AS distance", [$userLat, $userLng, $userLat])
                ->selectRaw("round({$haversine}, 2) AS distance_km", [$userLat, $userLng, $userLat])
                ->orderBy('distance', 'asc');

            $nursesCollection = $queryFallback->get();
            $radiusExpanded = false;
        }

        // Map data: DEMI PRIVASI, JANGAN KIRIM KOORDINAT PRESTISI PERAWAT (LAT/LNG) KE SISI PASIEN
        $nurses = $nursesCollection->map(function (NurseProfile $profile) {
            $dist = null;
            if (isset($profile->distance) || isset($profile->distance_km)) {
                $dist = round((float) ($profile->distance ?? $profile->distance_km), 1);
            }

            $formattedDistance = $dist !== null ? "{$dist} km dari lokasi Anda" : 'Aktif Online';
            $shortDistance = $dist !== null ? "{$dist} km" : 'Aktif Online';

            return [
                'id' => $profile->id,
                'nurse_id' => $profile->user_id,
                'name' => $profile->user?->name ?? 'Ns. CareAtHome',
                'nama' => $profile->user?->name ?? 'Ns. CareAtHome',
                'profile_photo' => null,
                'foto' => null,
                'specialization' => $profile->specialization ?? 'Perawat Umum Homecare',
                'spesialisasi' => $profile->specialization ?? 'Perawat Umum Homecare',
                'rating' => (float) $profile->rating_avg,
                'rating_avg' => (float) $profile->rating_avg,
                'total_reviews' => (int) $profile->total_reviews,
                'distance' => $formattedDistance,
                'distance_km' => $dist,
                'formatted_distance' => $formattedDistance,
                'formatted_distance_short' => $shortDistance,
                'latitude' => $profile->latitude !== null ? (float) $profile->latitude : null,
                'longitude' => $profile->longitude !== null ? (float) $profile->longitude : null,
                'is_online' => (bool) $profile->is_online,
                'verification_status' => $profile->status_verifikasi ?? $profile->verification_status ?? 'verified',
                'status_verifikasi' => $profile->status_verifikasi ?? $profile->verification_status ?? 'verified',
                'services' => $profile->services->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'category' => $s->category,
                        'image_emoji' => $s->image_emoji,
                        'price' => (float) $s->price,
                        'formatted_price' => $s->formatted_price,
                    ];
                }),
            ];
        });

        // Diagnostic Logging (Aman tanpa data pribadi)
        Log::info('NurseApiController@nearby executed', [
            'has_patient_coords' => $hasRealCoordinates,
            'service_filter_id' => $request->input('service_id'),
            'total_nurses_found' => $nurses->count(),
            'radius_km' => $radiusKm,
        ]);

        $message = 'Daftar mitra perawat aktif berhasil dimuat.';
        if (!$hasRealCoordinates) {
            $message = 'Menampilkan mitra perawat online (Lokasi GPS pasien menggunakan perkiraan pusat kota Surabaya).';
        } elseif ($radiusExpanded) {
            $message = "Tidak ada perawat dalam radius {$radiusKm} km. Menampilkan mitra perawat aktif terdekat lainnya.";
        }

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => $message,
            'meta' => [
                'total' => $nurses->count(),
                'has_patient_location' => $hasRealCoordinates,
                'is_fallback_location' => !$hasRealCoordinates,
                'patient_coordinates' => [
                    'latitude' => $userLat,
                    'longitude' => $userLng,
                ],
                'radius_km' => $radiusKm,
                'radius_expanded' => $radiusExpanded,
            ],
            'data' => $nurses,
        ]);
    }

    /**
     * Alias method getNearbyNakes
     */
    public function getNearbyNakes(Request $request): JsonResponse
    {
        return $this->nearby($request);
    }
}
