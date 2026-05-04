<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAboutChangelogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_changelog_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/changelog')->assertStatus(401);
    }

    public function test_admin_changelog_returns_markdown_when_logged_in(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $response = $this->getJson('/api/v1/admin/changelog');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'markdown',
                ],
            ]);

        $md = $response->json('data.markdown');
        $this->assertIsString($md);
        $this->assertStringContainsString('Changelog', $md);
    }
}
