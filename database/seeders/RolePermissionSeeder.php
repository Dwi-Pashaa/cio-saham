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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar Permission Terperinci
        $permissions = [
            // Dashboard & Analisis Pertumbuhan Perusahaan
            'view-dashboard',
            'view-company-growth',
            'view-finance-logs',
            'sync-finance-api',

            // Profil Pemilik Saham & Direktori Investor
            'view-shareholders',
            'view-shareholder-directory',
            'create-shareholder',
            'edit-shareholder',
            'delete-shareholder',

            // Data / Alokasi Saham (1 Pemilik -> Banyak Saham)
            'view-share-holdings',
            'create-share-holding',
            'edit-share-holding',
            'delete-share-holding',

            // Transaksi Saham
            'view-share-transactions',
            'create-share-transaction',

            // Laporan Keuntungan Saham / Imbal Hasil
            'view-investment-reports',
            'create-investment-report',
            'edit-investment-report',
            'delete-investment-report',

            // Pengaturan
            'view-settings',
            'manage-settings',

            // Kompatibilitas Menu User & Role
            'lihat pengguna',
            'buat pengguna',
            'ubah pengguna',
            'hapus pengguna',
            'lihat role',
            'buat role',
            'ubah role',
            'hapus role',
        ];

        // 1. Buat Semua Permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // 2. Role Admin / Super Admin: ASSIGN SEMUA PERMISSIONS
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Alias lowercase admin role
        $adminLowerRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminLowerRole->syncPermissions(Permission::all());

        // 3. Role Pemegang Saham (Read-only akses portofolio & laporan)
        $shareholderRole = Role::firstOrCreate(['name' => 'Pemegang Saham', 'guard_name' => 'web']);
        $shareholderRole->syncPermissions([
            'view-dashboard',
            'view-company-growth',
            'view-share-holdings',
            'view-share-transactions',
            'view-shareholder-directory',
            'view-investment-reports',
        ]);

        // 4. Default Super Admin User
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

        $adminUser->assignRole($adminRole);
        $adminUser->assignRole($adminLowerRole);

        // 5. Default User Pemegang Saham (Yoga Pratama)
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
        $yogaUser->assignRole($shareholderRole);
    }
}
