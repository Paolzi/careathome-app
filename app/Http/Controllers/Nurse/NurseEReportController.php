<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EReport;
use App\Models\EReportPhoto;
use App\Models\NurseProfile;
use App\Models\NurseWallet;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\PatientOrderStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NurseEReportController extends Controller
{
    /**
     * Tampilkan formulir atau data E-Report untuk pesanan tertentu.
     */
    public function show(Request $request, Order $order)
    {
        /** @var User $user */
        $user = Auth::user();

        // Validasi akses: Hanya perawat yang ditugaskan atau admin
        if ($order->nurse_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki hak akses untuk pesanan ini.');
        }

        $order->load(['patient', 'serviceCatalog', 'eReport.photos']);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'order' => $order,
                'e_report' => $order->eReport,
            ]);
        }

        return view('pages.nurse.ereport-form', [
            'order' => $order,
            'eReport' => $order->eReport,
        ]);
    }

    /**
     * Simpan Lembar E-Report Klinis, selesaikan pesanan, dan jalankan Automated Settlement.
     */
    public function store(Request $request, Order $order)
    {
        /** @var User $user */
        $user = Auth::user();

        // Validasi perawat pemilik order
        if ($order->nurse_id !== $user->id && !$user->hasRole('admin')) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk pesanan ini.');
        }

        // Cek jika pesanan sudah selesai atau sudah ada E-Report
        if ($order->eReport) {
            return back()->with('error', 'E-Report untuk pesanan ini sudah pernah diisi sebelumnya.');
        }

        $validated = $request->validate([
            'systolic' => ['required', 'integer', 'between:50,300'],
            'diastolic' => ['required', 'integer', 'between:30,200'],
            'body_temp' => ['required', 'numeric', 'between:30.0,45.0'],
            'pulse_rate' => ['required', 'integer', 'between:30,250'],
            'spo2' => ['required', 'integer', 'between:50,100'],
            'gds' => ['nullable', 'integer', 'between:20,800'],
            'clinical_notes' => ['required', 'string', 'min:2'],
            'family_education' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'captions' => ['nullable', 'array'],
            'captions.*' => ['nullable', 'string', 'max:255'],
        ], [
            'systolic.required' => 'Nilai Tekanan Darah Sistolik wajib diisi.',
            'diastolic.required' => 'Nilai Tekanan Darah Diastolik wajib diisi.',
            'body_temp.required' => 'Suhu tubuh pasien wajib diisi.',
            'pulse_rate.required' => 'Denyut nadi pasien wajib diisi.',
            'spo2.required' => 'Saturasi oksigen (SpO2) wajib diisi.',
            'clinical_notes.required' => 'Ringkasan asuhan keperawatan / catatan klinis wajib diisi.',
            'clinical_notes.min' => 'Catatan klinis minimal berisi 2 karakter.',
        ]);

        try {
            DB::transaction(function () use ($order, $user, $validated, $request) {
                // 1. Simpan Lembar E-Report
                $eReport = EReport::create([
                    'order_id' => $order->id,
                    'nurse_id' => $order->nurse_id ?? $user->id,
                    'patient_id' => $order->patient_id,
                    'systolic' => (int) $validated['systolic'],
                    'diastolic' => (int) $validated['diastolic'],
                    'body_temp' => (float) $validated['body_temp'],
                    'pulse_rate' => (int) $validated['pulse_rate'],
                    'spo2' => (int) $validated['spo2'],
                    'gds' => !empty($validated['gds']) ? (int) $validated['gds'] : null,
                    'clinical_notes' => $validated['clinical_notes'],
                    'family_education' => $validated['family_education'] ?? null,
                ]);

                // 2. Simpan Foto Dokumentasi Tindakan
                if ($request->hasFile('photos')) {
                    $captions = $request->input('captions', []);
                    foreach ($request->file('photos') as $idx => $photo) {
                        $storedPath = $photo->store("documents/ereports/{$order->id}", 'public');
                        $captionText = $captions[$idx] ?? ('Dokumentasi Tindakan ' . ($idx + 1));

                        EReportPhoto::create([
                            'e_report_id' => $eReport->id,
                            'photo_path' => $storedPath,
                            'caption' => $captionText,
                        ]);
                    }
                }

                // 3. Ubah status pesanan secara otomatis menjadi 'selesai'
                $order->status = 'selesai';
                if ($order->payment_status !== 'paid') {
                    $order->payment_status = 'paid';
                }
                $order->save();

                // 4. Automated Settlement Pembagian Pendapatan (Cek agar tidak double credit jika sudah dibayar di muka)
                $basePrice = (float) ($order->base_price ?: ($order->total_amount * 0.8));
                $transportFee = (float) ($order->transport_fee ?: 10000);
                $platformFee = round($basePrice * 0.15); // 15% dari biaya tindakan
                $nurseEarnings = round(($basePrice * 0.85) + $transportFee); // 85% tindakan + 100% transport

                $nurseId = $order->nurse_id ?? $user->id;
                $wallet = Wallet::getOrCreateForUser($nurseId);
                $nurseWallet = NurseWallet::getOrCreateForNurse($nurseId);

                $alreadyCredited = WalletTransaction::where('order_id', $order->id)
                    ->whereIn('type', ['income', 'order_income'])
                    ->where(function ($q) use ($wallet, $nurseWallet) {
                        $q->where('wallet_id', $wallet->id)
                          ->orWhere('nurse_wallet_id', $nurseWallet->id);
                    })
                    ->exists();

                if (!$alreadyCredited) {
                    // Update Dompet Khusus Perawat (nurse_wallets)
                    $nurseWallet->balance += (int) $nurseEarnings;
                    $nurseWallet->save();

                    // Sinkronisasi ke model Wallet universal
                    $wallet->balance = $nurseWallet->balance;
                    $wallet->save();

                    // Sinkronisasi ke profil perawat
                    $profile = NurseProfile::where('user_id', $nurseId)->first();
                    if ($profile) {
                        $profile->balance = $nurseWallet->balance;
                        $profile->save();
                    }

                    // Simpan detail riwayat transaksi ke wallet_transactions
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'nurse_wallet_id' => $nurseWallet->id,
                        'order_id' => $order->id,
                        'type' => 'order_income',
                        'amount' => (int) $order->total_amount,
                        'platform_fee' => (int) $platformFee,
                        'net_amount' => (int) $nurseEarnings,
                        'balance_after' => $wallet->balance,
                        'status' => 'completed',
                        'description' => "Pendapatan layanan #{$order->order_number} ({$order->serviceCatalog?->name})",
                    ]);

                    // Kreditkan juga ke dompet Admin jika belum ada
                    $adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
                    if ($adminUser) {
                        $adminWallet = Wallet::getOrCreateForUser($adminUser->id);
                        $adminWallet->balance += (int) $platformFee;
                        $adminWallet->save();

                        WalletTransaction::create([
                            'wallet_id' => $adminWallet->id,
                            'order_id' => $order->id,
                            'type' => 'admin_fee',
                            'amount' => (int) $platformFee,
                            'platform_fee' => (int) $platformFee,
                            'net_amount' => (int) $platformFee,
                            'balance_after' => $adminWallet->balance,
                            'status' => 'completed',
                            'description' => "Komisi platform (15%) pesanan #{$order->order_number}",
                        ]);
                    }
                }

                /** @var User|null $patientUser */
                $patientUser = $order->patient;
                if ($patientUser) {
                    $patientUser->notify(new PatientOrderStatusNotification(
                        $order,
                        'selesai',
                        "E-Report Klinis & Tanda Vital (TTV) untuk pesanan #{$order->order_number} telah diterbitkan oleh perawat {$user->name}. Anda kini dapat melihat hasil pemeriksaan medis di Riwayat Layanan."
                    ));
                }

                AuditLog::log('nurse_ereport_submitted', $order, null, [
                    'order_id' => $order->id,
                    'e_report_id' => $eReport->id,
                    'net_amount' => $nurseEarnings,
                    'platform_fee' => $platformFee,
                ]);
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'E-Report klinis berhasil disimpan. Pesanan dinyatakan selesai dan saldo pendapatan telah ditambahkan ke dompet Anda.',
                ]);
            }

            return redirect()->route('perawat.dashboard')->with('status', 'E-Report klinis berhasil disimpan. Pesanan dinyatakan selesai dan saldo pendapatan telah berhasil masuk ke dompet Anda.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses E-Report: ' . $e->getMessage())->withInput();
        }
    }
}

