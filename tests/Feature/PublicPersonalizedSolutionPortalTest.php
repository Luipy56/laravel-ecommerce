<?php

namespace Tests\Feature;

use App\Mail\PersonalizedSolutionImprovementRequestedAdminMail;
use App\Mail\PersonalizedSolutionResolvedMail;
use App\Models\PersonalizedSolution;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicPersonalizedSolutionPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_store_invalid_postal_validation_message_respects_accept_language(): void
    {
        $response = $this->postJson(
            '/api/v1/personalized-solutions',
            [
                'email' => 'local_'.uniqid('', true).'@ietf.org',
                'phone' => null,
                'problem_description' => 'Test',
                'address_street' => null,
                'address_city' => null,
                'address_province' => null,
                'address_postal_code' => 'X1',
                'address_note' => null,
            ],
            ['Accept-Language' => 'ca'],
        );

        $response->assertUnprocessable();
        $response->assertJsonPath(
            'message',
            'El codi postal ha de tenir només xifres (fins a 20).',
        );
    }

    public function test_store_returns_public_token_and_public_api_allows_manage(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/personalized-solutions', [
            'email' => 'portal_'.uniqid('', true).'@ietf.org',
            'phone' => null,
            'problem_description' => 'Custom install request',
            'address_street' => null,
            'address_city' => null,
            'address_province' => null,
            'address_postal_code' => '08001',
            'address_note' => null,
        ]);

        $response->assertCreated();
        $token = $response->json('data.public_token');
        $this->assertNotEmpty($token);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);

        $show = $this->getJson('/api/v1/public/personalized-solutions/'.$token);
        $show->assertOk();
        $show->assertJsonPath('success', true);
        $show->assertJsonPath('data.status', PersonalizedSolution::STATUS_PENDING_REVIEW);

        $newEmail = 'updated_'.uniqid('', true).'@ietf.org';
        $patch = $this->patchJson('/api/v1/public/personalized-solutions/'.$token, [
            'email' => $newEmail,
            'phone' => '600000000',
            'address_street' => 'Carrer Test 1',
            'address_city' => 'Barcelona',
            'address_province' => 'Barcelona',
            'address_postal_code' => '08002',
            'address_note' => '',
        ]);
        $patch->assertOk();
        $patch->assertJsonPath('data.email', $newEmail);
    }

    public function test_request_improvements_sends_admin_mail_when_configured(): void
    {
        Mail::fake();
        config(['mail.admin_notification_address' => 'ops@ietf.org']);

        $create = $this->postJson('/api/v1/personalized-solutions', [
            'email' => 'imp_'.uniqid('', true).'@ietf.org',
            'phone' => null,
            'problem_description' => 'Need changes',
            'address_street' => null,
            'address_city' => null,
            'address_province' => null,
            'address_postal_code' => '08001',
            'address_note' => null,
        ]);
        $create->assertCreated();
        $token = $create->json('data.public_token');

        $this->postJson('/api/v1/public/personalized-solutions/'.$token.'/request-improvements', [
            'message' => 'Please adjust the height.',
        ])->assertOk();

        Mail::assertSent(PersonalizedSolutionImprovementRequestedAdminMail::class);
    }

    public function test_admin_sets_completed_sends_resolved_mail(): void
    {
        Mail::fake();
        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $solution = PersonalizedSolution::query()->orderBy('id')->first();
        $this->assertNotNull($solution);

        $this->putJson('/api/v1/admin/personalized-solutions/'.$solution->id, [
            'email' => $solution->email ?? 'upd@ietf.org',
            'phone' => $solution->phone,
            'address_street' => $solution->address_street,
            'address_city' => $solution->address_city,
            'address_province' => $solution->address_province,
            'address_postal_code' => $solution->address_postal_code ?: '08001',
            'address_note' => $solution->address_note,
            'problem_description' => $solution->problem_description,
            'resolution' => $solution->resolution ?? 'Done.',
            'status' => PersonalizedSolution::STATUS_COMPLETED,
            'client_id' => $solution->client_id,
            'order_id' => $solution->order_id,
            'is_active' => true,
        ], ['Accept-Language' => 'ca'])->assertOk();

        Mail::assertSent(PersonalizedSolutionResolvedMail::class);
    }
}
