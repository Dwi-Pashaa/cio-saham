<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Shareholder;
use App\Models\ShareHolding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareholderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([VerifyCsrfToken::class]);

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\ShareholderSeeder::class);

        $this->adminUser = User::where('email', 'admin@cionetwork.id')->first();
    }

    /**
     * Test Dashboard loads successfully with Company Growth Analytics & Multi-Web Finance Log Chart.
     */
    public function test_admin_can_view_dashboard_with_growth_and_finance_chart(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Pertumbuhan Usaha');
        $response->assertSee('Grafik Realtime Finansial');
        $response->assertSee('Struktur Kepemilikan Saham');
    }

    /**
     * Test Shareholders list displays 1-to-many shareholder data correctly.
     */
    public function test_admin_can_view_shareholders_list(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/shareholders');

        $response->assertStatus(200);
        $response->assertSee('Yoga Pratama');
        $response->assertSee('Yogi Hermawan');
        $response->assertSee('Fadil Muhammad');
        $response->assertSee('2 Data Saham'); // Yoga has 2 shares
    }

    /**
     * Test Viewing Shareholder detail shows multiple shares belonging to that 1 shareholder.
     */
    public function test_admin_can_view_shareholder_detail_with_multiple_shares(): void
    {
        $yoga = Shareholder::where('name', 'Yoga Pratama')->first();
        $this->assertNotNull($yoga);
        $this->assertEquals(2, $yoga->holdings()->count());

        $response = $this->actingAs($this->adminUser)->get(route('shareholders.show', $yoga->id));

        $response->assertStatus(200);
        $response->assertSee('Yoga Pratama');
        $response->assertSee('CIO-CORE-01');
        $response->assertSee('CIO Network Core');
        $response->assertSee('CIO-SAAS-02');
        $response->assertSee('CIO SaaS Solution');
        $response->assertSee('15.000 Lembar'); // 10k + 5k
    }

    /**
     * Test Admin can create a new shareholder with an initial share.
     */
    public function test_admin_can_create_new_shareholder_with_initial_share(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/shareholders/store', [
            'name'                    => 'Budi Santoso',
            'id_card_number'          => '3201999988880001',
            'email'                   => 'budi@cionetwork.id',
            'phone'                   => '081233445566',
            'address'                 => 'Jl. Sudirman No. 100',
            'status'                  => 'active',
            'share_code'              => 'CIO-CORE-01',
            'entity_name'             => 'CIO Network Core',
            'total_shares'            => 2000,
            'nominal_value_per_share' => 10000,
            'acquisition_date'        => '2026-03-01',
        ]);

        $budi = Shareholder::where('name', 'Budi Santoso')->first();
        $this->assertNotNull($budi);
        $response->assertRedirect(route('shareholders.show', $budi->id));

        $this->assertDatabaseHas('shareholders', [
            'name'           => 'Budi Santoso',
            'id_card_number' => '3201999988880001',
        ]);

        $this->assertDatabaseHas('share_holdings', [
            'shareholder_id' => $budi->id,
            'share_code'     => 'CIO-CORE-01',
            'total_shares'   => 2000,
        ]);
    }

    /**
     * Test Admin can add additional share to existing shareholder (1-to-many).
     */
    public function test_admin_can_add_additional_share_to_shareholder(): void
    {
        $fadil = Shareholder::where('name', 'Fadil Muhammad')->first();
        $this->assertEquals(1, $fadil->holdings()->count());

        $response = $this->actingAs($this->adminUser)->post('/share-holdings/store', [
            'shareholder_id'          => $fadil->id,
            'share_code'              => 'CIO-AI-03',
            'entity_name'             => 'CIO AI Automation Unit',
            'total_shares'            => 4000,
            'nominal_value_per_share' => 10000,
            'acquisition_date'        => '2026-03-15',
            'status'                  => 'active',
        ]);

        $response->assertRedirect(route('shareholders.show', $fadil->id));

        $fadil->refresh();
        $this->assertEquals(2, $fadil->holdings()->count()); // Now Fadil has 2 shares!
        $this->assertEquals(5000, $fadil->total_shares); // 1000 + 4000
    }
}
