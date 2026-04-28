<?php

namespace Tests\Feature;

use App\Models\Client;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_receives_401_for_reports_summary(): void
    {
        $this->getJson('/api/v1/reports/summary')->assertUnauthorized();
    }

    public function test_admin_receives_shop_scoped_summary(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->getJson('/api/v1/reports/summary')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('role', 'admin')
            ->assertJsonPath('data.scope', 'shop')
            ->assertJsonStructure([
                'data' => [
                    'sales_by_period',
                    'top_products',
                    'low_stock',
                    'summary',
                ],
            ]);
    }

    public function test_verified_client_receives_client_scoped_summary(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'reports.client@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($client, 'web');

        $this->getJson('/api/v1/reports/summary')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('role', 'client')
            ->assertJsonPath('data.scope', 'client')
            ->assertJsonPath('data.low_stock', []);
    }

    public function test_unverified_client_receives_403(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'unverified.reports@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        $this->actingAs($client, 'web');

        $this->getJson('/api/v1/reports/summary')
            ->assertStatus(403)
            ->assertJsonPath('code', 'email_not_verified');
    }
}
