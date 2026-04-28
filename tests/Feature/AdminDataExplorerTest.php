<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDataExplorerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_data_explorer_schema(): void
    {
        $this->getJson('/api/v1/admin/data-explorer/schema')->assertStatus(401);
    }

    public function test_authenticated_admin_gets_schema_and_queries_orders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $schema = $this->getJson('/api/v1/admin/data-explorer/schema')->assertOk()
            ->assertJsonPath('success', true);

        $tables = $schema->json('data.tables');
        $this->assertNotEmpty($tables);

        $query = $this->postJson('/api/v1/admin/data-explorer/query', [
            'table' => 'orders',
            'page' => 1,
            'per_page' => 15,
            'sort_direction' => 'desc',
            'sort_column' => 'id',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertGreaterThanOrEqual(1, count($query->json('data')));
        $this->assertNotNull($query->json('meta.total'));
    }

    public function test_aggregate_counts_orders_by_kind(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $resp = $this->postJson('/api/v1/admin/data-explorer/aggregate', [
            'table' => 'orders',
            'metric' => 'count',
            'group_by' => 'kind',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $rows = $resp->json('data');
        $this->assertNotEmpty($rows);
    }
}
