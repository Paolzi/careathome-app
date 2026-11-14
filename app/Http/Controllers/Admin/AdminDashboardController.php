<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NurseProfile;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard Analitik Eksekutif, Statistik Pemesanan, dan Metrik Finansial.
     */
    public function index(Request $request): View
    {
        $days = (int) $request->query('days', 14);
        if (! in_array($days, [7, 14, 30, 90])) {
            $days = 14;
        }
        $startDate = now('Asia/Jakarta')->subDays($days)->startOfDay();

        // Kartu Ringkasan Finansial & Operasional
        $paidPayments = Payment::whereIn('payment_status', ['paid', 'settlement']);
        $totalGmv = (int) $paidPayments->sum(DB::raw('COALESCE(total_amount, gross_amount)'));
        $totalPlatformFee = (int) $paidPayments->sum('admin_fee');
        $totalNurseEarnings = (int) $paidPayments->sum('nurse_earning');

        if ($totalPlatformFee === 0 && $totalGmv > 0) {
            $feePercent = Setting::getFloat('platform_fee_percent', 15.0);
            $totalPlatformFee = (int) round($totalGmv * ($feePercent / 100));
            $totalNurseEarnings = $totalGmv - $totalPlatformFee;
        }

        $totalPayoutWithdrawn = (int) PayoutRequest::whereIn('status', ['transferred', 'completed', 'approved'])->sum('amount');

        $completedOrdersCount = Order::whereIn('status', ['selesai', 'completed'])->count();
        $totalOrdersCount = Order::count();
        $successfulTransactionsCount = Payment::whereIn('payment_status', ['paid', 'settlement'])->count();
        $verifiedNursesCount = NurseProfile::where('verification_status', 'verified')->count();
        $pendingNursesCount = NurseProfile::whereIn('verification_status', ['submitted', 'pending', 'draft'])->count();
        $totalNursesCount = NurseProfile::count();

        // Mutasi Transaksi Log Real-time
        $recentMutations = WalletTransaction::with(['wallet.user', 'payment.order.serviceCatalog'])
            ->latest()
            ->take(10)
            ->get();

        // Data Chart 1: Grafik Tren Pemesanan ("Pesan Sekarang" vs "Pesan Untuk Nanti")
        $ordersTrendRaw = Order::where('created_at', '>=', $startDate)
            ->selectRaw("DATE(created_at) as date, COALESCE(booking_type, 'now') as b_type, COUNT(*) as total")
            ->groupBy('date', 'b_type')
            ->get();

        // Data Chart 2: Grafik Pendapatan Masuk vs Penarikan Payout Perawat
        $revenueRaw = Payment::whereIn('payment_status', ['paid', 'settlement'])
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(COALESCE(total_amount, gross_amount)) as total_gmv')
            ->groupBy('date')
            ->get();

        $payoutRaw = PayoutRequest::whereIn('status', ['transferred', 'completed', 'approved', 'pending'])
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total_payout')
            ->groupBy('date')
            ->get();

        $chartLabels = [];
        $chartBookingNow = [];
        $chartBookingScheduled = [];
        $chartRevenueIn = [];
        $chartPayoutOut = [];

        for ($i = $days; $i >= 0; $i--) {
            $dateObj = now('Asia/Jakarta')->subDays($i);
            $d = $dateObj->format('Y-m-d');
            $chartLabels[] = $dateObj->format('d M');

            $nowRow = $ordersTrendRaw->where('date', $d)->where('b_type', 'now')->first();
            $schRow = $ordersTrendRaw->where('date', $d)->where('b_type', 'scheduled')->first();
            $chartBookingNow[] = $nowRow ? (int) $nowRow->total : 0;
            $chartBookingScheduled[] = $schRow ? (int) $schRow->total : 0;

            $revRow = $revenueRaw->firstWhere('date', $d);
            $payRow = $payoutRaw->firstWhere('date', $d);
            $chartRevenueIn[] = $revRow ? (int) $revRow->total_gmv : 0;
            $chartPayoutOut[] = $payRow ? (int) $payRow->total_payout : 0;
        }

        // Data Chart 3: Distribusi Status Pesanan (Donut Chart)
        $statusSelesaiCount = Order::whereIn('status', ['selesai', 'completed'])->count();
        $statusBerlangsungCount = Order::whereIn('status', ['diterima', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung', 'accepted', 'on_the_way', 'in_progress'])->count();
        $statusTerjadwalCount = Order::whereIn('status', ['menunggu_konfirmasi', 'pending'])->count();
        $statusDibatalkanCount = Order::whereIn('status', ['dibatalkan', 'ditolak', 'cancelled', 'declined'])->count();

        $orderStatusDistribution = [
            'labels' => ['Selesai', 'Berlangsung', 'Terjadwal / Menunggu', 'Dibatalkan / Ditolak'],
            'counts' => [$statusSelesaiCount, $statusBerlangsungCount, $statusTerjadwalCount, $statusDibatalkanCount],
        ];

        // Log Penarikan Saldo (Payout) Perawat Terbaru
        $recentPayouts = PayoutRequest::with('nurse')
            ->latest()
            ->take(8)
            ->get();

        // Tabel Transaksi Pembayaran Terbaru
        $recentPayments = Payment::with('order.patient')
            ->latest()
            ->take(8)
            ->get();

        // Daftar Mitra Perawat (Registrasi Manual, Verifikasi & Jadwal)
        $topNurses = User::role('perawat')
            ->with(['nurseProfile', 'wallet', 'nurseSchedules'])
            ->get()
            ->map(function ($nurse) {
                $activeDaysCount = $nurse->nurseSchedules ? $nurse->nurseSchedules->where('is_active', true)->count() : 0;
                return [
                    'id' => $nurse->id,
                    'name' => $nurse->name,
                    'email' => $nurse->email,
                    'balance' => $nurse->wallet?->balance ?? 0,
                    'rating_avg' => $nurse->nurseProfile?->rating_avg ?? 0.0,
                    'verification_status' => $nurse->nurseProfile?->verification_status ?? 'draft',
                    'active_days_count' => $activeDaysCount,
                ];
            })
            ->sortByDesc('balance')
            ->values();

        return view('admin.dashboard', [
            'days' => $days,
            'totalGmv' => $totalGmv,
            'totalPlatformFee' => $totalPlatformFee,
            'totalNurseEarnings' => $totalNurseEarnings,
            'totalPayoutWithdrawn' => $totalPayoutWithdrawn,
            'successfulTransactionsCount' => $successfulTransactionsCount,
            'completedOrdersCount' => $completedOrdersCount,
            'totalOrdersCount' => $totalOrdersCount,
            'verifiedNursesCount' => $verifiedNursesCount,
            'pendingNursesCount' => $pendingNursesCount,
            'totalNursesCount' => $totalNursesCount,
            'chartLabels' => $chartLabels,
            'chartBookingNow' => $chartBookingNow,
            'chartBookingScheduled' => $chartBookingScheduled,
            'chartRevenueIn' => $chartRevenueIn,
            'chartPayoutOut' => $chartPayoutOut,
            'orderStatusDistribution' => $orderStatusDistribution,
            'recentPayouts' => $recentPayouts,
            'recentPayments' => $recentPayments,
            'recentMutations' => $recentMutations,
            'topNurses' => $topNurses,
        ]);
    }
}
