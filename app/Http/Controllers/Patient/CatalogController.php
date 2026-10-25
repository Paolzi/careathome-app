<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
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

        return view('pages.patient.catalog', [
            'services' => $services,
            'categories' => $categories,
            'totalActiveServices' => $totalActiveServices,
            'currentSearch' => $search,
            'currentCategory' => $category,
            'currentSort' => $sortBy,
            'currentPriceBracket' => $priceBracket,
        ]);
    }
}
