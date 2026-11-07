<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\NurseProfile;
use App\Models\ServiceCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
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

        // Kategori dinamis dari database untuk filter pills
        $categories = ServiceCatalog::active()
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $totalActiveServices = ServiceCatalog::active()->count();

        // Kueri Mitra Perawat Aktif & Terdekat (Radius 10 KM dengan Fallback)
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');
        $radiusInKm = 10;

        $nurseQuery = NurseProfile::with(['user', 'services'])
            ->where('is_online', true)
            ->where('verification_status', 'verified')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('user', function ($q) {
                $q->where('status', '!=', 'suspended');
            });

        $hasPatientCoords = filled($userLat) && filled($userLng);

        if ($hasPatientCoords) {
            // Rumus Haversine untuk filter radius 10 km
            $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))";
            $nurseQuery->select('nurse_profiles.*')
                  ->selectRaw("round({$haversine}, 2) AS distance", [$userLat, $userLng, $userLat])
                  ->selectRaw("round({$haversine}, 2) AS distance_km", [$userLat, $userLng, $userLat])
                  ->having('distance', '<=', $radiusInKm)
                  ->orderBy('distance', 'asc');
        } else {
            // Fallback jika lokasi pasien belum aktif / belum diizinkan
            $nurseQuery->orderBy('location_updated_at', 'desc')->latest();
        }

        $onlineNurses = $nurseQuery->get();

        return view('pages.patient.catalog', [
            'services' => $services,
            'categories' => $categories,
            'totalActiveServices' => $totalActiveServices,
            'currentSearch' => $search,
            'currentCategory' => $category,
            'currentSort' => $sortBy,
            'currentPriceBracket' => $priceBracket,
            'onlineNurses' => $onlineNurses,
            'userLat' => $userLat,
            'userLng' => $userLng,
            'hasPatientCoords' => $hasPatientCoords,
        ]);
    }
}
