<?php

namespace Tests\Feature;

use App\Models\NurseProfile;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\ServiceCatalog;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\OrderPaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CareAtHomeFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\ServiceCatalogSeeder::class);
        Setting::updateOrCreate(['key' => 'platform_fee_percent'], ['value' => '15.00']);
        Setting::updateOrCreate(['key' => 'max_radius_km'], ['value' => '10.00']);
        Setting::updateOrCreate(['key' => 'location_timeout_minutes'], ['value' => '15']);
    }

    /**
     * 1. Test Pembagian 15% Platform dan 85% Perawat:
     * Total 100.000 -> Admin 15.000, Perawat 85.000. Saldo dompet & riwayat transaksi bertambah akurat.
     */
    public function test_payment_calculates_15_percent_admin_fee_and_85_percent_nurse_earning_and_updates_wallets(): void
    {
        // Siapkan Admin & Wallet
        $admin = User::firstOrCreate(
            ['email' => 'admin@careathome.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['admin']);
        $adminWallet = Wallet::firstOrCreate(['user_id' => $admin->id], ['balance' => 0]);
        $initialAdminBalance = $adminWallet->fresh()->balance;

        // Siapkan Perawat & Wallet
        $nurse = User::create([
            'name' => 'Nurse Test Split',
            'email' => 'nurse_split_' . uniqid() . '@careathome.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $nurse->syncRoles(['perawat']);
        $nurseWallet = Wallet::create(['user_id' => $nurse->id, 'balance' => 0]);

        // Siapkan Pasien & Layanan
        $patient = User::firstOrCreate(
            ['email' => 'pasien@careathome.com'],
            ['name' => 'Pasien', 'password' => bcrypt('password')]
        );
        $service = ServiceCatalog::first() ?? ServiceCatalog::create([
            'name' => 'Test Service',
            'slug' => 'test-service-' . uniqid(),
            'price' => 80000,
            'category' => 'Test',
            'category_badge' => 'Test',
            'badge_color' => 'bg-emerald-50',
            'image_emoji' => '🩹',
            'estimated_duration' => '60 Menit',
            'description' => 'Test description',
            'is_active' => true,
        ]);

        // Buat Order dengan Total Rp 100.000
        $order = Order::create([
            'order_number' => 'ORD-SPLIT-' . uniqid(),
            'patient_id' => $patient->id,
            'nurse_id' => $nurse->id,
            'service_catalog_id' => $service->id,
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'scheduled_time' => '10:00 - 11:00',
            'patient_name' => 'Budi Pasien',
            'patient_phone' => '081234567890',
            'address' => 'Jl. Test No. 1',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'distance_km' => 2.0,
            'base_price' => 80000,
            'transport_fee' => 15000,
            'platform_fee' => 5000,
            'total_amount' => 100000,
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'unpaid',
        ]);

        $paymentService = app(OrderPaymentService::class);
        $result = $paymentService->payOrder($order);

        // Verifikasi hasil kalkulasi
        $this->assertEquals(100000, $result['total_amount']);
        $this->assertEquals(15000, $result['admin_fee']); // 15% dari 100.000
        $this->assertEquals(85000, $result['nurse_earning']); // 85% dari 100.000

        // Verifikasi dompet perawat bertambah 85.000
        $nurseWallet->refresh();
        $this->assertEquals(85000, $nurseWallet->balance);

        // Verifikasi dompet admin bertambah 15.000
        $adminWallet->refresh();
        $this->assertEquals($initialAdminBalance + 15000, $adminWallet->balance);

        // Verifikasi mutasi wallet_transactions tercatat immutable
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $nurseWallet->id,
            'type' => 'order_income',
            'amount' => 85000,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $adminWallet->id,
            'type' => 'order_income',
            'amount' => 15000,
        ]);

        // Verifikasi status order berubah
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('diterima', $order->status);
    }

    /**
     * 2. Test Pesanan di Luar 10 km Ditolak oleh Backend.
     */
    public function test_order_creation_is_rejected_when_nurse_is_outside_10km_radius(): void
    {
        $patient = User::firstOrCreate(
            ['email' => 'pasien@careathome.com'],
            ['name' => 'Pasien', 'password' => bcrypt('password')]
        );
        $patient->syncRoles(['pasien']);

        // Perawat di Bogor (~43 km dari titik pasien Jakarta Pusat)
        $farNurse = User::create([
            'name' => 'Nurse Far Away',
            'email' => 'nurse_far_' . uniqid() . '@careathome.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $farNurse->syncRoles(['perawat']);

        NurseProfile::create([
            'user_id' => $farNurse->id,
            'is_online' => true,
            'latitude' => -6.5950, // Bogor
            'longitude' => 106.8166,
            'location_updated_at' => now(),
            'verification_status' => 'verified',
        ]);

        $service = ServiceCatalog::first();

        // Coba pesan dari titik Monas (-6.2088, 106.8456)
        $response = $this->actingAs($patient)->postJson('/api/v1/orders', [
            'service_catalog_id' => $service->id,
            'nurse_id' => $farNurse->id,
            'scheduled_date' => now()->addDays(2)->format('Y-m-d'),
            'scheduled_time' => '10:00 - 11:00',
            'patient_name' => 'Pasien Uji',
            'patient_phone' => '081299998888',
            'address' => 'Monas Jakarta Pusat',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        // Harus ditolak dengan status HTTP 422 Unprocessable Entity
        $response->assertStatus(422);
        $this->assertTrue(
            str_contains($response->getContent(), 'radius maksimal') ||
            str_contains($response->getContent(), '10 km') ||
            str_contains($response->getContent(), 'terlalu jauh')
        );
    }

    /**
     * 3. Test Perawat OFF Tidak Muncul di Katalog Pasien Radius 10 KM.
     */
    public function test_nurse_who_is_offline_does_not_appear_in_patient_catalog(): void
    {
        $patient = User::firstOrCreate(
            ['email' => 'pasien@careathome.com'],
            ['name' => 'Pasien', 'password' => bcrypt('password')]
        );

        // Perawat lokasi dekat (~1 km) tetapi is_online = false
        $offlineNurse = User::create([
            'name' => 'Nurse Offline Test',
            'email' => 'nurse_offline_' . uniqid() . '@careathome.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $offlineNurse->syncRoles(['perawat']);

        NurseProfile::create([
            'user_id' => $offlineNurse->id,
            'is_online' => false, // OFFLINE
            'latitude' => -6.2100,
            'longitude' => 106.8450,
            'location_updated_at' => now(),
            'verification_status' => 'verified',
        ]);

        // Panggil endpoint pencarian perawat terdekat
        $response = $this->actingAs($patient)->getJson('/api/v1/perawat/terdekat?lat=-6.2088&lng=106.8456');

        $response->assertStatus(200);
        $nurseIds = collect($response->json('data'))->pluck('user_id')->toArray();

        // Perawat offline tidak boleh ada di list
        $this->assertNotContains($offlineNurse->id, $nurseIds);
    }

    /**
     * 4. Test Payout Melebihi Saldo Ditolak.
     */
    public function test_payout_request_exceeding_wallet_balance_is_rejected(): void
    {
        $nurse = User::create([
            'name' => 'Nurse Payout Test',
            'email' => 'nurse_payout_' . uniqid() . '@careathome.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $nurse->syncRoles(['perawat']);

        // Saldo hanya Rp 50.000
        Wallet::create(['user_id' => $nurse->id, 'balance' => 50000]);

        NurseProfile::create([
            'user_id' => $nurse->id,
            'verification_status' => 'verified',
            'is_online' => true,
        ]);

        // Coba ajukan penarikan Rp 100.000 (melebihi saldo)
        $response = $this->actingAs($nurse)->post('/perawat/payout', [
            'amount' => 100000,
            'bank_name' => 'BCA',
            'account_number' => '123456789',
            'account_holder' => 'Nurse Payout Test',
        ]);

        // Harus gagal/redirect dengan flash message error saldo tidak mencukupi
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('payout_requests', [
            'nurse_user_id' => $nurse->id,
            'amount' => 100000,
        ]);
    }

    /**
     * 5. Test Otorisasi Role: Perawat Dilarang Mengakses Halaman Admin.
     */
    public function test_nurse_cannot_access_admin_dashboard_due_to_role_protection(): void
    {
        $nurse = User::firstOrCreate(
            ['email' => 'perawat@careathome.com'],
            ['name' => 'Perawat', 'password' => bcrypt('password')]
        );
        $nurse->syncRoles(['perawat']);

        $response = $this->actingAs($nurse)->get('/admin/dashboard');

        // Spatie role middleware akan melempar 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * 6. Test Admin User Management: Suspend user and auto nurse offline.
     */
    public function test_admin_can_toggle_user_status_and_suspended_nurse_becomes_offline(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@careathome.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['admin']);

        $nurse = User::create([
            'name' => 'Nurse To Suspend',
            'email' => 'nurse_suspend_' . uniqid() . '@careathome.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $nurse->syncRoles(['perawat']);

        $profile = NurseProfile::create([
            'user_id' => $nurse->id,
            'is_online' => true,
            'verification_status' => 'verified',
        ]);

        // Admin suspends user
        $response = $this->actingAs($admin)->post("/admin/users/{$nurse->id}/toggle-status", [
            'status' => 'suspended',
            'suspended_reason' => 'Pelanggaran SOP',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('suspended', $nurse->fresh()->status);
        $this->assertFalse((bool) $profile->fresh()->is_online);

        // Admin re-activates user
        $reactivateResponse = $this->actingAs($admin)->post("/admin/users/{$nurse->id}/toggle-status", [
            'status' => 'active',
        ]);

        $reactivateResponse->assertSessionHas('success');
        $this->assertEquals('active', $nurse->fresh()->status);
    }

    /**
     * 7. Test Admin User Management: Admin cannot suspend themselves.
     */
    public function test_admin_cannot_suspend_themselves(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@careathome.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['admin']);

        $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/toggle-status", [
            'status' => 'suspended',
            'suspended_reason' => 'Self suspend attempt',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('active', $admin->fresh()->status);
    }

    /**
     * 8. Test Admin Payout: Transfer with proof file upload.
     */
    public function test_admin_can_transfer_payout_with_proof(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = User::firstOrCreate(
            ['email' => 'admin@careathome.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['admin']);

        $nurse = User::create([
            'name' => 'Nurse Payout Admin Test',
            'email' => 'nurse_po_admin_' . uniqid() . '@careathome.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $nurse->syncRoles(['perawat']);
        $wallet = Wallet::create(['user_id' => $nurse->id, 'balance' => 50000]);

        $payout = PayoutRequest::create([
            'nurse_user_id' => $nurse->id,
            'amount' => 50000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Nurse Name',
            'status' => 'pending',
        ]);

        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('proof_transfer.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->post("/admin/payouts/{$payout->id}/transfer", [
            'transferred_at' => now()->format('Y-m-d'),
            'proof_file' => $fakeFile,
        ]);

        $response->assertSessionHas('success');
        $payout->refresh();
        $this->assertEquals('transferred', $payout->status);
        $this->assertNotNull($payout->proof_path);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'payout_paid',
            'amount' => 50000,
        ]);
    }

    /**
     * 9. Test Admin Payout: Reject payout releases held funds.
     */
    public function test_admin_can_reject_payout_and_refund_held_balance(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@careathome.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['admin']);

        $nurse = User::create([
            'name' => 'Nurse Payout Reject Test',
            'email' => 'nurse_po_rej_' . uniqid() . '@careathome.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $nurse->syncRoles(['perawat']);
        $wallet = Wallet::create(['user_id' => $nurse->id, 'balance' => 20000]);

        $payout = PayoutRequest::create([
            'nurse_user_id' => $nurse->id,
            'amount' => 50000,
            'bank_name' => 'BCA',
            'account_number' => '0000000000',
            'account_holder' => 'Wrong Name',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/admin/payouts/{$payout->id}/reject", [
            'rejection_reason' => 'Nomor rekening tidak cocok dengan nama akun.',
        ]);

        $response->assertSessionHas('success');
        $payout->refresh();
        $this->assertEquals('rejected', $payout->status);
        $this->assertEquals('Nomor rekening tidak cocok dengan nama akun.', $payout->rejection_reason);

        // Saldo dikembalikan (20.000 + 50.000 = 70.000)
        $wallet->refresh();
        $this->assertEquals(70000, $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'payout_release',
            'amount' => 50000,
            'balance_after' => 70000,
        ]);
    }
}
