<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChatTest extends TestCase
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

    public function test_guest_cannot_chat(): void
    {
        $this->postJson('/api/v1/admin/chat', [
            'message' => 'hola',
            'provider' => 'heuristic',
        ])->assertStatus(401);
    }

    public function test_admin_identity_reply_as_sierra(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->loginAsAdmin();

        $resp = $this->postJson('/api/v1/admin/chat', [
            'message' => 'Hola, cómo te llamas y cuál es tu función?',
            'provider' => 'heuristic',
        ])->assertOk()->assertJsonPath('success', true);

        $reply = (string) $resp->json('data.reply');
        $this->assertStringContainsString('Sierra', $reply);
        $this->assertSame([], $resp->json('data.tools'));
        $this->assertStringNotContainsString('products total=', $reply);
    }

    public function test_admin_can_search_catalog_via_heuristic(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->loginAsAdmin();

        $resp = $this->postJson('/api/v1/admin/chat', [
            'message' => 'busca producto evo',
            'provider' => 'heuristic',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertNotEmpty($resp->json('data.reply'));
        $this->assertContains('catalog_search', $resp->json('data.tools'));
        $this->assertStringNotContainsString('products total=', (string) $resp->json('data.reply'));
    }

    public function test_admin_write_request_is_refused(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->loginAsAdmin();

        $resp = $this->postJson('/api/v1/admin/chat', [
            'message' => 'crea un producto nuevo subiendo un png',
            'provider' => 'heuristic',
        ])->assertOk();

        $this->assertStringContainsString('solo puedo leer', $resp->json('data.reply'));
        $this->assertSame([], $resp->json('data.tools'));
    }
}
