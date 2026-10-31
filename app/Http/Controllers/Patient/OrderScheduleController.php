<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderScheduleController extends Controller
{
    protected OrderPaymentService $paymentService;

    public function __construct(OrderPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Helper privat untuk memuat data pesanan pasien.
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
     * Tampilan 2: Halaman Gateway Pembayaran & Konfirmasi Tagihan (Simplified: Satu Tombol Bayar Sekarang).
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
            'paymentService' => $this->paymentService,
        ]);
    }

    /**
     * Tampilan 3: Halaman Pusat Pembatalan Pesanan & Refund.
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
     * Endpoint token pembayaran (kompatibilitas frontend).
     */
    public function getPaymentToken(Request $request, $order): JsonResponse
    {
        $orderModel = $order instanceof Order ? $order : Order::findOrFail($order);

        if ($orderModel->payment_status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan ini sudah lunas.',
            ], 422);
        }

        $breakdown = $this->paymentService->calculateBreakdown($orderModel->total_amount);

        return response()->json([
            'status' => 'success',
            'message' => 'Tagihan siap dibayar.',
            'data' => [
                'order_id' => $orderModel->id,
                'order_number' => $orderModel->order_number,
                'gross_amount' => $breakdown['total_amount'],
                'admin_fee' => $breakdown['admin_fee'],
                'nurse_earning' => $breakdown['nurse_earning'],
                'fee_percent' => $breakdown['fee_percent'],
            ],
        ]);
    }

    /**
     * Proses pembayaran langsung "Bayar Sekarang".
     * Dalam satu DB transaction:
     * - Status payment = paid, paid_at = now()
     * - Hitung pembagian (15% platform, 85% perawat)
     * - Langsung kredit saldo: wallet perawat & wallet admin
     * - Catat 2 baris di wallet_transactions (tipe order_income)
     * - Status pesanan lanjut ke tahap berikutnya ('diterima')
     */
    public function pay(Request $request, $order): JsonResponse
    {
        $orderModel = $order instanceof Order ? $order : Order::findOrFail($order);

        try {
            $result = $this->paymentService->payOrder($orderModel);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil dikonfirmasi! Dana telah dicatat dan dialokasikan ke dompet mitra perawat & platform.',
                'data' => [
                    'order_number' => $result['order']->order_number,
                    'gross_amount' => $result['total_amount'],
                    'admin_fee' => $result['admin_fee'],
                    'nurse_earning' => $result['nurse_earning'],
                    'fee_percent' => $result['fee_percent'],
                    'payment_status' => 'paid',
                    'order_status' => $result['order']->status,
                    'settlement_time' => now()->toIso8601String(),
                ],
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memproses pembatalan pesanan dan refund reversal dompet jika sudah dibayar.
     */
    public function cancel(Request $request, $order): JsonResponse
    {
        $orderModel = $order instanceof Order ? $order : Order::findOrFail($order);

        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $userId = $currentUser?->id;

        if ($userId && (int) $orderModel->patient_id !== (int) $userId && !($currentUser?->hasRole('admin') ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki otorisasi untuk membatalkan pesanan ini.',
            ], 403);
        }

        if (!$orderModel->isCancellable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan dengan status "' . $orderModel->status_label . '" sudah tidak dapat dibatalkan secara mandiri karena perawat telah dalam perjalanan atau tindakan telah berlangsung.',
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

        try {
            $updatedOrder = $this->paymentService->cancelOrder($orderModel, $fullReason);

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibatalkan dan reversal dana telah dicatat.',
                'data' => [
                    'order_number' => $updatedOrder->order_number,
                    'status' => $updatedOrder->status,
                    'status_label' => $updatedOrder->status_label,
                    'payment_status' => $updatedOrder->payment_status,
                    'refund_status' => $updatedOrder->refund_status,
                    'refund_amount' => $updatedOrder->refund_amount,
                    'refunded_at' => $updatedOrder->refunded_at?->toIso8601String(),
                    'cancelled_at' => $updatedOrder->cancelled_at?->toIso8601String(),
                    'cancellation_reason' => $updatedOrder->cancellation_reason,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
