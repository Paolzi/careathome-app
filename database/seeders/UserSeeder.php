<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@careathome.com'],
            [
                'name' => 'Administrator CareAtHome',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // 2. Akun Perawat (Mitra)
        $perawat1 = User::updateOrCreate(
            ['email' => 'perawat@careathome.com'],
            [
                'name' => 'Ns. Siti Nurhaliza, S.Kep',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $perawat1->syncRoles(['perawat']);

        $perawat2 = User::updateOrCreate(
            ['email' => 'perawat2@careathome.com'],
            [
                'name' => 'Ns. Budi Pratama, S.Kep',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $perawat2->syncRoles(['perawat']);

        // 3. Akun Pasien
        $pasien1 = User::updateOrCreate(
            ['email' => 'pasien@careathome.com'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $pasien1->syncRoles(['pasien']);

        $pasien2 = User::updateOrCreate(
            ['email' => 'pasien2@careathome.com'],
            [
                'name' => 'Dewi Lestari',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $pasien2->syncRoles(['pasien']);
    }
}
