<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Asset;
use App\Models\Shareholder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $staffUser;
    protected Shareholder $shareholder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([VerifyCsrfToken::class]);

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->adminUser = User::where('email', 'admin@cionetwork.id')->first();

        // Buat Shareholder dummy
        $this->shareholder = Shareholder::create([
            'name'           => 'Budi Investor Saham',
            'email'          => 'budi.investor@example.com',
            'phone'          => '081211112222',
            'id_card_number' => '3201012345678901',
            'status'         => 'active',
        ]);

        // Buat Role Staff dengan izin terbatas (lihat aset, tambah aset, ubah aset, hapus aset - TANPA 'atur pemilik aset')
        $staffRole = Role::firstOrCreate(['name' => 'Staf Gudang', 'guard_name' => 'web']);
        $staffRole->syncPermissions([
            'lihat dashboard',
            'lihat aset',
            'tambah aset',
            'ubah aset',
            'hapus aset',
        ]);

        $this->staffUser = User::factory()->create([
            'name'     => 'Staf Inventaris',
            'email'    => 'staf@cionetwork.id',
            'username' => 'staf_inventaris',
        ]);
        $this->staffUser->assignRole($staffRole);
    }

    /**
     * 1. Pengguna tanpa izin tidak dapat mengakses modul aset (HTTP 403).
     */
    public function test_user_without_permission_cannot_access_assets(): void
    {
        $guestUser = User::factory()->create([
            'name'     => 'User Polos',
            'email'    => 'polos@cionetwork.id',
            'username' => 'user_polos',
        ]);

        $response = $this->actingAs($guestUser)->get('/assets');
        $response->assertStatus(403);

        $createResponse = $this->actingAs($guestUser)->get('/assets/create');
        $createResponse->assertStatus(403);
    }

    /**
     * 2. Pengguna dengan izin 'lihat aset' dapat membuka halaman index.
     */
    public function test_user_with_view_permission_can_see_asset_index(): void
    {
        $response = $this->actingAs($this->staffUser)->get('/assets');
        $response->assertStatus(200);
        $response->assertSee('Daftar Inventaris Aset');
        $response->assertSee('assets-table');
    }

    /**
     * 3. Pengguna TANPA permission 'atur pemilik aset' dipaksa menjadi Milik PT.
     */
    public function test_user_without_owner_permission_forces_owner_to_pt(): void
    {
        $response = $this->actingAs($this->staffUser)->post('/assets/store', [
            'name'           => 'Switch Cisco Catalyst 2960',
            'type'           => 'Perangkat Jaringan',
            'price'          => '8.500.000',
            'serial_number'  => 'SN-CISCO-001',
            'mac_address'    => '00:1A:2B:3C:4D:5E',
            'owner_type'     => 'shareholder',              // Coba tembak sebagai investor
            'shareholder_id' => $this->shareholder->id,     // Coba kaitkan ke investor
            'purchase_date'  => '2026-10-06',
            'notes'          => 'Switch Core Lantai 1',
        ]);

        $response->assertRedirect('/assets');

        // Harus tersimpan sebagai milik PT, bukan shareholder
        $this->assertDatabaseHas('assets', [
            'name'           => 'Switch Cisco Catalyst 2960',
            'type'           => 'Perangkat Jaringan',
            'price'          => 8500000.00,
            'serial_number'  => 'SN-CISCO-001',
            'mac_address'    => '00:1A:2B:3C:4D:5E',
            'owner_type'     => 'pt',
            'shareholder_id' => null,
            'owner_name'     => 'PT CIO NETWORK SOLUTION',
        ]);
    }

    /**
     * 4. Pengguna DENGAN permission 'atur pemilik aset' dapat menetapkan aset ke Investor.
     */
    public function test_user_with_owner_permission_can_assign_asset_to_shareholder(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/assets/store', [
            'name'           => 'Server Dell PowerEdge R740',
            'type'           => 'Server & Komputer',
            'price'          => '45.000.000',
            'serial_number'  => 'SN-DELL-9988',
            'mac_address'    => 'F0:2F:74:AA:BB:CC',
            'owner_type'     => 'shareholder',
            'shareholder_id' => $this->shareholder->id,
            'purchase_date'  => '2026-10-01',
            'notes'          => 'Server titipan modal investor',
        ]);

        $response->assertRedirect('/assets');

        $this->assertDatabaseHas('assets', [
            'name'           => 'Server Dell PowerEdge R740',
            'price'          => 45000000.00,
            'owner_type'     => 'shareholder',
            'shareholder_id' => $this->shareholder->id,
            'owner_name'     => 'Budi Investor Saham',
        ]);
    }

    /**
     * 5. Validasi field wajib (name, type, price).
     */
    public function test_required_fields_validation(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/assets/store', [
            'name'  => '',
            'type'  => '',
            'price' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'type', 'price']);
    }

    /**
     * 6. Siklus update dan delete aset.
     */
    public function test_update_and_delete_asset(): void
    {
        $asset = Asset::create([
            'name'           => 'Laptop Asus ROG Strix',
            'type'           => 'Elektronik & Gadget',
            'price'          => 22000000,
            'serial_number'  => 'ROG-12345',
            'owner_type'     => 'pt',
            'owner_name'     => 'PT CIO NETWORK SOLUTION',
            'purchase_date'  => '2026-09-15',
            'created_by'     => $this->adminUser->id,
        ]);

        // Update
        $updateResponse = $this->actingAs($this->adminUser)->put('/assets/' . $asset->id, [
            'name'           => 'Laptop Asus ROG Strix G16 (Updated)',
            'type'           => 'Elektronik & Gadget',
            'price'          => '23.500.000',
            'serial_number'  => 'ROG-12345-REV',
            'owner_type'     => 'pt',
            'purchase_date'  => '2026-09-15',
            'notes'          => 'Upgrade RAM 32GB',
        ]);

        $updateResponse->assertRedirect('/assets');

        $this->assertDatabaseHas('assets', [
            'id'            => $asset->id,
            'name'          => 'Laptop Asus ROG Strix G16 (Updated)',
            'price'         => 23500000.00,
            'serial_number' => 'ROG-12345-REV',
            'notes'         => 'Upgrade RAM 32GB',
        ]);

        // Delete
        $deleteResponse = $this->actingAs($this->adminUser)->delete('/assets/' . $asset->id);
        $deleteResponse->assertRedirect('/assets');

        $this->assertDatabaseMissing('assets', [
            'id' => $asset->id,
        ]);
    }

    /**
     * 7. DataTables AJAX endpoint mengembalikan respons JSON dan metrik KPI.
     */
    public function test_datatables_ajax_endpoint_returns_json(): void
    {
        Asset::create([
            'name'           => 'Router MikroTik CCR2004',
            'type'           => 'Perangkat Jaringan',
            'price'          => 12000000,
            'serial_number'  => 'SN-MIKROTIK-77',
            'mac_address'    => 'CC:2D:E0:11:22:33',
            'owner_type'     => 'pt',
            'owner_name'     => 'PT CIO NETWORK SOLUTION',
            'purchase_date'  => '2026-10-01',
            'created_by'     => $this->adminUser->id,
        ]);

        Asset::create([
            'name'           => 'OLT ZTE C320',
            'type'           => 'Perangkat Jaringan',
            'price'          => 30000000,
            'serial_number'  => 'ZTE-OLT-99',
            'owner_type'     => 'shareholder',
            'shareholder_id' => $this->shareholder->id,
            'owner_name'     => $this->shareholder->name,
            'purchase_date'  => '2026-10-02',
            'created_by'     => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/assets', ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'totalCount',
            'totalPriceSum',
            'ptPriceSum',
            'investorPriceSum',
        ]);

        $response->assertSee('Router MikroTik CCR2004');
        $response->assertSee('OLT ZTE C320');
        $response->assertSee('PT CIO NETWORK SOLUTION');
        $response->assertSee('Budi Investor Saham');
    }

    /**
     * 8. Pengujian upload multiple images saat membuat aset.
     */
    public function test_user_can_upload_multiple_images_when_creating_asset(): void
    {
        Storage::fake('public');

        $image1 = UploadedFile::fake()->create('foto1.jpg', 100, 'image/jpeg');
        $image2 = UploadedFile::fake()->create('foto2.png', 100, 'image/png');

        $response = $this->actingAs($this->adminUser)->postJson('/assets/store', [
            'name'  => 'Server Rack Dell PowerEdge R740',
            'type'  => 'Server & Komputer',
            'price' => '45.000.000',
            'images' => [$image1, $image2],
        ]);

        $response->assertStatus(200);

        $asset = Asset::where('name', 'Server Rack Dell PowerEdge R740')->first();
        $this->assertNotNull($asset);
        $this->assertCount(2, $asset->images);

        // Pastikan foto tersimpan di disk public
        foreach ($asset->images as $img) {
            Storage::disk('public')->assertExists($img->image_path);
        }
    }

    /**
     * 9. Pengujian penghapusan satu gambar spesifik dari aset.
     */
    public function test_user_can_delete_individual_asset_image(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->create('foto_test.jpg', 100, 'image/jpeg');
        $path = $image->store('assets/images', 'public');

        $asset = Asset::create([
            'name'          => 'Switch Ruijie 24 Port',
            'type'          => 'Perangkat Jaringan',
            'price'         => 3500000,
            'owner_type'    => 'pt',
            'owner_name'    => 'PT CIO NETWORK SOLUTION',
            'created_by'    => $this->adminUser->id,
        ]);

        $assetImage = $asset->images()->create([
            'image_path' => $path,
            'is_primary' => true,
        ]);

        Storage::disk('public')->assertExists($path);

        $deleteResponse = $this->actingAs($this->adminUser)->deleteJson('/assets/images/' . $assetImage->id);
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('asset_images', [
            'id' => $assetImage->id,
        ]);

        Storage::disk('public')->assertMissing($path);
    }
}
