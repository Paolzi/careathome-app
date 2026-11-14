<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Api\NurseApiController;
use App\Http\Controllers\Controller;
use App\Models\ServiceCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public const DEFAULT_FALLBACK_LAT = -7.3144;
    public const DEFAULT_FALLBACK_LNG = 112.7304;

    /**
     * Tampilkan katalog layanan medis dan daftar mitra perawat terverifikasi untuk pasien.
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

        $categories = ServiceCatalog::active()
            ->selectRaw('category, category as name, count(*) as count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $totalActiveServices = ServiceCatalog::active()->count();

        $rawLat = $request->input('latitude') ?? $request->input('lat');
        $rawLng = $request->input('longitude') ?? $request->input('lng');

        if (in_array(strtolower((string) $rawLat), ['null', 'undefined', ''], true)) {
            $rawLat = null;
        }
        if (in_array(strtolower((string) $rawLng), ['null', 'undefined', ''], true)) {
            $rawLng = null;
        }

        if ($rawLat === null && Session::has('patient_last_lat')) {
            $rawLat = Session::get('patient_last_lat');
            $rawLng = Session::get('patient_last_lng');
        }

        $hasPatientCoords = filled($rawLat) && filled($rawLng);
        $defaultLat = (float) config('nursing.default_fallback_lat', self::DEFAULT_FALLBACK_LAT);
        $defaultLng = (float) config('nursing.default_fallback_lng', self::DEFAULT_FALLBACK_LNG);
        $effectiveLat = $hasPatientCoords ? (float) $rawLat : $defaultLat;
        $effectiveLng = $hasPatientCoords ? (float) $rawLng : $defaultLng;

        $bookingType = $request->query('booking_type', 'now');
        $scheduledDate = $request->query('scheduled_date', Carbon::now('Asia/Jakarta')->toDateString());
        $scheduledTime = $request->query('scheduled_time', Carbon::now('Asia/Jakarta')->format('H:i'));

        $payload = NurseApiController::buildNursesAndRecommendations(
            $effectiveLat,
            $effectiveLng,
            $bookingType,
            $scheduledDate,
            $scheduledTime
        );

        return view('pages.patient.catalog', [
            'services' => $services,
            'categories' => $categories,
            'totalActiveServices' => $totalActiveServices,
            'currentSearch' => $search,
            'currentCategory' => $category,
            'currentSort' => $sortBy,
            'currentPriceBracket' => $priceBracket,
            'onlineNurses' => $payload['nurses'],
            'recommendations' => $payload['recommendations'],
            'availableNursesCount' => $payload['available_count'],
            'userLat' => $rawLat,
            'userLng' => $rawLng,
            'hasPatientCoords' => $hasPatientCoords,
        ]);
    }

    public function getNearbyNakes(Request $request): \Illuminate\Http\JsonResponse
    {
        return app(NurseApiController::class)->nearby($request);
    }
}
