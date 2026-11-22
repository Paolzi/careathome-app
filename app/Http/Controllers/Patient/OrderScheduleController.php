<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\PatientPayout;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderScheduleController extends Controller
{
    protected OrderPaymentService $paymentService;

    public function __construct(OrderPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Resolve user pasien yang sedang login (atau fallback demo pasien).
     */
    protected function resolveCurrentPatient(): ?User
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if ($currentUser) {
            return $currentUser;
        }

        return User::whereHas('roles', function ($query) {
            $query->where('name', 'pasien');
        })->first() ?? User::first();
    }

    /**
     * Helper privat untuk memuat data pesanan pasien.
     */
    protected function getPatientOrders(Request $request): array
    {
        $currentUser = $this->resolveCurrentPatient();
        $userId = $currentUser?->id ?? 1;

        $activeOrders = Order::with(['serviceCatalog', 'nurse.nurseProfile', 'latestPayment', 'eReport'])
            ->where(function ($query) use ($userId, $currentUser) {
                $query->where('patient_id', $userId);
                if ($currentUser && $currentUser->name) {
                    $query->orWhere('patient_name', $currentUser->name);
                }
            })
            ->whereIn('status', ['menunggu_konfirmasi', 'diterima', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $pastOrders = Order::with(['serviceCatalog', 'nurse.nurseProfile', 'latestPayment', 'eReport.photos', 'review'])
            ->where(function ($query) use ($userId, $currentUser) {
                $query->where('patient_id', $userId);
                if ($currentUser && $currentUser->name) {
                    $query->orWhere('patient_name', $currentUser->name);
                }
            })
            ->whereIn('status', ['selesai', 'completed', 'dibatalkan', 'ditolak', 'declined'])
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
        $escrowHeldOrders = $activeOrders->concat($pastOrders)->where('payment_status', 'paid');

        return view('pages.patient.payment', [
            'unpaidOrders' => $unpaidOrders,
            'escrowHeldOrders' => $escrowHeldOrders,
            'totalPending' => $unpaidOrders->count(),
            'totalHeld' => $escrowHeldOrders->count(),
            'totalOrders' => $activeOrders->count() + $pastOrders->count(),
            'paymentService' => $this->paymentService,
        ]);
    }

    /**
     * Tampilan 3: Halaman Pusat Pembatalan Pesanan & Refund.
     */
    public function cancellationView(Request $request): View
    {
        $currentUser = $this->resolveCurrentPatient();
        [$activeOrders, $pastOrders] = $this->getPatientOrders($request);

        $cancellableOrders = $activeOrders->filter(function ($o) {
            return $o->isCancellable();
        });

        $cancelledOrders = $pastOrders->whereIn('status', ['dibatalkan', 'ditolak', 'declined']);
        $totalRefundGross = (int) $cancelledOrders->sum('refund_amount');

        $patientPayouts = $currentUser
            ? PatientPayout::where('user_id', $currentUser->id)
                ->latest('processed_at')
                ->latest('id')
                ->get()
            : collect();

        $totalWithdrawn = (int) $patientPayouts->where('status', 'completed')->sum('amount');
        $availableRefundBalance = max(0, $totalRefundGross - $totalWithdrawn);

        return view('pages.patient.cancellation', [
            'cancellableOrders' => $cancellableOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalCancelled' => $cancelledOrders->count(),
            'totalRefundAmount' => $availableRefundBalance,
            'totalRefundGross' => $totalRefundGross,
            'totalWithdrawn' => $totalWithdrawn,
            'availableRefundBalance' => $availableRefundBalance,
            'patientPayouts' => $patientPayouts,
        ]);
    }

    /**
     * Proses pencairan (payout / ambil uang) dari saldo dana pengembalian pasien.
     */
    public function requestRefundPayout(Request $request): RedirectResponse|JsonResponse
    {
        $patient = $this->resolveCurrentPatient();
        if (!$patient) {
            $msg = 'Akun pasien tidak ditemukan.';
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 403)
                : back()->with('error', $msg);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ], [
            'amount.required' => 'Nominal penarikan dana wajib diisi.',
            'amount.integer' => 'Nominal penarikan dana harus berupa angka bulat.',
            'amount.min' => 'Nominal penarikan dana minimal Rp 1.',
        ]);

        $amount = (int) $validated['amount'];

        try {
            $result = DB::transaction(function () use ($patient, $amount) {
                $lockedUser = User::where('id', $patient->id)->lockForUpdate()->firstOrFail();

                $totalRefundGross = (int) Order::where(function ($query) use ($lockedUser) {
                        $query->where('patient_id', $lockedUser->id);
                        if ($lockedUser->name) {
                            $query->orWhere('patient_name', $lockedUser->name);
                        }
                    })
                    ->whereIn('status', ['dibatalkan', 'ditolak', 'declined'])
                    ->sum('refund_amount');

                $totalWithdrawn = (int) PatientPayout::where('user_id', $lockedUser->id)
                    ->where('status', 'completed')
                    ->lockForUpdate()
                    ->sum('amount');

                $availableBalance = max(0, $totalRefundGross - $totalWithdrawn);

                if ($availableBalance <= 0) {
                    throw new \DomainException('Saldo dana pengembalian Anda saat ini Rp 0 dan belum dapat ditarik.');
                }

                if ($amount > $availableBalance) {
                    throw new \DomainException('Saldo dana pengembalian Anda (Rp ' . number_format($availableBalance, 0, ',', '.') . ') tidak mencukupi untuk penarikan sebesar Rp ' . number_format($amount, 0, ',', '.') . '.');
                }

                $remainingBalance = max(0, $availableBalance - $amount);
                $nowWib = now('Asia/Jakarta');

                $payout = PatientPayout::create([
                    'user_id' => $lockedUser->id,
                    'amount' => $amount,
                    'status' => 'completed',
                    'reference_number' => 'RFD-' . $nowWib->format('YmdHis') . '-' . $lockedUser->id . '-' . random_int(100, 999),
                    'notes' => 'Penarikan dana pengembalian (payout pasien) sebesar Rp ' . number_format($amount, 0, ',', '.') . ' pada ' . $nowWib->format('d M Y, H:i') . ' WIB',
                    'processed_at' => $nowWib,
                ]);

                AuditLog::log('patient_refund_payout', $payout, [
                    'available_balance_before' => $availableBalance,
                ], [
                    'amount' => $amount,
                    'available_balance_after' => $remainingBalance,
                    'reference_number' => $payout->reference_number,
                ]);

                return [
                    'payout' => $payout,
                    'remaining_balance' => $remainingBalance,
                ];
            });

            try {
                $timeStr = now('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
                $patient->notify(new \App\Notifications\SystemActivityNotification(
                    'patient_refund_payout',
                    'Penarikan Dana Pengembalian Berhasil',
                    'Penarikan dana pengembalian sebesar Rp ' . number_format($amount, 0, ',', '.') . " berhasil diproses ({$timeStr}). Sisa dana pengembalian: Rp " . number_format($result['remaining_balance'], 0, ',', '.') . '.'
                ));

                foreach (User::role('admin')->get() as $adminUser) {
                    $adminUser->notify(new \App\Notifications\SystemActivityNotification(
                        'patient_refund_payout',
                        'Aktivitas Payout Dana Pasien',
                        "Pasien {$patient->name} menarik dana pengembalian sebesar Rp " . number_format($amount, 0, ',', '.') . '.'
                    ));
                }
            } catch (\Throwable $e) {
                report($e);
            }

            $successMsg = 'Dana pengembalian sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil diambil! Sisa dana pengembalian: Rp ' . number_format($result['remaining_balance'], 0, ',', '.') . '.';

            return $request->expectsJson()
                ? response()->json([
                    'status' => 'success',
                    'message' => $successMsg,
                    'data' => [
                        'reference_number' => $result['payout']->reference_number,
                        'amount' => $amount,
                        'remaining_balance' => $result['remaining_balance'],
                    ],
                ])
                : back()->with('status', $successMsg);
        } catch (\DomainException $e) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            $errMsg = 'Terjadi kesalahan sistem saat memproses penarikan dana: ' . $e->getMessage();
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $errMsg], 500)
                : back()->with('error', $errMsg)->withInput();
        }
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

        $isOwner = !$userId
            || ((int) $orderModel->patient_id === (int) $userId)
            || ($currentUser && strcasecmp((string) $orderModel->patient_name, (string) $currentUser->name) === 0);

        if (!$isOwner && !($currentUser?->hasRole('admin') ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki otorisasi untuk membatalkan pesanan ini.',
            ], 403);
        }

        if (!$orderModel->isCancellable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan dengan status "' . $orderModel->status_label . '" sudah selesai atau dibatalkan sebelumnya.',
            ], 422);
        }

        $reason = $request->input('reason', 'Perubahan jadwal mendadak / pembatalan mandiri pasien');
        $notes = $request->input('notes', '');

        $fullReason = $reason;
        if (!empty($notes)) {
            $fullReason .= ' - Catatan: ' . $notes;
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
