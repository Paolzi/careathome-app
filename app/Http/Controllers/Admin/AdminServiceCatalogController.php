<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ServiceCatalog;
use App\Models\ServiceTariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminServiceCatalogController extends Controller
{
    /**
     * [SCRUM-57] Master Data Katalog Layanan & Riwayat Tarif.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $category = $request->query('category', 'all');

        $query = ServiceCatalog::with(['tariffs.creator', 'orders']);

        if (filled($search)) {
            $query->search($search);
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $services = $query->latest()->paginate(10)->withQueryString();
        $categories = ServiceCatalog::select('category')->distinct()->pluck('category');

        return view('admin.services.index', [
            'services' => $services,
            'search' => $search,
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:service_catalogs,name'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'indications' => ['nullable', 'string'],
            'contraindications' => ['nullable', 'string'],
            'estimated_duration' => ['required', 'string', 'max:50'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'price' => ['required', 'integer', 'min:10000'],
            'image_emoji' => ['nullable', 'string', 'max:10'],
            'badge_color' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);

        $admin = Auth::user();

        DB::transaction(function () use ($validated, $admin) {
            $service = ServiceCatalog::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'category' => $validated['category'],
                'description' => $validated['description'],
                'indications' => $validated['indications'] ?? null,
                'contraindications' => $validated['contraindications'] ?? null,
                'estimated_duration' => $validated['estimated_duration'],
                'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
                'price' => $validated['price'],
                'image_emoji' => $validated['image_emoji'] ?: '🩺',
                'badge_color' => $validated['badge_color'] ?: 'emerald',
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Catat tarif perdana di service_tariffs
            ServiceTariff::create([
                'service_id' => $service->id,
                'price' => $validated['price'],
                'effective_from' => now(),
                'created_by' => $admin->id,
            ]);

            AuditLog::log('service_created', $service, null, $service->toArray());
        });

        return redirect()->route('admin.services.index')->with('status', 'Layanan medis baru dan tarif perdana berhasil ditambahkan ke katalog.');
    }

    public function edit(ServiceCatalog $service): View
    {
        $service->load(['tariffs.creator']);
        return view('admin.services.edit', ['service' => $service]);
    }

    public function update(Request $request, ServiceCatalog $service)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:service_catalogs,name,' . $service->id],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'indications' => ['nullable', 'string'],
            'contraindications' => ['nullable', 'string'],
            'estimated_duration' => ['required', 'string', 'max:50'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'price' => ['required', 'integer', 'min:10000'],
            'image_emoji' => ['nullable', 'string', 'max:10'],
            'badge_color' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);

        $admin = Auth::user();
        $oldValues = $service->toArray();
        $newPrice = (int) $validated['price'];
        $oldPrice = (int) $service->price;

        DB::transaction(function () use ($service, $validated, $admin, $oldValues, $newPrice, $oldPrice) {
            $service->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'category' => $validated['category'],
                'description' => $validated['description'],
                'indications' => $validated['indications'] ?? null,
                'contraindications' => $validated['contraindications'] ?? null,
                'estimated_duration' => $validated['estimated_duration'],
                'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
                'price' => $newPrice,
                'image_emoji' => $validated['image_emoji'] ?: $service->image_emoji,
                'badge_color' => $validated['badge_color'] ?: $service->badge_color,
                'is_active' => $validated['is_active'] ?? $service->is_active,
            ]);

            // Jika tarif berubah, buat entri riwayat baru di service_tariffs
            if ($newPrice !== $oldPrice) {
                ServiceTariff::create([
                    'service_id' => $service->id,
                    'price' => $newPrice,
                    'effective_from' => now(),
                    'created_by' => $admin->id,
                ]);
            }

            AuditLog::log('service_updated', $service, $oldValues, $service->fresh()->toArray());
        });

        return redirect()->route('admin.services.index')->with('status', 'Layanan medis berhasil diperbarui.');
    }

    /**
     * Hapus layanan (atau nonaktifkan jika sudah pernah dipesan untuk menjaga data integritas).
     */
    public function destroy(ServiceCatalog $service)
    {
        $hasOrders = $service->orders()->exists();

        if ($hasOrders) {
            $service->update(['is_active' => false]);
            AuditLog::log('service_deactivated', $service, ['is_active' => true], ['is_active' => false]);

            return back()->with('status', "Layanan '{$service->name}' telah dinonaktifkan dari katalog (tidak dihapus permanen karena terdapat riwayat pesanan).");
        }

        $oldValues = $service->toArray();
        $service->delete();

        AuditLog::log('service_deleted', null, $oldValues, null);

        return back()->with('status', "Layanan '{$service->name}' berhasil dihapus permanen.");
    }
}
