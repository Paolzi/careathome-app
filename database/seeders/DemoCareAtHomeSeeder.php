<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\NurseProfile;
use App\Models\NurseVerification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\ServiceCatalog;
use App\Models\ServiceTariff;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCareAtHomeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Inisialisasi Settings
        Setting::updateOrCreate(['key' => 'platform_fee_percent'], ['value' => '15.00']);
        Setting::updateOrCreate(['key' => 'max_radius_km'], ['value' => '10.00']);
        Setting::updateOrCreate(['key' => 'location_timeout_minutes'], ['value' => '15']);

        // 2. Akun Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@careathome.com'],
            [
                'name' => 'Administrator CareAtHome',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $admin->syncRoles(['admin']);

        // Wallet Platform Admin
        $adminWallet = Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            ['balance' => 0]
        );

        // 3. Pasien Demo (Jakarta Pusat: Monas / Menteng)
        $pasien = User::firstOrCreate(
            ['email' => 'pasien@careathome.com'],
            [
                'name' => 'Ahmad Fauzi (Pasien Demo)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $pasien->syncRoles(['pasien']);

        // 4. Sinkronisasi Layanan & Tarif
        $services = ServiceCatalog::all();
        foreach ($services as $service) {
            ServiceTariff::firstOrCreate(
                [
                    'service_id' => $service->id,
                    'price' => $service->price ?? 150000,
                ],
                [
                    'effective_from' => now()->subMonths(1),
                    'created_by' => $admin->id,
                ]
            );
        }
        $serviceIds = $services->pluck('id')->toArray();

        // 5. Perawat 1: Verified, Online, Dekat (~1.4 km) - Siti Nurhaliza
        $perawat1 = User::firstOrCreate(
            ['email' => 'perawat@careathome.com'],
            [
                'name' => 'Ns. Siti Nurhaliza, S.Kep',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $perawat1->syncRoles(['perawat']);

        $profile1 = NurseProfile::updateOrCreate(
            ['user_id' => $perawat1->id],
            [
                'is_online' => true,
                'latitude' => -6.2200000, // Sekitar Kuningan / Setiabudi
                'longitude' => 106.8400000,
                'location_updated_at' => now(),
                'verification_status' => 'verified',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subWeeks(2),
                'str_number' => 'STR-12345678-2026',
                'str_expired_at' => now()->addYears(2),
                'sip_number' => 'SIP-98765432-2026',
                'sip_expired_at' => now()->addYears(2),
            ]
        );
        // Sediakan 4 layanan pertama
        $profile1->services()->sync(array_slice($serviceIds, 0, 4));

        $wallet1 = Wallet::firstOrCreate(
            ['user_id' => $perawat1->id],
            ['balance' => 0]
        );

        // 6. Perawat 2: Verified, Online, Sedang (~4.8 km) - Budi Pratama
        $perawat2 = User::firstOrCreate(
            ['email' => 'perawat2@careathome.com'],
            [
                'name' => 'Ns. Budi Pratama, S.Kep',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $perawat2->syncRoles(['perawat']);

        $profile2 = NurseProfile::updateOrCreate(
            ['user_id' => $perawat2->id],
            [
                'is_online' => true,
                'latitude' => -6.2500000, // Sekitar Tebet / Pancoran
                'longitude' => 106.8300000,
                'location_updated_at' => now(),
                'verification_status' => 'verified',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subWeeks(1),
                'str_number' => 'STR-87654321-2027',
                'str_expired_at' => now()->addYears(3),
                'sip_number' => 'SIP-56781234-2027',
                'sip_expired_at' => now()->addYears(3),
            ]
        );
        $profile2->services()->sync(array_slice($serviceIds, 2, 5));

        $wallet2 = Wallet::firstOrCreate(
            ['user_id' => $perawat2->id],
            ['balance' => 0]
        );

        // 7. Perawat 3: Jauh (> 10 km, Bogor ~43 km) - Dewi Farida
        $perawat3 = User::firstOrCreate(
            ['email' => 'perawat_far@careathome.com'],
            [
                'name' => 'Ns. Dewi Farida, S.Kep (Bogor)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $perawat3->syncRoles(['perawat']);

        $profile3 = NurseProfile::updateOrCreate(
            ['user_id' => $perawat3->id],
            [
                'is_online' => true,
                'latitude' => -6.5950000, // Kota Bogor
                'longitude' => 106.8166000,
                'location_updated_at' => now(),
                'verification_status' => 'verified',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subDays(5),
                'str_number' => 'STR-11223344-2028',
                'str_expired_at' => now()->addYears(4),
                'sip_number' => 'SIP-44332211-2028',
                'sip_expired_at' => now()->addYears(4),
            ]
        );
        $profile3->services()->sync($serviceIds);
        Wallet::firstOrCreate(['user_id' => $perawat3->id], ['balance' => 0]);

        // 8. Perawat 4: Offline (Dekat tapi Sedang Tidak Aktif Bekerja) - Rian Hidayat
        $perawat4 = User::firstOrCreate(
            ['email' => 'perawat_offline@careathome.com'],
            [
                'name' => 'Ns. Rian Hidayat, S.Kep (Offline)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $perawat4->syncRoles(['perawat']);

        $profile4 = NurseProfile::updateOrCreate(
            ['user_id' => $perawat4->id],
            [
                'is_online' => false, // OFFLINE
                'latitude' => -6.2100000,
                'longitude' => 106.8450000,
                'location_updated_at' => now()->subHours(1),
                'verification_status' => 'verified',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subDays(10),
            ]
        );
        $profile4->services()->sync(array_slice($serviceIds, 0, 3));
        Wallet::firstOrCreate(['user_id' => $perawat4->id], ['balance' => 0]);

        // 9. Perawat 5: Pending Verification (Untuk Demo SCRUM-55 Audit Modal) - Maya Indah
        $perawat5 = User::firstOrCreate(
            ['email' => 'perawat_pending@careathome.com'],
            [
                'name' => 'Ns. Maya Indah, S.Kep (Pending Verifikasi)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $perawat5->syncRoles(['perawat']);

        $profile5 = NurseProfile::updateOrCreate(
            ['user_id' => $perawat5->id],
            [
                'is_online' => false,
                'verification_status' => 'pending',
                'str_number' => 'STR-99887766-2025',
                'str_expired_at' => now()->addYears(1),
                'sip_number' => 'SIP-66778899-2025',
                'sip_expired_at' => now()->addYears(1),
            ]
        );

        foreach (['ktp', 'str', 'sip', 'ijazah'] as $docType) {
            \App\Models\NurseDocument::updateOrCreate(
                [
                    'nurse_profile_id' => $profile5->id,
                    'document_type' => $docType,
                ],
                [
                    'file_path' => "nurse_documents/{$perawat5->id}/{$docType}_sample.pdf",
                    'original_name' => "dokumen_{$docType}.pdf",
                    'file_size' => '250 KB',
                    'mime_type' => 'application/pdf',
                    'status' => 'pending',
                ]
            );
        }
        Wallet::firstOrCreate(['user_id' => $perawat5->id], ['balance' => 0]);

        // 10. Sample Pesanan Selesai & Pembayaran (15% Admin, 85% Perawat)
        // Hitung: Total 100.000 -> Admin 15.000, Perawat 85.000
        $order = Order::firstOrCreate(
            ['order_number' => 'ORD-DEMO-001'],
            [
                'patient_id' => $pasien->id,
                'nurse_id' => $perawat1->id,
                'service_catalog_id' => $services->first()->id ?? 1,
                'scheduled_date' => now()->subDays(1)->format('Y-m-d'),
                'scheduled_time' => '09:00 - 10:00',
                'patient_name' => $pasien->name,
                'patient_phone' => '081234567890',
                'address' => 'Jl. MH Thamrin No. 1, Jakarta Pusat',
                'latitude' => -6.2088000,
                'longitude' => 106.8456000,
                'patient_latitude' => -6.2088000,
                'patient_longitude' => 106.8456000,
                'distance_km' => 1.42,
                'base_price' => 80000,
                'transport_fee' => 15000,
                'platform_fee' => 5000,
                'total_amount' => 100000,
                'commission_amount' => 15000,
                'nurse_earnings' => 85000,
                'status' => 'selesai',
                'payment_status' => 'paid',
            ]
        );

        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'order_number' => $order->order_number,
                'transaction_id' => 'PAY-DEMO-001',
                'gross_amount' => 100000,
                'total_amount' => 100000,
                'admin_fee' => 15000,
                'nurse_earning' => 85000,
                'fee_percent' => 15.00,
                'payment_method' => 'direct_payment',
                'payment_type' => 'direct',
                'payment_status' => 'paid',
                'status' => 'paid',
                'escrow_status' => 'held',
                'paid_at' => now()->subDays(1),
            ]
        );

        // Kreditkan saldo wallet sesuai ledger jika belum ada transaksi
        if (WalletTransaction::where('payment_id', $payment->id)->count() === 0) {
            // Kredit Perawat 1 (85.000)
            $wallet1->balance += 85000;
            $wallet1->save();
            WalletTransaction::create([
                'wallet_id' => $wallet1->id,
                'type' => 'order_income',
                'amount' => 85000,
                'balance_after' => $wallet1->balance,
                'payment_id' => $payment->id,
                'description' => "Pendapatan layanan pesanan #{$order->order_number} (Bagian Mitra 85%)",
            ]);

            // Kredit Platform Admin (15.000)
            $adminWallet->balance += 15000;
            $adminWallet->save();
            WalletTransaction::create([
                'wallet_id' => $adminWallet->id,
                'type' => 'order_income',
                'amount' => 15000,
                'balance_after' => $adminWallet->balance,
                'payment_id' => $payment->id,
                'description' => "Bagi hasil platform fee 15% dari pesanan #{$order->order_number}",
            ]);
        }

        // 11. Sample Payout Request untuk Demo Payout [SCRUM-59]
        // Perawat 1 menarik Rp 50.000 (status pending)
        if (PayoutRequest::where('nurse_user_id', $perawat1->id)->count() === 0 && $wallet1->balance >= 50000) {
            $holdAmount = 50000;
            $wallet1->balance -= $holdAmount;
            $wallet1->save();

            $payout = PayoutRequest::create([
                'nurse_user_id' => $perawat1->id,
                'amount' => $holdAmount,
                'bank_name' => 'BCA',
                'account_number' => '8830123456',
                'account_holder' => 'Siti Nurhaliza',
                'status' => 'pending',
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet1->id,
                'type' => 'payout_hold',
                'amount' => $holdAmount,
                'balance_after' => $wallet1->balance,
                'payout_request_id' => $payout->id,
                'description' => "Penahanan saldo untuk permohonan penarikan dana #PO-{$payout->id}",
            ]);
        }

        // Catat audit log demo
        AuditLog::log($admin->id, 'demo_seeder_initialized', Setting::class, null, null, ['status' => 'success']);
    }
}
