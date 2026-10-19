<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Daftar Permissions berdasarkan Karakteristik Pengguna (SRS CareAtHome)
        $permissions = [
            // Pasien Permissions
            'buat-pesanan',
            'bayar-pesanan',
            'lihat-ereport',
            'beri-ulasan',
            'kelola-profil-pasien',

            // Perawat (Mitra) Permissions
            'kelola-profil-perawat',
            'unggah-dokumen-legalitas',
            'atur-ketersediaan',
            'terima-tolak-pesanan',
            'isi-ereport',
            'tarik-saldo',

            // Admin Permissions
            'kelola-pengguna',
            'verifikasi-legalitas',
            'kelola-katalog',
            'monitor-transaksi',
            'otorisasi-payout',
            'unduh-laporan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Buat Roles dan Hubungkan Permissions
        // Role: Pasien
        $pasienRole = Role::firstOrCreate(['name' => 'pasien', 'guard_name' => 'web']);
        $pasienRole->syncPermissions([
            'buat-pesanan',
            'bayar-pesanan',
            'lihat-ereport',
            'beri-ulasan',
            'kelola-profil-pasien',
        ]);

        // Role: Perawat
        $perawatRole = Role::firstOrCreate(['name' => 'perawat', 'guard_name' => 'web']);
        $perawatRole->syncPermissions([
            'kelola-profil-perawat',
            'unggah-dokumen-legalitas',
            'atur-ketersediaan',
            'terima-tolak-pesanan',
            'isi-ereport',
            'tarik-saldo',
        ]);

        // Role: Admin (Memiliki semua hak akses)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());
    }
}
