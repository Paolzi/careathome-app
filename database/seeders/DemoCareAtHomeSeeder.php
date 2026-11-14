<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCareAtHomeSeeder extends Seeder
{
    /**
     * Seeder Konfigurasi Platform, Akun Admin & Akun Pasien.
     * Data dummy perawat dihapus agar seluruh perawat berasal dari Registrasi Manual dan Verifikasi Dokumen.
     */
    public function run(): void
    {
        // 1. Pastikan Role tersedia
        $this->call(RolePermissionSeeder::class);

        // 2. Pastikan Katalog Layanan Medis ter-seed lengkap
        $this->call(ServiceCatalogSeeder::class);

        // 3. Pengaturan Platform Global
        $settings = [
            'location_timeout_minutes' => '1440',
            'transport_fee_base' => '10000',
            'transport_fee_per_km' => '2500',
            'platform_service_fee' => '5000',
            'platform_commission_percent' => '15',
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 4. Akun Super Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@careathome.id'],
            [
                'name' => 'Super Admin CareAtHome',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // 5. Akun Pasien Utama
        $pasien1 = User::updateOrCreate(
            ['email' => 'pasien@careathome.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $pasien1->syncRoles(['pasien']);
        Wallet::updateOrCreate(['user_id' => $pasien1->id], ['balance' => 1500000]);

        $pasien2 = User::updateOrCreate(
            ['email' => 'siti.pasien@careathome.id'],
            [
                'name' => 'Hj. Siti Aminah',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $pasien2->syncRoles(['pasien']);
        Wallet::updateOrCreate(['user_id' => $pasien2->id], ['balance' => 850000]);
    }
}
