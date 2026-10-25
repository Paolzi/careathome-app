<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ServiceCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{
    /**
     * Hitung rincian biaya pemesanan (Price Breakdown Engine).
     * Endpoint: POST /api/v1/orders/calculate-price
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_catalog_id' => ['required', 'exists:service_catalogs,id'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $service = ServiceCatalog::findOrFail($request->input('service_catalog_id'));
        $distanceKm = (float) $request->input('distance_km', 2.5);

        $breakdown = Order::calculatePriceBreakdown((float) $service->price, $distanceKm);

        return response()->json([
            'status' => 'success',
            'message' => 'Kalkulasi rincian biaya berhasil dihitung.',
            'data' => array_merge($breakdown, [
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                    'estimated_duration' => $service->estimated_duration,
                ],
            ]),
        ]);
    }

    /**
     * Simpan pembuatan pesanan baru (Order Booking).
     * Endpoint: POST /api/v1/orders
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_catalog_id' => ['required', 'exists:service_catalogs,id'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'string', 'max:50'],
            'patient_name' => ['required', 'string', 'max:150'],
            'patient_phone' => ['required', 'string', 'min:9', 'max:25'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'clinical_notes' => ['nullable', 'string', 'max:2000'],
            'medical_photo' => ['nullable', 'file', 'image', 'max:5120'], // Max 5MB
        ], [
            'service_catalog_id.required' => 'Layanan medis wajib dipilih.',
            'scheduled_date.required' => 'Tanggal kunjungan wajib diisi.',
            'scheduled_date.after_or_equal' => 'Tanggal kunjungan tidak boleh di masa lampau.',
            'scheduled_time.required' => 'Waktu/sesi kunjungan wajib dipilih.',
            'patient_name.required' => 'Nama lengkap pasien wajib diisi.',
            'patient_phone.required' => 'Nomor kontak/WhatsApp pasien wajib diisi.',
            'address.required' => 'Alamat lengkap lokasi tindakan wajib diisi.',
            'medical_photo.image' => 'Berkas foto kondisi medis harus berupa file gambar (JPG/PNG).',
            'medical_photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi formulir pemesanan gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $service = ServiceCatalog::findOrFail($validated['service_catalog_id']);

        // Upload foto kondisi medis jika ada
        $photoPath = null;
        if ($request->hasFile('medical_photo')) {
            $photoPath = $request->file('medical_photo')->store('medical_photos', 'public');
        }

        // Tentukan user pemesan (jika sudah login atau fallback ke akun pasien aktif)
        $userId = Auth::id() ?? $request->input('patient_id');
        if (!$userId) {
            $defaultPatient = \App\Models\User::role('pasien')->first() ?? \App\Models\User::first();
            $userId = $defaultPatient?->id ?? 1;
        }

        $distanceKm = (float) ($validated['distance_km'] ?? 2.5);
        $breakdown = Order::calculatePriceBreakdown((float) $service->price, $distanceKm);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'patient_id' => $userId,
            'service_catalog_id' => $service->id,
            'nurse_id' => null, // Belum ditentukan sampai diterima perawat
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'],
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'distance_km' => $breakdown['distance_km'],
            'clinical_notes' => $validated['clinical_notes'] ?? null,
            'medical_photo_path' => $photoPath,
            'base_price' => $breakdown['base_price'],
            'transport_fee' => $breakdown['transport_fee'],
            'platform_fee' => $breakdown['platform_fee'],
            'total_amount' => $breakdown['total_amount'],
            'commission_amount' => $breakdown['commission_amount'],
            'nurse_earnings' => $breakdown['nurse_earnings'],
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'unpaid',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan layanan medis homecare berhasil dibuat.',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'scheduled_date' => $order->scheduled_date->format('Y-m-d'),
                'scheduled_time' => $order->scheduled_time,
                'patient_name' => $order->patient_name,
                'patient_phone' => $order->patient_phone,
                'address' => $order->address,
                'distance_km' => $order->distance_km,
                'price_breakdown' => $breakdown,
                'created_at' => $order->created_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * Tampilkan detail pesanan berdasarkan Order Number atau ID.
     * Endpoint: GET /api/v1/orders/{orderNumberOrId}
     */
    public function show(string $orderNumberOrId): JsonResponse
    {
        $order = Order::with(['serviceCatalog', 'patient', 'nurse'])
            ->where('order_number', $orderNumberOrId)
            ->orWhere('id', $orderNumberOrId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'payment_status' => $order->payment_status,
                'service' => [
                    'id' => $order->serviceCatalog->id ?? null,
                    'name' => $order->serviceCatalog->name ?? 'Layanan Medis',
                    'category' => $order->serviceCatalog->category ?? '-',
                    'image_emoji' => $order->serviceCatalog->image_emoji ?? '🩺',
                ],
                'patient' => [
                    'name' => $order->patient_name,
                    'phone' => $order->patient_phone,
                    'address' => $order->address,
                    'latitude' => $order->latitude,
                    'longitude' => $order->longitude,
                ],
                'schedule' => [
                    'date' => $order->scheduled_date->format('d M Y'),
                    'time' => $order->scheduled_time,
                ],
                'clinical_notes' => $order->clinical_notes,
                'medical_photo_url' => $order->medical_photo_path ? Storage::url($order->medical_photo_path) : null,
                'financials' => [
                    'base_price' => $order->base_price,
                    'transport_fee' => $order->transport_fee,
                    'platform_fee' => $order->platform_fee,
                    'total_amount' => $order->total_amount,
                    'formatted_base_price' => 'Rp ' . number_format($order->base_price, 0, ',', '.'),
                    'formatted_transport_fee' => 'Rp ' . number_format($order->transport_fee, 0, ',', '.'),
                    'formatted_platform_fee' => 'Rp ' . number_format($order->platform_fee, 0, ',', '.'),
                    'formatted_total_amount' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                ],
                'created_at' => $order->created_at->format('d M Y H:i'),
            ],
        ]);
    }

    /**
     * Daftar pesanan milik pasien.
     * Endpoint: GET /api/v1/orders
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id() ?? $request->query('patient_id');
        $query = Order::with('serviceCatalog')->latest();

        if ($userId) {
            $query->where('patient_id', $userId);
        }

        $orders = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    /**
     * Pembatalan Pesanan & Refund Escrow API (SCRUM-47).
     * Endpoint: POST /api/v1/orders/{orderNumberOrId}/cancel
     */
    public function cancel(Request $request, string $orderNumberOrId): JsonResponse
    {
        $order = Order::where('order_number', $orderNumberOrId)
            ->orWhere('id', $orderNumberOrId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        if (!$order->isCancellable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak dapat dibatalkan karena nakes telah dalam perjalanan atau tindakan sedang berlangsung.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alasan pembatalan wajib diisi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $reason = $request->input('reason');
        if ($request->filled('notes')) {
            $reason .= ' - ' . $request->input('notes');
        }

        $refundAmount = 0.0;
        if ($order->payment_status === 'paid') {
            $refundAmount = (float) $order->total_amount;
            $order->payment_status = 'refunded';
            $order->refund_status = 'refunded';
            $order->refund_amount = $refundAmount;
            $order->refunded_at = now();
            $order->refund_notes = 'Escrow refund 100% diproses via API pembatalan pesanan.';
        } else {
            $order->refund_status = 'none';
        }

        $order->status = 'dibatalkan';
        $order->cancellation_reason = $reason;
        $order->cancelled_at = now();
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => $refundAmount > 0 
                ? 'Pesanan berhasil dibatalkan. Pengembalian dana Escrow 100% senilai Rp ' . number_format($refundAmount, 0, ',', '.') . ' telah diproses.'
                : 'Pesanan berhasil dibatalkan.',
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'refund_status' => $order->refund_status,
                'refund_amount' => $order->refund_amount,
                'refunded_at' => $order->refunded_at?->toIso8601String(),
                'cancelled_at' => $order->cancelled_at?->toIso8601String(),
            ],
        ]);
    }
}
