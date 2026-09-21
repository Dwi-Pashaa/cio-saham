<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar Role
        $roles = [
            "Admin",
            "Investor"
        ];

        foreach ($roles as $value) {
            Role::firstOrCreate(['name' => $value]);
        }

        $permissions = [
            'buat pengguna', 'lihat pengguna', 'ubah pengguna', 'hapus pengguna',
            'buat type investasi', 'lihat type investasi', 'ubah type investasi', 'hapus type investasi',
            'buat kategori investasi', 'lihat kategori investasi', 'ubah kategori investasi', 'hapus kategori investasi',
            'buat investor', 'lihat investor', 'ubah investor', 'hapus investor',
            'buat transfer', 'lihat transfer', 'edit transfer', 'hapus transfer',
            'download excel',
            'download pdf',
        ];
        

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
        }
    }
}
