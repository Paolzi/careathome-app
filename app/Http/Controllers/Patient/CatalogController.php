<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\NurseProfile;
use App\Models\ServiceCatalog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public const DEFAULT_FALLBACK_LAT = -7.3144;
    public const DEFAULT_FALLBACK_LNG = 112.7304;

    /**
     * Tampilkan katalog layanan medis untuk pasien.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $category = $request->query('category', 'Semua');
        $sortBy = $request->query('sort_by', 'nama_asc');
        $priceBracket = $request->query('price_bracket', 'all');

        $query = ServiceCatalog::query()->active();

        if (filled($search)) {
            $query->search($search);
        }

        if (filled($category) && $category !== 'Semua') {
            $query->category($category);
        }

        if ($priceBracket === 'under_150k') {
            $query->where('price', '<', 150000);
        } elseif ($priceBracket === '150k_200k') {
            $query->whereBetween('price', [150000, 200000]);
        } elseif ($priceBracket === 'above_200k') {
            $query->where('price', '>', 200000);
        }

        $query->sort($sortBy);
        $services = $query->get();

        // Kategori dinamis dari database untuk filter pills (mendukung category & name)
        $categories = ServiceCatalog::active()
            ->selectRaw('category, category as name, count(*) as count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $totalActiveServices = ServiceCatalog::active()->count();

        // Kueri Mitra Perawat Aktif & Terdekat (Radius 10 KM dengan Fallback Graceful)
        $rawLat = $request->input('latitude') ?? $request->input('lat');
        $rawLng = $request->input('longitude') ?? $request->input('lng');

        // Normalisasi null / undefined
        if (in_array(strtolower((string) $rawLat), ['null', 'undefined', ''], true)) {
            $rawLat = null;
        }
        if (in_array(strtolower((string) $rawLng), ['null', 'undefined', ''], true)) {
            $rawLng = null;
        }

        // Ambil dari session jika request kosong
        if ($rawLat === null && session()->has('patient_last_lat')) {
            $rawLat = session('patient_last_lat');
            $rawLng = session('patient_last_lng');
        }

        $hasPatientCoords = filled($rawLat) && filled($rawLng);
        $defaultLat = (float) config('nursing.default_fallback_lat', self::DEFAULT_FALLBACK_LAT);
        $defaultLng = (float) config('nursing.default_fallback_lng', self::DEFAULT_FALLBACK_LNG);
        $effectiveLat = $hasPatientCoords ? (float) $rawLat : $defaultLat;
        $effectiveLng = $hasPatientCoords ? (float) $rawLng : $defaultLng;

        $defaultRadius = (float) config('nursing.max_radius_km', 10.0);
        $radiusKm = (float) Setting::getFloat('max_radius_km', $defaultRadius);

        $defaultTimeout = config('nursing.location_timeout_minutes', app()->isLocal() ? 120 : 15);
        $timeoutMinutes = (int) Setting::getInt('location_timeout_minutes', $defaultTimeout);
        $thresholdTime = now()->subMinutes($timeoutMinutes);

        // Rumus Haversine SQL
        $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))";

        $nurseBaseQuery = NurseProfile::with(['user', 'services'])
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
            })
            ->select('nurse_profiles.*')
            ->selectRaw("round({$haversine}, 2) AS distance", [$effectiveLat, $effectiveLng, $effectiveLat])
            ->selectRaw("round({$haversine}, 2) AS distance_km", [$effectiveLat, $effectiveLng, $effectiveLat])
            ->orderBy('distance', 'asc');

        $isLocalOrTesting = app()->environment('local', 'testing') || app()->isLocal();
        if (!$isLocalOrTesting) {
            $nurseBaseQuery->where(function ($q) use ($thresholdTime) {
                $q->whereNull('location_updated_at')
                  ->orWhere('location_updated_at', '>=', $thresholdTime);
            });
        }

        if ($hasPatientCoords) {
            $latDelta = $radiusKm / 111.045;
            $lngDelta = $radiusKm / (111.045 * max(cos(deg2rad($effectiveLat)), 0.0001));

            $nursesCollection = (clone $nurseBaseQuery)
                ->whereBetween('latitude', [$effectiveLat - $latDelta, $effectiveLat + $latDelta])
                ->whereBetween('longitude', [$effectiveLng - $lngDelta, $effectiveLng + $lngDelta])
                ->having('distance', '<=', $radiusKm)
                ->get();

            // Fallback jika tidak ada dalam radius 10 km
            if ($nursesCollection->isEmpty()) {
                $nursesCollection = $nurseBaseQuery->get();
            }
        } else {
            // Jika koordinat null di localhost, ambil seluruh perawat online diurutkan dari terdekat
            $nursesCollection = $nurseBaseQuery->get();
        }

        $onlineNurses = $nursesCollection->map(function (NurseProfile $profile) {
            $dist = isset($profile->distance) ? round((float) $profile->distance, 1) : null;
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

        return view('pages.patient.catalog', [
            'services' => $services,
            'categories' => $categories,
            'totalActiveServices' => $totalActiveServices,
            'currentSearch' => $search,
            'currentCategory' => $category,
            'currentSort' => $sortBy,
            'currentPriceBracket' => $priceBracket,
            'onlineNurses' => $onlineNurses,
            'userLat' => $rawLat,
            'userLng' => $rawLng,
            'hasPatientCoords' => $hasPatientCoords,
        ]);
    }

    /**
     * Endpoint API JSON alias getNearbyNakes
     */
    public function getNearbyNakes(Request $request): \Illuminate\Http\JsonResponse
    {
        return app(\App\Http\Controllers\Api\NurseApiController::class)->nearby($request);
    }
}
