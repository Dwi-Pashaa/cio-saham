<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\CashIncome;
use App\Models\CashOutcome;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashIncomeOutcomeTest extends TestCase
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
        Storage::fake('public');
    }

    /**
     * Test Cash Incomes Index and Store.
     */
    public function test_admin_can_create_cash_income_with_and_without_admin_fee(): void
    {
        // 1. Without admin fee
        $file1 = UploadedFile::fake()->create('proof1.jpg', 100, 'image/jpeg');
        $response1 = $this->actingAs($this->adminUser)->post('/cash-incomes/store', [
            'transaction_date' => '2026-10-04',
            'sender_name'      => 'PT Klien Utama',
            'bank_name'        => 'BCA PT CIO',
            'account_number'   => '1234567890',
            'amount'           => '10.000.000',
            'has_admin_fee'    => 'tidak',
            'admin_fee'        => '0',
            'proof_file'       => $file1,
            'notes'            => 'Pembayaran kontrak layanan Q4',
        ]);

        $response1->assertRedirect('/cash-incomes');
        $this->assertDatabaseHas('cash_incomes', [
            'sender_name'   => 'PT Klien Utama',
            'amount'        => 10000000.00,
            'has_admin_fee' => 0,
            'admin_fee'     => 0.00,
            'net_amount'    => 10000000.00,
        ]);

        // 2. With admin fee
        $file2 = UploadedFile::fake()->create('proof2.jpg', 100, 'image/jpeg');
        $response2 = $this->actingAs($this->adminUser)->post('/cash-incomes/store', [
            'transaction_date' => '2026-10-04',
            'sender_name'      => 'Mitra Finansial',
            'bank_name'        => 'Mandiri',
            'account_number'   => '987654321',
            'amount'           => '5.000.000',
            'has_admin_fee'    => 'ya',
            'admin_fee'        => '6.500',
            'proof_file'       => $file2,
            'notes'            => 'Setoran modal dengan potongan transfer antar bank',
        ]);

        $response2->assertRedirect('/cash-incomes');
        $this->assertDatabaseHas('cash_incomes', [
            'sender_name'   => 'Mitra Finansial',
            'amount'        => 5000000.00,
            'has_admin_fee' => 1,
            'admin_fee'     => 6500.00,
            'net_amount'    => 4993500.00,
        ]);

        // 3. View Index and DataTables AJAX
        $indexResponse = $this->actingAs($this->adminUser)->get('/cash-incomes');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('table-cash-incomes');

        $ajaxResponse = $this->actingAs($this->adminUser)->getJson('/cash-incomes', ['X-Requested-With' => 'XMLHttpRequest']);
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertSee('PT Klien Utama');
        $ajaxResponse->assertSee('Mitra Finansial');
    }

    /**
     * Test Cash Outcomes Index and Store (with and without asset & admin fee).
     */
    public function test_admin_can_create_cash_outcome_with_admin_fee(): void
    {
        $proofFile = UploadedFile::fake()->create('proof_out.jpg', 100, 'image/jpeg');
        $receiptFile = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post('/cash-outcomes/store', [
            'transaction_date' => '2026-10-04',
            'recipient_name'   => 'Vendor Server Dell Indonesia',
            'bank_name'        => 'BCA',
            'account_number'   => '5554443322',
            'amount'           => '25.000.000',
            'has_admin_fee'    => 'ya',
            'admin_fee'        => '2.500',
            'proof_file'       => $proofFile,
            'receipt_file'     => $receiptFile,
            'notes'            => 'Pengadaan unit server bare-metal data center',
        ]);

        $response->assertRedirect('/cash-outcomes');
        $this->assertDatabaseHas('cash_outcomes', [
            'recipient_name' => 'Vendor Server Dell Indonesia',
            'amount'         => 25000000.00,
            'has_admin_fee'  => 1,
            'admin_fee'      => 2500.00,
            'total_amount'   => 25002500.00,
        ]);

        // View Index and DataTables AJAX
        $indexResponse = $this->actingAs($this->adminUser)->get('/cash-outcomes');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('table-cash-outcomes');

        $ajaxResponse = $this->actingAs($this->adminUser)->getJson('/cash-outcomes', ['X-Requested-With' => 'XMLHttpRequest']);
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertSee('Vendor Server Dell Indonesia');
    }

    /**
     * Test Dashboard displays current cash balance which decreases on outcome.
     */
    public function test_dashboard_displays_current_cash_balance_and_updates_with_income_and_outcome(): void
    {
        // 1. Initial State: income 15.000.000
        CashIncome::create([
            'transaction_number' => 'INC-DASH-1',
            'transaction_date'   => '2026-10-06',
            'sender_name'        => 'Klien Alfa',
            'bank_name'          => 'BCA',
            'account_number'     => '123456',
            'amount'             => 15000000.00,
            'has_admin_fee'      => false,
            'admin_fee'          => 0,
            'net_amount'         => 15000000.00,
            'proof_file'         => 'incomes/test.jpg',
            'notes'              => 'Uang Masuk',
            'created_by'         => $this->adminUser->id,
        ]);

        $res1 = $this->actingAs($this->adminUser)->get('/dashboard');
        $res1->assertStatus(200);
        $res1->assertSee('Total Saldo Saat Ini');
        $res1->assertSee('Rp 15.000.000');

        // 2. Add Outcome: 4.000.000 + 5.000 admin fee = 4.005.000
        CashOutcome::create([
            'transaction_number' => 'OUT-DASH-1',
            'transaction_date'   => '2026-10-06',
            'recipient_name'     => 'Vendor Fiber',
            'bank_name'          => 'Mandiri',
            'account_number'     => '654321',
            'amount'             => 4000000.00,
            'has_admin_fee'      => true,
            'admin_fee'          => 5000.00,
            'total_amount'       => 4005000.00,
            'proof_file'         => 'outcomes/test.jpg',
            'notes'              => 'Pengeluaran Fiber',
            'created_by'         => $this->adminUser->id,
        ]);

        // 3. Current balance should automatically decrease: 15.000.000 - 4.005.000 = 10.995.000
        $res2 = $this->actingAs($this->adminUser)->get('/dashboard');
        $res2->assertStatus(200);
        $res2->assertSee('Total Saldo Saat Ini');
        $res2->assertSee('Rp 10.995.000');

        // 4. Verify Total Asset is displayed without deduction
        \App\Models\Asset::create([
            'name'          => 'Server Utama',
            'type'          => 'Hardware',
            'price'         => 35000000.00,
            'owner_type'    => 'pt',
            'owner_name'    => 'PT CIO NETWORK SOLUTION',
            'created_by'    => $this->adminUser->id,
        ]);

        $res3 = $this->actingAs($this->adminUser)->get('/dashboard');
        $res3->assertStatus(200);
        $res3->assertSee('Total Asset');
        $res3->assertSee('Rp 35.000.000');
        $res3->assertSee('Grafik Tren Keuangan &amp; Aset Perusahaan', false);
        $res3->assertSee('ftc-kpi-income', false);
        $res3->assertSee('ftc-kpi-outcome', false);
        $res3->assertSee('ftc-kpi-asset', false);

        // 5. Test AJAX Chart Data endpoint with 3 lines (green income, red outcome, blue asset)
        $chartRes = $this->actingAs($this->adminUser)->getJson('/dashboard/chart-data?range=30d');
        $chartRes->assertStatus(200);
        $chartRes->assertJsonStructure([
            'range',
            'categories',
            'series' => [
                '*' => ['name', 'color', 'data']
            ],
            'summary' => ['total_income', 'total_outcome', 'total_asset']
        ]);

        $series = $chartRes->json('series');
        $this->assertCount(3, $series);
        $this->assertEquals('Pemasukan', $series[0]['name']);
        $this->assertEquals('#10b981', $series[0]['color']);
        $this->assertEquals('Pengeluaran', $series[1]['name']);
        $this->assertEquals('#ef4444', $series[1]['color']);
        $this->assertEquals('Asset', $series[2]['name']);
        $this->assertEquals('#206bc4', $series[2]['color']);
    }
}
