<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NurseProfile;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * [SCRUM-56] Financial Dashboard & GMV Metrics.
     */
    public function index(Request $request): View
    {
        $days = (int) $request->query('days', 30);
        $startDate = now()->subDays($days)->startOfDay();

        // Kartu Ringkasan Finansial & Operasional
        $paidPayments = Payment::whereIn('payment_status', ['paid', 'settlement']);
        $totalGmv = (int) $paidPayments->sum(DB::raw('COALESCE(total_amount, gross_amount)'));
        $totalPlatformFee = (int) $paidPayments->sum('admin_fee');
        $totalNurseEarnings = (int) $paidPayments->sum('nurse_earning');

        // Jika data lama belum ada breakdown admin_fee, hitung 15% dari GMV
        if ($totalPlatformFee === 0 && $totalGmv > 0) {
            $feePercent = Setting::getFloat('platform_fee_percent', 15.0);
            $totalPlatformFee = (int) round($totalGmv * ($feePercent / 100));
            $totalNurseEarnings = $totalGmv - $totalPlatformFee;
        }

        $completedOrdersCount = Order::where('status', 'selesai')->count();
        $totalOrdersCount = Order::count();
        $activeNursesCount = NurseProfile::active()->count();
        $totalNursesCount = NurseProfile::count();

        // Data Grafik GMV Harian untuk Chart.js
        $chartDataRaw = Payment::whereIn('payment_status', ['paid', 'settlement'])
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(COALESCE(total_amount, gross_amount)) as total_gmv, SUM(admin_fee) as platform_fee')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $chartLabels = [];
        $chartGmv = [];
        $chartPlatformFee = [];

        for ($i = $days; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $row = $chartDataRaw->firstWhere('date', $d);
            $gmv = $row ? (int) $row->total_gmv : 0;
            $chartGmv[] = $gmv;
            $chartPlatformFee[] = $row ? (int) $row->platform_fee : (int) round($gmv * 0.15);
        }

        // Tabel Transaksi Pembayaran Terbaru
        $recentPayments = Payment::with('order.patient')
            ->latest()
            ->take(8)
            ->get();

        // Top Perawat Berdasarkan Pendapatan
        $topNurses = User::role('perawat')
            ->with(['nurseProfile', 'wallet'])
            ->get()
            ->map(function ($nurse) {
                return [
                    'id' => $nurse->id,
                    'name' => $nurse->name,
                    'balance' => $nurse->wallet?->balance ?? 0,
                    'rating_avg' => $nurse->nurseProfile?->rating_avg ?? 0.0,
                    'verification_status' => $nurse->nurseProfile?->verification_status ?? 'draft',
                    'is_online' => $nurse->nurseProfile?->is_online ?? false,
                ];
            })
            ->sortByDesc('balance')
            ->take(5);

        return view('admin.dashboard', [
            'days' => $days,
            'totalGmv' => $totalGmv,
            'totalPlatformFee' => $totalPlatformFee,
            'totalNurseEarnings' => $totalNurseEarnings,
            'completedOrdersCount' => $completedOrdersCount,
            'totalOrdersCount' => $totalOrdersCount,
            'activeNursesCount' => $activeNursesCount,
            'totalNursesCount' => $totalNursesCount,
            'chartLabels' => $chartLabels,
            'chartGmv' => $chartGmv,
            'chartPlatformFee' => $chartPlatformFee,
            'recentPayments' => $recentPayments,
            'topNurses' => $topNurses,
        ]);
    }
}
