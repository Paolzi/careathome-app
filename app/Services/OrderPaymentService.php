<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    /**
     * Hitung porsi finansial pesanan berdasarkan persentase fee dinamis di Settings.
     */
    public function calculateBreakdown(float|int|Order $orderInput): array
    {
        $feePercent = Setting::getFloat('platform_fee_percent', 15.0);

        if ($orderInput instanceof Order) {
            $basePrice = (float) ($orderInput->base_price ?? ($orderInput->total_amount * 0.8));
            $transportFee = (float) ($orderInput->transport_fee ?? 10000.0);
            $platformFee = (float) ($orderInput->platform_fee ?? 5000.0);
            $total = (int) round($orderInput->total_amount ?: ($basePrice + $transportFee + $platformFee));

            $adminFee = (int) round(($basePrice * ($feePercent / 100.0)) + $platformFee);
            $nurseEarning = (int) ($total - $adminFee);

            return [
                'total_amount' => $total,
                'fee_percent' => $feePercent,
                'admin_fee' => $adminFee,
                'nurse_earning' => $nurseEarning,
                'base_price' => (int) round($basePrice),
                'transport_fee' => (int) round($transportFee),
                'platform_fee' => (int) round($platformFee),
            ];
        }

        $total = (int) round((float) $orderInput);
        $adminFee = (int) round($total * ($feePercent / 100.0));
        $nurseEarning = (int) ($total - $adminFee);

        return [
            'total_amount' => $total,
            'fee_percent' => $feePercent,
            'admin_fee' => $adminFee,
            'nurse_earning' => $nurseEarning,
            'base_price' => (int) round($total * 0.85),
            'transport_fee' => 10000,
            'platform_fee' => 5000,
        ];
    }

    /**
     * Proses pembayaran langsung "Bayar Sekarang" dalam satu transaksi DB yang aman dan idempotent.
     *
     * @throws \Exception
     */
    public function payOrder(int|string|Order $orderInput): array
    {
        return DB::transaction(function () use ($orderInput) {
            $orderId = $orderInput instanceof Order ? $orderInput->id : (int) $orderInput;

            // 1. Lock baris order untuk idempotency & concurrency safety
            /** @var Order $order */
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === 'paid') {
                throw new \DomainException('Pesanan ini sudah dibayar sebelumnya dan dana telah tercatat.');
            }

            if ($order->status === 'dibatalkan') {
                throw new \DomainException('Pesanan yang telah dibatalkan tidak dapat dibayar.');
            }

            // 2. Hitung pembagian (15% platform, 85% nakes atau sesuai setting)
            $breakdown = $this->calculateBreakdown($order);
            $totalAmount = $breakdown['total_amount'];
            $adminFee = $breakdown['admin_fee'];
            $nurseEarning = $breakdown['nurse_earning'];
            $feePercent = $breakdown['fee_percent'];

            // 3. Catat / Update Payment Record
            /** @var Payment $payment */
            $payment = Payment::firstOrNew(['order_id' => $order->id]);
            $payment->fill([
                'order_number' => $order->order_number,
                'transaction_id' => 'PAY-' . $order->order_number . '-' . time(),
                'gross_amount' => $totalAmount,
                'total_amount' => $totalAmount,
                'admin_fee' => $adminFee,
                'nurse_earning' => $nurseEarning,
                'fee_percent' => $feePercent,
                'payment_method' => 'direct_payment',
                'payment_type' => 'direct',
                'payment_status' => 'paid',
                'status' => 'paid',
                'escrow_status' => 'held',
                'paid_at' => now(),
                'settlement_time' => now(),
            ]);
            $payment->save();

            // 4. Update status pesanan ke tahap berikutnya ('diterima')
            $oldStatus = $order->status;
            $order->payment_status = 'paid';
            if ($order->status === 'menunggu_konfirmasi') {
                $order->status = 'diterima';
            }
            $order->save();

            // 5. Jika order belum memiliki nakes terikat, pasangkan ke nakes online verified terdekat (misal Dinda)
            if (!$order->nurse_id) {
                $onlineNurse = User::role('perawat')
                    ->whereHas('nurseProfile', function ($q) {
                        $q->where('verification_status', 'verified')->where('is_online', true);
                    })
                    ->first();
                if ($onlineNurse) {
                    $order->nurse_id = $onlineNurse->id;
                    $order->save();
                }
            }

            // 6. Kredit Wallet Perawat & Catat wallet_transaction
            if ($order->nurse_id) {
                $nurseWallet = Wallet::where('user_id', $order->nurse_id)->lockForUpdate()->first();
                if (!$nurseWallet) {
                    $nurseWallet = Wallet::create(['user_id' => $order->nurse_id, 'balance' => 0]);
                    $nurseWallet = Wallet::where('id', $nurseWallet->id)->lockForUpdate()->first();
                }

                $nurseWallet->balance += $nurseEarning;
                $nurseWallet->save();

                // Sinkronkan juga model NurseWallet
                $nw = \App\Models\NurseWallet::getOrCreateForNurse($order->nurse_id);
                $nw->balance = $nurseWallet->balance;
                $nw->save();

                // Sinkronkan model NurseProfile
                $p = \App\Models\NurseProfile::where('user_id', $order->nurse_id)->first();
                if ($p) {
                    $p->balance = $nurseWallet->balance;
                    $p->save();
                }

                WalletTransaction::create([
                    'wallet_id' => $nurseWallet->id,
                    'nurse_wallet_id' => $nw->id,
                    'order_id' => $order->id,
                    'type' => 'order_income',
                    'amount' => $nurseEarning,
                    'platform_fee' => $adminFee,
                    'net_amount' => $nurseEarning,
                    'status' => 'completed',
                    'balance_after' => $nurseWallet->balance,
                    'payment_id' => $payment->id,
                    'description' => "Pendapatan perawat (" . (100 - $feePercent) . "% + transport) pesanan #{$order->order_number}",
                ]);
            }

            // 7. Kredit Wallet Admin (Platform) & Catat wallet_transaction
            $adminUser = User::role('admin')->first() ?? User::where('id', 1)->first();
            if ($adminUser) {
                $adminWallet = Wallet::where('user_id', $adminUser->id)->lockForUpdate()->first();
                if (!$adminWallet) {
                    $adminWallet = Wallet::create(['user_id' => $adminUser->id, 'balance' => 0]);
                    $adminWallet = Wallet::where('id', $adminWallet->id)->lockForUpdate()->first();
                }

                $adminWallet->balance += $adminFee;
                $adminWallet->save();

                WalletTransaction::create([
                    'wallet_id' => $adminWallet->id,
                    'order_id' => $order->id,
                    'type' => 'order_income',
                    'amount' => $adminFee,
                    'platform_fee' => $adminFee,
                    'net_amount' => $adminFee,
                    'status' => 'completed',
                    'balance_after' => $adminWallet->balance,
                    'payment_id' => $payment->id,
                    'description' => "Komisi platform ({$feePercent}%) pesanan #{$order->order_number}",
                ]);
            }

            // 8. Audit log pencatatan
            AuditLog::log(
                'order_paid',
                $order,
                ['status' => $oldStatus, 'payment_status' => 'unpaid'],
                ['status' => $order->status, 'payment_status' => 'paid', 'total_amount' => $totalAmount, 'admin_fee' => $adminFee, 'nurse_earning' => $nurseEarning]
            );

            // Kirim notifikasi pembayaran Escrow ke Perawat & Pasien
            try {
                if ($order->nurse_id && ($nurseUser = User::find($order->nurse_id))) {
                    $nurseUser->notify(new \App\Notifications\SystemActivityNotification(
                        'order_paid',
                        'Pembayaran Escrow Lunas #' . $order->order_number,
                        "Pasien {$order->patient_name} telah melunasi pembayaran Escrow (Rp " . number_format($totalAmount, 0, ',', '.') . "). Bersiaplah menuju lokasi pada {$order->scheduled_time}.",
                        ['order_id' => $order->id, 'order_number' => $order->order_number, 'status' => 'paid']
                    ));
                }
                if ($order->patient_id && ($patientUser = User::find($order->patient_id))) {
                    $patientUser->notify(new \App\Notifications\PatientOrderStatusNotification(
                        $order,
                        'paid',
                        "Pembayaran Escrow pesanan #{$order->order_number} senilai Rp " . number_format($totalAmount, 0, ',', '.') . " berhasil! Perawat bersiap menuju lokasi Anda."
                    ));
                }
            } catch (\Throwable $e) {
                report($e);
            }

            return [
                'order' => $order,
                'payment' => $payment,
                'total_amount' => $totalAmount,
                'admin_fee' => $adminFee,
                'nurse_earning' => $nurseEarning,
                'fee_percent' => $feePercent,
            ];
        });
    }

    /**
     * Memproses pembatalan pesanan dan reversal transaksi wallet jika sudah dibayar.
     */
    public function cancelOrder(int|string|Order $orderInput, string $reason = ''): Order
    {
        return DB::transaction(function () use ($orderInput, $reason) {
            $orderId = $orderInput instanceof Order ? $orderInput->id : (int) $orderInput;

            /** @var Order $order */
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->status === 'dibatalkan') {
                return $order;
            }

            $oldStatus = $order->status;
            $oldPaymentStatus = $order->payment_status;

            // Jika sudah dibayar, buat reversal transaction
            if ($order->payment_status === 'paid') {
                /** @var Payment|null $payment */
                $payment = Payment::where('order_id', $order->id)
                    ->whereIn('payment_status', ['paid', 'settlement'])
                    ->lockForUpdate()
                    ->first();

                if ($payment) {
                    // 1. Reversal wallet perawat
                    if ($order->nurse_id && $payment->nurse_earning > 0) {
                        $nurseWallet = Wallet::where('user_id', $order->nurse_id)->lockForUpdate()->first();
                        if ($nurseWallet) {
                            $nurseWallet->balance = max(0, $nurseWallet->balance - $payment->nurse_earning);
                            $nurseWallet->save();

                            $nw = \App\Models\NurseWallet::getOrCreateForNurse($order->nurse_id);
                            $nw->balance = $nurseWallet->balance;
                            $nw->save();

                            $p = \App\Models\NurseProfile::where('user_id', $order->nurse_id)->first();
                            if ($p) {
                                $p->balance = $nurseWallet->balance;
                                $p->save();
                            }

                            $reversalNurseAmount = abs((int) $payment->nurse_earning);
                            WalletTransaction::create([
                                'wallet_id' => $nurseWallet->id,
                                'nurse_wallet_id' => $nw->id,
                                'order_id' => $order->id,
                                'type' => 'refund_reversal',
                                'amount' => -$reversalNurseAmount,
                                'platform_fee' => 0,
                                'net_amount' => $reversalNurseAmount,
                                'status' => 'completed',
                                'balance_after' => max(0, (int) $nurseWallet->balance),
                                'payment_id' => $payment->id,
                                'description' => "Reversal pendapatan pembatalan pesanan #{$order->order_number}",
                            ]);
                        }
                    }

                    // 2. Reversal wallet admin
                    if ($payment->admin_fee > 0) {
                        $adminUser = User::role('admin')->first() ?? User::where('id', 1)->first();
                        if ($adminUser) {
                            $adminWallet = Wallet::where('user_id', $adminUser->id)->lockForUpdate()->first();
                            if ($adminWallet) {
                                $adminWallet->balance = max(0, $adminWallet->balance - $payment->admin_fee);
                                $adminWallet->save();

                                $reversalAdminFee = abs((int) $payment->admin_fee);
                                WalletTransaction::create([
                                    'wallet_id' => $adminWallet->id,
                                    'order_id' => $order->id,
                                    'type' => 'refund_reversal',
                                    'amount' => -$reversalAdminFee,
                                    'platform_fee' => $reversalAdminFee,
                                    'net_amount' => $reversalAdminFee,
                                    'status' => 'completed',
                                    'balance_after' => max(0, (int) $adminWallet->balance),
                                    'payment_id' => $payment->id,
                                    'description' => "Reversal komisi platform pembatalan pesanan #{$order->order_number}",
                                ]);
                            }
                        }
                    }

                    // 3. Update status pembayaran menjadi refunded
                    $payment->fill([
                        'payment_status' => 'refunded',
                        'status' => 'refunded',
                        'escrow_status' => 'refunded',
                        'refund_amount' => $payment->total_amount ?: $payment->gross_amount,
                        'refunded_at' => now('Asia/Jakarta'),
                        'refund_notes' => $reason,
                    ]);
                    $payment->save();
                }

                $order->refund_status = 'refunded';
                $order->refund_amount = $order->total_amount;
                $order->refunded_at = now('Asia/Jakarta');
                $order->refund_notes = 'Pengembalian dana 100% senilai Rp ' . number_format($order->total_amount, 0, ',', '.') . ' telah selesai diproses.';
                $order->payment_status = 'refunded';
            } else {
                $order->refund_status = 'none';
                $order->refund_amount = 0;
            }

            $order->status = 'dibatalkan';
            $order->cancellation_reason = $reason;
            $order->cancelled_at = now('Asia/Jakarta');
            $order->save();

            AuditLog::log(
                'order_cancelled',
                $order,
                ['status' => $oldStatus, 'payment_status' => $oldPaymentStatus],
                ['status' => 'dibatalkan', 'payment_status' => $order->payment_status, 'cancellation_reason' => $reason]
            );

            try {
                if ($order->nurse_id && ($nurseUser = User::find($order->nurse_id))) {
                    $nurseUser->notify(new \App\Notifications\SystemActivityNotification(
                        'order_cancelled',
                        'Pesanan Dibatalkan Pasien #' . $order->order_number,
                        "Pesanan #{$order->order_number} oleh {$order->patient_name} telah dibatalkan. Alasan: {$reason}",
                        ['order_id' => $order->id, 'order_number' => $order->order_number, 'status' => 'dibatalkan']
                    ));
                }
                if ($order->patient_id && ($patientUser = User::find($order->patient_id))) {
                    $patientUser->notify(new \App\Notifications\PatientOrderStatusNotification(
                        $order,
                        'dibatalkan',
                        "Pesanan #{$order->order_number} berhasil dibatalkan." . ($order->refund_amount > 0 ? ' Dana refund 100% Rp ' . number_format($order->refund_amount, 0, ',', '.') . ' telah dikembalikan.' : '')
                    ));
                }
            } catch (\Throwable $e) {
                report($e);
            }

            return $order;
        });
    }
}
