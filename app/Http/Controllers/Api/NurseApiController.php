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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'service_id' => ['nullable', 'exists:service_catalogs,id'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Koordinat lokasi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lat = (float) $data['latitude'];
        $lng = (float) $data['longitude'];
        $radiusKm = (float) ($request->input('radius_km') ?? Setting::getFloat('max_radius_km', 10.0));

        $query = NurseProfile::with(['user', 'services'])
            ->active()
            ->nearby($lat, $lng, $radiusKm);

        if ($request->filled('service_id')) {
            $serviceId = (int) $request->input('service_id');
            $query->whereHas('services', function ($q) use ($serviceId) {
                $q->where('service_catalogs.id', $serviceId);
            });
        }

        $nurses = $query->get()->map(function (NurseProfile $profile) {
            return [
                'id' => $profile->id,
                'nurse_id' => $profile->user_id,
                'name' => $profile->user?->name ?? 'Ns. CareAtHome',
                'specialization' => $profile->specialization ?? 'Perawat Umum Homecare',
                'str_number' => $profile->str_number,
                'verification_status' => $profile->verification_status,
                'rating_avg' => (float) $profile->rating_avg,
                'total_reviews' => (int) $profile->total_reviews,
                'distance_km' => round((float) $profile->distance_km, 1),
                'formatted_distance' => round((float) $profile->distance_km, 1) . ' km',
                'latitude' => (float) $profile->latitude,
                'longitude' => (float) $profile->longitude,
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
            'message' => 'Daftar perawat aktif dalam radius ' . $radiusKm . ' km.',
            'meta' => [
                'total' => $nurses->count(),
                'patient_coordinates' => ['latitude' => $lat, 'longitude' => $lng],
                'radius_km' => $radiusKm,
            ],
            'data' => $nurses,
        ]);
    }
}
