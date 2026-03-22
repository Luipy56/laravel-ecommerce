<?php

namespace Tests\Feature;

use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

/**
 * Simulates a typical admin (non-superuser account from seed: manager) using the API after login.
 */
#[RequiresPhpExtension('pdo_sqlite')]
class AdminUserJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_rejects_wrong_password(): void
    {
        $this->seed(AdminSeeder::class);

        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    public function test_common_admin_can_login_and_use_main_endpoints(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.username', 'manager');

        $endpoints = [
            '/api/v1/admin/products',
            '/api/v1/admin/products/1',
            '/api/v1/admin/categories',
            '/api/v1/admin/categories/1',
            '/api/v1/admin/packs',
            '/api/v1/admin/packs/1',
            '/api/v1/admin/orders',
            '/api/v1/admin/orders/1',
            '/api/v1/admin/clients',
            '/api/v1/admin/clients/1',
            '/api/v1/admin/features',
            '/api/v1/admin/features/1',
            '/api/v1/admin/feature-names',
            '/api/v1/admin/feature-names/1',
            '/api/v1/admin/variant-groups',
            '/api/v1/admin/variant-groups/1',
            '/api/v1/admin/admins',
            '/api/v1/admin/admins/1',
            '/api/v1/admin/personalized-solutions',
            '/api/v1/admin/personalized-solutions/1',
            '/api/v1/admin/stats/postal-codes',
            '/api/v1/admin/stats/sales-by-period',
            '/api/v1/admin/stats/top-products',
            '/api/v1/admin/stats/low-stock',
        ];

        foreach ($endpoints as $uri) {
            $this->getJson($uri)
                ->assertOk()
                ->assertJsonPath('success', true);
        }
    }
}
