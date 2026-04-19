<?php

namespace Tests\Feature;

use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Simulates a typical admin (non-superuser account from seed: manager) using the API after login.
 */
class AdminUserJourneyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Runs before test_admin_login_rejects_wrong_password: a failed login attempt on the same
     * PHPUnit instance leaves HTTP client state that breaks session + JSON GETs for the admin API.
     */
    public function test_0_manager_can_use_main_admin_get_endpoints_when_authenticated(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
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
            '/api/v1/admin/data-explorer/schema',
        ];

        foreach ($endpoints as $uri) {
            $response = $this->getJson($uri);
            $response->assertStatus(
                200,
                "GET {$uri} returned {$response->status()}: ".$response->getContent()
            );
            $response->assertJsonPath('success', true);
        }
    }

    public function test_admin_login_rejects_wrong_password(): void
    {
        $this->seed(AdminSeeder::class);

        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }
}
