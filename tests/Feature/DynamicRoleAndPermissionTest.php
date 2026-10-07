<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DynamicRoleAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([VerifyCsrfToken::class]);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seed(RolePermissionSeeder::class);
        $this->adminUser = User::where('email', 'admin@cionetwork.id')->first();
    }

    public function test_admin_can_create_new_dynamic_role(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('role.store'), [
            'name' => 'Staf Keuangan',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('roles', [
            'name' => 'Staf Keuangan',
            'guard_name' => 'web',
        ]);
    }

    public function test_role_name_must_be_unique(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('role.store'), [
            'name' => 'Admin',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['code' => 400]);
        $response->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_admin_role_cannot_be_renamed_or_deleted(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();

        // Try update
        $updateResponse = $this->actingAs($this->adminUser)->putJson(route('role.update', $adminRole->id), [
            'name' => 'Super User',
        ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['status' => 'error']);

        // Try delete
        $deleteResponse = $this->actingAs($this->adminUser)->deleteJson(route('role.destroy', $adminRole->id));
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['status' => 'error']);

        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
    }

    public function test_role_with_assigned_users_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Auditor', 'guard_name' => 'web']);
        $user = User::create([
            'username' => 'auditor1',
            'name' => 'Auditor Test',
            'email' => 'auditor@cionetwork.id',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $response = $this->actingAs($this->adminUser)->deleteJson(route('role.destroy', $role->id));
        $response->assertStatus(200);
        $response->assertJson(['status' => 'error']);
        $this->assertDatabaseHas('roles', ['name' => 'Auditor']);

        // Remove user from role, then delete should succeed
        $user->removeRole($role);
        $deleteResponse = $this->actingAs($this->adminUser)->deleteJson(route('role.destroy', $role->id));
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['status' => 'success']);
        $this->assertDatabaseMissing('roles', ['name' => 'Auditor']);
    }

    public function test_admin_can_save_permissions_for_role_including_empty(): void
    {
        $role = Role::create(['name' => 'Manajer Kas', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)->put(route('role.savePermission', $role->id), [
            'permissions' => ['lihat pemasukan', 'tambah pemasukan', 'lihat pengeluaran'],
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue($role->fresh()->hasPermissionTo('lihat pemasukan'));
        $this->assertTrue($role->fresh()->hasPermissionTo('tambah pemasukan'));
        $this->assertTrue($role->fresh()->hasPermissionTo('lihat pengeluaran'));
        $this->assertFalse($role->fresh()->hasPermissionTo('hapus pemasukan'));

        // Can clear permissions (save empty array)
        $clearResponse = $this->actingAs($this->adminUser)->put(route('role.savePermission', $role->id), [
            'permissions' => [],
        ]);
        $clearResponse->assertSessionHas('success');
        $this->assertEquals(0, $role->fresh()->permissions()->count());
    }

    public function test_role_based_route_protection(): void
    {
        $role = Role::create(['name' => 'Staf Kasir', 'guard_name' => 'web']);
        $role->syncPermissions(['lihat pemasukan', 'tambah pemasukan']);

        $user = User::create([
            'username' => 'kasir1',
            'name' => 'Staf Kasir',
            'email' => 'kasir@cionetwork.id',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        // Can access cash-incomes
        $this->actingAs($user)->get(route('cash-incomes.index'))->assertStatus(200);

        // Cannot access level-akses (403)
        $this->actingAs($user)->get(route('role.index'))->assertStatus(403);

        // Cannot access users list (403)
        $this->actingAs($user)->get(route('user.index'))->assertStatus(403);

        // Cannot access cash-outcomes (403)
        $this->actingAs($user)->get(route('cash-outcomes.index'))->assertStatus(403);
    }
}
