<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NurseProfile;
use App\Models\NurseSchedule;
use App\Services\OsrmRoutingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class NurseApiController extends Controller
{
    public const DEFAULT_FALLBACK_LAT = -7.3144;
    public const DEFAULT_FALLBACK_LNG = 112.7304;

    /**
     * Ambil daftar mitra perawat terverifikasi beserta ketersediaan jadwal harian (WIB),
     * jarak rute jalan raya sebenarnya (OSRM), tanpa batasan radius 10 km,
     * serta Rekomendasi Cerdas apabila tidak ada perawat yang tersedia pada jam tersebut.
     */
    public function nearby(Request $request): JsonResponse
    {
        $data = $request->all();
        if (! isset($data['latitude']) && isset($data['lat'])) {
            $data['latitude'] = $data['lat'];
        }
        if (! isset($data['longitude']) && isset($data['lng'])) {
            $data['longitude'] = $data['lng'];
        }

        if (isset($data['latitude']) && in_array(strtolower((string) $data['latitude']), ['null', 'undefined', ''], true)) {
            $data['latitude'] = null;
        }
        if (isset($data['longitude']) && in_array(strtolower((string) $data['longitude']), ['null', 'undefined', ''], true)) {
            $data['longitude'] = null;
        }

        if (! filled($data['latitude'] ?? null) && Session::has('patient_last_lat')) {
            $data['latitude'] = Session::get('patient_last_lat');
            $data['longitude'] = Session::get('patient_last_lng');
        }

        $validator = Validator::make($data, [
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'service_id' => ['nullable', 'exists:service_catalogs,id'],
            'booking_type' => ['nullable', 'in:now,scheduled'],
            'scheduled_date' => ['nullable', 'string'],
            'scheduled_time' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter pencarian perawat tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rawLat = $data['latitude'] ?? null;
        $rawLng = $data['longitude'] ?? null;
        $hasRealCoordinates = filled($rawLat) && filled($rawLng);

        $defaultLat = (float) config('nursing.default_fallback_lat', self::DEFAULT_FALLBACK_LAT);
        $defaultLng = (float) config('nursing.default_fallback_lng', self::DEFAULT_FALLBACK_LNG);
        $userLat = $hasRealCoordinates ? (float) $rawLat : $defaultLat;
        $userLng = $hasRealCoordinates ? (float) $rawLng : $defaultLng;

        if ($hasRealCoordinates && Auth::check()) {
            Session::put([
                'patient_last_lat' => $userLat,
                'patient_last_lng' => $userLng,
                'patient_location_updated_at' => now('Asia/Jakarta')->toIso8601String(),
            ]);
        }

        $bookingType = $request->input('booking_type', 'now');
        $scheduledDate = $request->input('scheduled_date', Carbon::now('Asia/Jakarta')->toDateString());
        $scheduledTime = $request->input('scheduled_time', Carbon::now('Asia/Jakarta')->format('H:i'));
        $serviceId = $request->filled('service_id') ? (int) $request->input('service_id') : null;
        $onlyAvailable = filter_var($request->input('only_available', false), FILTER_VALIDATE_BOOLEAN);

        $payload = self::buildNursesAndRecommendations(
            $userLat,
            $userLng,
            $bookingType,
            $scheduledDate,
            $scheduledTime,
            $serviceId,
            $onlyAvailable
        );

        $nurses = $payload['nurses'];
        $availableCount = $payload['available_count'];

        $message = $availableCount > 0
            ? "Menampilkan {$availableCount} mitra perawat terverifikasi yang tersedia sesuai jadwal pilihan Anda (WIB)."
            : 'Belum ada perawat yang tersedia pada waktu tersebut. Silakan lihat Rekomendasi Cerdas di bawah.';

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => $message,
            'meta' => [
                'total' => $nurses->count(),
                'available_count' => $availableCount,
                'booking_type' => $bookingType,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'current_wib_time' => Carbon::now('Asia/Jakarta')->format('H:i') . ' WIB',
                'has_patient_location' => $hasRealCoordinates,
                'patient_coordinates' => [
                    'latitude' => $userLat,
                    'longitude' => $userLng,
                ],
            ],
            'data' => $nurses,
            'recommendations' => $payload['recommendations'],
        ]);
    }

    /**
     * Bangun daftar perawat terverifikasi beserta jarak rute jalan raya (OSRM),
     * jadwal harian 7 hari (WIB), dan Rekomendasi Cerdas.
     */
    public static function buildNursesAndRecommendations(
        float $userLat,
        float $userLng,
        string $bookingType = 'now',
        ?string $scheduledDate = null,
        ?string $scheduledTime = null,
        ?int $serviceId = null,
        bool $onlyAvailable = false
    ): array {
        $nowWib = Carbon::now('Asia/Jakarta');
        $scheduledDate = $scheduledDate ?: $nowWib->toDateString();
        $scheduledTime = $scheduledTime ?: $nowWib->format('H:i');

        $baseQuery = NurseProfile::with(['user', 'services'])
            ->where(function ($q) {
                $q->where('verification_status', 'verified')
                  ->orWhere('status_verifikasi', 'verified')
                  ->orWhere('status_legalitas', 'verified');
            })
            ->whereHas('user', function ($q) {
                $q->where('status', '!=', 'suspended');
            });

        if ($serviceId) {
            $baseQuery->whereHas('services', function ($q) use ($serviceId) {
                $q->where('service_catalogs.id', $serviceId);
            });
        }

        $profiles = $baseQuery->get();

        $allMapped = $profiles->map(function (NurseProfile $profile) use ($userLat, $userLng, $bookingType, $scheduledDate, $scheduledTime) {
            $nLat = $profile->latitude !== null ? (float) $profile->latitude : self::DEFAULT_FALLBACK_LAT;
            $nLng = $profile->longitude !== null ? (float) $profile->longitude : self::DEFAULT_FALLBACK_LNG;

            // Hitung jarak jalan raya sebenarnya via OSRM
            $roadDistanceKm = OsrmRoutingService::getDrivingDistanceKm($nLat, $nLng, $userLat, $userLng);
            $formattedDistance = "{$roadDistanceKm} km (Rute Jalan)";
            $shortDistance = "{$roadDistanceKm} km";

            $schedules = NurseSchedule::ensureDefaultSchedulesForNurse((int) $profile->user_id);
            $availability = NurseSchedule::checkAvailability(
                (int) $profile->user_id,
                $bookingType,
                $scheduledDate,
                $scheduledTime
            );

            $formattedSchedules = $schedules->map(function (NurseSchedule $sch) {
                return [
                    'day_of_week' => $sch->day_of_week,
                    'day_name' => $sch->day_name,
                    'start_time' => substr($sch->start_time, 0, 5),
                    'end_time' => substr($sch->end_time, 0, 5),
                    'is_active' => (bool) $sch->is_active,
                    'label' => $sch->is_active
                        ? substr($sch->start_time, 0, 5) . ' - ' . substr($sch->end_time, 0, 5) . ' WIB'
                        : 'Libur',
                ];
            })->values();

            return [
                'id' => $profile->id,
                'nurse_id' => $profile->user_id,
                'name' => $profile->user?->name ?? 'Ns. CareAtHome',
                'nama' => $profile->user?->name ?? 'Ns. CareAtHome',
                'profile_photo' => null,
                'foto' => null,
                'specialization' => $profile->specialization ?? 'Perawat Homecare Terverifikasi',
                'spesialisasi' => $profile->specialization ?? 'Perawat Homecare Terverifikasi',
                'str_number' => $profile->str_number ?? '-',
                'sip_number' => $profile->sip_number ?? '-',
                'rating' => (float) ($profile->rating_avg ?: 5.0),
                'rating_avg' => (float) ($profile->rating_avg ?: 5.0),
                'total_reviews' => (int) $profile->total_reviews,
                'distance' => $formattedDistance,
                'distance_km' => $roadDistanceKm,
                'formatted_distance' => $formattedDistance,
                'formatted_distance_short' => $shortDistance,
                'latitude' => $nLat,
                'longitude' => $nLng,
                'is_available_for_slot' => (bool) $availability['available'],
                'availability_reason' => $availability['reason'],
                'schedule_window' => $availability['schedule_window'],
                'day_name' => $availability['day_name'],
                'schedules' => $formattedSchedules,
                'verification_status' => 'verified',
                'status_verifikasi' => 'verified',
                'services' => $profile->services->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'category' => $s->category,
                        'image_emoji' => $s->image_emoji,
                        'price' => (float) $s->price,
                        'formatted_price' => $s->formatted_price,
                    ];
                })->values(),
            ];
        })
        ->sortBy([
            ['is_available_for_slot', 'desc'],
            ['distance_km', 'asc'],
        ])
        ->values();

        $availableNurses = $allMapped->where('is_available_for_slot', true)->values();
        $nursesToReturn = $onlyAvailable ? $availableNurses : $allMapped;

        // Bangun Rekomendasi Cerdas (Smart Recommendations)
        $slotCandidates = [
            ['slot' => '08:00 - 10:00', 'time' => '08:30', 'label' => '08:00 - 10:00 WIB (Pagi)'],
            ['slot' => '10:00 - 12:00', 'time' => '10:30', 'label' => '10:00 - 12:00 WIB (Menjelang Siang)'],
            ['slot' => '13:00 - 15:00', 'time' => '13:30', 'label' => '13:00 - 15:00 WIB (Siang)'],
            ['slot' => '15:00 - 17:00', 'time' => '15:30', 'label' => '15:00 - 17:00 WIB (Sore)'],
            ['slot' => '19:00 - 21:00', 'time' => '19:30', 'label' => '19:00 - 21:00 WIB (Malam)'],
        ];

        $nearestTimesToday = [];
        foreach ($slotCandidates as $cand) {
            $readyNames = [];
            foreach ($profiles as $p) {
                $chk = NurseSchedule::checkAvailability((int) $p->user_id, 'scheduled', $scheduledDate, $cand['time']);
                if ($chk['available']) {
                    $readyNames[] = $p->user?->name ?? 'Perawat';
                }
            }
            if (count($readyNames) > 0) {
                $nearestTimesToday[] = [
                    'date' => $scheduledDate,
                    'slot' => $cand['slot'],
                    'time' => $cand['time'],
                    'label' => $cand['label'],
                    'available_nurses_count' => count($readyNames),
                    'nurse_names' => array_slice($readyNames, 0, 3),
                ];
            }
        }

        $nextDaysRecommendations = [];
        $baseDateObj = Carbon::parse($scheduledDate, 'Asia/Jakarta');
        for ($i = 1; $i <= 3; $i++) {
            $nextDate = $baseDateObj->copy()->addDays($i);
            $dayNum = (int) $nextDate->dayOfWeekIso;
            $dayName = NurseSchedule::DAYS[$dayNum] ?? $nextDate->format('l');
            $readyNursesForDay = [];

            foreach ($profiles as $p) {
                $schedules = NurseSchedule::ensureDefaultSchedulesForNurse((int) $p->user_id);
                $daySch = $schedules->firstWhere('day_of_week', $dayNum);
                if ($daySch && $daySch->is_active) {
                    $readyNursesForDay[] = [
                        'nurse_id' => $p->user_id,
                        'name' => $p->user?->name ?? 'Perawat',
                        'hours' => substr($daySch->start_time, 0, 5) . ' - ' . substr($daySch->end_time, 0, 5) . ' WIB',
                        'suggested_slot' => substr($daySch->start_time, 0, 5) . ' - ' . Carbon::createFromFormat('H:i', substr($daySch->start_time, 0, 5))->addHours(2)->format('H:i'),
                    ];
                }
            }

            if (count($readyNursesForDay) > 0) {
                $nextDaysRecommendations[] = [
                    'date' => $nextDate->toDateString(),
                    'formatted_date' => $dayName . ', ' . $nextDate->format('d M Y'),
                    'day_name' => $dayName,
                    'available_nurses_count' => count($readyNursesForDay),
                    'nurses' => $readyNursesForDay,
                ];
            }
        }

        return [
            'nurses' => $nursesToReturn,
            'all_nurses' => $allMapped,
            'available_count' => $availableNurses->count(),
            'recommendations' => [
                'nearest_times_today' => $nearestTimesToday,
                'next_days' => $nextDaysRecommendations,
                'popular_nurses' => $allMapped->take(6)->values(),
            ],
        ];
    }

    public function getNearbyNakes(Request $request): JsonResponse
    {
        return $this->nearby($request);
    }
}
