<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // 1. Dashboard
            'lihat dashboard',
            'lihat dashboard perusahaan',

            // 2. Master Data - Pengguna
            'lihat pengguna',
            'tambah pengguna',
            'ubah pengguna',
            'hapus pengguna',

            // 2. Master Data - Level Akses
            'lihat level akses',
            'tambah level akses',
            'ubah level akses',
            'atur hak akses',
            'hapus level akses',

            // 3. Manajemen Investor - Data Investor
            'lihat investor',
            'tambah investor',
            'ubah investor',
            'hapus investor',

            // 3. Manajemen Investor - Kepemilikan Saham
            'lihat kepemilikan saham',
            'tambah kepemilikan saham',
            'ubah kepemilikan saham',
            'hapus kepemilikan saham',

            // 3. Manajemen Investor - Laporan Imbal Hasil
            'lihat laporan imbal hasil',
            'tambah laporan imbal hasil',
            'ubah laporan imbal hasil',
            'hapus laporan imbal hasil',

            // 3. Manajemen Investor - Portofolio Investor
            'lihat portofolio investor',

            // 4. Transaksi Kas - Pemasukan
            'lihat pemasukan',
            'tambah pemasukan',
            'ubah pemasukan',
            'hapus pemasukan',

            // 4. Transaksi Kas - Pengeluaran
            'lihat pengeluaran',
            'tambah pengeluaran',
            'ubah pengeluaran',
            'hapus pengeluaran',

            // 4. Transaksi Kas - Tabungan
            'lihat tabungan',
            'bagikan ke tabungan',
            'hapus tabungan',

            // 5. Manajemen Aset
            'lihat aset',
            'tambah aset',
            'ubah aset',
            'hapus aset',
            'atur pemilik aset',

            // 6. Pengaturan
            'lihat pengaturan',
            'ubah pengaturan',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        Permission::whereNotIn('name', $permissions)->delete();

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $shareholderRole = Role::firstOrCreate(['name' => 'Pemegang Saham', 'guard_name' => 'web']);

        $adminLowerRole = Role::where('name', 'admin')->first();
        if ($adminLowerRole && $adminLowerRole->id !== $adminRole->id) {
            foreach ($adminLowerRole->users as $user) {
                $user->assignRole($adminRole);
            }
            $adminLowerRole->delete();
        }

        $investorRole = Role::where('name', 'Investor')->first();
        if ($investorRole && $investorRole->id !== $shareholderRole->id) {
            foreach ($investorRole->users as $user) {
                $user->assignRole($shareholderRole);
            }
            $investorRole->delete();
        }

        if ($shareholderRole->wasRecentlyCreated || $shareholderRole->permissions()->count() === 0) {
            $shareholderRole->syncPermissions([
                'lihat dashboard',
                'lihat pemasukan',
                'lihat pengeluaran',
                'lihat kepemilikan saham',
                'lihat laporan imbal hasil',
                'lihat portofolio investor',
            ]);
        }

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@cionetwork.id'],
            [
                'username' => 'admin',
                'name' => 'Administrator CIO Saham',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles([$adminRole]);

        $yogaUser = User::firstOrCreate(
            ['email' => 'yoga@cionetwork.id'],
            [
                'username' => 'yoga',
                'name' => 'Yoga Pratama',
                'phone' => '081298765432',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $yogaUser->syncRoles([$shareholderRole]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
