<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentApiController extends Controller
{
    protected MidtransPaymentService $paymentService;

    public function __construct(MidtransPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Membuat Snap Token dan parameter pembayaran Virtual Account / QRIS.
     * Endpoint: POST /api/v1/payments/create-token
     */
    public function createPaymentToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required_without:order_number'],
            'order_number' => ['required_without:order_id'],
            'payment_method' => ['nullable', 'string', 'in:bca_va,mandiri_va,bni_va,bri_va,qris'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = null;
        if ($request->filled('order_id')) {
            $order = Order::find($request->input('order_id'));
        } elseif ($request->filled('order_number')) {
            $order = Order::where('order_number', $request->input('order_number'))->first();
        }

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan ini sudah dibayar dan dana telah tersimpan di rekening penampung (escrow).',
            ], 422);
        }

        $paymentMethod = $request->input('payment_method', 'bca_va');
        $paymentData = $this->paymentService->createPayment($order, $paymentMethod);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi pembayaran escrow berhasil dibuat.',
            'data' => $paymentData,
        ]);
    }

    /**
     * Simulasi Pembayaran Instan ke Rekening Penampung (Escrow).
     * Endpoint: POST /api/v1/payments/simulate
     */
    public function simulatePayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_number' => ['required_without:order_id'],
            'order_id' => ['required_without:order_number'],
            'payment_method' => ['nullable', 'string', 'in:bca_va,mandiri_va,bni_va,bri_va,qris'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = null;
        if ($request->filled('order_id')) {
            $order = Order::find($request->input('order_id'));
        } else {
            $order = Order::where('order_number', $request->input('order_number'))->first();
        }

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan sudah berstatus Paid.',
            ], 422);
        }

        $paymentMethod = $request->input('payment_method', 'bca_va');
        $result = $this->paymentService->simulateInstantPayment($order, $paymentMethod);

        return response()->json($result);
    }

    /**
     * Ambil Status Terkini Pembayaran & Escrow.
     * Endpoint: GET /api/v1/payments/{orderNumberOrId}
     */
    public function show(string $orderNumberOrId): JsonResponse
    {
        $order = Order::with('latestPayment')
            ->where('order_number', $orderNumberOrId)
            ->orWhere('id', $orderNumberOrId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        $payment = $order->latestPayment;

        return response()->json([
            'status' => 'success',
            'data' => [
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'order_status_label' => $order->status_label,
                'payment_status' => $order->payment_status,
                'total_amount' => $order->total_amount,
                'formatted_total_amount' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                'escrow' => [
                    'status' => $payment?->escrow_status ?? ($order->payment_status === 'paid' ? 'held' : 'unpaid'),
                    'status_label' => $payment?->escrow_status_label ?? 'Belum Ada Dana Escrow',
                    'transaction_id' => $payment?->transaction_id,
                    'paid_at' => $payment?->paid_at?->toIso8601String(),
                ],
                'refund' => [
                    'status' => $order->refund_status,
                    'amount' => $order->refund_amount,
                    'refunded_at' => $order->refunded_at?->toIso8601String(),
                ],
            ],
        ]);
    }
}
