<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransPaymentService
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool $isProduction;
    protected bool $isSanitized;
    protected bool $is3ds;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-careathome-test-key'));
        $this->clientKey = config('services.midtrans.client_key', env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-careathome-test-key'));
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        $this->is3ds = (bool) config('services.midtrans.is_3ds', true);

        $this->configureMidtrans();
    }

    /**
     * Konfigurasi parameter global Midtrans SDK.
     */
    protected function configureMidtrans(): void
    {
        Config::$serverKey = $this->serverKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = $this->isSanitized;
        Config::$is3ds = $this->is3ds;
    }

    /**
     * Membuat sesi transaksi pembayaran escrow dan menerbitkan Snap Token / Virtual Account.
     */
    public function createPayment(Order $order, string $paymentMethod = 'bca_va'): array
    {
        $bank = null;
        $paymentType = 'bank_transfer';
        $vaNumber = null;

        switch ($paymentMethod) {
            case 'bca_va':
                $bank = 'bca';
                $vaNumber = '88012' . substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11);
                break;
            case 'mandiri_va':
                $bank = 'mandiri';
                $paymentType = 'echannel';
                $vaNumber = '70012' . substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11);
                break;
            case 'bni_va':
                $bank = 'bni';
                $vaNumber = '98812' . substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11);
                break;
            case 'bri_va':
                $bank = 'bri';
                $vaNumber = '12812' . substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11);
                break;
            case 'qris':
                $paymentType = 'qris';
                $bank = null;
                $vaNumber = 'QRIS-CAH-' . $order->order_number;
                break;
            default:
                $bank = 'bca';
                $vaNumber = '88012' . substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11);
        }

        $snapToken = null;
        $redirectUrl = null;
        $isLiveGateway = false;

        // Coba koneksi ke Midtrans Snap API jika Server Key bukan default dummy
        if ($this->serverKey && !str_contains($this->serverKey, 'test-key') && class_exists(Snap::class)) {
            try {
                $uniqueOrderId = $order->order_number . '-' . time();
                $params = [
                    'transaction_details' => [
                        'order_id' => $uniqueOrderId,
                        'gross_amount' => (int) $order->total_amount,
                    ],
                    'item_details' => [
                        [
                            'id' => 'SERVICE-' . $order->service_catalog_id,
                            'price' => (int) $order->base_price,
                            'quantity' => 1,
                            'name' => substr($order->serviceCatalog->name ?? 'Tindakan Keperawatan', 0, 50),
                        ],
                        [
                            'id' => 'TRANSPORT',
                            'price' => (int) $order->transport_fee,
                            'quantity' => 1,
                            'name' => 'Transportasi Nakes (' . $order->distance_km . ' km)',
                        ],
                        [
                            'id' => 'PLATFORM_FEE',
                            'price' => (int) $order->platform_fee,
                            'quantity' => 1,
                            'name' => 'Biaya Layanan Platform',
                        ],
                    ],
                    'customer_details' => [
                        'first_name' => $order->patient_name,
                        'phone' => $order->patient_phone,
                        'billing_address' => [
                            'address' => $order->address,
                        ],
                    ],
                ];

                $snapToken = Snap::getSnapToken($params);
                $redirectUrl = Snap::createTransaction($params)->redirect_url ?? null;
                $isLiveGateway = true;
            } catch (Exception $e) {
                Log::warning('Midtrans Snap API Warning: ' . $e->getMessage() . '. Menggunakan fallback simulator sandbox lokal.');
            }
        }

        // Jika mode pengujian lokal atau Snap gagal, sediakan token simulasi terstandar
        if (!$snapToken) {
            $snapToken = 'SNAP-ESCROW-' . strtoupper(substr(md5($order->order_number . time()), 0, 24));
            $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken;
        }

        // Catat atau perbarui entitas pembayaran di database
        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id, 'payment_status' => 'pending'],
            [
                'order_number' => $order->order_number,
                'transaction_id' => 'TRX-' . strtoupper(substr(md5($order->order_number . time()), 0, 16)),
                'payment_method' => $paymentMethod,
                'payment_type' => $paymentType,
                'bank' => $bank,
                'va_number' => $vaNumber,
                'gross_amount' => $order->total_amount,
                'currency' => 'IDR',
                'escrow_status' => 'unpaid',
                'snap_token' => $snapToken,
                'snap_redirect_url' => $redirectUrl,
                'payment_payload' => [
                    'source' => $isLiveGateway ? 'midtrans_snap_api' : 'sandbox_simulator',
                    'client_key' => $this->clientKey,
                    'is_production' => $this->isProduction,
                ],
            ]
        );

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'order_number' => $order->order_number,
            'gross_amount' => $order->total_amount,
            'formatted_amount' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
            'payment_method' => $paymentMethod,
            'payment_type' => $paymentType,
            'bank' => strtoupper($bank ?? 'QRIS'),
            'va_number' => $vaNumber,
            'snap_token' => $snapToken,
            'redirect_url' => $redirectUrl,
            'client_key' => $this->clientKey,
            'is_live' => $isLiveGateway,
            'expiry_time' => now()->addHours(2)->format('d M Y, H:i') . ' WIB',
        ];
    }

    /**
     * Memvalidasi keabsahan notifikasi webhook melalui kalkulasi SHA-512 Signature Key.
     * Rumus resmi Midtrans: SHA512(order_id + status_code + gross_amount + ServerKey)
     */
    public function verifySignatureKey(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        // Pada environment local testing tanpa key resmi, bypass verifikasi agar pengujian berjalan mulus
        if (app()->environment('local') && str_contains($this->serverKey, 'test-key')) {
            return true;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Menangani notifikasi Webhook callback dari Payment Gateway secara transaksional (ACID).
     */
    public function handleWebhookNotification(array $payload): array
    {
        $orderIdRaw = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '0.00';
        $signatureKey = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';
        $transactionId = $payload['transaction_id'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'bank_transfer';

        // Bersihkan akhiran timestamp jika ada (misal: CAH-20261026-0001-1718290 -> CAH-20261026-0001)
        $orderNumber = explode('-', $orderIdRaw);
        if (count($orderNumber) >= 3) {
            $orderNumber = $orderNumber[0] . '-' . $orderNumber[1] . '-' . $orderNumber[2];
        } else {
            $orderNumber = $orderIdRaw;
        }

        // Validasi signature key keamanan
        if (!empty($signatureKey) && !$this->verifySignatureKey($orderIdRaw, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning("Midtrans Webhook: Signature Key tidak valid untuk order: {$orderIdRaw}");
            return [
                'status' => 'error',
                'message' => 'Invalid signature key.',
                'code' => 403,
            ];
        }

        // Eksekusi pembaruan status dalam transaksi database aman (ACID Compliant)
        return DB::transaction(function () use ($orderNumber, $transactionStatus, $fraudStatus, $transactionId, $paymentType, $payload) {
            $order = Order::where('order_number', $orderNumber)->lockForUpdate()->first();

            if (!$order) {
                Log::error("Midtrans Webhook: Pesanan {$orderNumber} tidak ditemukan di database.");
                return [
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan.',
                    'code' => 404,
                ];
            }

            $payment = Payment::where('order_id', $order->id)->latest()->lockForUpdate()->first();
            if (!$payment) {
                $payment = new Payment([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'gross_amount' => $order->total_amount,
                ]);
            }

            $payment->transaction_id = $transactionId ?? $payment->transaction_id;
            $payment->payment_type = $paymentType;
            $payment->payment_payload = $payload;

            // Pemetaan status pembayaran Midtrans ke status Escrow CareAtHome
            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'challenge') {
                    $payment->payment_status = 'pending';
                    $payment->escrow_status = 'unpaid';
                } elseif ($fraudStatus === 'accept') {
                    $payment->payment_status = 'settlement';
                    $payment->escrow_status = 'held'; // Terkunci aman di rekening escrow
                    $payment->paid_at = now();
                    $payment->settlement_time = now();
                    $order->payment_status = 'paid';
                }
            } elseif ($transactionStatus === 'settlement') {
                // Pembayaran lunas terkonfirmasi oleh bank / payment gateway
                $payment->payment_status = 'settlement';
                $payment->escrow_status = 'held'; // Dana ditahan di escrow platform sampai E-Report selesai
                $payment->paid_at = now();
                $payment->settlement_time = now();
                $order->payment_status = 'paid';
            } elseif ($transactionStatus === 'pending') {
                $payment->payment_status = 'pending';
                $payment->escrow_status = 'unpaid';
                $order->payment_status = 'unpaid';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $payment->payment_status = 'failed';
                $payment->escrow_status = 'unpaid';
                $order->payment_status = 'unpaid';
            } elseif ($transactionStatus === 'refund') {
                $payment->payment_status = 'refunded';
                $payment->escrow_status = 'refunded';
                $payment->refund_amount = (float) $order->total_amount;
                $payment->refunded_at = now();
                $order->payment_status = 'refunded';
                $order->refund_status = 'refunded';
            }

            $payment->save();
            $order->save();

            Log::info("Midtrans Webhook: Status transaksi pesanan {$order->order_number} berhasil diperbarui menjadi {$payment->payment_status} (Escrow: {$payment->escrow_status}).");

            return [
                'status' => 'success',
                'message' => 'Status pembayaran & escrow berhasil disinkronisasi.',
                'order_number' => $order->order_number,
                'payment_status' => $payment->payment_status,
                'escrow_status' => $payment->escrow_status,
                'code' => 200,
            ];
        });
    }

    /**
     * Simulasi Pembayaran Instan (Sandbox Testing untuk demonstrasi langsung).
     * Mengunci dana ke rekening penampung (Escrow Held) dan mengubah status pesanan menjadi Paid.
     */
    public function simulateInstantPayment(Order $order, string $paymentMethod = 'bca_va'): array
    {
        return DB::transaction(function () use ($order, $paymentMethod) {
            $payment = Payment::where('order_id', $order->id)->latest()->first();

            if (!$payment) {
                $payment = new Payment([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'gross_amount' => $order->total_amount,
                ]);
            }

            $bank = match ($paymentMethod) {
                'mandiri_va' => 'mandiri',
                'bni_va' => 'bni',
                'bri_va' => 'bri',
                'qris' => null,
                default => 'bca',
            };

            $payment->payment_method = $paymentMethod;
            $payment->bank = $bank;
            $payment->payment_status = 'settlement';
            $payment->escrow_status = 'held'; // Rekening penampung aktif mengunci dana
            $payment->paid_at = now();
            $payment->settlement_time = now();
            $payment->transaction_id = 'SIM-SETTLED-' . strtoupper(substr(md5($order->order_number . time()), 0, 12));
            $payment->payment_payload = [
                'simulator' => true,
                'settlement_note' => 'Dana berhasil dikunci di rekening penampung (escrow account) CareAtHome.',
                'executed_at' => now()->toIso8601String(),
            ];
            $payment->save();

            $order->payment_status = 'paid';
            $order->save();

            return [
                'status' => 'success',
                'message' => 'Pembayaran berhasil dikonfirmasi! Dana senilai Rp ' . number_format($order->total_amount, 0, ',', '.') . ' kini aman tersimpan di rekening escrow CareAtHome.',
                'data' => [
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'escrow_status' => $payment->escrow_status,
                    'escrow_status_label' => $payment->escrow_status_label,
                    'total_amount' => $order->total_amount,
                    'transaction_id' => $payment->transaction_id,
                    'paid_at' => $payment->paid_at->format('d M Y, H:i') . ' WIB',
                ],
            ];
        });
    }

    /**
     * Memproses Pengembalian Dana Escrow 100% saat pesanan dibatalkan sebelum tindakan.
     */
    public function processEscrowRefund(Order $order, string $reason): array
    {
        return DB::transaction(function () use ($order, $reason) {
            $payment = Payment::where('order_id', $order->id)->latest()->first();

            $refundAmount = (float) $order->total_amount;

            if ($payment) {
                $payment->payment_status = 'refunded';
                $payment->escrow_status = 'refunded';
                $payment->refund_amount = $refundAmount;
                $payment->refunded_at = now();
                $payment->refund_notes = 'Pengembalian dana 100% dari rekening penampung (Escrow) ke pasien akibat pembatalan: ' . $reason;
                $payment->save();
            }

            $order->payment_status = 'refunded';
            $order->refund_status = 'refunded';
            $order->refund_amount = $refundAmount;
            $order->refunded_at = now();
            $order->refund_notes = 'Pengembalian dana 100% dari rekening penampung (Escrow) ke pasien akibat pembatalan: ' . $reason;
            $order->save();

            return [
                'refund_processed' => true,
                'refund_amount' => $refundAmount,
                'formatted_refund_amount' => 'Rp ' . number_format($refundAmount, 0, ',', '.'),
            ];
        });
    }
}
