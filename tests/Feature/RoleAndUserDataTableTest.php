<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleAndUserDataTableTest extends TestCase
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

    public function test_admin_can_view_role_datatable(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('role.index'));
        $response->assertStatus(200);
        $response->assertSee('table-roles');

        $ajaxResponse = $this->actingAs($this->adminUser)->getJson(route('role.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertSee('Admin');
    }

    public function test_admin_can_view_user_datatable(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('user.index'));
        $response->assertStatus(200);
        $response->assertSee('table-users');

        $ajaxResponse = $this->actingAs($this->adminUser)->getJson(route('user.index'), ['X-Requested-With' => 'XMLHttpRequest']);
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertSee('admin@cionetwork.id');
    }
}
