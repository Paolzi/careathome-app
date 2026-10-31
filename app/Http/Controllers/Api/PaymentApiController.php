<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentApiController extends Controller
{
    protected OrderPaymentService $paymentService;

    public function __construct(OrderPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Membuat ringkasan pembayaran dan pembagian finansial (15% platform, 85% perawat).
     * Endpoint: POST /api/v1/payments/create-token
     */
    public function createPaymentToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required_without:order_number'],
            'order_number' => ['required_without:order_id'],
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
                'message' => 'Pesanan ini sudah lunas.',
            ], 422);
        }

        $breakdown = $this->paymentService->calculateBreakdown($order->total_amount);

        return response()->json([
            'status' => 'success',
            'message' => 'Detail rincian tagihan siap.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'gross_amount' => $breakdown['total_amount'],
                'admin_fee' => $breakdown['admin_fee'],
                'nurse_earning' => $breakdown['nurse_earning'],
                'fee_percent' => $breakdown['fee_percent'],
            ],
        ]);
    }

    /**
     * Eksekusi Pembayaran Langsung "Bayar Sekarang".
     * Endpoint: POST /api/v1/payments/simulate
     */
    public function simulatePayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_number' => ['required_without:order_id'],
            'order_id' => ['required_without:order_number'],
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

        try {
            $result = $this->paymentService->payOrder($order);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil dicatat.',
                'data' => [
                    'order_number' => $result['order']->order_number,
                    'gross_amount' => $result['total_amount'],
                    'admin_fee' => $result['admin_fee'],
                    'nurse_earning' => $result['nurse_earning'],
                    'fee_percent' => $result['fee_percent'],
                    'payment_status' => 'paid',
                    'order_status' => $result['order']->status,
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
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lihat status pembayaran pesanan.
     * Endpoint: GET /api/v1/payments/{orderNumberOrId}
     */
    public function show(string $orderNumberOrId): JsonResponse
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

        $payment = Payment::where('order_id', $order->id)->latest()->first();

        $breakdown = $this->paymentService->calculateBreakdown($order->total_amount);

        return response()->json([
            'status' => 'success',
            'data' => [
                'order_number' => $order->order_number,
                'total_amount' => $breakdown['total_amount'],
                'admin_fee' => $payment?->admin_fee ?? $breakdown['admin_fee'],
                'nurse_earning' => $payment?->nurse_earning ?? $breakdown['nurse_earning'],
                'fee_percent' => $payment?->fee_percent ?? $breakdown['fee_percent'],
                'payment_status' => $order->payment_status,
                'paid_at' => $payment?->paid_at?->toIso8601String(),
                'status' => $order->status,
            ],
        ]);
    }
}
