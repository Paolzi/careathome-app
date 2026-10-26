<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Services\MidtransPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderScheduleController extends Controller
{
    protected MidtransPaymentService $paymentService;

    public function __construct(MidtransPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Helper privat untuk memuat data pesanan pasien.
     * Khusus akun demo pasien (pasien@careathome.com), jika DB kosong akan disediakan data in-memory lokal
     * tanpa memasukkannya ke database. Akun terdaftar riil tetap membaca murni dari database.
     */
    protected function getPatientOrders(Request $request): array
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $userId = $currentUser?->id;

        if (!$userId) {
            $defaultPatient = User::whereHas('roles', function ($query) {
                $query->where('name', 'pasien');
            })->first() ?? User::first();

            $userId = $defaultPatient?->id ?? 1;
            $currentUser = $defaultPatient;
        }

        $activeOrders = Order::with(['serviceCatalog', 'nurse', 'latestPayment'])
            ->where('patient_id', $userId)
            ->whereIn('status', ['menunggu_konfirmasi', 'diterima', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $pastOrders = Order::with(['serviceCatalog', 'nurse', 'latestPayment'])
            ->where('patient_id', $userId)
            ->whereIn('status', ['selesai', 'dibatalkan'])
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Jika akun adalah akun simulasi demo (pasien@careathome.com) dan basis data kosong,
        // sediakan mock objek in-memory lokal (TIDAK dimasukkan ke database)
        $isDemoAccount = $currentUser && $currentUser->email === 'pasien@careathome.com';
        if ($isDemoAccount && $activeOrders->isEmpty() && $pastOrders->isEmpty()) {
            $demoService = ServiceCatalog::first() ?? new ServiceCatalog([
                'id' => 1,
                'name' => 'Perawatan Luka Diabetes & Ganti Balutan',
                'category' => 'wound_care',
                'price' => 150000,
                'estimated_duration' => '45 - 60 Menit',
                'image_emoji' => '🩹',
            ]);

            $demoNurse = User::whereHas('roles', function ($q) {
                $q->where('name', 'perawat');
            })->first() ?? new User([
                'id' => 99,
                'name' => 'Ns. Siti Nurhaliza, S.Kep',
                'email' => 'perawat@careathome.com',
            ]);

            // Status lokal yang disimpan di session untuk demo interaktif tanpa menyentuh database
            $demoOrderStatus = session('demo_order_status', 'menunggu_konfirmasi');
            $demoPaymentStatus = session('demo_payment_status', 'unpaid');
            $demoRefundStatus = session('demo_refund_status', 'none');
            $demoRefundAmount = session('demo_refund_amount', 0);
            $demoReason = session('demo_cancellation_reason', null);

            // Objek simulasi in-memory: Pesanan Aktif (Menunggu Pembayaran Escrow)
            $mockActive = new Order([
                'id' => 9001,
                'order_number' => 'CAH-' . date('Ymd') . '-DEMO1',
                'patient_id' => $userId,
                'service_catalog_id' => $demoService->id ?? 1,
                'nurse_id' => $demoNurse->id ?? 99,
                'scheduled_date' => now()->toDateString(),
                'scheduled_time' => '10:00 - 11:30 WIB',
                'patient_name' => $currentUser->name,
                'patient_phone' => '081234567890',
                'address' => 'Jl. Kesehatan No. 45, Kebayoran Baru, Jakarta Selatan',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'distance_km' => 3.2,
                'clinical_notes' => 'Pembersihan luka gangren diabetes melitus tipe 2 dan pergantian perban hidrokoloid steril.',
                'base_price' => 150000,
                'transport_fee' => 12500,
                'platform_fee' => 5000,
                'total_amount' => 167500,
                'commission_amount' => 22500,
                'nurse_earnings' => 140000,
                'status' => $demoOrderStatus,
                'payment_status' => $demoPaymentStatus,
                'refund_status' => $demoRefundStatus,
                'refund_amount' => $demoRefundAmount,
                'cancellation_reason' => $demoReason,
                'cancelled_at' => $demoOrderStatus === 'dibatalkan' ? now() : null,
                'refunded_at' => $demoRefundStatus === 'refunded' ? now() : null,
                'refund_notes' => $demoRefundStatus === 'refunded' ? 'Dana Escrow 100% senilai Rp 167.500 telah otomatis dikembalikan ke rekening/e-wallet pasien.' : null,
            ]);
            $mockActive->setRelation('serviceCatalog', $demoService);
            $mockActive->setRelation('nurse', $demoNurse);

            // Objek simulasi in-memory: Riwayat Pembatalan & 100% Refund Escrow
            $mockPast = new Order([
                'id' => 9002,
                'order_number' => 'CAH-' . date('Ymd', strtotime('-1 day')) . '-DEMO2',
                'patient_id' => $userId,
                'service_catalog_id' => $demoService->id ?? 1,
                'nurse_id' => $demoNurse->id ?? 99,
                'scheduled_date' => now()->subDay()->toDateString(),
                'scheduled_time' => '14:00 - 15:00 WIB',
                'patient_name' => $currentUser->name,
                'patient_phone' => '081234567890',
                'address' => 'Jl. Kesehatan No. 45, Kebayoran Baru, Jakarta Selatan',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'distance_km' => 3.2,
                'clinical_notes' => 'Permintaan tindakan injeksi berkala sesuai resep dokter.',
                'base_price' => 120000,
                'transport_fee' => 10000,
                'platform_fee' => 5000,
                'total_amount' => 135000,
                'commission_amount' => 18000,
                'nurse_earnings' => 112000,
                'status' => 'dibatalkan',
                'payment_status' => 'refunded',
                'cancellation_reason' => 'Perubahan jadwal mendadak / ada keperluan keluarga',
                'cancelled_at' => now()->subDay(),
                'refund_status' => 'refunded',
                'refund_amount' => 135000,
                'refunded_at' => now()->subDay(),
                'refund_notes' => 'Dana Escrow 100% senilai Rp 135.000 telah otomatis dikembalikan ke rekening/e-wallet pasien.',
            ]);
            $mockPast->setRelation('serviceCatalog', $demoService);
            $mockPast->setRelation('nurse', $demoNurse);

            if ($demoOrderStatus === 'dibatalkan') {
                $activeOrders = collect([]);
                $pastOrders = collect([$mockActive, $mockPast]);
            } else {
                $activeOrders = collect([$mockActive]);
                $pastOrders = collect([$mockPast]);
            }
        }

        return [$activeOrders, $pastOrders];
    }

    /**
     * Tampilan 1: Halaman Jadwal Kunjungan & Detail Booking Nakes.
     */
    public function index(Request $request): View
    {
        [$activeOrders, $pastOrders] = $this->getPatientOrders($request);

        return view('pages.patient.orders', [
            'activeOrders' => $activeOrders,
            'pastOrders' => $pastOrders,
            'totalOrders' => $activeOrders->count() + $pastOrders->count(),
        ]);
    }

    /**
     * Tampilan 2: Halaman Gateway Pembayaran Escrow & Konfirmasi Tagihan.
     */
    public function paymentView(Request $request): View
    {
        [$activeOrders, $pastOrders] = $this->getPatientOrders($request);

        $unpaidOrders = $activeOrders->where('payment_status', '!=', 'paid');
        $escrowHeldOrders = $activeOrders->where('payment_status', 'paid');

        return view('pages.patient.payment', [
            'unpaidOrders' => $unpaidOrders,
            'escrowHeldOrders' => $escrowHeldOrders,
            'totalPending' => $unpaidOrders->count(),
            'totalHeld' => $escrowHeldOrders->count(),
            'totalOrders' => $activeOrders->count(),
        ]);
    }

    /**
     * Tampilan 3: Halaman Pusat Pembatalan Pesanan & Refund Escrow.
     */
    public function cancellationView(Request $request): View
    {
        [$activeOrders, $pastOrders] = $this->getPatientOrders($request);

        $cancellableOrders = $activeOrders->filter(function ($o) {
            return $o->isCancellable();
        });

        $cancelledOrders = $pastOrders->where('status', 'dibatalkan');

        return view('pages.patient.cancellation', [
            'cancellableOrders' => $cancellableOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalCancelled' => $cancelledOrders->count(),
            'totalRefundAmount' => $cancelledOrders->sum('refund_amount'),
        ]);
    }

    /**
     * Menerbitkan Snap Token & Detail Virtual Account untuk Gateway Pembayaran Escrow.
     */
    public function getPaymentToken(Request $request, $order): JsonResponse
    {
        if ((int) $order === 9001 || (string) $order === '9001') {
            return response()->json([
                'status' => 'success',
                'message' => 'Sesi pembayaran escrow siap (Simulasi Demo Pasien).',
                'data' => [
                    'order_id' => 9001,
                    'order_number' => 'CAH-' . date('Ymd') . '-DEMO1',
                    'gross_amount' => 167500,
                    'payment_method' => $request->input('payment_method', 'bca_va'),
                    'va_number' => '88012' . rand(10000000, 99999999),
                    'bank' => 'bca',
                    'snap_token' => 'demo-token-' . uniqid(),
                    'escrow_status' => 'pending',
                ],
            ]);
        }

        $orderModel = $order instanceof Order ? $order : Order::findOrFail($order);

        if ($orderModel->payment_status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan ini sudah lunas dan dana tersimpan di rekening escrow CareAtHome.',
            ], 422);
        }

        $paymentMethod = (string) $request->input('payment_method', 'bca_va');
        $paymentData = $this->paymentService->createPayment($orderModel, $paymentMethod);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi pembayaran escrow siap.',
            'data' => $paymentData,
        ]);
    }

    /**
     * Proses pembayaran pesanan ke dalam rekening penampung aman (Escrow Payment).
     */
    public function pay(Request $request, $order): JsonResponse
    {
        $request->validate([
            'payment_method' => ['required', 'string', 'in:bca_va,mandiri_va,bni_va,bri_va,qris'],
        ], [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        if ((int) $order === 9001 || (string) $order === '9001') {
            session([
                'demo_payment_status' => 'paid',
                'demo_order_status' => 'diterima',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Simulasi Pembayaran Escrow Berhasil! Dana senilai Rp 167.500 telah tersimpan aman di Rekening Penampung (Escrow). [Simulasi Demo Pasien]',
                'data' => [
                    'order_number' => 'CAH-' . date('Ymd') . '-DEMO1',
                    'gross_amount' => 167500,
                    'payment_status' => 'paid',
                    'escrow_status' => 'held_in_escrow',
                    'settlement_time' => now()->toIso8601String(),
                ],
            ]);
        }

        $orderModel = $order instanceof Order ? $order : Order::findOrFail($order);

        if ($orderModel->payment_status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan ini sudah dibayar sebelumnya dan dana telah tersimpan di rekening escrow.',
            ], 422);
        }

        $paymentMethod = (string) $request->input('payment_method', 'bca_va');
        $result = $this->paymentService->simulateInstantPayment($orderModel, $paymentMethod);

        return response()->json($result);
    }

    /**
     * Memproses pembatalan pesanan oleh pasien dan pengembalian dana escrow otomatis 100%.
     */
    public function cancel(Request $request, $order): JsonResponse
    {
        if ((int) $order === 9001 || (string) $order === '9001' || (int) $order === 9002 || (string) $order === '9002') {
            $reason = (string) $request->input('reason', 'Perubahan jadwal mendadak / ada keperluan keluarga');
            $notes = (string) $request->input('notes', '');
            if (!empty($notes)) {
                $reason .= ' - Catatan: ' . $notes;
            }

            session([
                'demo_order_status' => 'dibatalkan',
                'demo_payment_status' => 'refunded',
                'demo_refund_status' => 'refunded',
                'demo_refund_amount' => 167500,
                'demo_cancellation_reason' => $reason,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibatalkan. Pengembalian dana (Escrow Refund) sebesar Rp 167.500 telah diproses 100% dan dikembalikan ke rekening/e-wallet Anda. [Simulasi Demo Pasien]',
                'data' => [
                    'order_number' => 'CAH-' . date('Ymd') . '-DEMO1',
                    'status' => 'dibatalkan',
                    'status_label' => 'Dibatalkan',
                    'payment_status' => 'refunded',
                    'refund_status' => 'refunded',
                    'refund_amount' => 167500,
                    'refunded_at' => now()->toIso8601String(),
                    'cancelled_at' => now()->toIso8601String(),
                    'cancellation_reason' => $reason,
                ],
            ]);
        }

        $orderModel = $order instanceof Order ? $order : Order::findOrFail($order);

        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $userId = $currentUser?->id;

        // Otorisasi: hanya pemilik pesanan atau admin yang dapat membatalkan
        if ($userId && (int) $orderModel->patient_id !== (int) $userId && !($currentUser?->hasRole('admin') ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki otorisasi untuk membatalkan pesanan ini.',
            ], 403);
        }

        if (!$orderModel->isCancellable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan dengan status "' . $orderModel->status_label . '" sudah tidak dapat dibatalkan secara mandiri karena perawat telah dalam perjalanan atau tindakan telah berlangsung. Silakan hubungi pusat bantuan.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Silakan pilih alasan pembatalan pesanan.',
        ]);

        $fullReason = $validated['reason'];
        if (!empty($validated['notes'])) {
            $fullReason .= ' - Catatan: ' . $validated['notes'];
        }

        $refundProcessed = false;
        $refundAmount = 0.0;

        // Logika Otomatisasi Refund Escrow (KF-07, KF-10)
        if ($orderModel->payment_status === 'paid') {
            $refundRes = $this->paymentService->processEscrowRefund($orderModel, $fullReason);
            $refundProcessed = true;
            $refundAmount = $refundRes['refund_amount'];
        } else {
            $orderModel->refund_status = 'none';
            $orderModel->refund_amount = 0.00;
        }

        $orderModel->status = 'dibatalkan';
        $orderModel->cancellation_reason = $fullReason;
        $orderModel->cancelled_at = now();
        $orderModel->save();

        $message = $refundProcessed
            ? 'Pesanan berhasil dibatalkan. Pengembalian dana (Escrow Refund) sebesar Rp ' . number_format($refundAmount, 0, ',', '.') . ' telah diproses 100% dan dikembalikan ke rekening/e-wallet Anda.'
            : 'Pesanan #' . $orderModel->order_number . ' telah berhasil dibatalkan.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'order_number' => $orderModel->order_number,
                'status' => $orderModel->status,
                'status_label' => $orderModel->status_label,
                'payment_status' => $orderModel->payment_status,
                'refund_status' => $orderModel->refund_status,
                'refund_amount' => $orderModel->refund_amount,
                'refunded_at' => $orderModel->refunded_at?->toIso8601String(),
                'cancelled_at' => $orderModel->cancelled_at?->toIso8601String(),
                'cancellation_reason' => $orderModel->cancellation_reason,
            ],
        ]);
    }
}
