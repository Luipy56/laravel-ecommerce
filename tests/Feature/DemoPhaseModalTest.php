<?php

namespace Tests\Feature;

use Tests\TestCase;

class DemoPhaseModalTest extends TestCase
{
    public function test_spa_shell_does_not_inject_is_demo_when_disabled(): void
    {
        config(['app.is_demo' => false]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('window.__LARAVEL_IS_DEMO__', $html);
    }

    public function test_spa_shell_injects_is_demo_when_config_enabled(): void
    {
        config(['app.is_demo' => true]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('window.__LARAVEL_IS_DEMO__ = true', $html);
    }

    public function test_admin_routes_do_not_inject_is_demo_when_disabled(): void
    {
        config(['app.is_demo' => false]);

        $html = $this->get('/admin/login')->assertOk()->getContent();
        $this->assertStringNotContainsString('window.__LARAVEL_IS_DEMO__', $html);
    }
}
