<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PersonalizedSolution;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_read_nav_alerts(): void
    {
        $this->getJson('/api/v1/admin/nav-alerts')->assertStatus(401);
    }

    public function test_nav_alerts_true_when_demo_data_has_pending_work(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->getJson('/api/v1/admin/nav-alerts')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.orders_need_attention', true)
            ->assertJsonPath('data.personalized_solutions_need_attention', true);
    }

    public function test_nav_alerts_false_when_no_pending_orders_or_solutions(): void
    {
        $this->seed(DatabaseSeeder::class);

        Order::query()->where('kind', Order::KIND_ORDER)->update([
            'status' => Order::STATUS_SENT,
            'installation_requested' => false,
            'installation_status' => null,
        ]);

        PersonalizedSolution::query()->update([
            'status' => PersonalizedSolution::STATUS_COMPLETED,
            'improvement_feedback' => null,
            'improvement_feedback_at' => null,
        ]);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->getJson('/api/v1/admin/nav-alerts')
            ->assertOk()
            ->assertJsonPath('data.orders_need_attention', false)
            ->assertJsonPath('data.personalized_solutions_need_attention', false);
    }
}
