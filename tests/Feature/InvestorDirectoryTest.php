<?php

namespace Tests\Feature;

use App\Models\Shareholder;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InvestorDirectoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed roles & permissions
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('investor-directory.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_shareholder_can_view_and_search_investor_directory(): void
    {
        $user = User::where('email', 'yoga@cionetwork.id')->first();
        if (!$user) {
            $shareholderRole = Role::where('name', 'Pemegang Saham')->first();
            $user = User::create([
                'username' => 'yoga_' . uniqid(),
                'name'     => 'Yoga Pratama',
                'email'    => 'yoga_' . uniqid() . '@cionetwork.id',
                'password' => bcrypt('password'),
            ]);
            $user->assignRole($shareholderRole);
        }

        $shareholder = Shareholder::firstOrCreate(
            ['email' => 'budi.test@cionetwork.id'],
            [
                'name'           => 'Budi Santoso',
                'id_card_number' => '3201123456780001',
                'phone'          => '08123456789',
                'status'         => 'active',
            ]
        );

        // 1. Akses halaman index direktori
        $response = $this->actingAs($user)->get(route('investor-directory.index'));
        $response->assertStatus(200);
        $response->assertSee('Direktori Pemegang Saham');
        $response->assertSee('Portofolio Investor');
        $response->assertSee('Budi Santoso');

        // 2. Test pencarian dengan kata kunci
        $searchResponse = $this->actingAs($user)->get(route('investor-directory.index', ['search' => 'Budi']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Budi Santoso');

        // 3. Test halaman show portofolio (Read-Only)
        $showResponse = $this->actingAs($user)->get(route('investor-directory.show', $shareholder->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Budi Santoso');
        $showResponse->assertSee('Rincian Alokasi Instrumen Saham');
        $showResponse->assertDontSee('Tambah Saham untuk Pemilik Ini');
        $showResponse->assertDontSee('Edit Profil');
    }

    public function test_shareholder_cannot_access_admin_management_crud(): void
    {
        $user = User::where('email', 'yoga@cionetwork.id')->first();
        if (!$user) {
            $shareholderRole = Role::where('name', 'Pemegang Saham')->first();
            $user = User::create([
                'username' => 'yoga_' . uniqid(),
                'name'     => 'Yoga Pratama',
                'email'    => 'yoga_' . uniqid() . '@cionetwork.id',
                'password' => bcrypt('password'),
            ]);
            $user->assignRole($shareholderRole);
        }

        // Akses form create shareholder harus 403 Forbidden
        $response = $this->actingAs($user)->get(route('shareholders.create'));
        $response->assertStatus(403);
    }
}
