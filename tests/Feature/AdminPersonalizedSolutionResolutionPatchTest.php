<?php

namespace Tests\Feature;

use App\Models\PersonalizedSolution;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPersonalizedSolutionResolutionPatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_patch_resolution(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->patchJson('/api/v1/admin/personalized-solutions/1/resolution', [
            'resolution' => 'Hello',
        ])->assertStatus(401);
    }

    public function test_admin_can_patch_resolution_and_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->patchJson('/api/v1/admin/personalized-solutions/1/resolution', [
            'resolution' => 'Proposed fix: custom hinge set.',
            'status' => PersonalizedSolution::STATUS_REVIEWED,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.resolution', 'Proposed fix: custom hinge set.')
            ->assertJsonPath('data.status', PersonalizedSolution::STATUS_REVIEWED);

        // `resolution` is encrypted in the DB; query via the model so the cast decrypts it.
        $solution = PersonalizedSolution::findOrFail(1);
        $this->assertSame('Proposed fix: custom hinge set.', $solution->resolution);
        $this->assertSame(PersonalizedSolution::STATUS_REVIEWED, $solution->status);
    }

    public function test_patch_without_resolution_or_status_returns_422(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->patchJson('/api/v1/admin/personalized-solutions/1/resolution', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
