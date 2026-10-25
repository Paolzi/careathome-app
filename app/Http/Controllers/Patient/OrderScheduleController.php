<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderScheduleController extends Controller
{
    /**
     * Tampilkan halaman daftar jadwal & riwayat pesanan pasien.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        // Ambil pesanan milik pasien login (atau fallback pasien pertama untuk demo lokal)
        if (!$userId) {
            $defaultPatient = \App\Models\User::role('pasien')->first() ?? \App\Models\User::first();
            $userId = $defaultPatient?->id ?? 1;
        }

        // Pesanan aktif (dalam proses / belum selesai)
        $activeOrders = Order::with('serviceCatalog')
            ->where('patient_id', $userId)
            ->whereIn('status', ['menunggu_konfirmasi', 'diterima', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Riwayat pesanan (sudah selesai / dibatalkan)
        $pastOrders = Order::with('serviceCatalog')
            ->where('patient_id', $userId)
            ->whereIn('status', ['selesai', 'dibatalkan'])
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.patient.orders', [
            'activeOrders' => $activeOrders,
            'pastOrders' => $pastOrders,
            'totalOrders' => $activeOrders->count() + $pastOrders->count(),
        ]);
    }

    /**
     * Proses pembayaran pesanan ke dalam rekening penampung aman (Escrow Payment).
     */
    public function pay(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'payment_method' => ['required', 'string', 'in:bca_va,mandiri_va,bni_va,bri_va,qris'],
        ], [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan ini sudah dibayar sebelumnya dan dana telah tersimpan di rekening escrow.',
            ], 422);
        }

        // Simulasi penguncian dana ke rekening penampung (Escrow System)
        $order->payment_status = 'paid';
        
        // Catat metode pembayaran jika ada
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran berhasil dikonfirmasi! Dana senilai ' . 'Rp ' . number_format($order->total_amount, 0, ',', '.') . ' kini aman tersimpan di rekening escrow CareAtHome.',
            'data' => [
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'total_amount' => $order->total_amount,
                'payment_method' => $request->input('payment_method'),
            ],
        ]);
    }
}
