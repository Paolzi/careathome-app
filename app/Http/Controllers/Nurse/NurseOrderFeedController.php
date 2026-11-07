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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $declinedOrderIds = session()->get('nurse_declined_orders', []);

        $nurseLat = $profile->latitude ? (float) $profile->latitude : null;
        $nurseLng = $profile->longitude ? (float) $profile->longitude : null;

        // Query pesanan masuk yang relevan:
        // 1. Ditugaskan khusus ke perawat ini ATAU Open broadcast (nurse_id IS NULL)
        // 2. Status 'menunggu_konfirmasi' atau 'diterima' yang belum dikerjakan
        // 3. Belum ditolak oleh perawat ini
        $availableOrders = Order::with(['serviceCatalog', 'patient', 'latestPayment'])
            ->whereNotIn('id', $declinedOrderIds)
            ->whereNotIn('status', ['selesai', 'completed', 'dibatalkan', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung'])
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

            $order->calculated_distance = $distance;
            $order->estimated_nurse_earnings = $estimatedEarnings;

            return $order;
        })
        // Urutkan pesanan: Prioritaskan pesanan yang ditugaskan khusus, lalu jarak terdekat
        ->sortBy(function ($order) use ($user) {
            $priority = ($order->nurse_id === $user->id) ? 0 : 1;
            return [$priority, $order->calculated_distance];
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
    public function accept(Request $request, $orderInput)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasRole('perawat')) {
            abort(403, 'Akses ditolak.');
        }

        $profile = NurseProfile::where('user_id', $user->id)->first();
        if (!$profile || $profile->verification_status !== 'verified') {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda harus berstatus Terverifikasi (STR & SIP tervalidasi) untuk menerima order medis.',
                ], 422);
            }
            return back()->with('error', 'Anda harus berstatus Terverifikasi untuk menerima order medis.');
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

                AuditLog::log('nurse_accepted_job_feed', $lockedOrder, null, [
                    'nurse_id' => $user->id,
                    'nurse_name' => $user->name,
                    'order_number' => $lockedOrder->order_number,
                ]);

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
    public function decline(Request $request, $orderInput)
    {
        /** @var User $user */
        $user = Auth::user();

        $orderId = $orderInput instanceof Order ? $orderInput->id : (int) $orderInput;

        try {
            DB::transaction(function () use ($orderId, $user) {
                /** @var Order $lockedOrder */
                $lockedOrder = Order::where('id', $orderId)->lockForUpdate()->first();

                if ($lockedOrder && $lockedOrder->nurse_id === $user->id) {
                    // Jika order sebelumnya ditugaskan ke perawat ini, kembalikan ke broadcast pool terbuka
                    $lockedOrder->nurse_id = null;
                    $lockedOrder->status = 'menunggu_konfirmasi';
                    $lockedOrder->save();
                }

                AuditLog::log('nurse_declined_job_feed', $lockedOrder, null, [
                    'nurse_id' => $user->id,
                    'order_id' => $orderId,
                ]);
            });

            // Simpan ke declined order list session agar tidak muncul lagi di feed perawat ini
            $declined = session()->get('nurse_declined_orders', []);
            $declined[] = $orderId;
            session()->put('nurse_declined_orders', array_unique($declined));

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

