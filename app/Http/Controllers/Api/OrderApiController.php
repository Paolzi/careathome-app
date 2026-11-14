<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NurseSchedule;
use App\Models\Order;
use App\Models\ServiceCatalog;
use App\Services\OsrmRoutingService;
use Carbon\Carbon;
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
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'nurse_id' => ['nullable', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
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

        if ($request->filled('nurse_id') && $request->filled('latitude') && $request->filled('longitude')) {
            $nurseUser = \App\Models\User::find($request->input('nurse_id'));
            $profile = $nurseUser?->nurseProfile;
            if ($profile) {
                $distanceKm = $profile->distanceTo(
                    (float) $request->input('latitude'),
                    (float) $request->input('longitude')
                );
            }
        }

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
     * Simpan pembuatan pesanan baru (Order Booking: "Pesan Sekarang" maupun "Pesan Untuk Nanti").
     * Endpoint: POST /api/v1/orders
     */
    public function store(Request $request): JsonResponse
    {
        $nowWib = Carbon::now('Asia/Jakarta');
        $bookingType = $request->input('booking_type', 'scheduled');
        if (! in_array($bookingType, ['now', 'scheduled'], true)) {
            $bookingType = 'scheduled';
        }

        // Jika "Pesan Sekarang", otomatis gunakan tanggal & jam saat ini (WIB)
        if ($bookingType === 'now') {
            $request->merge([
                'scheduled_date' => $nowWib->toDateString(),
                'scheduled_time' => $nowWib->format('H:i') . ' WIB (Pesan Sekarang)',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'service_catalog_id' => ['required', 'exists:service_catalogs,id'],
            'nurse_id' => ['nullable', 'exists:users,id'],
            'booking_type' => ['nullable', 'in:now,scheduled'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:' . $nowWib->toDateString()],
            'scheduled_time' => ['required', 'string', 'max:50'],
            'patient_name' => ['required', 'string', 'max:150'],
            'patient_phone' => ['required', 'string', 'min:9', 'max:25'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'clinical_notes' => ['nullable', 'string', 'max:2000'],
            'medical_photo' => ['nullable', 'file', 'image', 'max:5120'],
        ], [
            'service_catalog_id.required' => 'Layanan medis wajib dipilih.',
            'scheduled_date.required' => 'Tanggal kunjungan wajib diisi.',
            'scheduled_date.after_or_equal' => 'Tanggal kunjungan tidak boleh di masa lampau.',
            'scheduled_time.required' => 'Waktu/jam kunjungan (WIB) wajib dipilih.',
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

        $photoPath = null;
        if ($request->hasFile('medical_photo')) {
            $photoPath = $request->file('medical_photo')->store('medical_photos', 'public');
        }

        $userId = Auth::id() ?? $request->input('patient_id');
        if (! $userId && $request->filled('patient_name')) {
            $patientByName = \App\Models\User::role('pasien')->where('name', $request->input('patient_name'))->first();
            $userId = $patientByName?->id;
        }
        if (! $userId) {
            $defaultPatient = \App\Models\User::role('pasien')->first() ?? \App\Models\User::first();
            $userId = $defaultPatient?->id ?? 1;
        }

        $distanceKm = (float) ($validated['distance_km'] ?? 2.5);
        $selectedNurseId = $validated['nurse_id'] ?? null;

        // Jika pasien belum memilih perawat spesifik, cari perawat verified yang jadwalnya tersedia pada waktu tersebut
        if (! $selectedNurseId) {
            $verifiedNurses = \App\Models\User::role('perawat')
                ->whereHas('nurseProfile', function ($q) {
                    $q->where('verification_status', 'verified');
                })
                ->where('status', '!=', 'suspended')
                ->get();

            foreach ($verifiedNurses as $candidate) {
                $avail = NurseSchedule::checkAvailability(
                    $candidate->id,
                    $bookingType,
                    $validated['scheduled_date'],
                    $validated['scheduled_time']
                );
                if ($avail['available']) {
                    $selectedNurseId = $candidate->id;
                    break;
                }
            }
        }

        // Validasi jadwal & hitung jarak jalan raya sebenarnya (OSRM) tanpa batasan radius 10 km
        if ($selectedNurseId) {
            $nurse = \App\Models\User::find($selectedNurseId);
            $profile = $nurse?->nurseProfile;

            if (! $profile || $profile->verification_status !== 'verified' || $nurse->isSuspended()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Perawat yang dipilih belum terverifikasi atau sedang dinonaktifkan.',
                ], 422);
            }

            $avail = NurseSchedule::checkAvailability(
                (int) $selectedNurseId,
                $bookingType,
                $validated['scheduled_date'],
                $validated['scheduled_time']
            );

            if (! $avail['available']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Perawat ' . ($nurse->name ?? '') . ' tidak tersedia pada waktu tersebut (' . $avail['reason'] . '). Silakan pilih jam lain atau perawat lain pada Rekomendasi Cerdas.',
                ], 422);
            }

            if (isset($validated['latitude']) && isset($validated['longitude'])) {
                $patientLat = (float) $validated['latitude'];
                $patientLng = (float) $validated['longitude'];
                $distanceKm = $profile->distanceTo($patientLat, $patientLng);
            }
        }

        $breakdown = Order::calculatePriceBreakdown((float) $service->price, $distanceKm);

        $scheduledTimeDisplay = $validated['scheduled_time'];
        if (! str_contains(strtoupper($scheduledTimeDisplay), 'WIB')) {
            $scheduledTimeDisplay .= ' WIB';
        }

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'booking_type' => $bookingType,
            'patient_id' => $userId,
            'service_catalog_id' => $service->id,
            'nurse_id' => $selectedNurseId,
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $scheduledTimeDisplay,
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'patient_latitude' => $validated['latitude'] ?? null,
            'patient_longitude' => $validated['longitude'] ?? null,
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
                'booking_type' => $order->booking_type,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'scheduled_date' => $order->scheduled_date->format('Y-m-d'),
                'scheduled_time' => $order->scheduled_time,
                'patient_name' => $order->patient_name,
                'patient_phone' => $order->patient_phone,
                'address' => $order->address,
                'distance_km' => $order->distance_km,
                'price_breakdown' => $breakdown,
                'created_at' => $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
            ],
        ], 201);
    }

    /**
     * Tampilkan detail pesanan berdasarkan Order Number atau ID.
     */
    public function show(string $orderNumberOrId): JsonResponse
    {
        $order = Order::with(['serviceCatalog', 'patient', 'nurse'])
            ->where('order_number', $orderNumberOrId)
            ->orWhere('id', $orderNumberOrId)
            ->first();

        if (! $order) {
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
                'booking_type' => $order->booking_type ?? 'scheduled',
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
                'created_at' => $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB',
            ],
        ]);
    }

    /**
     * Daftar pesanan milik pasien.
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
     * Pembatalan Pesanan & Refund API.
     */
    public function cancel(Request $request, string $orderNumberOrId, \App\Services\MidtransPaymentService $paymentService): JsonResponse
    {
        $order = Order::where('order_number', $orderNumberOrId)
            ->orWhere('id', $orderNumberOrId)
            ->first();

        if (! $order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        if (! $order->isCancellable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak dapat dibatalkan karena tindakan telah selesai atau dibatalkan sebelumnya.',
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
            $refundRes = $paymentService->processEscrowRefund($order, $reason);
            $refundAmount = (float) $refundRes['refund_amount'];
        } else {
            $order->refund_status = 'none';
        }

        $order->status = 'dibatalkan';
        $order->cancellation_reason = $reason;
        $order->cancelled_at = now('Asia/Jakarta');
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => $refundAmount > 0
                ? 'Pesanan berhasil dibatalkan. Pengembalian dana 100% senilai Rp ' . number_format($refundAmount, 0, ',', '.') . ' telah diproses.'
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
