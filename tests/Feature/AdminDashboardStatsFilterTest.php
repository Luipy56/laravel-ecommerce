<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardStatsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_by_period_with_month_returns_single_row(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $y = (int) date('Y');
        $response = $this->getJson('/api/v1/admin/stats/sales-by-period?month=6');
        $response->assertOk()->assertJsonPath('success', true);
        $response->assertJsonPath('current_year', $y);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('06', $response->json('data.0.month'));
    }

    public function test_sales_by_period_with_year_uses_that_anchor(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $response = $this->getJson('/api/v1/admin/stats/sales-by-period?year=2020');
        $response->assertOk()->assertJsonPath('success', true);
        $response->assertJsonPath('current_year', 2020);
        $response->assertJsonPath('previous_year', 2019);
        $this->assertCount(12, $response->json('data'));
    }

    public function test_top_products_accepts_year_without_changing_response_shape(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->getJson('/api/v1/admin/stats/top-products?year=2020')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
