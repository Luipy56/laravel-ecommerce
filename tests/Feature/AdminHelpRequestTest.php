<?php

namespace Tests\Feature;

use App\Jobs\ProcessAdminHelpIssueJob;
use App\Services\AdminHelpIssueRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminHelpRequestTest extends TestCase
{
    use RefreshDatabase;

    private function loginAsAdmin(): void
    {
        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();
    }

    public function test_help_request_requires_authentication(): void
    {
        $this->postJson('/api/v1/admin/help-requests', [
            'comment' => 'Need a new export button on orders.',
        ])->assertStatus(401);
    }

    public function test_help_request_validates_comment(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->loginAsAdmin();

        $this->postJson('/api/v1/admin/help-requests', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);

        $this->postJson('/api/v1/admin/help-requests', [
            'comment' => str_repeat('x', 4001),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_help_request_stores_pending_json_when_logged_in(): void
    {
        Queue::fake();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->loginAsAdmin();

        $response = $this->postJson('/api/v1/admin/help-requests', [
            'title' => 'Orders export',
            'comment' => 'Add CSV export on the admin orders list.',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        Queue::assertPushed(ProcessAdminHelpIssueJob::class);

        $service = app(AdminHelpIssueRequestService::class);
        $ids = $service->listPendingIds();
        $this->assertCount(1, $ids);

        $payload = $service->readPayload('pending', $ids[0]);
        $this->assertIsArray($payload);
        $this->assertSame('pending', $payload['status']);
        $this->assertSame('Add CSV export on the admin orders list.', $payload['comment']);
        $this->assertSame('Orders export', $payload['title']);
        $this->assertSame('admin_help', $payload['meta']['source']);
        $this->assertSame('manager', $payload['submittedBy']['username']);
        $this->assertSame('waiting for human validation', $payload['label']);

        File::deleteDirectory($service->storageRoot());
    }

    public function test_help_request_ignores_to_staging_label_and_uses_human_validation(): void
    {
        Queue::fake();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->loginAsAdmin();

        $this->postJson('/api/v1/admin/help-requests', [
            'comment' => 'Attempt to bypass validation with to-staging label.',
            'label' => 'to-staging',
        ])->assertOk();

        $service = app(AdminHelpIssueRequestService::class);
        $ids = $service->listPendingIds();
        $payload = $service->readPayload('pending', $ids[0]);
        $this->assertSame('waiting for human validation', $payload['label']);

        File::deleteDirectory($service->storageRoot());
    }

    public function test_help_request_falls_back_to_human_validation_for_invalid_label(): void
    {
        Queue::fake();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->loginAsAdmin();

        $this->postJson('/api/v1/admin/help-requests', [
            'comment' => 'Unknown label should not break intake.',
            'label' => 'not-a-real-label',
        ])->assertOk();

        $service = app(AdminHelpIssueRequestService::class);
        $ids = $service->listPendingIds();
        $payload = $service->readPayload('pending', $ids[0]);
        $this->assertSame('waiting for human validation', $payload['label']);

        File::deleteDirectory($service->storageRoot());
    }

    public function test_help_request_service_moves_invalid_payload_to_failed(): void
    {
        $service = app(AdminHelpIssueRequestService::class);
        $service->ensureDirectories();

        $id = 'test-invalid-payload';
        $service->storePending([
            'id' => $id,
            'receivedAt' => now()->utc()->toIso8601String(),
            'submittedBy' => ['id' => 1, 'username' => 'manager'],
            'title' => null,
            'comment' => '',
            'meta' => ['source' => 'admin_help'],
            'status' => 'pending',
        ]);

        $this->assertTrue($service->claim($id));
        $validated = $service->validatePayload($service->readPayload('processing', $id) ?? []);
        $this->assertNull($validated);
        $this->assertTrue($service->moveToFailed($id, 'invalid_payload'));
        $this->assertNotNull($service->readPayload('failed', $id));

        File::deleteDirectory($service->storageRoot());
    }
}
