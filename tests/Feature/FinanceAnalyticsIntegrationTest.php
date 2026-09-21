<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ShareholderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceAnalyticsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([VerifyCsrfToken::class]);

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ShareholderSeeder::class);

        $this->adminUser = User::where('email', 'admin@cionetwork.id')->first();
    }

    /**
     * Test Dashboard page renders with analytics, chart, and equity summary.
     */
    public function test_dashboard_renders_with_finance_analytics(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Total Saldo Konsolidasi');
        $response->assertSee('Grafik Realtime Finansial');
        $response->assertSee('Struktur Kepemilikan Saham');
        $response->assertSee('Yoga Pratama');
    }

    /**
     * Test Internal AJAX Endpoint: Overview
     */
    public function test_api_finance_overview_returns_valid_json(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson('/api/finance/overview?client_code=ALL');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'success',
            'data' => [
                'balance' => ['total', 'manual', 'xendit'],
                'current_month' => ['revenue', 'expenses', 'net_profit', 'profit_margin'],
                'equity_summary',
            ],
        ]);
    }

    /**
     * Test Internal AJAX Endpoint: Chart Data
     */
    public function test_api_finance_chart_returns_valid_time_series(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson('/api/finance/chart?range=7d&interval=daily&client_code=ALL');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'success',
            'data' => [
                'categories',
                'series',
                'summary' => ['total_inflow', 'total_outflow', 'net_profit'],
            ],
        ]);
    }

    /**
     * Test Internal AJAX Endpoint: Chart Data with 1d hourly range
     */
    public function test_api_finance_chart_supports_1d_hourly_range(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson('/api/finance/chart?range=1d&interval=hourly&client_code=ALL');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'success',
            'data' => [
                'categories',
                'series',
                'summary' => ['total_inflow', 'total_outflow', 'net_profit'],
            ],
        ]);
    }

    /**
     * Test Internal AJAX Endpoint: Growth Data
     */
    public function test_api_finance_growth_returns_valid_metrics(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson('/api/finance/growth?client_code=ALL');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'success',
            'data' => [
                'financial_health_score',
                'status',
                'profitability' => ['gross_margin', 'net_margin', 'runway_months'],
            ],
        ]);
    }

    /**
     * Test Internal AJAX Endpoint: History Feed Data
     */
    public function test_api_finance_history_returns_paginated_items(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson('/api/finance/history?page=1&per_page=15');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'success',
            'data' => [
                'items',
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ],
        ]);
    }

    /**
     * Test Internal AJAX Endpoint: Manual Sync Trigger
     */
    public function test_api_finance_sync_triggers_log_synchronization(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/finance/sync');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'success',
            'message',
            'data' => ['synced_count'],
        ]);
    }
}
