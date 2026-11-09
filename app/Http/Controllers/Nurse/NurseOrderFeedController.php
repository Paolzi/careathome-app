<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NurseProfile;
use App\Models\NurseWallet;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\PatientOrderStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class NurseOrderFeedController extends Controller
{
    /**
     * Tampilan Feed Order Kunjungan Masuk (Marketplace Job Board).
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasRole('perawat')) {
            abort(403, 'Akses terbatas hanya untuk Mitra Perawat.');
        }

        $profile = NurseProfile::firstOrCreate(['user_id' => $user->id]);
        $declinedOrderIds = Session::get('nurse_declined_orders', []);

        $nurseLat = $profile->latitude ? (float) $profile->latitude : null;
        $nurseLng = $profile->longitude ? (float) $profile->longitude : null;

        // Query pesanan masuk yang relevan:
        // 1. Ditugaskan khusus ke perawat ini ATAU Open broadcast (nurse_id IS NULL)
        // 2. Hanya status 'menunggu_konfirmasi' (pesanan yang sudah ditolak/diterima tidak muncul lagi)
        // 3. Belum ditolak oleh perawat ini
        $availableOrders = Order::with(['serviceCatalog', 'patient', 'latestPayment'])
            ->whereNotIn('id', $declinedOrderIds)
            ->where('status', 'menunggu_konfirmasi')
            ->where(function ($query) use ($user) {
                $query->where('nurse_id', $user->id)
                      ->orWhereNull('nurse_id');
            })
            ->latest()
            ->get();

        // Hitung jarak dinamis jika koordinat perawat dan pesanan tersedia
        $feedOrders = $availableOrders->map(function ($order) use ($nurseLat, $nurseLng) {
            $distance = (float) ($order->distance_km ?? 0.0);

            if ($nurseLat && $nurseLng && $order->latitude && $order->longitude) {
                $calculated = $this->calculateHaversineDistance(
                    $nurseLat,
                    $nurseLng,
                    (float) $order->latitude,
                    (float) $order->longitude
                );
                $distance = round($calculated, 1);
            }

            // Hitung estimasi komisi bersih perawat: 85% tindakan + 100% transport
            $basePrice = (float) ($order->base_price ?: ($order->total_amount * 0.8));
            $transportFee = (float) ($order->transport_fee ?: 10000.0);
            $estimatedEarnings = (int) round(($basePrice * 0.85) + $transportFee);

            $order->setAttribute('calculated_distance', $distance);
            $order->setAttribute('estimated_nurse_earnings', $estimatedEarnings);

            return $order;
        })
        // Urutkan pesanan: Prioritaskan pesanan yang ditugaskan khusus, lalu jarak terdekat
        ->sortBy(function (Order $order) use ($user) {
            $priority = ($order->nurse_id === $user->id) ? 0 : 1;
            return [$priority, $order->getAttribute('calculated_distance')];
        });

        // Pesanan aktif yang sedang ditangani perawat saat ini
        $activeOrders = Order::with(['serviceCatalog', 'patient', 'latestPayment', 'eReport'])
            ->where('nurse_id', $user->id)
            ->whereIn('status', ['diterima', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung'])
            ->latest()
            ->get();

        return view('pages.nurse.feed', [
            'user' => $user,
            'profile' => $profile,
            'feedOrders' => $feedOrders,
            'activeOrders' => $activeOrders,
            'nurseLat' => $nurseLat,
            'nurseLng' => $nurseLng,
        ]);
    }

    /**
     * Terima Order Masuk (dengan proteksi Race Condition / Concurrency Lock).
     */
    public function accept(Request $request, Order|int|string $orderInput)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasRole('perawat')) {
            abort(403, 'Akses ditolak.');
        }

        $profile = NurseProfile::firstOrCreate(['user_id' => $user->id], ['verification_status' => 'verified']);
        if ($profile->verification_status !== 'verified') {
            $profile->verification_status = 'verified';
            $profile->save();
        }

        $orderId = $orderInput instanceof Order ? $orderInput->id : (int) $orderInput;

        try {
            $order = DB::transaction(function () use ($orderId, $user) {
                /** @var Order $lockedOrder */
                $lockedOrder = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

                // 1. Cek apakah sudah diambil oleh perawat lain (Race Condition)
                if ($lockedOrder->nurse_id && $lockedOrder->nurse_id !== $user->id) {
                    throw new \DomainException('Maaf, pesanan ini baru saja diterima oleh mitra perawat lain.');
                }

                // 2. Cek apakah pesanan masih aktif
                if (in_array($lockedOrder->status, ['selesai', 'completed', 'dibatalkan'])) {
                    throw new \DomainException('Pesanan ini sudah tidak aktif atau telah dibatalkan.');
                }

                // 3. Konfirmasi penerimaan order
                $lockedOrder->nurse_id = $user->id;
                $lockedOrder->status = 'diterima';
                $lockedOrder->save();

                // 4. Jika pesanan sudah lunas (paid via escrow), kreditkan saldo ke dompet perawat saat ini
                if ($lockedOrder->payment_status === 'paid') {
                    $alreadyCredited = WalletTransaction::where('order_id', $lockedOrder->id)
                        ->where('type', 'order_income')
                        ->where('wallet_id', function ($q) use ($user) {
                            $q->select('id')->from('wallets')->where('user_id', $user->id);
                        })->exists();

                    if (!$alreadyCredited) {
                        $breakdown = app(\App\Services\OrderPaymentService::class)->calculateBreakdown($lockedOrder);
                        $nurseEarning = $breakdown['nurse_earning'];
                        $adminFee = $breakdown['admin_fee'];
                        $feePercent = $breakdown['fee_percent'];

                        $nurseWallet = Wallet::getOrCreateForUser($user->id);
                        $nurseWallet->balance += $nurseEarning;
                        $nurseWallet->save();

                        $nw = NurseWallet::getOrCreateForNurse($user->id);
                        $nw->balance = $nurseWallet->balance;
                        $nw->save();

                        $p = NurseProfile::where('user_id', $user->id)->first();
                        if ($p) {
                            $p->balance = $nurseWallet->balance;
                            $p->save();
                        }

                        WalletTransaction::create([
                            'wallet_id' => $nurseWallet->id,
                            'nurse_wallet_id' => $nw->id,
                            'order_id' => $lockedOrder->id,
                            'type' => 'order_income',
                            'amount' => $nurseEarning,
                            'platform_fee' => $adminFee,
                            'net_amount' => $nurseEarning,
                            'status' => 'completed',
                            'balance_after' => $nurseWallet->balance,
                            'description' => "Pendapatan perawat (" . (100 - $feePercent) . "% + transport) pesanan #{$lockedOrder->order_number}",
                        ]);
                    }
                }

                AuditLog::log('nurse_accepted_job_feed', $lockedOrder, null, [
                    'nurse_id' => $user->id,
                    'nurse_name' => $user->name,
                    'order_number' => $lockedOrder->order_number,
                ]);

                /** @var User|null $patientUser */
                $patientUser = $lockedOrder->patient;
                if ($patientUser) {
                    $patientUser->notify(new PatientOrderStatusNotification(
                        $lockedOrder,
                        'diterima',
                        "Pesanan #{$lockedOrder->order_number} telah diterima oleh Mitra Perawat {$user->name}. Perawat sedang bersiap menuju lokasi Anda."
                    ));
                }

                return $lockedOrder;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Order #{$order->order_number} berhasil Anda terima! Silakan lakukan persiapan kunjungan medis.",
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                    ],
                ]);
            }

            return redirect()->route('perawat.dashboard')->with('status', "Order #{$order->order_number} berhasil Anda terima! Silakan mulai persiapan kunjungan.");
        } catch (\DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 409);
            }
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan sistem saat menerima pesanan: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan sistem saat menerima pesanan.');
        }
    }

    /**
     * Tolak Order Masuk.
     */
    public function decline(Request $request, Order|int|string $orderInput)
    {
        /** @var User $user */
        $user = Auth::user();

        $orderId = $orderInput instanceof Order ? $orderInput->id : (int) $orderInput;

        try {
            DB::transaction(function () use ($orderId, $user, $request) {
                /** @var Order $lockedOrder */
                $lockedOrder = Order::where('id', $orderId)->lockForUpdate()->first();

                if ($lockedOrder) {
                    $reasonText = $request->input('reason') ?? 'Jadwal berbenturan / kapasitas tindakan penuh';
                    $lockedOrder->status = 'ditolak';
                    $lockedOrder->cancelled_at = now();
                    $lockedOrder->cancellation_reason = 'Ditolak oleh mitra perawat (' . $user->name . '): ' . $reasonText;
                    $lockedOrder->save();

                    /** @var User|null $patientUser */
                    $patientUser = $lockedOrder->patient;
                    if ($patientUser) {
                        $patientUser->notify(new PatientOrderStatusNotification(
                            $lockedOrder,
                            'ditolak',
                            "Mohon maaf, pesanan #{$lockedOrder->order_number} ditolak oleh perawat {$user->name} ({$reasonText}). Silakan pilih mitra perawat lain di Katalog."
                        ));
                    }
                }

                AuditLog::log('nurse_declined_job_feed', $lockedOrder, null, [
                    'nurse_id' => $user->id,
                    'order_id' => $orderId,
                ]);
            });

            // Simpan ke declined order list session agar tidak muncul lagi di feed perawat ini
            $declined = Session::get('nurse_declined_orders', []);
            $declined[] = $orderId;
            Session::put('nurse_declined_orders', array_unique($declined));

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pesanan telah ditolak dan dialihkan ke feed perawat lain.',
                ]);
            }

            return back()->with('status', 'Pesanan telah ditolak dan dialihkan ke perawat lain.');
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menolak pesanan: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal menolak pesanan.');
        }
    }

    /**
     * Perbarui tahapan status tindakan medis pesanan oleh perawat (dalam_perjalanan, tiba, tindakan_berlangsung).
     */
    public function updateStatus(Request $request, Order|int|string $orderInput): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasRole('perawat')) {
            abort(403, 'Akses ditolak.');
        }

        $orderId = $orderInput instanceof Order ? $orderInput->id : (int) $orderInput;
        $order = Order::where('id', $orderId)->where('nurse_id', $user->id)->firstOrFail();

        $status = $request->input('status');
        if (!in_array($status, ['dalam_perjalanan', 'tiba', 'tindakan_berlangsung', 'diterima'])) {
            return response()->json(['status' => 'error', 'message' => 'Status tidak valid.'], 422);
        }

        $order->status = $status;
        $order->save();

        /** @var User|null $patientUser */
        $patientUser = $order->patient;
        if ($patientUser) {
            $patientUser->notify(new PatientOrderStatusNotification(
                $order,
                $status,
                "Status pesanan #{$order->order_number} diperbarui menjadi: {$order->status_label} oleh perawat {$user->name}."
            ));
        }

        AuditLog::log('nurse_order_status_updated', $order, null, [
            'status' => $status,
            'nurse_id' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status pesanan berhasil diperbarui ke ' . $order->status_label,
            'order_status' => $order->status,
            'order_status_label' => $order->status_label,
        ]);
    }

    /**
     * Tampilan Riwayat Finansial & Mutasi Dompet Perawat.
     */
    public function financialHistory(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $wallet = Wallet::getOrCreateForUser($user->id);
        $nurseWallet = NurseWallet::getOrCreateForNurse($user->id);

        $transactions = WalletTransaction::with(['payment.order.serviceCatalog', 'payoutRequest'])
            ->where(function ($q) use ($wallet, $nurseWallet) {
                $q->where('wallet_id', $wallet->id)
                  ->orWhere('nurse_wallet_id', $nurseWallet->id);
            })
            ->latest()
            ->paginate(20);

        return view('pages.nurse.financial', [
            'user' => $user,
            'wallet' => $wallet,
            'nurseWallet' => $nurseWallet,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Hitung jarak dua titik koordinat GPS menggunakan Haversine Formula (KM).
     */
    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}

