<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NurseProfile;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NurseApiController extends Controller
{
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

        $validator = Validator::make($data, [
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'service_id' => ['nullable', 'exists:service_catalogs,id'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format koordinat lokasi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userLat = filled($data['latitude'] ?? null) ? (float) $data['latitude'] : null;
        $userLng = filled($data['longitude'] ?? null) ? (float) $data['longitude'] : null;
        $radiusKm = (float) ($request->input('radius_km') ?? Setting::getFloat('max_radius_km', 10.0));
        $hasPatientCoords = ($userLat !== null && $userLng !== null);

        $query = NurseProfile::with(['user', 'services'])
            ->where('is_online', true)
            ->where('verification_status', 'verified')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('user', function ($q) {
                $q->where('status', '!=', 'suspended');
            });

        if ($hasPatientCoords) {
            // Rumus Haversine presisi untuk filter radius maksimal 10 km
            $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))";

            $query->select('nurse_profiles.*')
                  ->selectRaw("round({$haversine}, 2) AS distance", [$userLat, $userLng, $userLat])
                  ->selectRaw("round({$haversine}, 2) AS distance_km", [$userLat, $userLng, $userLat])
                  ->having('distance', '<=', $radiusKm)
                  ->orderBy('distance', 'asc');
        } else {
            // Fallback jika lokasi pasien belum aktif / belum diizinkan
            $query->select('nurse_profiles.*')
                  ->orderBy('location_updated_at', 'desc')
                  ->latest();
        }

        if ($request->filled('service_id')) {
            $serviceId = (int) $request->input('service_id');
            $query->whereHas('services', function ($q) use ($serviceId) {
                $q->where('service_catalogs.id', $serviceId);
            });
        }

        $nurses = $query->get()->map(function (NurseProfile $profile) use ($hasPatientCoords) {
            $dist = null;
            if ($hasPatientCoords && (isset($profile->distance) || isset($profile->distance_km))) {
                $dist = round((float) ($profile->distance ?? $profile->distance_km), 1);
            }

            return [
                'id' => $profile->id,
                'nurse_id' => $profile->user_id,
                'name' => $profile->user?->name ?? 'Ns. CareAtHome',
                'specialization' => $profile->specialization ?? 'Perawat Umum Homecare',
                'str_number' => $profile->str_number,
                'verification_status' => $profile->verification_status,
                'rating_avg' => (float) $profile->rating_avg,
                'total_reviews' => (int) $profile->total_reviews,
                'distance_km' => $dist,
                'formatted_distance' => $dist !== null ? "{$dist} km" : 'Aktif Online',
                'latitude' => (float) $profile->latitude,
                'longitude' => (float) $profile->longitude,
                'location_updated_at' => $profile->location_updated_at?->diffForHumans() ?? 'Baru saja',
                'services' => $profile->services->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'image_emoji' => $s->image_emoji,
                        'price' => (float) $s->price,
                        'formatted_price' => $s->formatted_price,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => $hasPatientCoords
                ? 'Daftar perawat aktif dalam radius ' . $radiusKm . ' km.'
                : 'Daftar perawat aktif online (Lokasi GPS pasien belum aktif).',
            'meta' => [
                'total' => $nurses->count(),
                'has_patient_location' => $hasPatientCoords,
                'patient_coordinates' => $hasPatientCoords ? ['latitude' => $userLat, 'longitude' => $userLng] : null,
                'radius_km' => $hasPatientCoords ? $radiusKm : null,
            ],
            'data' => $nurses,
        ]);
    }
}
