<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MidtransPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected MidtransPaymentService $paymentService;

    public function __construct(MidtransPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Endpoint Penerima Notifikasi Webhook Midtrans Payment Gateway.
     * Route: POST /api/v1/payments/webhook
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Fallback jika body berupa raw JSON string
        if (empty($payload)) {
            $payload = json_decode($request->getContent(), true) ?? [];
        }

        Log::info('Midtrans Webhook Received:', [
            'order_id' => $payload['order_id'] ?? null,
            'status' => $payload['transaction_status'] ?? null,
            'payment_type' => $payload['payment_type'] ?? null,
        ]);

        if (empty($payload)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payload kosong atau format JSON tidak valid.',
            ], 400);
        }

        $result = $this->paymentService->handleWebhookNotification($payload);

        return response()->json([
            'status' => $result['status'] ?? 'success',
            'message' => $result['message'] ?? 'Notification received.',
            'order_number' => $result['order_number'] ?? null,
            'payment_status' => $result['payment_status'] ?? null,
            'escrow_status' => $result['escrow_status'] ?? null,
        ], $result['code'] ?? 200);
    }
}
