<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCatalogApiController extends Controller
{
    /**
     * Display a listing of active service catalog with search & filtering.
     * (SCRUM-41: BE API Master Katalog & Filtering Layanan)
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $sortBy = $request->query('sort_by', 'nama_asc');
        $minPrice = $request->query('min_price') !== null ? (float) $request->query('min_price') : null;
        $maxPrice = $request->query('max_price') !== null ? (float) $request->query('max_price') : null;

        $query = ServiceCatalog::query()
            ->active()
            ->search($search)
            ->category($category)
            ->priceRange($minPrice, $maxPrice)
            ->sort($sortBy);

        $services = $query->get()->map(function (ServiceCatalog $item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'category' => $item->category,
                'category_badge' => $item->category_badge,
                'badge_color' => $item->badge_color,
                'image_emoji' => $item->image_emoji,
                'description' => $item->description,
                'indications' => $item->indications,
                'contraindications' => $item->contraindications,
                'inclusions' => $item->inclusions ?? [],
                'estimated_duration' => $item->estimated_duration,
                'estimated_duration_minutes' => $item->estimated_duration_minutes,
                'price' => (float) $item->price,
                'formatted_price' => $item->formatted_price,
            ];
        });

        // Kategori aktif dan jumlah layanannya
        $allCategories = ServiceCatalog::active()
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->category,
                'count' => $row->count,
            ]);

        $priceStats = ServiceCatalog::active()->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar katalog layanan berhasil diambil.',
            'data' => $services,
            'meta' => [
                'total' => $services->count(),
                'categories' => $allCategories,
                'price_stats' => [
                    'min' => (float) ($priceStats->min_price ?? 0),
                    'max' => (float) ($priceStats->max_price ?? 0),
                ],
                'filters_applied' => [
                    'search' => $search,
                    'category' => $category ?: 'Semua',
                    'sort_by' => $sortBy,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                ],
            ],
        ]);
    }

    /**
     * Display the specified service details.
     */
    public function show(string $slugOrId): JsonResponse
    {
        $service = ServiceCatalog::active()
            ->where(function ($q) use ($slugOrId) {
                $q->where('slug', $slugOrId)
                  ->orWhere('id', $slugOrId);
            })
            ->first();

        if (!$service) {
            return response()->json([
                'status' => 'error',
                'message' => 'Layanan medis tidak ditemukan atau sedang tidak aktif.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'category' => $service->category,
                'category_badge' => $service->category_badge,
                'badge_color' => $service->badge_color,
                'image_emoji' => $service->image_emoji,
                'description' => $service->description,
                'indications' => $service->indications,
                'contraindications' => $service->contraindications,
                'inclusions' => $service->inclusions ?? [],
                'estimated_duration' => $service->estimated_duration,
                'estimated_duration_minutes' => $service->estimated_duration_minutes,
                'price' => (float) $service->price,
                'formatted_price' => $service->formatted_price,
            ],
        ]);
    }
}
