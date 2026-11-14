<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Account
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

        // 2. Pasien Account
        // Catatan: Akun perawat tidak di-seed secara otomatis; perawat mendaftar secara manual melalui halaman registrasi.
        $pasien = User::updateOrCreate(
            ['email' => 'pasien@careathome.id'],
            [
                'name' => 'Budi Santoso (Pasien)',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $pasien->syncRoles(['pasien']);

        Wallet::firstOrCreate(
            ['user_id' => $pasien->id],
            ['balance' => 500000]
        );
    }
}
