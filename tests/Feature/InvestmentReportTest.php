<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\InvestmentReport;
use App\Models\Shareholder;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InvestmentReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->withoutMiddleware(VerifyCsrfToken::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed roles & permissions
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('investment-reports.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_crud_investment_report_manually(): void
    {
        $admin = User::where('email', 'admin@cionetwork.id')->first();
        if (!$admin) {
            $adminRole = Role::where('name', 'Admin')->first();
            $admin = User::create([
                'username' => 'admin_test_' . uniqid(),
                'name'     => 'Admin Test',
                'email'    => 'admin_test_' . uniqid() . '@cionetwork.id',
                'password' => bcrypt('password'),
            ]);
            $admin->assignRole($adminRole);
        }

        $shareholder = Shareholder::firstOrCreate(
            ['email' => 'investor.report@cionetwork.id'],
            [
                'name'           => 'Investor Laporan Test',
                'id_card_number' => '3201998877660001',
                'phone'          => '081299887766',
                'status'         => 'active',
            ]
        );

        // 1. Admin creates report manually (Modal Awal: 200, Keuntungan: 50)
        $storeResponse = $this->actingAs($admin)->post(route('investment-reports.store'), [
            'shareholder_id'  => $shareholder->id,
            'year'            => 2025,
            'initial_capital' => 200,
            'profit_amount'   => 50,
            'status'          => 'distributed',
            'notes'           => 'Keuntungan tahun pertama',
        ]);

        $storeResponse->assertRedirect(route('investment-reports.index'));
        $this->assertDatabaseHas('investment_reports', [
            'shareholder_id'  => $shareholder->id,
            'year'            => 2025,
            'initial_capital' => 200,
            'profit_amount'   => 50,
        ]);

        $report = InvestmentReport::where('shareholder_id', $shareholder->id)->where('year', 2025)->first();
        $this->assertNotNull($report);

        // 2. Admin view index list
        $indexResponse = $this->actingAs($admin)->get(route('investment-reports.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Investor Laporan Test');
        $indexResponse->assertSee('2025');

        // 3. Admin update report manually (ubah profit jadi 75)
        $updateResponse = $this->actingAs($admin)->put(route('investment-reports.update', $report->id), [
            'shareholder_id'  => $shareholder->id,
            'year'            => 2025,
            'initial_capital' => 200,
            'profit_amount'   => 75,
            'status'          => 'distributed',
            'notes'           => 'Keuntungan direvisi',
        ]);

        $updateResponse->assertRedirect(route('investment-reports.index'));
        $this->assertDatabaseHas('investment_reports', [
            'id'            => $report->id,
            'profit_amount' => 75,
        ]);

        // 4. Admin delete report
        $deleteResponse = $this->actingAs($admin)->delete(route('investment-reports.destroy', $report->id));
        $deleteResponse->assertRedirect(route('investment-reports.index'));
        $this->assertDatabaseMissing('investment_reports', [
            'id' => $report->id,
        ]);
    }

    public function test_shareholder_can_view_only_own_reports_read_only(): void
    {
        $shareholderRole = Role::where('name', 'Pemegang Saham')->first();

        $user = User::where('email', 'yoga@cionetwork.id')->first();
        if (!$user) {
            $user = User::create([
                'username' => 'yoga_' . uniqid(),
                'name'     => 'Yoga Pratama',
                'email'    => 'yoga@cionetwork.id',
                'password' => bcrypt('password'),
            ]);
            $user->assignRole($shareholderRole);
        }

        $shareholderYoga = Shareholder::firstOrCreate(
            ['email' => 'yoga@cionetwork.id'],
            [
                'user_id'        => $user->id,
                'name'           => 'Yoga Pratama',
                'id_card_number' => '3201123456780002',
                'phone'          => '081298765432',
                'status'         => 'active',
            ]
        );

        $shareholderOther = Shareholder::firstOrCreate(
            ['email' => 'other.investor@cionetwork.id'],
            [
                'name'           => 'Other Secret Investor',
                'id_card_number' => '3201123456780003',
                'phone'          => '081298765433',
                'status'         => 'active',
            ]
        );

        // Buat 1 laporan untuk Yoga dan 1 untuk investor lain
        $reportYoga = InvestmentReport::create([
            'shareholder_id'  => $shareholderYoga->id,
            'year'            => 2025,
            'initial_capital' => 200,
            'profit_amount'   => 50,
            'status'          => 'distributed',
            'notes'           => 'Laporan Yoga',
        ]);

        $reportOther = InvestmentReport::create([
            'shareholder_id'  => $shareholderOther->id,
            'year'            => 2025,
            'initial_capital' => 500,
            'profit_amount'   => 150,
            'status'          => 'distributed',
            'notes'           => 'Laporan Rahasia Other',
        ]);

        // Yoga mengakses index laporan
        $response = $this->actingAs($user)->get(route('investment-reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Yoga Pratama');
        $response->assertSee('Laporan Yoga');
        $response->assertDontSee('Other Secret Investor');
        $response->assertDontSee('Laporan Rahasia Other');

        // Pastikan Yoga TIDAK melihat tombol Tambah Laporan
        $response->assertDontSee('Tambah Laporan Tahunan');

        // Yoga mencoba membuat laporan -> 403 Forbidden
        $forbiddenStore = $this->actingAs($user)->post(route('investment-reports.store'), [
            'shareholder_id'  => $shareholderYoga->id,
            'year'            => 2026,
            'initial_capital' => 100,
            'profit_amount'   => 20,
        ]);
        $forbiddenStore->assertStatus(403);

        // Yoga mencoba menghapus laporan -> 403 Forbidden
        $forbiddenDelete = $this->actingAs($user)->delete(route('investment-reports.destroy', $reportYoga->id));
        $forbiddenDelete->assertStatus(403);
    }
}
