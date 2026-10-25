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
        $activeOrders = Order::with(['serviceCatalog', 'nurse'])
            ->where('patient_id', $userId)
            ->whereIn('status', ['menunggu_konfirmasi', 'diterima', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Riwayat pesanan (sudah selesai / dibatalkan)
        $pastOrders = Order::with(['serviceCatalog', 'nurse'])
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

    /**
     * Memproses pembatalan pesanan oleh pasien dan pengembalian dana escrow otomatis (SCRUM-47).
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $userId = Auth::id();
        if ($userId && $order->patient_id !== $userId && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki otorisasi untuk membatalkan pesanan ini.',
            ], 403);
        }

        if (!$order->isCancellable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan dengan status "' . $order->status_label . '" sudah tidak dapat dibatalkan secara mandiri karena perawat telah dalam perjalanan atau tindakan telah berlangsung. Silakan hubungi pusat bantuan.',
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

        // Logika Otomatisasi Refund Escrow (KF-07, KF-10, SCRUM-47)
        if ($order->payment_status === 'paid') {
            $refundProcessed = true;
            $refundAmount = (float) $order->total_amount;

            $order->payment_status = 'refunded';
            $order->refund_status = 'refunded';
            $order->refund_amount = $refundAmount;
            $order->refunded_at = now();
            $order->refund_notes = 'Pengembalian dana 100% dari rekening penampung (Escrow) ke pasien akibat pembatalan sebelum tindakan medis.';
        } else {
            $order->refund_status = 'none';
            $order->refund_amount = 0.00;
        }

        $order->status = 'dibatalkan';
        $order->cancellation_reason = $fullReason;
        $order->cancelled_at = now();
        $order->save();

        $message = $refundProcessed
            ? 'Pesanan berhasil dibatalkan. Pengembalian dana (Escrow Refund) sebesar Rp ' . number_format($refundAmount, 0, ',', '.') . ' telah diproses 100% dan dikembalikan ke rekening/e-wallet Anda.'
            : 'Pesanan #' . $order->order_number . ' telah berhasil dibatalkan.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'payment_status' => $order->payment_status,
                'refund_status' => $order->refund_status,
                'refund_amount' => $order->refund_amount,
                'refunded_at' => $order->refunded_at?->toIso8601String(),
                'cancelled_at' => $order->cancelled_at?->toIso8601String(),
                'cancellation_reason' => $order->cancellation_reason,
            ],
        ]);
    }
}
