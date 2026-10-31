<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NurseProfile;
use App\Models\PayoutRequest;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NurseDashboardController extends Controller
{
    /**
     * Halaman Dashboard Utama Mitra Perawat.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $profile = NurseProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'verification_status' => 'draft',
                'is_online' => false,
                'balance' => 0,
            ]
        );

        $wallet = Wallet::getOrCreateForUser($user->id);

        $services = ServiceCatalog::active()->get();
        $selectedServiceIds = $profile->services()->pluck('service_catalogs.id')->toArray();

        $recentTransactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->latest()
            ->take(10)
            ->get();

        $payoutRequests = PayoutRequest::where('nurse_user_id', $user->id)
            ->latest()
            ->get();

        return view('pages.nurse.dashboard', [
            'user' => $user,
            'profile' => $profile,
            'wallet' => $wallet,
            'services' => $services,
            'selectedServiceIds' => $selectedServiceIds,
            'recentTransactions' => $recentTransactions,
            'payoutRequests' => $payoutRequests,
        ]);
    }

    /**
     * Toggle status online / offline perawat.
     * Validasi: Hanya perawat dengan status verifikasi 'verified' yang boleh ON.
     */
    public function toggleOnline(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isSuspended()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda sedang disuspensi. Tidak dapat mengaktifkan status kerja.',
            ], 403);
        }

        $profile = NurseProfile::firstOrCreate(['user_id' => $user->id]);

        $request->validate([
            'is_online' => ['required', 'boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $desiredStatus = (bool) $request->input('is_online');

        if ($desiredStatus) {
            if ($profile->verification_status !== 'verified') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status kerja hanya dapat diaktifkan jika dokumen STR & SIP Anda telah berstatus Terverifikasi (Verified) oleh Admin.',
                ], 422);
            }

            $profile->is_online = true;
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $profile->latitude = (float) $request->input('latitude');
                $profile->longitude = (float) $request->input('longitude');
                $profile->location_updated_at = now();
            }
        } else {
            $profile->is_online = false;
        }

        $profile->save();

        AuditLog::log('nurse_toggle_online', $profile, null, [
            'is_online' => $profile->is_online,
            'latitude' => $profile->latitude,
            'longitude' => $profile->longitude,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $profile->is_online ? 'Status kerja berhasil diaktifkan. Anda kini muncul di katalog pasien dalam radius 10 km.' : 'Status kerja dinonaktifkan (Offline).',
            'data' => [
                'is_online' => $profile->is_online,
                'location_updated_at' => $profile->location_updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Endpoint update koordinat lokasi perawat (Heartbeat Geolocation).
     * Maksimal dipanggil tiap 30 detik dari frontend.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $profile = NurseProfile::where('user_id', $user->id)->first();
        if (!$profile) {
            return response()->json(['status' => 'error', 'message' => 'Profil perawat tidak ditemukan.'], 404);
        }

        if (!$profile->is_online || $profile->verification_status !== 'verified') {
            return response()->json([
                'status' => 'error',
                'message' => 'Update lokasi hanya berlaku saat perawat berstatus aktif bekerja (Online & Verified).',
            ], 422);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $profile->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'location_updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Koordinat lokasi berhasil diperbarui.',
            'data' => [
                'latitude' => $profile->latitude,
                'longitude' => $profile->longitude,
                'location_updated_at' => $profile->location_updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Perbarui pilihan layanan medis yang disediakan perawat.
     */
    public function updateServices(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $profile = NurseProfile::firstOrCreate(['user_id' => $user->id]);

        $validated = $request->validate([
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['exists:service_catalogs,id'],
        ]);

        $profile->services()->sync($validated['service_ids'] ?? []);

        return back()->with('status', 'Daftar layanan yang Anda sediakan berhasil diperbarui.');
    }

    /**
     * Permintaan penarikan saldo (Tarik Saldo) oleh perawat.
     */
    public function requestPayout(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:10000'],
            'bank_name' => ['required', 'string', 'max:50'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:100'],
        ], [
            'amount.min' => 'Nominal penarikan saldo minimal Rp 10.000.',
            'bank_name.required' => 'Nama bank tujuan wajib diisi.',
            'account_number.required' => 'Nomor rekening tujuan wajib diisi.',
            'account_holder.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        $amount = (int) $validated['amount'];

        try {
            DB::transaction(function () use ($user, $validated, $amount) {
                /** @var Wallet $wallet */
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

                if ($wallet->balance < $amount) {
                    throw new \DomainException('Saldo tersedia Anda (Rp ' . number_format($wallet->balance, 0, ',', '.') . ') tidak mencukupi untuk penarikan sebesar Rp ' . number_format($amount, 0, ',', '.') . '.');
                }

                // Kurangi saldo (ditahan)
                $wallet->balance -= $amount;
                $wallet->save();

                // Buat record payout_request
                $payout = PayoutRequest::create([
                    'nurse_user_id' => $user->id,
                    'amount' => $amount,
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'account_holder' => $validated['account_holder'],
                    'status' => 'pending',
                ]);

                // Catat transaksi wallet_transactions dengan tipe payout_hold
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'payout_hold',
                    'amount' => -$amount,
                    'balance_after' => $wallet->balance,
                    'payout_request_id' => $payout->id,
                    'description' => "Penarikan saldo (Hold) ke rekening {$payout->bank_name} {$payout->account_number} a.n {$payout->account_holder}",
                ]);

                AuditLog::log('nurse_payout_requested', $payout, null, [
                    'amount' => $amount,
                    'bank' => $payout->bank_name,
                    'account' => $payout->account_number,
                ]);
            });

            return back()->with('status', 'Pengajuan penarikan dana sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil diajukan dan sedang menunggu otorisasi transfer admin.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses penarikan: ' . $e->getMessage())->withInput();
        }
    }
}
